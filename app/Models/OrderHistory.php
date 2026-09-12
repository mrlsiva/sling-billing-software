<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $fillable = [
        'order_id',
        'edited_by',
        'edited_on',
        'order',
        'order_details',
        'payment_details',
        'remarks',
    ];

    protected $casts = [
        'order' => 'array',
        'order_details' => 'array',
        'payment_details' => 'array',
        'edited_on' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
