<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Traits\Log;
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
}
