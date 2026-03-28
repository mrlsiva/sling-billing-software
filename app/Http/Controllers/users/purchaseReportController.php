<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\PurchaseReportExport;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class purchaseReportController extends Controller
{
    use Log;

    public function purchase(Request $request)
    {
        $query = PurchaseOrder::query()->where('shop_id',Auth::user()->owner_id)
            ->with(['vendor', 'category', 'sub_category', 'product']);

        // Date filters
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $datas = $query->with(['vendor','category','sub_category','product'])
               ->latest()
               ->paginate(10);

        return view('users.reports.purchase', compact('datas'));
    }

    public function download_excel(Request $request, $company)
    {
        $from = $request->from;
        $to   = $request->to;

        $datas = PurchaseOrder::with(['vendor','category','sub_category','product'])->where('shop_id',Auth::user()->owner_id)
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [
                    \Carbon\Carbon::parse($from)->startOfDay(),
                    \Carbon\Carbon::parse($to)->endOfDay()
                ]);
            })
            ->get();

        return Excel::download(new PurchaseReportExport($datas), 'purchase_report.xlsx');
    }

    public function download_pdf(Request $request, $company)
    {
        $from = $request->from;
        $to   = $request->to;

        $datas = PurchaseOrder::with(['vendor','category','sub_category','product'])->where('shop_id',Auth::user()->owner_id)
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [
                    \Carbon\Carbon::parse($from)->startOfDay(),
                    \Carbon\Carbon::parse($to)->endOfDay()
                ]);
            })
            ->get();

        $pdf = Pdf::loadView('users.exports.purchase_report_pdf', compact('datas'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('purchase_report.pdf');
    }
    
}
