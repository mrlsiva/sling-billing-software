<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\BranchDailyReportExport;
use App\Models\ProductHistory;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class dailyReportController extends Controller
{
    use Log;

    public function daily(Request $request, $company)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        /*
        |--------------------------------------------------
        | Orders
        |--------------------------------------------------
        */

        $orderQuery = Order::where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id)
            ->whereDate('billed_on', $date);

        // ✅ Correct total (before pagination)
        $totalSales = $orderQuery->sum('bill_amount');

        $orders = $orderQuery
            ->with([
                'branch',
                'shop',
                'customer',
                'billedBy',
                'payments.payment' // ✅ IMPORTANT for mode of payment
            ])
            ->orderByDesc('id')
            ->paginate(10);

        /*
        |--------------------------------------------------
        | Product History (IN / OUT)
        |--------------------------------------------------
        */

        $productIn = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('to', Auth::user()->id)
            ->get();

        $productOut = ProductHistory::with('product')
            ->whereDate('transfer_on', $date)
            ->where('from', Auth::user()->id)
            ->get();

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

        return view('branches.reports.daily', compact(
            'orders',
            'totalSales',
            'productIn',
            'productOut',
            'productInAmount',
            'productOutAmount'
        ));
    }

    public function download_excel(Request $request, $company)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        $orderQuery = Order::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id);

        $orders = $orderQuery
            ->whereDate('billed_on', $date)
            ->with(['branch','customer','billedBy'])
            ->get();

        return Excel::download(
            new BranchDailyReportExport($orders),
            'daily_report.xlsx'
        );
    }

    public function download_pdf(Request $request)
    {
        $date = $request->date ?? Carbon::today()->toDateString();

        $orderQuery = Order::where('shop_id', Auth::user()->parent_id)->where('branch_id', Auth::user()->id);

        $orders = $orderQuery
            ->whereDate('billed_on', $date)
            ->with(['branch','customer','billedBy'])
            ->get();

        $totalSales = $orders->sum('bill_amount');


        $user = Auth::user();

        $pdf = Pdf::loadView('branches.exports.daily_report_pdf', compact(
            'orders','totalSales'
        ))->setPaper('a4','landscape');

        return $pdf->download('daily_report.pdf');
    }
}
