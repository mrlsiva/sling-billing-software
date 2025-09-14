<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPayment extends Model
{
    protected $fillable = [
        'vendor_id','payment_id','amount','paid_on','comment'
    ];

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }
}
