<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DailyReportExport implements FromView
{
    protected $orders;
    protected $purchases;
    protected $payments;
    protected $refunds;

    public function __construct($orders,$purchases,$payments,$refunds,$productIn,$productOut,$productInAmount,$productOutAmount,$paymentSummary,$totalSales)
    {
        $this->orders = $orders;
        $this->purchases = $purchases;
        $this->payments = $payments;
        $this->refunds = $refunds;
        $this->productIn = $productIn;
        $this->productOut = $productOut;
        $this->productInAmount = $productInAmount;
        $this->productOutAmount = $productOutAmount;
        $this->paymentSummary = $paymentSummary;
        $this->totalSales = $totalSales;
    }

    public function view(): View
    {
        //$totalSales = $this->orders->sum('bill_amount');
        $totalPurchase = $this->purchases->sum('gross_cost');
        $totalVendorPaid = $this->payments->sum('amount');
        $totalRefund = $this->refunds->sum('refund_amount');
        $profit = $this->totalSales - $totalPurchase + $totalRefund;

        return view('users.exports.daily_report',[
            'orders'=>$this->orders,
            'purchases'=>$this->purchases,
            'payments'=>$this->payments,
            'refunds'=>$this->refunds,
            'productIn'=>$this->productIn,
            'productOut'=>$this->productOut,
            'productInAmount'=>$this->productInAmount,
            'productOutAmount'=>$this->productOutAmount,
            'paymentSummary'=>$this->paymentSummary,
            'totalSales'=>$this->totalSales,
            'totalPurchase'=>$totalPurchase,
            'totalVendorPaid'=>$totalVendorPaid,
            'totalRefund'=>$totalRefund,
            'profit'=>$profit
        ]);
    }
}
