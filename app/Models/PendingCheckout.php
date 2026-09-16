<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingCheckout extends Model
{
    protected $fillable = [
        'shop_id',
        'branch_id',
        'customer_id',
        'ecommerce_user_id',
        'razorpay_order_id',
        'amount',
        'discount',
        'cart',
        'billing_customer',
        'status',
        'order_id',
    ];

    protected $casts = [
        'cart' => 'array',
        'billing_customer' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
