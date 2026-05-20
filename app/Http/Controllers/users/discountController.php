<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;

class discountController extends Controller
{
    public function index(Request $request,$company,$branch)
    {
        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0]
        ])->get();

        $orderQuery = Order::where('shop_id', Auth::user()->owner_id)->where('order_discount','!=',0);

        if ($branch != 0) {
            $orderQuery->where('branch_id', $branch);
        }

        // Date filters
        if ($request->filled('from')) {
            $orderQuery->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $orderQuery->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $orderQuery->orderByDesc('id')->paginate(10);

        return view('users.discounts.index', compact('orders','branches'));

    }
}
