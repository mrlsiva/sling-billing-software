<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopPayment extends Model
{
    protected $fillable = [
        'shop_id','payment_id'
    ];

    public function shop()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }
}
