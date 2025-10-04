<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrderExport;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use App\Traits\Log;
use DB;

class orderReportsController extends Controller
{
    use Log;

    public function order(Request $request,$company,$branch)
    {

        $branches = User::where([['parent_id',Auth::user()->id],['is_active',1],['is_lock',0],['is_delete',0]])->get();

        $query = Order::where('shop_id', Auth::user()->id);

        // If a specific branch is selected
        if ($branch != 0) {
            $query->where('branch_id', $branch);
        }

        // Date filters
        if ($request->filled('from')) {
            $query->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $query->orderBy('id', 'desc')->paginate(10);

        return view('users.reports.order',compact('orders','branches'));
    }

    public function download_excel(Request $request, $company, $branch)
    {
        $query = Order::where('shop_id', Auth::user()->id);

        if ($branch != 0) {
            $query->where('branch_id', $branch);
        }

        if ($request->filled('from')) {
            $query->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $query->orderBy('id', 'desc')->get();

        return Excel::download(new OrderExport($orders), 'orders_report.xlsx');
    }

    public function download_pdf(Request $request, $company, $branch)
    {
        $query = Order::where('shop_id', Auth::user()->id);

        if ($branch != 0) {
            $query->where('branch_id', $branch);
        }

        if ($request->filled('from')) {
            $query->whereDate('billed_on', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('billed_on', '<=', $request->to);
        }

        $orders = $query->orderBy('id', 'desc')->get();

        $totalOrders = $orders->count();
        $totalSales  = $orders->sum('bill_amount');
        $user        = Auth::user();

        $pdf = Pdf::loadView('users.exports.order_pdf', compact('orders', 'totalOrders', 'totalSales', 'user'))->setPaper('a4', 'landscape');

        return $pdf->download('orders.pdf');
    }

}
