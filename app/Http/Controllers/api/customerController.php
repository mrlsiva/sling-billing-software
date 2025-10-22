<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use App\Traits\Log;
use DB;

class customerController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function customer(Request $request)
    {
        if(Auth::user()->role_id == 2)
        {
            $users = Customer::where('user_id',Auth::user()->owner_id)->when(request('customer'), function ($query) {
                $search = request('customer');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })->orderBy('id','desc')->paginate(10);
        }

        if(Auth::user()->role_id == 3)
        {
            $customer_id1 = Customer::where('branch_id',Auth::user()->id)->pluck('id')->toArray();
            $customer_id2 = Order::where([['branch_id',Auth::user()->id],['shop_id',Auth::user()->parent_id]])->pluck('customer_id')->toArray();

            $customer_id = array_unique(array_merge($customer_id1, $customer_id2));

            $users = Customer::whereIn('id', $customer_id)
            ->when(request('customer'), function ($query) {
                $search = request('customer');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })->orderBy('id','desc')->paginate(10);
            
        }

        return $this->successResponse($users, 200, 'Successfully returned all customers.');
    }

    public function order(Request $request, Customer $customer)
    {

        if(Auth::user()->role_id == 2)
        {
            $orders = Order::where('customer_id',$customer->id)
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
                             ->orWhere('phone', 'like', "%{$search}%")
                             ->orWhere('gst', 'like', "%{$search}%");
                      });
                });
            })->orderBy('id','desc')->paginate(10);
        }

        if(Auth::user()->role_id == 3)
        {

            $orders = Order::where([['customer_id',$customer->id],['branch_id',Auth::user()->id]])
            ->when(request('order'), function ($query) {
                $search = request('order');
                $query->where(function ($q) use ($search) {
                    // Bill No
                    $q->where('bill_id', 'like', "%{$search}%")
                    // Customer Name / Phone
                    ->orWhereHas('customer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('gst', 'like', "%{$search}%");
                    });
                });
            })->orderBy('id','desc')->paginate(10);
        }

        return $this->successResponse($orders, 200, 'Successfully returned all orders of the customers.');

    }

}
