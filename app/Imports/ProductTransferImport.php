<?php

namespace App\Imports;

use App\Models\ProductTransfer;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductTransferImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new ProductTransfer([
            //
        ]);
    }
}
