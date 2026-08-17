<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderPaymentDetail;
use App\Models\ProductImeiNumber;
use App\Models\BillingAddress;
use App\Traits\ResponseHelper;
use App\Models\StockVariation;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OrderDetail;
use App\Models\UserDetail;
use App\Models\BillSetup;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Credit;
use App\Traits\common;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;


class orderController extends Controller
{
    use Log,Notifications,ResponseHelper,common;

    public function store(Request $request,$company)
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [
            'customer' => [
                'nullable',
                'integer',
                'exists:customers,id',
            ],

            // 'billed_by' => [
            //     'nullable',
            //     'integer',
            //     'exists:users,id',
            // ],

            'discount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'cart' => [
                'required',
                'array',
                'min:1',
            ],

            'cart.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'cart.*.qty' => [
                'required',
                'numeric',
                'min:1',
            ],

            'cart.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'cart.*.tax_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'cart.*.variation_id' => [
                'nullable',
                'integer',
                'exists:stock_variations,id',
            ],

            'cart.*.imeis' => [
                'nullable',
                'array',
            ],

            'cart.*.imeis.*' => [
                'nullable',
                'string',
            ],

            'billing_customer' => [
                'nullable',
                'array',
            ],

            'billing_customer.billing_phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'billing_customer.billing_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'billing_customer.billing_address' => [
                'nullable',
                'string',
            ],

            'billing_customer.billing_pincode' => [
                'nullable',
                'string',
                'max:10',
            ],

            // 'payments' => [
            //     'required',
            //     'array',
            //     'min:1',
            // ],

            // 'payments.*.method' => [
            //     'required',
            //     'string',
            // ],

            // 'payments.*.amount' => [
            //     'required',
            //     'numeric',
            //     'min:0.01',
            // ],

            // 'payments.*.extra' => [
            //     'nullable',
            //     'array',
            // ],
        ]);

        if ($validator->fails()) {
            DB::rollBack();

            return $this->errorResponse(
                $validator->errors(),
                422,
                'Validation failed'
            );
        }

        DB::beginTransaction();

        $user = User::where('slug_name',$company)->first();
        //return $user;
        if($user->role_id == 2)
        {
            $billSetup = BillSetup::where([['shop_id', $user->owner_id],['branch_id',null],['is_active',1]])->first();
        }
        if($user->role_id == 3)
        {
            $billSetup = BillSetup::where([['shop_id', $user->parent_id],['branch_id', $user->id], ['is_active', 1]])->first();
        }

        if (!$billSetup) {

            return $this->errorResponse("No active bill setup found.",400,"Failed to place Order");
        }

        // Active bill prefix
        $billPrefix = $billSetup->bill_number;

        if($user->role_id == 2)
        {
            // Get last order with this branch
            $lastOrder = Order::where([['shop_id', $user->owner_id],['branch_id', null]])->orderBy('id', 'desc')->first();
        }
        if($user->role_id == 3)
        {
            $lastOrder = Order::where([['shop_id', $user->parent_id],['branch_id', $user->id]])->orderBy('id', 'desc')->first();
        }


        $newBillNo = $billPrefix . '01'; // default start if no orders

        if ($lastOrder && $lastOrder->bill_id) {
            $lastBillNo = $lastOrder->bill_id;

            if (Str::startsWith($lastBillNo, $billPrefix)) {
                // continue sequence
                $lastNumber = (int) Str::replaceFirst($billPrefix, '', $lastBillNo);
                $newBillNo = $billPrefix . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
            } else {
                // reset sequence for new prefix
                $newBillNo = $billPrefix . '01';
            }
        }


        $cart = $request->input('cart', []);
        $billAmount = collect($cart)->sum(function ($item) {
            return ($item['qty'] * $item['price']);
        });

        // ✅ Calculate total product discount
        $totalProductDiscount = collect($cart)->sum(function ($item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                return 0;
            }

            // Calculate per-item discount
            if ($product->discount_type == 1) {
                // Flat discount per unit
                $discount = $product->discount * $item['qty'];
            } elseif ($product->discount_type == 2) {
                // Percentage discount
                $discount = (($product->discount / 100) * $item['price']) * $item['qty'];
            } else {
                $discount = 0;
            }

            return $discount;
        });

        if($user->role_id == 2)
        {
            $auth = UserDetail::where('user_id',$user->id)->first();
        }
        if($user->role_id == 3)
        {
            $auth = UserDetail::where('user_id',$user->id)->first();
        }

        $billAmount = $auth->able_to_round_price == 1 ? round($billAmount) : $billAmount;

        $order = Order::create([
            'shop_id'                   => $user->role_id == 2 ? $user->owner_id : $user->parent_id,
            'branch_id'                 => $user->role_id == 2 ? null : $user->id,
            'bill_id'                   => $newBillNo,
            //'billed_by'                 => $request->billed_by,
            'customer_id'               => $request->customer,
            'order_discount'            => $request->discount,
            'total_product_discount'    => $totalProductDiscount,
            'bill_amount'               => $billAmount,
            'billed_on'                 => Carbon::now(),
            'is_online_order'           => 1,
            'is_paid'                   => $request->is_paid,
            'status'                    => 0,
        ]);

        $billingData = $request->input('billing_customer');
        if($billingData['billing_phone'] != null)
        {
            $billing_customer = BillingAddress::create(
                [
                    'user_id'  => $user->id,
                    'order_id' => $order->id,
                    'phone'    => $billingData['billing_phone'] ?? null,
                    'name'     => $billingData['billing_name'],
                    'address'   => $billingData['billing_address'],
                    'pincode'   => $billingData['billing_pincode'] ?? null,
                ]
            );
        }


        foreach ($cart as $item) {

            $product = Product::where('id',$item['product_id'])->first();
            $variation = !empty($item['variation_id'])? StockVariation::find($item['variation_id']) : null;
            OrderDetail::create([
                'order_id'      => $order->id,
                'product_id'    => $item['product_id'],
                'name'          => $product->name,
                'quantity'      => $item['qty'],
                'price'         => $item['price'],
                'selling_price' => $item['price'],
                'tax_amount'    => $item['tax_amount'],
                'tax_percent'   => $product->tax->name,
                'discount_type' => $product->discount_type,
                'discount'      => $product->discount,
                'imei'          => isset($item['imeis']) ? implode(',', $item['imeis']) : null,
                // Variation support
                'size_id'       => $variation?->size_id,
                'colour_id'     => $variation?->colour_id,
                'status'        => 0,
            ]);

            //return $user;

            // if($user->role_id == 2)
            // {

            //     $stock = Stock::where([['shop_id',$user->owner_id],['branch_id',null],['product_id',$item['product_id']]])->first();
            // }
            // if($user->role_id == 3)
            // {
            //     $stock = Stock::where([['shop_id',$user->parent_id],['branch_id',$user->id],['product_id',$item['product_id']]])->first();
            // }

            // //return $stock;

            // // Reduce variation stock FIRST
            // if ($variation) {
            //     $variation->quantity -= $item['qty'];
            //     $variation->save();
            // }
            // else
            // {
            //     $variation = StockVariation::where('stock_id',$stock->id)->first();
            //     $variation->quantity -= $item['qty'];
            //     $variation->save();
            // }

            // // Reduce Quantity
            // $stock->quantity = $stock->quantity - $item['qty'];

            // // Remove sold IMEI numbers from stock
            // if (!empty($item['imeis'])) {

            //     // Convert comma-separated IMEI string to array
            //     $existingImeis = !empty($stock->imei) ? explode(',', $stock->imei) : [];

            //     // Remove sold IMEIs
            //     $remainingImeis = array_values(array_diff($existingImeis, $item['imeis']));

            //     // Convert back to comma-separated string
            //     $stock->imei = implode(',', $remainingImeis);
            // }


            // $stock->save();

            // if (!empty($item['imeis'])) {
            //     ProductImeiNumber::where('product_id', $item['product_id'])
            //         ->whereIn('name', $item['imeis'])
            //         ->update(['is_sold' => 1]);
            // }


            //$stock->update(['quantity' => $stock->quantity - $item['qty'] ]);

        }

        // $payments = $request->input('payments', []);
        // foreach ($payments as $payment) {

        //     $payment_id = Payment::where('name',$payment['method'])->first()->id;
        //     $extra = $payment['extra'] ?? [];

        //     $order_payment = OrderPaymentDetail::create([
        //         'order_id'   => $order->id,
        //         'payment_id' => $payment_id,
        //         'amount'     => $payment['amount'],
        //         'number'     => $extra['cheque_number'] ?? $extra['upi_id'] ?? $extra['card_number'] ?? $extra['finance_card'] ?? null,
        //         'card'       => $extra['card_name'] ?? null,
        //         'finance_id' => $extra['finance_type'] ?? null,
        //     ]);

        //     if($payment_id == 6)
        //     {
        //         Credit::create([
        //             'order_payment_detail_id' => $order_payment->id,
        //             'amount'     => $payment['amount'],
        //             'remaining_amount'     => $payment['amount'],
        //         ]);
        //     }
        // }

        

        //Log
        $this->addToLog($this->unique(),Auth::id(),'Order','App/Models/Order','orders',$order->id,'Insert',null,null,'Success','Order Created Successfully');

        if($user->role_id == 2)
        {
            //Notifiction
            $this->notification($user->owner_id, null,'App/Models/Order', $order->id, null, json_encode($request->all()), now(), Auth::user()->id, Auth::user()->name.' placed order in shop '. $user->name . ' with an amount of ' . $billAmount. 'via e-commerce site.',null, null,14);
        }
        else
        {
            //Notifiction
            $this->notification($user->parent_id, $user->id,'App/Models/Order', $order->id, null, json_encode($request->all()), now(), Auth::user()->id, Auth::user()->name.' placed order in shop '. $user->name . ' with an amount of ' . $billAmount. 'via e-commerce site.',null, null,14);
        }

        DB::commit();

        return $this->successResponse($order->id, 200, 'Order saved successfully');

    }

    public function list(Request $request,$company)
    {
        $user = User::where('slug_name',$company)->first();
        //return $user;
        if($user->role_id == 2)
        {
            $orders = Order::where([['shop_id',$user->owner_id],['branch_id',null],['customer_id',Auth::user()->customer_id],['is_online_order',1]])
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // search by bill id
                    $q->where('bill_id', 'like', "%{$search}%")
                    // customer name / phone
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('gst', 'like', "%{$search}%");
                    });
                });
            })->orderBy('id','desc')->paginate(10);
        }

        if($user->role_id == 3)
        {
            $orders = Order::where([['shop_id',$user->parent_id],['branch_id',$user->id],['customer_id',Auth::user()->customer_id],['is_online_order',1]])
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // search by bill id
                    $q->where('bill_id', 'like', "%{$search}%")
                    // customer name / phone
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('gst', 'like', "%{$search}%");
                    });
                });
            })->orderBy('id','desc')->paginate(10);
        }

        return $this->successResponse($orders, 200, 'Successfully return all orders.');
    }

    public function view(Request $request,$company,$id)
    {
        $order = Order::with('customer','billingAddress')->where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::with('payment','finance')->where('order_id',$id)->get();

        return $this->successResponse(compact('order', 'order_details', 'order_payment_details'), 200, 'Order retrieved successfully.');
    }
}
