<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $fillable = [
        'order_id','refunded_by','refund_amount','refund_on','reason','payment_id','payment_info'
    ];

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    public function refunded_by()
    {
        return $this->belongsTo('App\Models\Staff','refunded_by');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }
}
