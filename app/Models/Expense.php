<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'shop_id',
        'branch_id',
        'title',
        'amount',
    ];

    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }
}
