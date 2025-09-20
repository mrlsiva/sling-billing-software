<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class CustomerExport implements FromCollection, WithHeadings
{
    protected $customers;

    public function __construct($customers)
    {
        $this->customers = $customers;
    }

    public function collection()
    {
        return $this->customers->map(function ($customer) {
            return [
                'Name'       => $customer->name,
                'Phone'      => $customer->phone,
                'Alt Phone'  => $customer->alt_phone ?? '-',
                'Address'    => $customer->address ?? '-',
                'Pincode'    => $customer->pincode ?? '-',
                'Gender'     => $customer->gender->name ?? '-',
                'Date of Birth' => $customer->dob ? Carbon::parse($customer->dob)->format('d M Y') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Name',
            'Phone',
            'Alt Phone',
            'Address',
            'Pincode',
            'Gender',
            'Date of Birth',
        ];
    }
}
