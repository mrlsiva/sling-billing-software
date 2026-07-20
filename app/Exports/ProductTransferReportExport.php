<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ProductTransferReportExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    protected $data;

    public function __construct($data)
    {
        $this->data = collect($data);
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Transfer Datetime',
            'Type',
            'From Branch',
            'To Branch',
            'Category',
            'Subcategory',
            'Item',
            'Item Code',
            'Quantity',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => 'dd-mm-yyyy',
        ];
    }
}
