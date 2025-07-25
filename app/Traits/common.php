<?php

namespace App\Traits;
use App\Models\User;
use Request;

trait common

{

    public static function userUnique()
    {
        // Get the maximum number from existing unique_ids like "B-00001"
        $max = User::where('unique_id', 'like', 'U-%')
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(unique_id, '-', -1) AS UNSIGNED)) as max_number")
            ->value('max_number');

        // If no existing ID found, start from 1
        $number = $max ? $max + 1 : 1;

        // Format as F-00001, F-00002, etc.
        return 'U-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }


}