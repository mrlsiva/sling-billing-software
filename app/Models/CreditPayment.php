<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    protected $fillable = [
        'credit_id','payment_id','amount','paid_on'
    ];

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment','payment_id');
    }
}
