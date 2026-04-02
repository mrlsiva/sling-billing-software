<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOverDue extends Model
{
    protected $fillable = [
        'shop_id','vendor_id','amount','remaining_amount'
    ];
}
