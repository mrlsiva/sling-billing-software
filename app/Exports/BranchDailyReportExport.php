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

    public function __construct($orders,$productIn,$productOut,$productInAmount,$productOutAmount,$totalSales,$date,$credit_amount,$expense_amount,$cash_summary,$extra = [])
    {
        $this->orders = $orders;
        $this->productIn = $productIn;
        $this->productOut = $productOut;
        $this->productInAmount = $productInAmount;
        $this->productOutAmount = $productOutAmount;
        $this->totalSales = $totalSales;
        $this->date = $date;
        $this->credit_amount = $credit_amount;
        $this->expense_amount = $expense_amount;
        $this->cash_summary = $cash_summary;
        $this->extra = $extra;
    }

    public function view(): View
    {
        //$totalSales = $this->orders->sum('bill_amount');

        return view('branches.exports.daily_report', array_merge([
            'orders'=>$this->orders,
            'totalSales'=>$this->totalSales,
            'productIn'=>$this->productIn,
            'productOut'=>$this->productOut,
            'productInAmount'=>$this->productInAmount,
            'productOutAmount'=>$this->productOutAmount,
            'date'=> $this->date,
            'credit_amount'=> $this->credit_amount,
            'expense_amount'=> $this->expense_amount,
            'cash_summary'=> $this->cash_summary,
        ], $this->extra));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Row where Order Report data starts. The summary table above it has a
                // fixed set of rows plus one dynamic "OS Recd in <Mode>" row per active
                // payment mode — computed here so this never drifts out of sync if a
                // payment mode is added/removed later.
                $fixedSummaryRows = 17; // header + Opening/Today/Monthly Sales, Product In/Out,
                                         // Cash/Card/Cheque/UPI/Finance/Exchange, Credit, Discount,
                                         // Expenses, Cash Given to HO, Cash Balance
                $osRecdRows = isset($this->extra['os_recd_summary']) ? $this->extra['os_recd_summary']->count() : 0;
                $blankAndOrderReportHeaderRows = 6; // 3 blank rows + section header + column header + 1

                $row = $fixedSummaryRows + $osRecdRows + $blankAndOrderReportHeaderRows;

                if ($this->orders->isNotEmpty()) {

                    foreach ($this->orders as $order) {

                        $sheet->setCellValue(
                            'E' . $row,
                            Date::dateTimeToExcel(
                                Carbon::parse($order->billed_on)
                            )
                        );

                        $sheet->getStyle('E' . $row)
                            ->getNumberFormat()
                            ->setFormatCode('dd-mm-yyyy hh:mm AM/PM');

                        $row++;
                    }
                }
            },
        ];
    }
}
