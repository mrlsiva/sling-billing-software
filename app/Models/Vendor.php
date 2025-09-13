<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'shop_id','name','phone','email','address','address1','city','state','gst','prepaid_amount','is_active'
    ];

    public function shop()
    {
        return $this->belongsTo('App\Models\User','shop_id');
    }
}
