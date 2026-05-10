<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseHelper;
use Illuminate\Http\Request;
use App\Models\OrderPaymentDetail;
use App\Models\PurchaseOrderRefund;
use App\Models\VendorPaymentDetail;
use App\Models\ProductHistory;
use App\Models\PurchaseOrder;
use App\Models\VendorPayment;
use App\Models\Refund;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use DB;

class reportsController extends Controller
{
    use ResponseHelper;

    // GET /api/reports/daily?branch=0&date=2025-01-01
    public function daily(Request $request)
    {
        $branch = $request->branch ?? 0;
        $date   = $request->date ?? Carbon::today()->toDateString();

        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1], ['is_lock', 0], ['is_delete', 0],
        ])->get();

        $orderQuery = Order::where('shop_id', Auth::user()->owner_id);
        if ($branch != 0) $orderQuery->where('branch_id', $branch);
        $orderQuery->whereDate('billed_on', $date);

        $orders = (clone $orderQuery)
            ->with(['branch', 'shop', 'customer', 'billedBy', 'payments.payment'])
            ->withSum('refunds as total_refund', 'refund_amount')
            ->orderByDesc('id')->get();

        $refundedIds  = (clone $orderQuery)->where('is_refunded', 1)->pluck('id');
        $totalRefund  = $refundedIds->isNotEmpty() ? Refund::whereIn('order_id', $refundedIds)->sum('refund_amount') : 0;
        $totalSales   = $orders->sum('bill_amount') - $totalRefund;
        $orderIds     = $orders->pluck('id');

        $paymentSummary = OrderPaymentDetail::select('payment_id', DB::raw('SUM(amount) as total_amount'))
            ->whereIn('order_id', $orderIds)->groupBy('payment_id')
            ->with('payment')->get();

        $productIn  = ProductHistory::with('product')->whereDate('transfer_on', $date)
            ->where('to', $branch != 0 ? $branch : Auth::user()->owner_id)->get();
        $productOut = ProductHistory::with('product')->whereDate('transfer_on', $date)
            ->where('from', $branch != 0 ? $branch : Auth::user()->owner_id)->get();

        $productInAmount  = $productIn->sum(fn($i)  => ($i->product->price ?? 0) * $i->quantity);
        $productOutAmount = $productOut->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);

        $purchases      = collect(); $payments = collect(); $refunds = collect();
        $totalPurchase  = 0; $totalVendorPaid = 0; $totalPurchaseRefund = 0; $profit = 0;

        if ($branch == 0) {
            $purchases     = PurchaseOrder::where('shop_id', Auth::user()->owner_id)->whereDate('invoice_date', $date)->with(['vendor', 'product'])->orderByDesc('id')->get();
            $payments      = VendorPaymentDetail::with(['purchaseOrder.vendor', 'vendorPayment'])->whereDate('paid_on', $date)->orderByDesc('id')->get();
            $refunds       = PurchaseOrderRefund::with(['purchase_order.product', 'vendor', 'refundedBy'])->whereDate('refund_on', $date)->orderByDesc('id')->get();
            $totalPurchase     = $purchases->sum('gross_cost');
            $totalVendorPaid   = $payments->sum('amount');
            $totalPurchaseRefund = $refunds->sum('refund_amount');
            $profit            = $totalSales - $totalPurchase + $totalPurchaseRefund;
        }

        $credit_amount = OrderPaymentDetail::whereIn('order_id',
            Order::where('shop_id', Auth::user()->owner_id)->where('branch_id', null)->whereDate('billed_on', $date)->pluck('id')
        )->where('payment_id', 6)->sum('amount');

        return $this->successResponse([
            'orders'            => $orders,
            'branches'          => $branches,
            'purchases'         => $purchases,
            'payments'          => $payments,
            'refunds'           => $refunds,
            'totalSales'        => $totalSales,
            'totalPurchase'     => $totalPurchase,
            'totalVendorPaid'   => $totalVendorPaid,
            'totalRefund'       => $totalRefund,
            'profit'            => $profit,
            'productIn'         => $productIn,
            'productOut'        => $productOut,
            'productInAmount'   => $productInAmount,
            'productOutAmount'  => $productOutAmount,
            'paymentSummary'    => $paymentSummary,
            'credit_amount'     => $credit_amount,
            'branch'            => $branch,
            'date'              => $date,
        ], 200, 'Daily report retrieved successfully.');
    }

    // GET /api/reports/orders?branch=0&from_date=&to_date=&search=
    public function orders(Request $request)
    {
        $branch = $request->branch ?? 0;

        $query = Order::where('shop_id', Auth::user()->owner_id)
            ->with(['branch', 'customer', 'billedBy', 'payments.payment'])
            ->withSum('refunds as total_refund', 'refund_amount');

        if ($branch != 0) $query->where('branch_id', $branch);

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('billed_on', [$request->from_date . ' 00:00:00', $request->to_date . ' 23:59:59']);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('billed_on', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('billed_on', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('bill_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
            );
        }

        $orders = $query->orderByDesc('id')->paginate(10);

        $branches = User::where([['parent_id', Auth::user()->owner_id], ['is_active', 1], ['is_lock', 0], ['is_delete', 0]])->get();

        return $this->successResponse(compact('orders', 'branches'), 200, 'Order report retrieved successfully.');
    }

    // GET /api/reports/sales?branch=0&from_date=&to_date=
    public function sales(Request $request)
    {
        $branch = $request->branch ?? 0;

        $query = Order::where('shop_id', Auth::user()->owner_id)
            ->with(['branch', 'customer', 'payments.payment'])
            ->withSum('refunds as total_refund', 'refund_amount');

        if ($branch != 0) $query->where('branch_id', $branch);
        if ($request->filled('from_date')) $query->whereDate('billed_on', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('billed_on', '<=', $request->to_date);

        $orders     = $query->orderByDesc('id')->paginate(10);
        $totalSales = $query->sum('bill_amount');

        $branches = User::where([['parent_id', Auth::user()->owner_id], ['is_active', 1], ['is_lock', 0], ['is_delete', 0]])->get();

        return $this->successResponse(compact('orders', 'totalSales', 'branches'), 200, 'Sales report retrieved successfully.');
    }

    // GET /api/reports/purchase?from_date=&to_date=&vendor=
    public function purchase(Request $request)
    {
        $query = PurchaseOrder::where('shop_id', Auth::user()->owner_id)
            ->with(['vendor', 'product', 'metric']);

        if ($request->filled('from_date')) $query->whereDate('invoice_date', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('invoice_date', '<=', $request->to_date);
        if ($request->filled('vendor'))    $query->whereHas('vendor', fn($q) => $q->where('name', 'like', '%' . $request->vendor . '%'));

        $purchases     = $query->orderByDesc('id')->paginate(10);
        $totalPurchase = $query->sum('gross_cost');

        return $this->successResponse(compact('purchases', 'totalPurchase'), 200, 'Purchase report retrieved successfully.');
    }

    // GET /api/reports/transfer?branch=0&from_date=&to_date=&product=
    public function transfer(Request $request)
    {
        $branch = $request->branch ?? 0;

        $query = ProductHistory::selectRaw('MAX(id) as id, invoice, MAX(`to`) as `to`, MAX(transfer_on) as transfer_on, MAX(transfer_by) as transfer_by')
            ->where(fn($q) => $q->where('from', $branch != 0 ? $branch : Auth::user()->owner_id)->orWhere('to', $branch != 0 ? $branch : Auth::user()->owner_id))
            ->groupBy('invoice')
            ->with(['transfer_from', 'transfer_to']);

        if ($request->filled('from_date')) $query->whereDate('transfer_on', '>=', $request->from_date);
        if ($request->filled('to_date'))   $query->whereDate('transfer_on', '<=', $request->to_date);
        if ($request->filled('product'))   $query->whereHas('product', fn($q) => $q->where('name', 'like', '%' . $request->product . '%'));

        $transfers = $query->orderByDesc('id')->paginate(10);
        $branches  = User::where([['parent_id', Auth::user()->owner_id], ['is_active', 1], ['is_lock', 0], ['is_delete', 0]])->get();

        return $this->successResponse(compact('transfers', 'branches'), 200, 'Transfer report retrieved successfully.');
    }
}