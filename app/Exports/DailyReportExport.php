<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DailyReportExport implements FromView, WithEvents
{
    protected $orders;
    protected $purchases;
    protected $payments;
    protected $refunds;

    public function __construct($orders,$purchases,$payments,$refunds,$productIn,$productOut,$productInAmount,$productOutAmount,$paymentSummary,$totalSales,$date,$credit_amount)
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
        $this->date = $date;
        $this->credit_amount = $credit_amount;
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
            'profit'=>$profit,
            'date' => $this->date,
            'credit_amount' => $this->credit_amount
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $row = 1;

                // Summary
                $row += 12;

                // Purchase Report
                if ($this->purchases->isNotEmpty()) {

                    $row += 3;

                    foreach ($this->purchases as $purchase) {

                        $sheet->setCellValue(
                            'G' . $row,
                            Date::dateTimeToExcel(
                                Carbon::parse($purchase->invoice_date)->startOfDay()
                            )
                        );

                        $sheet->getStyle('G' . $row)
                            ->getNumberFormat()
                            ->setFormatCode('dd-mm-yyyy');

                        $row++;
                    }

                    $row += 2;
                }

                // Vendor Payment Report
                if ($this->payments->isNotEmpty()) {

                    $row += 3;

                    foreach ($this->payments as $payment) {

                        $sheet->setCellValue(
                            'E' . $row,
                            Date::dateTimeToExcel(
                                Carbon::parse($payment->paid_on)->startOfDay()
                            )
                        );

                        $sheet->getStyle('E' . $row)
                            ->getNumberFormat()
                            ->setFormatCode('dd-mm-yyyy');

                        $row++;
                    }

                    $row += 2;
                }

                // Purchase Refund Report
                if ($this->refunds->isNotEmpty()) {

                    $row += 3;

                    foreach ($this->refunds as $refund) {

                        $sheet->setCellValue(
                            'G' . $row,
                            Date::dateTimeToExcel(
                                Carbon::parse($refund->refund_on)->startOfDay()
                            )
                        );

                        $sheet->getStyle('G' . $row)
                            ->getNumberFormat()
                            ->setFormatCode('dd-mm-yyyy');

                        $row++;
                    }

                    $row += 2;
                }

                // Product IN
                if ($this->productIn->isNotEmpty()) {
                    $row += $this->productIn->count() + 3;
                }

                // Product OUT
                if ($this->productOut->isNotEmpty()) {
                    $row += $this->productOut->count() + 3;
                }

                // Order Report
                if ($this->orders->isNotEmpty()) {

                    $row += 3;

                    foreach ($this->orders as $order) {

                        $sheet->setCellValue(
                            'E' . $row,
                            Date::dateTimeToExcel(
                                Carbon::parse($order->billed_on)->startOfDay()
                            )
                        );

                        $sheet->getStyle('E' . $row)
                            ->getNumberFormat()
                            ->setFormatCode('dd-mm-yyyy');

                        $row++;
                    }
                }
            },
        ];
    }
}
