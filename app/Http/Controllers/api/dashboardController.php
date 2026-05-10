<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class dashboardController extends Controller
{
    use ResponseHelper;

    public function index(Request $request)
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : today()->startOfDay();
        $toDate   = $request->to_date   ? Carbon::parse($request->to_date)->endOfDay()     : today()->endOfDay();

        $auth = User::with(['user_detail', 'bank_detail'])->find(Auth::user()->owner_id);

        $branches = User::where('parent_id', Auth::user()->owner_id)->get()->map(function ($branch) use ($fromDate, $toDate) {
            $branch->date_orders  = Order::where('branch_id', $branch->id)->whereBetween('billed_on', [$fromDate, $toDate])->count();
            $branch->date_sales   = Order::where('branch_id', $branch->id)->whereBetween('billed_on', [$fromDate, $toDate])->sum('bill_amount');
            $branch->total_orders = Order::where('branch_id', $branch->id)->count();
            $branch->total_sales  = Order::where('branch_id', $branch->id)->sum('bill_amount');
            return $branch;
        });

        return $this->successResponse([
            'auth'         => $auth,
            'branches'     => $branches,
            'date_orders'  => $branches->sum('date_orders'),
            'date_sales'   => $branches->sum('date_sales'),
            'total_orders' => $branches->sum('total_orders'),
            'total_sales'  => $branches->sum('total_sales'),
            'from_date'    => $fromDate->toDateString(),
            'to_date'      => $toDate->toDateString(),
        ], 200, 'Dashboard data retrieved successfully.');
    }
}