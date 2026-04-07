<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;

class dashboardController extends Controller
{
    public function index(Request $request)
    {
        $auth = User::where('id', Auth::user()->owner_id)->with(['user_detail', 'bank_detail'])->first();
        $branches = User::where('parent_id', Auth::user()->owner_id)->get()->map(function ($branch) {
            $branch->today_orders  = Order::where('branch_id', $branch->id)->whereDate('billed_on', today())->count();
            $branch->total_orders  = Order::where('branch_id', $branch->id)->count();
            $branch->today_sales   = Order::where('branch_id', $branch->id)->whereDate('billed_on', today())->sum('bill_amount');
            $branch->total_sales   = Order::where('branch_id', $branch->id)->sum('bill_amount');
            return $branch;
        });

        return view('users.dashboard', compact('auth', 'branches'));
    }
}
