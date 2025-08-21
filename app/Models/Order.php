<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'shop_id','branch_id','bill_id','billed_by','customer_id','bill_amount','billed_on'
    ];
}
