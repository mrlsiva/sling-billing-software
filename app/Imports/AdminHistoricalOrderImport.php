<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Only parses the sheet into rows. The admin order controller does the actual
 * creation (shop/branch/customer lookups, the order and its payment row),
 * since each row can span a different shop.
 */
class AdminHistoricalOrderImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public Collection $rows;

    public function collection(Collection $rows)
    {
        $this->rows = $rows;
    }
}
