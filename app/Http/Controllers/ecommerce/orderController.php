<?php

namespace App\Http\Controllers\ecommerce;

use App\Http\Controllers\Controller;
use App\Models\OrderPaymentDetail;
use App\Models\ProductImeiNumber;
use App\Models\BillingAddress;
use App\Traits\ResponseHelper;
use App\Models\StockVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\OrderDetail;
use App\Models\BillSetup;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Credit;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;



class orderController extends Controller
{
    public function store(Request $request,$company)
    {

        DB::beginTransaction();

        $user = User::where('slug_name',$company)->first();
        if($user->role_id = 2)
        {
            $billSetup = BillSetup::where([['shop_id', $user->owner_id],['branch_id', null],['is_active',1]])->first();
        }
        if($user->role_id = 3)
        {
            $billSetup = BillSetup::where([['branch_id', $user->id], ['is_active', 1]])->first();
        }


        if (!$billSetup) {

            return $this->errorResponse("No active bill setup found.",400,"Failed to place Order");
        }

        // Active bill prefix
        $billPrefix = $billSetup->bill_number;

        if($user->role_id = 2)
        {
            // Get last order with this branch
            $lastOrder = Order::where([['shop_id', $user->owner_id],['branch_id', null]])->orderBy('id', 'desc')->first();
        }
        if($user->role_id = 3)
        {
            $lastOrder = Order::where('branch_id', $user->id)->orderBy('id', 'desc')->first();
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

        if($user->role_id = 2)
        {
            $auth = UserDetail::where('user_id',$user->owner_id)->first();
        }
        if($user->role_id = 3)
        {
            $auth = UserDetail::where('user_id',$user->id)->first();
        }

        $billAmount = $auth->able_to_round_price == 1 ? round($billAmount) : $billAmount;

        $order = Order::create([
            'shop_id'                   => $user->role_id = 2 ? $user->owner_id : $user->id,
            'branch_id'                 => $user->role_id = 2 ? null : $user->id,
            'bill_id'                   => $newBillNo,
            'billed_by'                 => $request->billed_by,
            'customer_id'               => $request->customer,
            'order_discount'            => $request->discount,
            'total_product_discount'    => $totalProductDiscount,
            'bill_amount'               => $billAmount,
            'billed_on'                 => Carbon::now(),
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
            ]);

            $stock = Stock::where([['shop_id',$user->id],['branch_id',null],['product_id',$item['product_id']]])->first();

            // Reduce variation stock FIRST
            if ($variation) {
                $variation->quantity -= $item['qty'];
                $variation->save();
            }
            else
            {
                $variation = StockVariation::where('stock_id',$stock->id)->first();
                $variation->quantity -= $item['qty'];
                $variation->save();
            }

            // Reduce Quantity
            $stock->quantity = $stock->quantity - $item['qty'];

            // Remove sold IMEI numbers from stock
            if (!empty($item['imeis'])) {

                // Convert comma-separated IMEI string to array
                $existingImeis = !empty($stock->imei) ? explode(',', $stock->imei) : [];

                // Remove sold IMEIs
                $remainingImeis = array_values(array_diff($existingImeis, $item['imeis']));

                // Convert back to comma-separated string
                $stock->imei = implode(',', $remainingImeis);
            }


            $stock->save();

            if (!empty($item['imeis'])) {
                ProductImeiNumber::where('product_id', $item['product_id'])
                    ->whereIn('name', $item['imeis'])
                    ->update(['is_sold' => 1]);
            }


            //$stock->update(['quantity' => $stock->quantity - $item['qty'] ]);

        }

        $payments = $request->input('payments', []);
        foreach ($payments as $payment) {

            $payment_id = Payment::where('name',$payment['method'])->first()->id;
            $extra = $payment['extra'] ?? [];

            $order_payment = OrderPaymentDetail::create([
                'order_id'   => $order->id,
                'payment_id' => $payment_id,
                'amount'     => $payment['amount'],
                'number'     => $extra['cheque_number'] ?? $extra['upi_id'] ?? $extra['card_number'] ?? $extra['finance_card'] ?? null,
                'card'       => $extra['card_name'] ?? null,
                'finance_id' => $extra['finance_type'] ?? null,
            ]);

            if($payment_id == 6)
            {
                Credit::create([
                    'order_payment_detail_id' => $order_payment->id,
                    'amount'     => $payment['amount'],
                    'remaining_amount'     => $payment['amount'],
                ]);
            }
        }

        

        //Log
        $this->addToLog($this->unique(),Auth::id(),'Order','App/Models/Order','orders',$order->id,'Insert',null,null,'Success','Order Created Successfully');

        //Notifiction
        $this->notification(Auth::user()->id, null,'App/Models/Order', $order->id, null, json_encode($request->all()), now(), Auth::user()->id, 'HO ' . $user->name . ' placed an order for customer ' . $customer->name . ' with an amount of ' . $billAmount. '.',null, null,14);

        DB::commit();
        
        return response()->json([
            'status'   => 'success',
            'message'  => 'Order saved successfully',
            'order_id' => $order->id
        ]);

    }
}
