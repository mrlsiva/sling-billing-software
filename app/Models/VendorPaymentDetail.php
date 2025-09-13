<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPaymentDetail extends Model
{
    protected $fillable = [
        'purchase_order_id','payment_id','amount','paid_on','comment'
    ];

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }
}
