<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class SalesReportExport implements FromCollection
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->orders as $order) {
            foreach ($order->details as $detail) {

                $qty   = $detail->quantity;
                $net   = ($detail->price - $detail->tax_amount) * $detail->quantity;
                $tax   = $detail->tax_amount * $detail->quantity;
                $gross = $detail->price * $detail->quantity;

                $rows[] = [
                    'Order ID' => $order->bill_id,
                    'Date Time' => \Carbon\Carbon::parse($order->billed_on)->format('d M Y H:i'),
                    'Issued By' => 'User',
                    'Sales By' => optional($order->billedBy)->name,
                    'Customer' => optional($order->customer)->name,
                    'Mobile' => optional($order->customer)->phone,
                    'Address' => optional($order->customer)->address,
                    'Category' => optional($detail->product->category)->name,
                    'Subcategory' => optional($detail->product->sub_category)->name,
                    'Item' => $detail->name,
                    'Item Code' => $detail->product_id,
                    'Qty' => $qty,
                    'Gross' => round($gross, 2),
                    'Tax' => round($tax, 2),
                    'Net' => round($net, 2),
                ];
            }
        }

        return collect($rows);
    }
}
