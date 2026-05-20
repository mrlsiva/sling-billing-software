<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;

class orderDiscountController extends Controller
{
    public function index(Request $request)
    {

        $orderQuery = Order::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)->where('order_discount','!=',0);

        // Date filters
        if ($request->filled('from')) {
            $orderQuery->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $orderQuery->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $orderQuery->orderByDesc('id')->paginate(10);

        return view('branches.discounts.index', compact('orders'));

    }
}
