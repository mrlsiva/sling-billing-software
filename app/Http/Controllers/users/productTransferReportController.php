<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductTransferReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ProductHistory;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class productTransferReportController extends Controller
{
    use Log;

    public function transfer(Request $request, $company, $branch)
    {
        $branches = User::where([
            ['parent_id', Auth::user()->owner_id],
            ['is_active', 1],
            ['is_lock', 0],
            ['is_delete', 0]
        ])->get();

        $query = ProductHistory::with([
            'transfer_from',
            'transfer_to',
            'category',
            'sub_category',
            'product'
        ]);

        // Branch filter
        if ($branch && $branch != 0) {
            $query->where(function ($q) use ($branch) {
                $q->where('from', $branch)
                  ->orWhere('to', $branch);
            });
        }

        // Date filters
        if ($request->filled('from')) {
            $query->whereDate('transfer_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('transfer_on', '<=', $request->to);
        }

        $datas = $query->latest('transfer_on')->paginate(10);

        return view('users.reports.transfer', compact('datas', 'branches', 'branch'));
    }

    public function download_excel(Request $request, $company, $branch)
    {
        $current_branch = $branch;
        $query = ProductHistory::with([
            'transfer_from','transfer_to','category','sub_category','product'
        ]);

        if ($branch && $branch != 0) {
            $query->where(function ($q) use ($branch) {
                $q->where('from', $branch)
                  ->orWhere('to', $branch);
            });
        }

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

        return Excel::download(new \App\Exports\ProductTransferReportExport($data), 'transfer_report.xlsx');
    }

    public function download_pdf(Request $request, $company, $branch)
    {
        $current_branch = $branch;

        $query = ProductHistory::with([
            'transfer_from','transfer_to','category','sub_category','product'
        ]);

        if ($branch && $branch != 0) {
            $query->where(function ($q) use ($branch) {
                $q->where('from', $branch)
                  ->orWhere('to', $branch);
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('transfer_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('transfer_on', '<=', $request->to);
        }

        $datas = $query->get();

        $pdf = Pdf::loadView('users.exports.transfer_report_pdf', compact('datas','current_branch'));

        return $pdf->download('transfer_report.pdf');
    }
}
