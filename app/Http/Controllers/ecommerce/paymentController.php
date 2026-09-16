<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\ProductImeiNumber;
use App\Models\OrderPaymentDetail;
use App\Models\PendingCheckout;
use App\Models\BillingAddress;
use App\Traits\ResponseHelper;
use App\Models\StockVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OrderDetail;
use App\Models\UserDetail;
use App\Models\BillSetup;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;
use Carbon\Carbon;
use Razorpay\Api\Api;
use Razorpay\Api\Utility;
use DB;

class paymentController extends Controller
{
    use ResponseHelper;

    /**
     * Step 1: customer has a cart ready to pay for. We create a Razorpay
     * Order (just a payment intent, not our own Order yet) and stash the
     * cart in pending_checkouts, keyed by that Razorpay order id. Our real
     * Order is only created once the webhook confirms payment succeeded.
     */
    public function checkout(Request $request, $company)
    {
        $validator = Validator::make($request->all(), [
            'customer' => ['nullable', 'integer', 'exists:customers,id'],
            'discount' => ['nullable', 'numeric', 'min:0'],

            'cart' => ['required', 'array', 'min:1'],
            'cart.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'cart.*.qty' => ['required', 'numeric', 'min:1'],
            'cart.*.price' => ['required', 'numeric', 'min:0'],
            'cart.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'cart.*.variation_id' => ['nullable', 'integer', 'exists:stock_variations,id'],
            'cart.*.imeis' => ['nullable', 'array'],
            'cart.*.imeis.*' => ['nullable', 'string'],

            'billing_customer' => ['nullable', 'array'],
            'billing_customer.billing_phone' => ['nullable', 'string', 'max:20'],
            'billing_customer.billing_name' => ['nullable', 'string', 'max:255'],
            'billing_customer.billing_address' => ['nullable', 'string'],
            'billing_customer.billing_pincode' => ['nullable', 'string', 'max:10'],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 422, 'Validation failed');
        }

        $user = User::where('slug_name', $company)->first();

        $shopId = $user->role_id == 2 ? $user->owner_id : $user->parent_id;
        $branchId = $user->role_id == 2 ? null : $user->id;

        // Gateway keys live on the shop's own UserDetail row — branches share
        // their parent shop's configuration (there is no per-branch gateway).
        $gatewayOwnerId = $user->role_id == 2 ? $user->id : $user->parent_id;
        $userDetail = UserDetail::where('user_id', $gatewayOwnerId)->first();

        if (!$userDetail || !$userDetail->payment_gateway || !$userDetail->payment_gateway_key_id || !$userDetail->payment_gateway_key_secret) {
            return $this->errorResponse('Online payment is not set up for this shop yet.', 400, 'Payment gateway not configured');
        }

        if ($userDetail->payment_gateway !== 'razorpay') {
            return $this->errorResponse('This shop\'s configured gateway is not yet supported for online checkout.', 400, 'Unsupported gateway');
        }

        $cart = $request->input('cart', []);
        $discount = (float) ($request->discount ?? 0);

        $billAmount = collect($cart)->sum(fn ($item) => $item['qty'] * $item['price']);
        $totalTax = collect($cart)->sum(fn ($item) => $item['tax_amount'] ?? 0);
        $payable = $billAmount + $totalTax - $discount;

        if ($payable <= 0) {
            return $this->errorResponse('Order amount must be greater than zero.', 400, 'Invalid amount');
        }

        try {
            $api = new Api($userDetail->payment_gateway_key_id, $userDetail->payment_gateway_key_secret);

            $razorpayOrder = $api->order->create([
                'receipt' => 'chk_' . Str::random(12),
                'amount' => (int) round($payable * 100), // paise
                'currency' => 'INR',
                'payment_capture' => 1,
            ]);
        } catch (\Throwable $e) {
            Log::error('Razorpay checkout-initiate failed: ' . $e->getMessage());

            return $this->errorResponse('Could not start payment. Please try again.', 500, 'Payment gateway error');
        }

        PendingCheckout::create([
            'shop_id' => $shopId,
            'branch_id' => $branchId,
            'customer_id' => $request->customer,
            'ecommerce_user_id' => Auth::id(),
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $payable,
            'discount' => $discount,
            'cart' => $cart,
            'billing_customer' => $request->input('billing_customer'),
            'status' => 'pending',
        ]);

        return $this->successResponse([
            'razorpay_order_id' => $razorpayOrder['id'],
            'razorpay_key_id' => $userDetail->payment_gateway_key_id,
            'amount' => $payable,
            'currency' => 'INR',
        ], 200, 'Checkout initiated. Open Razorpay Checkout with these details to complete payment.');
    }

    /**
     * Step 2: Razorpay calls this once payment succeeds (or fails). Only on
     * a verified success do we build the real Order — this is also where
     * stock finally gets decremented, deliberately, so nothing is reserved
     * for a cart that never gets paid for.
     */
    public function webhook(Request $request, $company)
    {
        $user = User::where('slug_name', $company)->first();

        if (!$user) {
            return response()->json(['status' => 'shop not found'], 404);
        }

        $gatewayOwnerId = $user->role_id == 2 ? $user->id : $user->parent_id;
        $userDetail = UserDetail::where('user_id', $gatewayOwnerId)->first();

        if (!$userDetail || !$userDetail->payment_gateway_webhook_secret) {
            return response()->json(['status' => 'webhook not configured'], 400);
        }

        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');

        try {
            (new Utility())->verifyWebhookSignature($payload, $signature, $userDetail->payment_gateway_webhook_secret);
        } catch (\Throwable $e) {
            Log::warning('Razorpay webhook signature verification failed for ' . $company . ': ' . $e->getMessage());

            return response()->json(['status' => 'invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        $event = $data['event'] ?? null;

        if (!in_array($event, ['payment.captured', 'order.paid'])) {
            // Acknowledge everything else (failed payments, refunds, etc.)
            // so Razorpay doesn't keep retrying delivery of an event we
            // deliberately don't act on yet.
            return response()->json(['status' => 'ignored']);
        }

        $razorpayOrderId = $data['payload']['payment']['entity']['order_id'] ?? null;
        $razorpayPaymentId = $data['payload']['payment']['entity']['id'] ?? null;

        if (!$razorpayOrderId) {
            return response()->json(['status' => 'no order id in payload']);
        }

        $pending = PendingCheckout::where('razorpay_order_id', $razorpayOrderId)->first();

        if (!$pending) {
            return response()->json(['status' => 'unknown checkout']);
        }

        // Idempotency: Razorpay can deliver the same webhook more than once.
        if ($pending->status === 'paid') {
            return response()->json(['status' => 'already processed']);
        }

        DB::beginTransaction();

        try {
            $billSetup = BillSetup::where([
                ['shop_id', $pending->shop_id],
                ['branch_id', $pending->branch_id],
                ['is_active', 1],
            ])->first();

            if (!$billSetup) {
                DB::rollBack();
                Log::error("Razorpay webhook: no active bill setup for shop {$pending->shop_id} branch " . ($pending->branch_id ?? 'HO'));

                return response()->json(['status' => 'no active bill setup']);
            }

            $lastOrder = Order::where([
                ['shop_id', $pending->shop_id],
                ['branch_id', $pending->branch_id],
            ])->orderBy('id', 'desc')->first();

            $billPrefix = $billSetup->bill_number;
            $newBillNo = $billPrefix . '01';

            if ($lastOrder && $lastOrder->bill_id && Str::startsWith($lastOrder->bill_id, $billPrefix)) {
                $lastNumber = (int) Str::replaceFirst($billPrefix, '', $lastOrder->bill_id);
                $newBillNo = $billPrefix . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            }

            $cart = $pending->cart;

            $billAmount = collect($cart)->sum(fn ($item) => $item['qty'] * $item['price']);

            $totalProductDiscount = collect($cart)->sum(function ($item) {
                $product = Product::find($item['product_id']);

                if (!$product) {
                    return 0;
                }

                if ($product->discount_type == 1) {
                    return $product->discount * $item['qty'];
                }

                if ($product->discount_type == 2) {
                    return (($product->discount / 100) * $item['price']) * $item['qty'];
                }

                return 0;
            });

            $order = Order::create([
                'shop_id' => $pending->shop_id,
                'branch_id' => $pending->branch_id,
                'bill_id' => $newBillNo,
                'customer_id' => $pending->customer_id,
                'order_discount' => $pending->discount,
                'total_product_discount' => $totalProductDiscount,
                'bill_amount' => $billAmount,
                'billed_on' => Carbon::now(),
                'is_online_order' => 1,
                'is_paid' => 1,
                // Gateway payment already confirms + "approves" this order —
                // starting at status 0 would put it through the admin's manual
                // approve-and-enter-payment screen, which would try to
                // decrement stock a second time (we already did it below) and
                // fail with "Insufficient stock" once an item sells out.
                'status' => 1,
            ]);

            $billingData = $pending->billing_customer;

            if (!empty($billingData['billing_phone'])) {
                BillingAddress::create([
                    'user_id' => $pending->ecommerce_user_id,
                    'order_id' => $order->id,
                    'phone' => $billingData['billing_phone'] ?? null,
                    'name' => $billingData['billing_name'] ?? null,
                    'address' => $billingData['billing_address'] ?? null,
                    'pincode' => $billingData['billing_pincode'] ?? null,
                ]);
            }

            foreach ($cart as $item) {
                $product = Product::find($item['product_id']);
                $variation = !empty($item['variation_id']) ? StockVariation::find($item['variation_id']) : null;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'name' => $product->name,
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'selling_price' => $item['price'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'tax_percent' => $product->tax->name ?? null,
                    'discount_type' => $product->discount_type,
                    'discount' => $product->discount,
                    'imei' => isset($item['imeis']) ? implode(',', $item['imeis']) : null,
                    'size_id' => $variation?->size_id,
                    'colour_id' => $variation?->colour_id,
                    'status' => 1, // Approved — payment is already confirmed
                ]);

                // Stock is decremented only now — payment is confirmed.
                $stock = Stock::where('product_id', $item['product_id'])
                    ->where('shop_id', $pending->shop_id)
                    ->where('branch_id', $pending->branch_id)
                    ->first();

                if ($stock) {
                    if ($variation) {
                        $variation->quantity = max(0, $variation->quantity - $item['qty']);
                        $variation->save();
                    }

                    if (!empty($item['imeis'])) {
                        $existingImeis = !empty($stock->imei) ? explode(',', $stock->imei) : [];
                        $stock->imei = implode(',', array_values(array_diff($existingImeis, $item['imeis'])));

                        ProductImeiNumber::where('product_id', $item['product_id'])
                            ->whereIn('name', $item['imeis'])
                            ->update(['is_sold' => 1]);
                    }

                    $stock->quantity = max(0, $stock->quantity - $item['qty']);
                    $stock->save();
                } else {
                    Log::warning("Razorpay webhook: no stock row found for product {$item['product_id']} (shop {$pending->shop_id}, branch " . ($pending->branch_id ?? 'HO') . ') — order created without decrementing stock.');
                }
            }

            $razorpayPaymentMethod = Payment::where('name', 'Razorpay')->first();

            OrderPaymentDetail::create([
                'order_id' => $order->id,
                'payment_id' => $razorpayPaymentMethod->id ?? null,
                'amount' => $pending->amount,
                'number' => $razorpayPaymentId,
            ]);

            $pending->update([
                'status' => 'paid',
                'order_id' => $order->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Razorpay webhook order-creation failed for pending_checkout ' . $pending->id . ': ' . $e->getMessage());

            return response()->json(['status' => 'error'], 500);
        }

        return response()->json(['status' => 'ok']);
    }
}
