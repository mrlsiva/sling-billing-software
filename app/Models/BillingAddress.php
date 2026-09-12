<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    protected $fillable = [
        'user_id','order_id','name','phone','alt_phone','address', 'city', 'pincode','gst'
    ];

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }
}
