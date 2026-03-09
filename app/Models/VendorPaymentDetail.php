<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorPaymentDetail extends Model
{
    protected $fillable = [
        'vendor_payment_id','purchase_order_id','payment_id','amount','paid_on','comment'
    ];

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(\App\Models\PurchaseOrder::class);
    }

    public function vendorPayment()
    {
        return $this->belongsTo(\App\Models\VendorPayment::class);
    }
}
