<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\OrderPaymentDetail;
use App\Models\ProductHistory;
use App\Models\Refund;
use App\Models\Order;
use Carbon\Carbon;
use DB;

class branchReportsController extends Controller
{
    use ResponseHelper;

    // GET /api/branch/reports/daily?date=2025-01-01
    public function daily(Request $request)
    {
        $branchId = Auth::user()->id;
        $date     = $request->date ?? Carbon::today()->toDateString();

        $orders = Order::where('branch_id', $branchId)
            ->whereDate('billed_on', $date)
            ->with(['customer', 'billedBy', 'payments.payment'])
            ->withSum('refunds as total_refund', 'refund_amount')
            ->orderByDesc('id')->get();

        $refundedIds = Order::where('branch_id', $branchId)->whereDate('billed_on', $date)->where('is_refunded', 1)->pluck('id');
        $totalRefund = $refundedIds->isNotEmpty() ? Refund::whereIn('order_id', $refundedIds)->sum('refund_amount') : 0;
        $totalSales  = $orders->sum('bill_amount') - $totalRefund;
        $orderIds    = $orders->pluck('id');

        $paymentSummary = OrderPaymentDetail::select('payment_id', DB::raw('SUM(amount) as total_amount'))
            ->whereIn('order_id', $orderIds)->groupBy('payment_id')->with('payment')->get();

        $productIn  = ProductHistory::with('product')->whereDate('transfer_on', $date)->where('to', $branchId)->get();
        $productOut = ProductHistory::with('product')->whereDate('transfer_on', $date)->where('from', $branchId)->get();

        $productInAmount  = $productIn->sum(fn($i)  => ($i->product->price ?? 0) * $i->quantity);
        $productOutAmount = $productOut->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);

        $credit_amount = OrderPaymentDetail::whereIn('order_id', $orderIds)->where('payment_id', 6)->sum('amount');

        return $this->successResponse([
            'orders'           => $orders,
            'totalSales'       => $totalSales,
            'totalRefund'      => $totalRefund,
            'paymentSummary'   => $paymentSummary,
            'productIn'        => $productIn,
            'productOut'       => $productOut,
            'productInAmount'  => $productInAmount,
            'productOutAmount' => $productOutAmount,
            'credit_amount'    => $credit_amount,
            'date'             => $date,
        ], 200, 'Branch daily report retrieved successfully.');
    }

    // GET /api/branch/reports/orders?from_date=&to_date=&search=
    public function orders(Request $request)
    {
        $query = Order::where('branch_id', Auth::user()->id)
            ->with(['customer', 'billedBy', 'payments.payment'])
            ->withSum('refunds as total_refund', 'refund_amount');

        if ($request->filled('from_date')) $query->whereDate('billed_on', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('billed_on', '<=', $request->to_date);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('bill_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
            );
        }

        $orders = $query->orderByDesc('id')->paginate(10);

        return $this->successResponse($orders, 200, 'Branch order report retrieved successfully.');
    }

    // GET /api/branch/reports/sales?from_date=&to_date=
    public function sales(Request $request)
    {
        $query = Order::where('branch_id', Auth::user()->id)
            ->with(['customer', 'payments.payment'])
            ->withSum('refunds as total_refund', 'refund_amount');

        if ($request->filled('from_date')) $query->whereDate('billed_on', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('billed_on', '<=', $request->to_date);

        $orders     = $query->orderByDesc('id')->paginate(10);
        $totalSales = $query->sum('bill_amount');

        return $this->successResponse(compact('orders', 'totalSales'), 200, 'Branch sales report retrieved successfully.');
    }

    // GET /api/branch/reports/transfer?from_date=&to_date=&product=
    public function transfer(Request $request)
    {
        $branchId = Auth::user()->id;

        $query = ProductHistory::selectRaw('MAX(id) as id, invoice, MAX(`from`) as `from`, MAX(`to`) as `to`, MAX(transfer_on) as transfer_on, MAX(transfer_by) as transfer_by')
            ->where(fn($q) => $q->where('from', $branchId)->orWhere('to', $branchId))
            ->groupBy('invoice')
            ->with(['transfer_from', 'transfer_to']);

        if ($request->filled('from_date')) $query->whereDate('transfer_on', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('transfer_on', '<=', $request->to_date);
        if ($request->filled('product'))   $query->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $request->product . '%'));

        $transfers = $query->orderByDesc('id')->paginate(10);

        return $this->successResponse($transfers, 200, 'Branch transfer report retrieved successfully.');
    }
}