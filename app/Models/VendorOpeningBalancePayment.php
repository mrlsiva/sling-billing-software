<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOpeningBalancePayment extends Model
{
    protected $fillable = [
        'vendor_opening_balance_id','amount','paid_on','comment'
    ];
}
