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
    protected int $i = 1;

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
            'Sub Category',
            'Product',
            'Product Code',
            'Price (₹)',
            'Stock',
            'Total Price (₹)',
            'IMEI',
            'Variations',
        ];
    }

    public function map($stock): array
    {
        $hasVariations = $stock->variations->contains(
            fn($v) => $v->size_id !== null || $v->colour_id !== null
        );

        if ($hasVariations) {
            $variationText = $stock->variations->map(function ($v, $key) {
                return ($key + 1) . '. ' .
                    ($v->size?->name ?? '-') . ' / ' .
                    ($v->colour?->name ?? '-') .
                    ' (Qty: ' . $v->quantity . ')';
            })->implode("\n");
        } else {
            $variationText = 'No variations';
        }

        $price = $stock->product?->price ?? 0;

        return [
            $this->i++,
            optional($stock->category)->name   ?? '-',
            optional($stock->sub_category)->name ?? '-',
            optional($stock->product)->name     ?? '-',
            $stock->product?->code              ?? '-',
            $price,
            $stock->quantity,
            number_format($price * $stock->quantity, 2),
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