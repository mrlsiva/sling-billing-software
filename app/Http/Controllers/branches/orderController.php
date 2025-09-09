<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderPaymentDetail;
use Illuminate\Http\Request;
use App\Models\RefundDetail;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Order;
use App\Models\User;
use App\Models\Staff;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class orderController extends Controller
{
    use Log;

    public function index(Request $request)
    {
        $orders = Order::where('branch_id',Auth::user()->id)
        ->when(request('order'), function ($query) {
            $search = request('order');
            $query->where(function ($q) use ($search) {
                // Bill No
                $q->where('bill_id', 'like', "%{$search}%")
                  // Customer Name / Phone
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        })->orderBy('id','desc')->paginate(10);
        return view('branches.orders.index',compact('orders'));
    }

    public function refund(Request $request,$company,$id)
    {
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();
        $payments = Payment::where('is_active',1)->get();
        $staffs = Staff::where([['branch_id',Auth::user()->id],['is_active',1]])->get();
        return view('branches.orders.refund',compact('order','order_details','order_payment_details','payments','staffs'));
    }

    public function refunded(Request $request)
    {
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

                RefundDetail::create([
                    'refund_id'   => $refund->id,
                    'product_id'  => $detail->product_id,
                    'name'        => $detail->name,
                    'quantity'    => $qty,
                    'price'       => $detail->price,
                    'tax_amount'  => $detail->tax_amount,
                ]);
            }
        }

        Order::where('id',$request->order_id)->update(['is_refunded' => 1]);

        DB::commit();

        //Log
        $this->addToLog($this->unique(),Auth::id(),'Refund','App/Models/Refund','refunds',$refund->id,'Insert',null,null,'Success','Refund done Successfully');

        return redirect()->route('branch.order.index', request()->route('company'))->with('toast_success', 'Refund done successfully.');
    }
}
