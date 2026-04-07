<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BranchSalesReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;


class salesReportsController extends Controller
{
    public function sales(Request $request)
    {

        $from = $request->from;
        $to   = $request->to;

        $orders = Order::with(['customer','billedBy','details'])
            ->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)

            // ✅ Date filter
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('billed_on', [
                    Carbon::parse($from)->startOfDay(),
                    Carbon::parse($to)->endOfDay()
                ]);
            })

            ->latest()
            ->paginate(10);

        return view('branches.reports.sales', compact('orders'));
    }

    public function download_excel(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;

        $orders = Order::with(['customer','billedBy','details.product.category','details.product.sub_category'])
            ->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)

           

            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('billed_on', [
                    \Carbon\Carbon::parse($from)->startOfDay(),
                    \Carbon\Carbon::parse($to)->endOfDay()
                ]);
            })

            ->get();

        return Excel::download(new BranchSalesReportExport($orders), 'sales_report.xlsx');
    }



    public function download_pdf(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;

        $orders = Order::with(['customer','billedBy','details.product.category','details.product.sub_category'])
            ->where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id)

            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('billed_on', [
                    \Carbon\Carbon::parse($from)->startOfDay(),
                    \Carbon\Carbon::parse($to)->endOfDay()
                ]);
            })

            ->get();

        $pdf = Pdf::loadView('branches.exports.sales_report_pdf', compact('orders'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('sales_report.pdf');
    }
}
