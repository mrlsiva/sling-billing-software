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

    public function vendor()
    {
        return $this->belongsTo(\App\Models\Vendor::class);
    }

    public function details()
    {
        return $this->hasMany(\App\Models\VendorPaymentDetail::class);
    }
}
