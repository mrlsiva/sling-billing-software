<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubCategoryExport implements FromCollection, WithHeadings
{
    protected $sub_categories;

    public function __construct($sub_categories)
    {
        $this->sub_categories = $sub_categories;
    }

    public function collection()
    {
        return $this->sub_categories->map(function ($sub) {
            return [
                'Sub Category' => $sub->name,
                'Category'    => $sub->category->name ?? '-', // related category
                'Status'           => $sub->is_active == 1 ? 'Active' : 'Inactive',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sub Category',
            'Category',
            'Status',
        ];
    }
}
