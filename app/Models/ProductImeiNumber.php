<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImeiNumber extends Model
{
    protected $fillable = [
        'purchase_order_id','product_id','name','is_sold'
    ];
}
