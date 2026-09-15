<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashHandover extends Model
{
    protected $fillable = [
        'shop_id',
        'branch_id',
        'amount',
        'remarks',
    ];
}
