<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Traits\Log;
use DB;

class userController extends Controller
{
    use Log;

    public function index(Request $request)
    {

        $users = Customer::where('user_id',Auth::user()->id)->paginate(30);

        return view('users.customers.index',compact('users'));
    }

    public function order(Request $request,$company,$id)
    {

        $customer = Customer::where('id',$id)->first();
        $orders = Order::where('customer_id',$id)->paginate(30);

        return view('users.customers.order',compact('orders','customer'));

    }
}
