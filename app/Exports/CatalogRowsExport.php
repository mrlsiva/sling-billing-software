<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Category | Sub Category | Products (comma-separated product names).
 * One row per sub-category. Used for both the blank template and a
 * download of the current demo draft (so editing and re-uploading works).
 */
class CatalogRowsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function map($row): array
    {
        return [$row['category'], $row['sub_category'], $row['products']];
    }

    public function headings(): array
    {
        return ['Category', 'Sub Category', 'Products'];
    }
}
