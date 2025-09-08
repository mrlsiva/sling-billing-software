<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundDetail extends Model
{
    protected $fillable = [
        'refund_id','product_id','name','quantity','price','tax_amount'
    ];
}
