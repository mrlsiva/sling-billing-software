<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class salesReportController extends Controller
{
    public function sales(Request $request, $company, $branch)
    {
        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0]
        ])->get();

        $from = $request->from;
        $to   = $request->to;

        $orders = Order::with(['customer','billedBy','details'])
            ->where('shop_id', Auth::user()->owner_id)

            // ✅ Branch condition
            ->when($branch != 0, function ($q) use ($branch) {
                $q->where('branch_id', $branch);
            }, function ($q) {
                $q->whereNull('branch_id'); // OR ->where('branch_id', 0)
            })

            // ✅ Date filter
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('billed_on', [
                    Carbon::parse($from)->startOfDay(),
                    Carbon::parse($to)->endOfDay()
                ]);
            })

            ->latest()
            ->paginate(10);

        return view('users.reports.sales', compact('orders','branches'));
    }

    public function download_excel(Request $request, $company, $branch)
    {
        $from = $request->from;
        $to   = $request->to;

        $orders = Order::with(['customer','billedBy','details.product.category','details.product.sub_category'])
            ->where('shop_id', Auth::user()->owner_id)

            ->when($branch != 0, fn($q) => $q->where('branch_id', $branch),
                fn($q) => $q->whereNull('branch_id'))

            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('billed_on', [
                    \Carbon\Carbon::parse($from)->startOfDay(),
                    \Carbon\Carbon::parse($to)->endOfDay()
                ]);
            })

            ->get();

        return Excel::download(new SalesReportExport($orders), 'sales_report_' . now()->format('d-m-Y_h-i A') . '.xlsx');
    }



    public function download_pdf(Request $request, $company, $branch)
    {
        $from = $request->from;
        $to   = $request->to;

        $orders = Order::with(['customer','billedBy','details.product.category','details.product.sub_category'])
            ->where('shop_id', Auth::user()->owner_id)

            ->when($branch != 0, fn($q) => $q->where('branch_id', $branch),
                fn($q) => $q->whereNull('branch_id'))

            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('billed_on', [
                    \Carbon\Carbon::parse($from)->startOfDay(),
                    \Carbon\Carbon::parse($to)->endOfDay()
                ]);
            })

            ->get();

        $pdf = Pdf::loadView('users.exports.sales_report_pdf', compact('orders'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('sales_report_' . now()->format('d-m-Y_h-i A') . '.pdf');
    }
}
