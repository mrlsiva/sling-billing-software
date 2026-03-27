<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrderRefund;
use App\Models\VendorPaymentDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DailyReportExport;
use Illuminate\Http\Request;
use App\Models\OrderPaymentDetail;
use App\Models\ProductHistory;
use App\Models\VendorPayment;
use App\Models\PurchaseOrder;
use App\Models\Order;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class dailyReportsController extends Controller
{
    use Log;

    public function daily(Request $request, $company, $branch)
    {
        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0]
        ])->get();

        $date = $request->date ?? Carbon::today()->toDateString();

        /*
        |--------------------------------------------------
        | Default values (Important for Branch view)
        |--------------------------------------------------
        */

        $purchases = collect();
        $payments = collect();
        $refunds = collect();

        $totalSales = 0;
        $totalPurchase = 0;
        $totalVendorPaid = 0;
        $totalRefund = 0;
        $profit = 0;

        /*
        |--------------------------------------------------
        | Orders
        |--------------------------------------------------
        */

        $orderQuery = Order::where('shop_id', Auth::user()->owner_id);

        if ($branch != 0) {
            $orderQuery->where('branch_id', $branch);
        }

        $orderQuery->whereDate('billed_on', $date);

        $orders = $orderQuery
            ->with(['branch','shop','customer','billedBy','payments.payment'])
            ->orderByDesc('id')
            ->paginate(10);

        $totalSales = $orders->sum('bill_amount');

        $orderIds = $orders->pluck('id');

        $paymentSummary = OrderPaymentDetail::select(
                'payment_id',
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereIn('order_id', $orderIds)
            ->groupBy('payment_id')
            ->get();

        /*
        |--------------------------------------------------
        | Product History (IN / OUT)
        |--------------------------------------------------
        */

        $productInAmount = 0;
        $productOutAmount = 0;

        if ($branch == 0) 
        {

            $productIn = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('to', Auth::user()->owner_id)
                ->get();

            $productOut = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('from', Auth::user()->owner_id)
                ->get();

        } else 
        {

            $productIn = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('to', $branch)
                ->get();

            $productOut = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('from', $branch)
                ->get();
        }

        /*
        |--------------------------------------------------
        | Calculate Amount (price * quantity)
        |--------------------------------------------------
        */

        $productInAmount = $productIn->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });

        $productOutAmount = $productOut->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });

        /*
        |--------------------------------------------------
        | HO Only Data
        |--------------------------------------------------
        */

        if ($branch == 0) {

            $purchases = PurchaseOrder::where('shop_id', Auth::user()->owner_id)
                ->whereDate('invoice_date', $date)
                ->with(['vendor','product'])
                ->orderByDesc('id')
                ->get();

            $payments = VendorPaymentDetail::with([
                    'purchaseOrder.vendor',
                    'vendorPayment'
                ])
                ->whereDate('paid_on', $date)
                ->orderByDesc('id')
                ->get();

            $refunds = PurchaseOrderRefund::with(['purchase_order.product','vendor','refundedBy'])
                ->whereDate('refund_on', $date)
                ->orderByDesc('id')
                ->get();

            $totalPurchase = $purchases->sum('gross_cost');
            $totalVendorPaid = $payments->sum('amount');
            $totalRefund = $refunds->sum('refund_amount');

            $profit = $totalSales - $totalPurchase + $totalRefund;
        }

        return view('users.reports.daily', compact(
            'orders',
            'branches',
            'purchases',
            'payments',
            'refunds',
            'totalSales',
            'totalPurchase',
            'totalVendorPaid',
            'totalRefund',
            'profit',
            'branch',
            'productIn',
            'productOut',
            'productInAmount',
            'productOutAmount',
            'paymentSummary',
        ));
    }

    public function download_excel(Request $request, $company, $branch)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        $purchases = collect();
        $payments = collect();
        $refunds = collect();

        /*
        |----------------------------------------
        | Orders
        |----------------------------------------
        */

        $orderQuery = Order::where('shop_id', Auth::user()->owner_id);

        if ($branch != 0) {
            $orderQuery->where('branch_id', $branch);
        }

        $orders = $orderQuery
            ->whereDate('billed_on', $date)
            ->with(['branch','customer','billedBy','payments.payment'])
            ->get();

        $orderIds = $orders->pluck('id');

        /*
        |----------------------------------------
        | Payment Summary (Mode of Payment)
        |----------------------------------------
        */

        $paymentSummary = OrderPaymentDetail::select(
                'payment_id',
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereIn('order_id', $orderIds)
            ->groupBy('payment_id')
            ->with('payment')
            ->get();

        /*
        |----------------------------------------
        | Product IN / OUT
        |----------------------------------------
        */

        if ($branch == 0) {
            $productIn = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('to', Auth::user()->owner_id)
                ->get();

            $productOut = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('from', Auth::user()->owner_id)
                ->get();
        } else {
            $productIn = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('to', $branch)
                ->get();

            $productOut = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('from', $branch)
                ->get();
        }

        $productInAmount = $productIn->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });

        $productOutAmount = $productOut->sum(function ($item) {
            return ($item->product->price ?? 0) * $item->quantity;
        });

        /*
        |----------------------------------------
        | HO Data
        |----------------------------------------
        */

        if ($branch == 0) {

            $purchases = PurchaseOrder::where('shop_id', Auth::user()->owner_id)
                ->whereDate('invoice_date', $date)
                ->with(['vendor','product'])
                ->get();

            $payments = VendorPaymentDetail::with([
                    'purchaseOrder.vendor',
                    'vendorPayment'
                ])
                ->whereDate('paid_on', $date)
                ->get();

            $refunds = PurchaseOrderRefund::with([
                    'purchase_order.product',
                    'vendor',
                    'refundedBy'
                ])
                ->whereDate('refund_on', $date)
                ->get();
        }

        return Excel::download(
            new DailyReportExport(
                $orders,
                $purchases,
                $payments,
                $refunds,
                $productIn,
                $productOut,
                $productInAmount,
                $productOutAmount,
                $paymentSummary
            ),
            'daily_report.xlsx'
        );
    }

    public function download_pdf(Request $request, $company, $branch)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        /*
        |----------------------------------------
        | Default Values
        |----------------------------------------
        */

        $purchases = collect();
        $payments = collect();
        $refunds = collect();

        $totalPurchase = 0;
        $totalVendorPaid = 0;
        $totalRefund = 0;
        $profit = 0;

        /*
        |----------------------------------------
        | Orders
        |----------------------------------------
        */

        $orderQuery = Order::where('shop_id', Auth::user()->owner_id);

        if ($branch != 0) {
            $orderQuery->where('branch_id', $branch);
        }

        $orders = $orderQuery
        ->whereDate('billed_on', $date)
        ->with(['branch','customer','billedBy','payments.payment','shop'])
        ->get();

        $totalSales = $orders->sum('bill_amount');

        $orderIds = $orders->pluck('id');

        /*
        |----------------------------------------
        | Payment Summary (optional if needed)
        |----------------------------------------
        */
        $paymentSummary = OrderPaymentDetail::select(
                'payment_id',
                DB::raw('SUM(amount) as total_amount')
            )
            ->whereIn('order_id', $orderIds)
            ->groupBy('payment_id')
            ->with('payment')
            ->get();

        /*
        |----------------------------------------
        | Product IN / OUT
        |----------------------------------------
        */
        if ($branch == 0) {
            $productIn = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('to', Auth::user()->owner_id)
                ->get();

            $productOut = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('from', Auth::user()->owner_id)
                ->get();
        } else {
            $productIn = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('to', $branch)
                ->get();

            $productOut = ProductHistory::with('product')
                ->whereDate('transfer_on', $date)
                ->where('from', $branch)
                ->get();
        }

        $productInAmount = $productIn->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);
        $productOutAmount = $productOut->sum(fn($i) => ($i->product->price ?? 0) * $i->quantity);

        /*
        |----------------------------------------
        | HO Only Data
        |----------------------------------------
        */

        if ($branch == 0) {

            $purchases = PurchaseOrder::where('shop_id', Auth::user()->owner_id)
                ->whereDate('invoice_date', $date)
                ->with(['vendor','product'])
                ->get();

            $payments = VendorPaymentDetail::with([
                    'purchaseOrder.vendor',
                    'vendorPayment'
                ])
                ->whereDate('paid_on', $date)
                ->get();

            $refunds = PurchaseOrderRefund::with([
                    'purchase_order.product',
                    'vendor',
                    'refundedBy'
                ])
                ->whereDate('refund_on', $date)
                ->get();

            $totalPurchase = $purchases->sum('gross_cost');
            $totalVendorPaid = $payments->sum('amount');
            $totalRefund = $refunds->sum('refund_amount');

            $profit = $totalSales - $totalPurchase + $totalRefund;
        }

        $user = Auth::user();

        $pdf = Pdf::loadView('users.exports.daily_report_pdf', compact(
            'orders',
            'purchases',
            'payments',
            'refunds',
            'totalSales',
            'totalPurchase',
            'totalVendorPaid',
            'totalRefund',
            'profit',
            'user',
            'branch',
            'productIn',
            'productOut',
            'productInAmount',
            'productOutAmount'
        ))->setPaper('a4','landscape');

        return $pdf->download('daily_report.pdf');
    }
}
