<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromArray,
    WithHeadings,
    ShouldAutoSize
};

class ProductTransferErrorExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * Data to write in Excel
     */
    public function array(): array
    {
        return $this->errors;
    }

    /**
     * Excel column headings
     */
    public function headings(): array
    {
        return [
            'Row Number',
            'Category',
            'Product',
            'Error Message',
        ];
    }
}
