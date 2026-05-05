<?php

namespace App\Http\Controllers\users;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;

class dashboardController extends Controller
{
    private function getBranchData(Request $request)
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : today()->startOfDay();
        $toDate   = $request->to_date   ? Carbon::parse($request->to_date)->endOfDay()     : today()->endOfDay();

        $branches = User::where('parent_id', Auth::user()->owner_id)->get()->map(function ($branch) use ($fromDate, $toDate) {
            $branch->date_orders  = Order::where('branch_id', $branch->id)->whereBetween('billed_on', [$fromDate, $toDate])->count();
            $branch->date_sales   = Order::where('branch_id', $branch->id)->whereBetween('billed_on', [$fromDate, $toDate])->sum('bill_amount');
            $branch->total_orders = Order::where('branch_id', $branch->id)->count();
            $branch->total_sales  = Order::where('branch_id', $branch->id)->sum('bill_amount');
            return $branch;
        });

        return [
            'branches'     => $branches,
            'date_orders'  => $branches->sum('date_orders'),
            'date_sales'   => $branches->sum('date_sales'),
            'total_orders' => $branches->sum('total_orders'),
            'total_sales'  => $branches->sum('total_sales'),
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
        ];
    }

    public function index(Request $request)
    {
        $auth = User::where('id', Auth::user()->owner_id)->with(['user_detail', 'bank_detail'])->first();
        $data = $this->getBranchData($request);

        return view('users.dashboard', array_merge(compact('auth'), $data));
    }

    public function pdf(Request $request)
    {
        $auth = User::where('id', Auth::user()->owner_id)->with(['user_detail', 'bank_detail'])->first();
        $data = $this->getBranchData($request);

        $pdf = Pdf::loadView('users.dashboard_pdf', array_merge(compact('auth'), $data))
                  ->setPaper('a4', 'portrait');

        $filename = 'dashboard_report_' . $data['from_date']->format('d-m-Y') . '_to_' . $data['to_date']->format('d-m-Y') . '.pdf';

        return $pdf->download($filename);
    }
}
