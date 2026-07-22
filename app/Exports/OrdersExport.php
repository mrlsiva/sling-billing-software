<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OrdersExport implements FromView, WithEvents
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function view(): View
    {
        return view('branches.exports.order', [
            'orders' => $this->orders
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                foreach ($this->orders as $index => $order) {

                    $row = $index + 2; // Header is row 1

                    $sheet->setCellValue(
                        'D' . $row,
                        Date::dateTimeToExcel(
                            Carbon::parse($order->billed_on)->startOfDay()
                        )
                    );

                    $sheet->getStyle('D' . $row)
                        ->getNumberFormat()
                        ->setFormatCode('dd-mm-yyyy');
                }
            },
        ];
    }
}