<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderRefund extends Model
{
    protected $fillable = [
        'purchase_order_id','vendor_id','old_amount','quantity','refunded_by','refund_amount','refund_on','reason','need_to_deduct'
    ];

    public function purchase_order()
    {
        return $this->belongsTo('App\Models\PurchaseOrder');
    }

    public function refundedBy()
    {
        return $this->belongsTo('App\Models\User','refunded_by');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
