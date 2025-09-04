<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPaymentDetail;
use App\Models\User;
use App\Traits\Log;
use DB;

class posController extends Controller
{
    use Log;

    public function index(Request $request,$company,$branch)
    {

        $branches = User::where([['parent_id',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->get();

        if($branch != 0)
        {
            $orders = Order::where([['branch_id',$branch],['shop_id',Auth::user()->id]])->paginate(30);

        }
        else
        {
            $orders = Order::where('shop_id',Auth::user()->id)->paginate(30);
        }
        return view('users.orders.index',compact('orders','branches'));
    }

    public function get_bill(Request $request,$company,$id)
    {
        $user = User::with('user_detail','bank_detail')->where('id',Auth::user()->id)->first();
        $order = Order::where('id',$id)->first();
        $order_details = OrderDetail::where('order_id',$id)->get();
        $order_payment_details = OrderPaymentDetail::where('order_id',$id)->get();
        return view('branches.bill',compact('user','order','order_details','order_payment_details'));
    }
}
