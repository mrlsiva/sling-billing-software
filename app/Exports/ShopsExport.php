<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ShopsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $shops;

    public function __construct($shops)
    {
        $this->shops = $shops;
    }

    public function collection()
    {
        return $this->shops;
    }

    public function map($shop): array
    {
        return [
            $shop->id,
            $shop->name,
            $shop->slug_name,
            $shop->user_name,
            $shop->phone,
            $shop->alt_phone,
            $shop->email,
            optional($shop->user_detail)->gst,
            optional($shop->user_detail)->address,
            $shop->able_to_login == 1 ? 'Yes' : 'No',
            $shop->is_active == 1 ? 'Active' : 'Inactive',
            $shop->is_lock == 1 ? 'Locked' : 'Unlocked',
            optional($shop->created_at)->format('d-m-Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Slug Name',
            'User Name',
            'Phone',
            'Alternate Phone',
            'Email',
            'GST',
            'Address',
            'Online Login',
            'Status',
            'Lock',
            'Created On',
        ];
    }
}
