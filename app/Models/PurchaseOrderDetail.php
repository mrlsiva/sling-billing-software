<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    protected $fillable = [
        'purchase_order_id','old_amount','new_amount','updated_on','comment'
    ];
}
