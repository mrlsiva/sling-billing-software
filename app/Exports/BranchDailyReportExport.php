<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BranchDailyReportExport implements FromView
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function view(): View
    {
        $totalSales = $this->orders->sum('bill_amount');

        return view('branches.exports.daily_report',[
            'orders'=>$this->orders,
            'totalSales'=>$totalSales,
        ]);
    }
}
