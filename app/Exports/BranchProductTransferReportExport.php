<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BranchProductTransferReportExport implements FromCollection, WithHeadings
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
}
