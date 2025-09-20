<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings
{
    protected $products;

    public function __construct($products)
    {
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products->map(function ($product) {
            return [
                'Name'            => $product->name,
                'Code'            => $product->code,
                'HSN Code'        => $product->hsn_code,
                'Category'        => $product->category->name ?? '-',
                'Sub Category'    => $product->sub_category->name ?? '-',
                'Description'     => $product->description,
                'Metric'          => $product->metric->name ?? '-',
                'Quanity'         => $product->quantiy ?? '-',
                'Price'           => number_format($product->price, 2),
                'Discount Type'   => $product->discount_type == 1 ? 'Flat' : ($product->discount_type == 2 ? 'Percentage' : '-'),
                'Discount'        => $product->discount !== null ? number_format($product->discount, 2) : '-',
                'Tax' => $product->tax ? $product->tax->name . '%' : '-',
                'Status'          => $product->is_active == 1 ? 'Active' : 'Inactive',
            ];
        });
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
            'Quanity',
            'Price',
            'Discount Type',
            'Discount',
            'Tax',
            'Status',
        ];
    }
}
