<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashOpeningSeed extends Model
{
    protected $fillable = [
        'shop_id',
        'branch_id',
        'opening_balance',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];
}
