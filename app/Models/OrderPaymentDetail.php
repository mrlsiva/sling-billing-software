<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentDetail extends Model
{
    protected $fillable = [
        'order_id','payment_id','amount','number','card','finance_id'
    ];
}
