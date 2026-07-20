<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BranchDailyReportExport implements FromView, WithEvents
{
    protected $orders, $productIn, $productOut;
    protected $productInAmount, $productOutAmount;

    public function __construct($orders,$productIn,$productOut,$productInAmount,$productOutAmount,$totalSales,$date,$credit_amount)
    {
        $this->orders = $orders;
        $this->productIn = $productIn;
        $this->productOut = $productOut;
        $this->productInAmount = $productInAmount;
        $this->productOutAmount = $productOutAmount;
        $this->totalSales = $totalSales;
        $this->date = $date;
        $this->credit_amount = $credit_amount;
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
            'date'=> $this->date,
            'credit_amount'=> $this->credit_amount,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Row where Order Report data starts
                $row = 12;

                if ($this->orders->isNotEmpty()) {

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
