<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesReportExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Date Time',
            'Issued By',
            'Sales By',
            'Customer',
            'Mobile',
            'Address',
            'Category',
            'Subcategory',
            'Item',
            'Item Code',
            'Qty',
            'Gross (₹)',
            'Tax (₹)',
            'Net (₹)',
        ];
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->orders as $order) {
            foreach ($order->details as $detail) {

                $refundedQty = 0;

                if ($order->is_refunded && $order->refunds) {
                    foreach ($order->refunds as $refund) {
                        foreach ($refund->details as $rDetail) {
                            if ($rDetail->product_id == $detail->product_id) {
                                $refundedQty += $rDetail->quantity;
                            }
                        }
                    }
                }

                $qty = max(0, $detail->quantity - $refundedQty);

                $gross = $detail->price * $qty;
                $tax   = $detail->tax_amount * $qty;
                $net   = ($detail->price - $detail->tax_amount) * $qty;

                $qtyDisplay = $qty;
                if ($refundedQty > 0) {
                    $qtyDisplay .= " (Refunded: {$refundedQty})";
                }

                $rows[] = [
                    $order->bill_id,
                    Date::PHPToExcel(Carbon::parse($order->billed_on)),
                    'User',
                    optional($order->billedBy)->name,
                    optional($order->customer)->name,
                    optional($order->customer)->phone,
                    optional($order->customer)->address,
                    optional($detail->product->category)->name,
                    optional($detail->product->sub_category)->name,
                    $detail->name,
                    $detail->product_id,
                    $qtyDisplay,
                    round($gross, 2),
                    round($tax, 2),
                    round($net, 2),
                ];
            }
        }

        return collect($rows);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            // or simply: 'B' => 'dd-mm-yyyy hh:mm',
        ];
    }
}