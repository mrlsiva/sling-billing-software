<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = [
        'order_id','product_id','name','quantity','price','tax_amount','tax_percent','selling_price','discount_type','discount'
    ];
}
