<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoriesExport implements FromCollection, WithHeadings
{
    protected $categories;

    public function __construct($categories)
    {
        $this->categories = $categories;
    }

    public function collection()
    {
        // Map the categories with their subcategories
        return $this->categories->map(function ($category) {
            return [
                'Category Name' => $category->name,
                'Sub Category' => $category->sub_categories->pluck('name')->implode(', '),
                'Status' => $category->is_active == 1 ? 'Active' : 'Inactive',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Category',
            'Sub Category',
            'Status'
        ];
    }
}
