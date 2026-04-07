<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BranchProductTransferReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ProductHistory;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class productTransferReportsController extends Controller
{
    use Log;

    public function transfer(Request $request)
    {

        $query = ProductHistory::with([
            'transfer_from',
            'transfer_to',
            'category',
            'sub_category',
            'product'
        ])->where('shop_id', Auth::user()->parent_id)->where('from', Auth::user()->id)->orWhere('to', Auth::user()->id);
            

        // Date filters
        if ($request->filled('from')) {
            $query->whereDate('transfer_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('transfer_on', '<=', $request->to);
        }

        $datas = $query->latest('transfer_on')->paginate(10);

        return view('branches.reports.transfer', compact('datas'));
    }

    public function download_excel(Request $request)
    {
        $query = ProductHistory::with([
            'transfer_from','transfer_to','category','sub_category','product'
        ])->where('shop_id', Auth::user()->parent_id)->where('from', Auth::user()->id)->orWhere('to', Auth::user()->id);

        if ($request->filled('from')) {
            $query->whereDate('transfer_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('transfer_on', '<=', $request->to);
        }

        $data = $query->get()->map(function ($item,$current_branch) {
            return [
                'Transfer Datetime' => \Carbon\Carbon::parse($item->transfer_on)->format('d M Y H:i'),
               'Type' => ($current_branch == 0)
                    ? ($item->to == Auth::user()->id ? 'Stock_In' : 'Stock_Out')
                    : ($item->to == $current_branch ? 'Stock_In' : 'Stock_Out'),
                'From Branch'       => $item->transfer_from->user_name ?? '',
                'To Branch'         => $item->transfer_to->user_name ?? '',
                'Category'          => $item->category->name ?? '',
                'Subcategory'       => $item->sub_category->name ?? '',
                'Item'              => $item->product->name ?? '',
                'Item Code'         => $item->product->code ?? '',
                'Quantity'          => $item->quantity,
            ];
        });

        return Excel::download(new \App\Exports\BranchProductTransferReportExport($data), 'transfer_report.xlsx');
    }

    public function download_pdf(Request $request)
    {

        $query = ProductHistory::with([
            'transfer_from','transfer_to','category','sub_category','product'
        ])->where('shop_id', Auth::user()->parent_id)->where('from', Auth::user()->id)->orWhere('to', Auth::user()->id);

        if ($request->filled('from')) {
            $query->whereDate('transfer_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('transfer_on', '<=', $request->to);
        }

        $datas = $query->get();

        $pdf = Pdf::loadView('branches.exports.transfer_report_pdf', compact('datas'));

        return $pdf->download('transfer_report.pdf');
    }
    
}
