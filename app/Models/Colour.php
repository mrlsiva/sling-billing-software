<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colour extends Model
{
    protected $fillable = [
        'shop_id','name','is_active'
    ];
}
