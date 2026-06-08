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
use App\Models\Payment;
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
            $orders = Order::where([['branch_id',$branch],['shop_id',Auth::user()->owner_id]])
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
            $orders = Order::where('shop_id',Auth::user()->owner_id)->where('branch_id',null)
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
        $order_details = OrderDetail::where('order_id',$id)->get();
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
        $order_details = OrderDetail::where('order_id',$id)->get();
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
}
