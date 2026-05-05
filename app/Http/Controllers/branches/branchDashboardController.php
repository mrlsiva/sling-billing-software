<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;

class branchDashboardController extends Controller
{
    private function getData(Request $request)
    {
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : today()->startOfDay();
        $toDate   = $request->to_date   ? Carbon::parse($request->to_date)->endOfDay()     : today()->endOfDay();

        $branchId = Auth::user()->id;

        return [
            'date_orders'        => Order::where('branch_id', $branchId)->whereBetween('billed_on', [$fromDate, $toDate])->count(),
            'date_order_amount'  => Order::where('branch_id', $branchId)->whereBetween('billed_on', [$fromDate, $toDate])->sum('bill_amount'),
            'total_orders'       => Order::where('branch_id', $branchId)->count(),
            'total_order_amount' => Order::where('branch_id', $branchId)->sum('bill_amount'),
            'from_date'          => $fromDate,
            'to_date'            => $toDate,
        ];
    }

    public function index(Request $request)
    {
        $auth = User::where('id', Auth::user()->id)->with(['user_detail', 'bank_detail'])->first();
        $data = $this->getData($request);

        return view('branches.dashboard', array_merge(compact('auth'), $data));
    }

    public function pdf(Request $request)
    {
        $auth = User::where('id', Auth::user()->id)->with(['user_detail', 'bank_detail'])->first();
        $data = $this->getData($request);

        $pdf = Pdf::loadView('branches.dashboard_pdf', array_merge(compact('auth'), $data))
                  ->setPaper('a4', 'portrait');

        $filename = 'branch_dashboard_' . $data['from_date']->format('d-m-Y') . '_to_' . $data['to_date']->format('d-m-Y') . '.pdf';

        return $pdf->download($filename);
    }
}
