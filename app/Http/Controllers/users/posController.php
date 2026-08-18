<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\RefundDetail;
use App\Models\OrderDetail;
use App\Models\OrderPaymentDetail;
use App\Models\ProductImeiNumber;
use App\Models\StockVariation;
use App\Models\ShopPayment;
use App\Models\Payment;
use App\Models\Finance;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Refund;
use App\Traits\Log;
use App\Models\Staff;
use App\Models\Stock;
use Carbon\Carbon;
use DB;

class posController extends Controller
{
    use Log, Notifications;

    public function index(Request $request,$company,$branch)
    {

        $branches = User::where([['parent_id',Auth::user()->owner_id],['is_active',1],['is_lock',0],['is_delete',0]])->get();

        if($branch != 0)
        {
            $orders = Order::where([['branch_id',$branch],['shop_id',Auth::user()->owner_id],['is_online_order',0]])
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // search by bill id
                    $q->where('bill_id', 'like', "%{$search}%")
                      // branch name / username
                      ->orWhereHas('branch', function ($q1) use ($search) {
                          $q1->where('name', 'like', "%{$search}%")
                             ->orWhere('user_name', 'like', "%{$search}%");
                      })
                      // customer name / phone
                      ->orWhereHas('customer', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('gst', 'like', "%{$search}%");
                      });
                });
            })->orderBy('id','desc')->paginate(10);

        }
        else
        {
            $orders = Order::where('shop_id',Auth::user()->owner_id)->where('branch_id',null)->where('is_online_order',0)
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // search by bill id
                    $q->where('bill_id', 'like', "%{$search}%")
                      // branch name / username
                      ->orWhereHas('branch', function ($q1) use ($search) {
                          $q1->where('name', 'like', "%{$search}%")
                             ->orWhere('user_name', 'like', "%{$search}%");
                      })
                      // customer name / phone
                      ->orWhereHas('customer', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('gst', 'like', "%{$search}%");;
                      });
                });
            })->orderBy('id','desc')->paginate(10);
        }
        return view('users.orders.index',compact('orders','branches'));
    }

    public function get_bill(Request $request,$company,$branch,$id)
    {
        if($branch == 0)
        {
            $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->owner_id)->first();
        }
        else
        {
            $user = User::with('user_detail','bank_detail')->where('id',$branch)->first();
        }
        
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where([['order_id',$id],['status',1]])->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();

        if($branch == 0)
        {
            $user_detail = UserDetail::where('user_id',Auth::user()->owner_id)->first();
        }
        else
        {
            $user_detail = UserDetail::where('user_id',$branch)->first();

        }
        
        return view('bills.'.$user_detail->billType->blade,compact('user','order','order_details','order_payment_details'));
    }

    public function view_bill(Request $request,$company,$branch,$id)
    {
        if($branch == 0)
        {
            $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->owner_id)->first();
        }
        else
        {
            $user = User::with('user_detail','bank_detail')->where('id',$branch)->first();
        }
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where([['order_id',$id],['status',1]])->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();
        if($branch == 0)
        {
            $user_detail = UserDetail::where('user_id',Auth::user()->owner_id)->first();
        }
        else
        {
            $user_detail = UserDetail::where('user_id',$branch)->first();

        }

        return view('bills.bill',compact('user','order','order_details','order_payment_details', 'user_detail'));
    }

    public function refund(Request $request,$company,$id)
    {
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();
        $payments = Payment::where('is_active',1)->get();
        $staffs = Staff::where([['branch_id',null],['shop_id',Auth::user()->owner_id],['is_active',1]])->get();
        return view('users.orders.refund',compact('order','order_details','order_payment_details','payments','staffs'));
    }

    public function refunded(Request $request)
    {
        //return $request;
        DB::beginTransaction();

        //$user = User::where('id',Auth::user()->id)->first();

        $refund = Refund::create([
            'order_id'     => $request->order_id,
            'refunded_by'   => $request->refunded_by,
            'refund_amount' => $request->amount,
            'refund_on'   => Carbon::now(),
            'reason'   => $request->reason,
            'payment_id'   => $request->payment,
            'payment_info'   => $request->detail,
        ]);

        foreach ($request->orders_details as $orderDetailId) 
        {
            $qty = $request->quantity[$orderDetailId] ?? null;

            if ($qty !== null && $qty > 0) {
                $detail = OrderDetail::find($orderDetailId);

                $selectedImeis = $request->imeis[$detail->id] ?? [];
                $imeiString    = implode(',', $selectedImeis);
                $qty           = $request->quantity[$detail->id];

                RefundDetail::create([
                    'refund_id'   => $refund->id,
                    'product_id'  => $detail->product_id,
                    'name'        => $detail->name,
                    'quantity'    => $qty,
                    'price'       => $detail->price,
                    'tax_amount'  => $detail->tax_amount,
                    'tax_percent' => $detail->tax_percent,
                    'imei'        => $imeiString,
                    'size_id'     => $detail->size_id,
                    'colour_id'   => $detail->colour_id,
                ]);


                $stock = Stock::where([
                    ['shop_id', Auth::user()->owner_id],
                    ['branch_id', null],
                    ['product_id', $detail->product_id]
                ])->first();

                $existingImeis = !empty($stock->imei) ? explode(',', $stock->imei) : [];

                $newImeiList = array_merge($existingImeis, $selectedImeis);

                $stock->update([
                    'quantity'      => $stock->quantity + $qty,
                    'imei'          => implode(',', $newImeiList),
                ]);


                ProductImeiNumber::whereIn('name', $selectedImeis)
                ->where('product_id', $detail->product_id)
                ->update(['is_sold' => 0]); 

                $stock_variation = StockVariation::where([
                    ['stock_id', $stock->id],
                    ['product_id', $detail->product_id],
                    ['size_id', $detail->size_id],
                    ['colour_id', $detail->colour_id],
                ])->first();

                $stock_variation->update([
                    'quantity'      => $stock_variation->quantity + $qty,
                ]);

            }
        }

        Order::where('id',$request->order_id)->update(['is_refunded' => 1]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::id(),'Refund','App/Models/Refund','refunds',$refund->id,'Insert',null,null,'Success','Refund done Successfully');

        //Notifiction
        $this->notification(Auth::user()->parent_id, null,'App/Models/Refund', $refund->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Branch '.Auth::user()->name. ' refunded order '.$refund->order->bill_id.' to customer '.$refund->order->customer->name,null, null,14);

        return redirect()->route('order.index', ['company' => request()->route('company'),'branch' => 0])->with('toast_success', 'Refund done successfully.');
    }

    public function delete(Request $request, $company, Order $order)
    {
        DB::beginTransaction();

        try {

            $orderDetails = OrderDetail::where('order_id', $order->id)->get();

            foreach ($orderDetails as $orderDetail) {

                $stock = Stock::where([
                    ['shop_id', Auth::user()->owner_id],
                    ['branch_id', $order->branch_id],
                    ['product_id', $orderDetail->product_id]
                ])->first();

                if (!$stock) {
                    throw new \Exception('Stock not found.');
                }

                $existingImeis = !empty($stock->imei) ? explode(',', $stock->imei) : [];
                $orderImeis = !empty($orderDetail->imei) ? explode(',', $orderDetail->imei) : [];

                $newImeiList = array_unique(array_merge($existingImeis, $orderImeis));

                $stock->update([
                    'quantity' => $stock->quantity + $orderDetail->quantity,
                    'imei' => implode(',', $newImeiList),
                ]);

                ProductImeiNumber::whereIn('name', $orderImeis)
                    ->where('product_id', $orderDetail->product_id)
                    ->update(['is_sold' => 0]);

                $stockVariation = StockVariation::where([
                    ['stock_id', $stock->id],
                    ['product_id', $orderDetail->product_id],
                    ['size_id', $orderDetail->size_id],
                    ['colour_id', $orderDetail->colour_id]
                ])->first();

                if ($stockVariation) {
                    $stockVariation->increment('quantity', $orderDetail->quantity);
                }
            }

            OrderDetail::where('order_id', $order->id)->delete();
            OrderPaymentDetail::where('order_id', $order->id)->delete();

            $order->delete();

            DB::commit();

            return redirect()->back()->with('toast_success', 'Order deleted successfully.');

        } catch (\Exception $e) {

            DB::rollBack();

            return redirect()->back()->with('toast_error', $e->getMessage());
        }
    }

    public function online(Request $request,$company,$branch)
    {

        $branches = User::where([['parent_id',Auth::user()->owner_id],['is_active',1],['is_lock',0],['is_delete',0]])->get();

        if($branch != 0)
        {
            $orders = Order::where([['branch_id',$branch],['shop_id',Auth::user()->owner_id],['is_online_order',1]])
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // search by bill id
                    $q->where('bill_id', 'like', "%{$search}%")
                      // branch name / username
                      ->orWhereHas('branch', function ($q1) use ($search) {
                          $q1->where('name', 'like', "%{$search}%")
                             ->orWhere('user_name', 'like', "%{$search}%");
                      })
                      // customer name / phone
                      ->orWhereHas('customer', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('gst', 'like', "%{$search}%");
                      });
                });
            })->orderBy('id','desc')->paginate(10);

        }
        else
        {
            $orders = Order::where('shop_id',Auth::user()->owner_id)->where('branch_id',null)->where('is_online_order',1)
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // search by bill id
                    $q->where('bill_id', 'like', "%{$search}%")
                      // branch name / username
                      ->orWhereHas('branch', function ($q1) use ($search) {
                          $q1->where('name', 'like', "%{$search}%")
                             ->orWhere('user_name', 'like', "%{$search}%");
                      })
                      // customer name / phone
                      ->orWhereHas('customer', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('gst', 'like', "%{$search}%");;
                      });
                });
            })->orderBy('id','desc')->paginate(10);
        }
        return view('users.orders.online',compact('orders','branches'));
    }

    public function edit(Request $request,$company,$branch,$id)
    {
        $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->owner_id],['is_active', 1]])->pluck('payment_id')->toArray();
        $payments = Payment::whereIn('id',$shop_payment_ids)->get();
        $finances = Finance::where([['shop_id',Auth::user()->owner_id],['is_active',1]])->get();
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();

        return view('users.orders.edit',compact('order','order_details','order_payment_details','payments','finances'));
    }

    public function detail_status_update(Request $request)
    {
        $request->validate([
            'detail_id' => 'required|integer|exists:order_details,id',
            'status'    => 'required|integer|in:0,1,2',
        ]);

        $detail = OrderDetail::findOrFail($request->detail_id);

        // $currentStatus = (int) $detail->status;
        // $newStatus = (int) $request->status;

        // $allowedStatuses = [
        //     0 => [1, 2], // Placed -> Approved / Declined
        //     1 => [],     // Approved -> no further change
        //     2 => [],     // Declined -> no further change
        // ];

        // if (!in_array($newStatus, $allowedStatuses[$currentStatus] ?? [])) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Invalid status change.'
        //     ], 422);
        // }

        $detail->status = $request->status;
        $detail->save();

        return response()->json([
            'success' => true,
            'message' => 'Order detail status updated successfully.'
        ]);
    }

    public function update(Request $request, $company, $id)
    {
        //return $request;
        DB::beginTransaction();

        try {

            $order = Order::findOrFail($id);

            //Validate Order Status

            $request->validate([
                'status' => 'required|in:1,5',
            ]);

            $status = (int) $request->status;

            //Get Order Details

            $orderDetails = OrderDetail::where('order_id', $order->id)
                ->get();

            if ($orderDetails->isEmpty()) {

                return back()
                    ->with('error', 'No products found for this order.');
            }

            //DECLINED

            if ($status === 5) {

                OrderDetail::where('order_id', $order->id)->update(['status' => 2]);

                // Order status
                $order->status = 5;

                //Save Order

                $order->save();

                DB::commit();

                return redirect()->back()->with('toast_success', 'Order updated successfully.');
            }

            //APPROVED

            elseif ($status === 1) {

                // Validate product statuses
                foreach ($orderDetails as $detail) {

                    if (!in_array((int) $detail->status, [1, 2])) {

                        DB::rollBack();

                        return back()->with('error','All products must be either Approved or Declined.');
                    }
                }

                // At least one product must be approved
                $approvedDetails = $orderDetails->where('status', 1);

                if ($approvedDetails->isEmpty()) {

                    DB::rollBack();

                    return back()->with('error','At least one order product must be approved.');
                }

                // PAYMENT VALIDATION

                $payments = $request->input('payments', []);

                // If payments comes as JSON from hidden input
                if (is_string($payments)) {
                    $payments = json_decode($payments, true) ?? [];
                }

                $paymentTotal = collect($payments)->sum(function ($payment) {
                    return (float) ($payment['amount'] ?? 0);
                });

                $billAmount = (float) $order->bill_amount;

                if (round($paymentTotal, 2) != round($billAmount, 2)) {

                    DB::rollBack();

                    return back()->with('error','Payment amount must be exactly ₹' . number_format($billAmount, 2) . '. Received ₹' . number_format($paymentTotal, 2));
                }

                // CHECK STOCK FOR ALL APPROVED PRODUCTS FIRST

                foreach ($approvedDetails as $detail) {

                    $stock = Stock::where([
                        ['shop_id', $order->shop_id],
                        ['branch_id', $order->branch_id],
                        ['product_id', $detail->product_id],
                    ])->lockForUpdate()->first();

                    if (!$stock) {

                        DB::rollBack();

                        return back()->with('error','Stock not found for product: ' . $detail->name);
                    }

                    // Check main stock

                    if ($stock->quantity < $detail->quantity) {

                        DB::rollBack();

                        return back()->with('error', 'Insufficient stock for ' . $detail->name . '. Available: ' . $stock->quantity . ', Required: ' . $detail->quantity);
                    }

                    // Find variation

                    $variation = null;

                    
                    $variation = StockVariation::where('stock_id', $stock->id)->lockForUpdate()->first();

                    if (!$variation) {

                        DB::rollBack();

                        return back()->with('error','Stock variation not found for product: ' .$detail->name);
                    }

                    // Check variation stock

                    if ($variation->quantity < $detail->quantity) {

                        DB::rollBack();

                        return back()->with('error','Insufficient variation stock for ' .$detail->name . '. Available: ' . $variation->quantity . ', Required: ' . $detail->quantity);
                    }
                }

                // ALL STOCK VALIDATION PASSED
                // Now deduct stock

                foreach ($approvedDetails as $detail) {

                    $stock = Stock::where([
                        ['shop_id', $order->shop_id],
                        ['branch_id', $order->branch_id],
                        ['product_id', $detail->product_id],
                    ])->lockForUpdate()->first();

                    // Deduct variation stock

                    $variation = StockVariation::where('stock_id', $stock->id)->lockForUpdate()->first();

                    $variation->quantity -= $detail->quantity;
                    $variation->save();

                    // Deduct main stock

                    $stock->quantity -= $detail->quantity;

                    // Remove IMEI numbers

                    if (!empty($detail->imei)) {

                        $orderImeis = array_filter(
                            array_map('trim', explode(',', $detail->imei))
                        );

                        $existingImeis = !empty($stock->imei)
                            ? array_filter(
                                array_map('trim', explode(',', $stock->imei))
                            )
                            : [];

                        $remainingImeis = array_values(
                            array_diff($existingImeis, $orderImeis)
                        );

                        $stock->imei = implode(',', $remainingImeis);


                        // Mark IMEI as sold
                        ProductImeiNumber::where('product_id', $detail->product_id)
                            ->whereIn('name', $orderImeis)
                            ->update([
                                'is_sold' => 1
                            ]);
                    }

                    $stock->save();
                }

                // SAVE PAYMENT DETAILS

                foreach ($payments as $payment) {

                    $paymentModel = Payment::where('name', $payment['method'])->first();

                    if (!$paymentModel) {

                        DB::rollBack();

                        return back()->with('error','Payment method not found: ' .($payment['method'] ?? ''));
                    }

                    $extra = $payment['extra'] ?? [];

                    $orderPayment = OrderPaymentDetail::create([
                        'order_id'   => $order->id,
                        'payment_id' => $paymentModel->id,
                        'amount'     => $payment['amount'],
                        'number'     =>
                            $extra['cheque_number']
                            ?? $extra['upi_id']
                            ?? $extra['card_number']
                            ?? $extra['finance_card']
                            ?? null,
                        'card'       => $extra['card_name'] ?? null,
                        'finance_id' => $extra['finance_type'] ?? null,
                    ]);


                    // Credit payment
                    if ($paymentModel->id == 6) {

                        Credit::create([
                            'order_payment_detail_id' => $orderPayment->id,
                            'amount' => $payment['amount'],
                            'remaining_amount' => $payment['amount'],
                        ]);
                    }
                }
                // UPDATE ORDER

                $order->status = 1;
                $order->save();


                DB::commit();

                return redirect()->back()->with('toast_success', 'Order approved and stock updated successfully.');
            }
        } 
        catch (\Throwable $e) 
        {

            DB::rollBack();

            return back()
                ->with('error', $e->getMessage());
        }
    }

    public function status_update(Request $request, $company)
    {
        $request->validate([
            'id' => 'required|exists:orders,id',
            'order_status' => 'required|in:1,2,3,4',
        ]);

        $order = Order::findOrFail($request->id);

        // Prevent going backwards
        // if ((int) $request->status < (int) $order->status) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Order status cannot be moved backwards.'
        //     ], 422);
        // }

        $order->status = $request->order_status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully.'
        ]);
    }
}
