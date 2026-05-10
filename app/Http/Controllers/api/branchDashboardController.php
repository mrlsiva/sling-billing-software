<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class branchDashboardController extends Controller
{
    use ResponseHelper;

    public function index(Request $request)
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : today()->startOfDay();
        $toDate   = $request->to_date   ? Carbon::parse($request->to_date)->endOfDay()     : today()->endOfDay();

        $branchId = Auth::user()->id;
        $auth     = User::with(['user_detail', 'bank_detail'])->find($branchId);

        return $this->successResponse([
            'auth'               => $auth,
            'date_orders'        => Order::where('branch_id', $branchId)->whereBetween('billed_on', [$fromDate, $toDate])->count(),
            'date_order_amount'  => Order::where('branch_id', $branchId)->whereBetween('billed_on', [$fromDate, $toDate])->sum('bill_amount'),
            'total_orders'       => Order::where('branch_id', $branchId)->count(),
            'total_order_amount' => Order::where('branch_id', $branchId)->sum('bill_amount'),
            'from_date'          => $fromDate->toDateString(),
            'to_date'            => $toDate->toDateString(),
        ], 200, 'Branch dashboard data retrieved successfully.');
    }
}