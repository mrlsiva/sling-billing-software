<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finance extends Model
{
    protected $fillable = [
        'shop_id','name','is_active'
    ];
}
