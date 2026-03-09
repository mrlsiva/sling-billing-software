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

    public function __construct($orders,$purchases,$payments,$refunds)
    {
        $this->orders = $orders;
        $this->purchases = $purchases;
        $this->payments = $payments;
        $this->refunds = $refunds;
    }

    public function view(): View
    {
        $totalSales = $this->orders->sum('bill_amount');
        $totalPurchase = $this->purchases->sum('gross_cost');
        $totalVendorPaid = $this->payments->sum('amount');
        $totalRefund = $this->refunds->sum('refund_amount');
        $profit = $totalSales - $totalPurchase + $totalRefund;

        return view('users.exports.daily_report',[
            'orders'=>$this->orders,
            'purchases'=>$this->purchases,
            'payments'=>$this->payments,
            'refunds'=>$this->refunds,
            'totalSales'=>$totalSales,
            'totalPurchase'=>$totalPurchase,
            'totalVendorPaid'=>$totalVendorPaid,
            'totalRefund'=>$totalRefund,
            'profit'=>$profit
        ]);
    }
}
