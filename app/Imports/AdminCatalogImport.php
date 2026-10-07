<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Only parses the sheet into rows (Category | Sub Category | Products). The demo
 * catalog import in shopSetupController does the actual merge into the draft.
 */
class AdminCatalogImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public Collection $rows;

    public function collection(Collection $rows)
    {
        $this->rows = $rows;
    }
}
