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
        $orders = Order::where('branch_id',Auth::user()->id)->paginate(30);
        return view('branches.orders.index',compact('orders'));
    }
}
