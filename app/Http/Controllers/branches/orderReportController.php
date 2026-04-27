<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class orderReportController extends Controller
{
    use Log;
    
    public function order(Request $request)
    {
        $query = Order::where('branch_id', Auth::user()->id);

        // Apply date filters if provided
        if ($request->filled('from')) {
            $query->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $query->withSum('refunds as total_refund', 'refund_amount')->orderBy('id', 'desc')->paginate(10);

        return view('branches.reports.order', compact('orders'));
    }

    public function download_excel(Request $request)
    {
        $query = Order::where('branch_id', Auth::user()->id);

        // Apply date filters if provided
        if ($request->filled('from')) {
            $query->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $query->withSum('refunds as total_refund', 'refund_amount')->orderBy('id', 'desc')->paginate(10);

        return Excel::download(new OrdersExport($orders), 'orders_' . now()->format('d-m-Y_h-i A') . '.xlsx');
    }

    public function download_pdf(Request $request)
    {
        $query = Order::where('branch_id', Auth::user()->id);

        if ($request->filled('from')) {
            $query->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $query->withSum('refunds as total_refund', 'refund_amount')->orderBy('id', 'desc')->get();

        // Totals
        $totalOrders = $orders->count();
        $totalSales = $orders->sum(function ($order) {
            return $order->bill_amount - ($order->total_refund ?? 0);
        });

        $user = Auth::user(); // for logo

        $pdf = Pdf::loadView('branches.exports.order_pdf', compact('orders', 'totalOrders', 'totalSales', 'user'))
                  ->setPaper('a4', 'landscape'); // you can use portrait too

        return $pdf->download('orders_' . now()->format('d-m-Y_h-i A') . '.pdf');
    }
}
