<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $fillable = [
        'order_payment_detail_id','amount','remaining_amount','status'
    ];

    public function order_payment_detail()
    {
        return $this->belongsTo('App\Models\OrderPaymentDetail','order_payment_detail_id');
    }

    public function creditPayments()
    {
        return $this->hasMany(CreditPayment::class, 'credit_id');
    }

    
}
