<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Only parses the sheet into rows. The admin shop controller does the actual
 * creation (logo/icon, password, related records), since one row can need
 * several models and a generated password shown once — more than a single
 * ToModel row-to-model mapping can express.
 */
class AdminShopImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public Collection $rows;

    public function collection(Collection $rows)
    {
        $this->rows = $rows;
    }
}
