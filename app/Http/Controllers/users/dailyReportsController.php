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
use App\Models\PurchaseOrder;
use App\Models\VendorPayment;
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
            ->with(['branch','shop','customer','billedBy'])
            ->orderByDesc('id')
            ->paginate(10);

        $totalSales = $orders->sum('bill_amount');

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
            'branch'
        ));
    }

    public function download_excel(Request $request, $company, $branch)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        /*
        |----------------------------------------
        | Default Values (for branch)
        |----------------------------------------
        */

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
            ->with(['branch','customer','billedBy'])
            ->get();

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
        }

        return Excel::download(
            new DailyReportExport($orders, $purchases, $payments, $refunds),
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
            ->with(['branch','customer','billedBy'])
            ->get();

        $totalSales = $orders->sum('bill_amount');

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
            'branch'
        ))->setPaper('a4','landscape');

        return $pdf->download('daily_report.pdf');
    }
}
