<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorOverDuePayment extends Model
{
    protected $fillable = [
        'vendor_over_due_id','amount','paid_on','comment'
    ];
}
