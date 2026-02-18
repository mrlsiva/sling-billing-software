<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstBill extends Model
{
    protected $fillable = [
        'shop_id','branch_id','order_id','reference_no','transfer_on','issued_by','sold_by','customer_name','customer_phone','customer_address','category','sub_category','product','imie','item_code','quantity','gross'
    ];

    public function shop()
    {
        return $this->belongsTo('App\Models\User','shop_id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\User','branch_id');
    }
}
