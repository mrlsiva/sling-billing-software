<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromQuery, WithHeadings, WithChunkReading, WithMapping
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function map($product): array
    {
        return [
            $product->name,
            $product->code,
            $product->hsn_code,
            $product->category->name ?? '-',
            $product->sub_category->name ?? '-',
            $product->description,
            $product->metric->name ?? '-',
            number_format($product->price, 2),
            $product->discount_type == 1 ? 'Flat' : ($product->discount_type == 2 ? 'Percentage' : '-'),
            $product->discount !== null ? number_format($product->discount, 2) : '-',
            $product->tax ? $product->tax->name . '%' : '-',
            $product->is_active == 1 ? 'Active' : 'Inactive',
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'HSN Code',
            'Category',
            'SubCategory',
            'Description',
            'Metric',
            'Price',
            'Discount Type',
            'Discount',
            'Tax',
            'Status',
        ];
    }
}
