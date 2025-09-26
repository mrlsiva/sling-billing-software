<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;

class branchDashboardController extends Controller
{
    public function index(Request $request)
    {
        $auth = User::where('id', Auth::user()->id)->with(['user_detail', 'bank_detail'])->first();

        $today_orders = Order::where([['branch_id',Auth::user()->id],['billed_on',now()]])->count();
        $total_orders = Order::where('branch_id',Auth::user()->id)->count();
        $today_order_amount = Order::where([['branch_id',Auth::user()->id],['billed_on',now()]])->sum('bill_amount');
        $total_order_amount = Order::where('branch_id',Auth::user()->id)->sum('bill_amount');

        return view('branches.dashboard',compact('auth','today_orders','total_orders','today_order_amount','total_order_amount'));
    }
}
