<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BranchDailyReportExport implements FromView
{
    protected $orders, $productIn, $productOut;
    protected $productInAmount, $productOutAmount;

    public function __construct($orders,$productIn,$productOut,$productInAmount,$productOutAmount,$totalSales)
    {
        $this->orders = $orders;
        $this->productIn = $productIn;
        $this->productOut = $productOut;
        $this->productInAmount = $productInAmount;
        $this->productOutAmount = $productOutAmount;
        $this->totalSales = $totalSales;
    }

    public function view(): View
    {
        //$totalSales = $this->orders->sum('bill_amount');

        return view('branches.exports.daily_report',[
            'orders'=>$this->orders,
            'totalSales'=>$this->totalSales,
            'productIn'=>$this->productIn,
            'productOut'=>$this->productOut,
            'productInAmount'=>$this->productInAmount,
            'productOutAmount'=>$this->productOutAmount,
        ]);
    }
}
