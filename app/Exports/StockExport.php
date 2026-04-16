<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $stocks;

    public function __construct($stocks)
    {
        $this->stocks = $stocks;
    }

    public function collection()
    {
        return $this->stocks;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Category',
            'Product',
            'Metric',
            'Price (₹)',
            'Stock',
            'Total Price (₹)',
            'IMEI',
            'Variations',
        ];
    }

    public function map($stock): array
    {
        static $i = 1;
        $variation = \App\Models\StockVariation::where('stock_id', $stock->id)->first();
        // ✅ Build variations string
        if($variation && ($variation->size_id !== null || $variation->colour_id !== null)){
            $variationText = $stock->variations->map(function ($v, $key) {
                return ($key + 1) . '. ' .
                    ($v->size->name ?? '-') . ' / ' .
                    ($v->colour->name ?? '-') .
                    ' (Qty: ' . $v->quantity . ')';
            })->implode("\n"); // line break inside Excel cell
        } else {
            $variationText = 'No variations';
        }

        return [
            $i++,
            optional($stock->category)->name . ' - ' . optional($stock->sub_category)->name,
            optional($stock->product)->name,
            optional($stock->product->metric)->name ?? '-',
            $stock->product->price ?? 0,
            $stock->quantity,
            number_format(($stock->product->price ?? 0) * $stock->quantity, 2),
            $stock->imei ?? '-',
            $variationText,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            'I' => [ // column for Variations
                'alignment' => [
                    'wrapText' => true,
                ],
            ],
        ];
    }
}