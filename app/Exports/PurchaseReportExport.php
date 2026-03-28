<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseReportExport implements FromCollection, WithHeadings
{
    protected $datas;

    public function __construct($datas)
    {
        $this->datas = $datas;
    }

    public function headings(): array
    {
        return [
            'Purchase Datetime',
            'Type',
            'Invoice No',
            'Invoice Date',
            'Due Date',
            'Vendor',
            'Category',
            'Sub Category',
            'Item',
            'Item Code',
            'Qty',
            'Base Rate (₹)',
            'Value (₹)',
            'GST (₹)',
            'Base Value (₹)',
            'NLC (₹)',
        ];
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->datas as $data) {

            $qty = $data->quantity;

            $value = $data->gross_cost;
            $gst   = $data->gross_cost - $data->net_cost;
            $base  = $data->net_cost;
            $nlc   = $qty > 0 ? ($value / $qty) : 0;

            $rows[] = [
                \Carbon\Carbon::parse($data->created_at)->format('d M Y H:i'),
                'Purchase Ordered',
                $data->invoice_no,
                \Carbon\Carbon::parse($data->invoice_date)->format('d M Y'),
                \Carbon\Carbon::parse($data->due_date)->format('d M Y'),
                optional($data->vendor)->name,
                optional($data->category)->name,
                optional($data->sub_category)->name,
                optional($data->product)->name,
                optional($data->product)->code,
                $qty,
                $data->price_per_unit,
                round($value, 2),
                round($gst, 2),
                round($base, 2),
                round($nlc, 2),
            ];
        }

        return collect($rows);
    }
}