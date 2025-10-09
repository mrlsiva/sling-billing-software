<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'shop_id','branch_id','bill_id','billed_by','customer_id','bill_amount','billed_on','is_refunded','total_product_discount'
    ];

    public function shop()
    {
        return $this->belongsTo('App\Models\User','user_id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\User','branch_id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer');
    }

    public function billedBy()
    {
        return $this->belongsTo('App\Models\Staff','billed_by');
    }
}
