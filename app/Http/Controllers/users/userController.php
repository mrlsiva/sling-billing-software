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

        $users = Customer::where('user_id',Auth::user()->id)
        ->when(request('customer'), function ($query) {
            $search = request('customer');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        })->orderBy('id','desc')->paginate(10);

        return view('users.customers.index',compact('users'));
    }

    public function order(Request $request,$company,$id)
    {

        $customer = Customer::where('id',$id)->first();
        $orders = Order::where('customer_id',$id)
        ->when(request('order'), function ($query) {
            $search = request('order');
            $query->where(function ($q) use ($search) {
                // Bill No
                $q->where('bill_id', 'like', "%{$search}%")
                  // Branch Name / Username
                  ->orWhereHas('branch', function ($q1) use ($search) {
                      $q1->where('name', 'like', "%{$search}%")
                         ->orWhere('user_name', 'like', "%{$search}%");
                  })
                  // Customer Name / Phone
                  ->orWhereHas('customer', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        })->orderBy('id','desc')->paginate(10);

        return view('users.customers.order',compact('orders','customer'));

    }
}
