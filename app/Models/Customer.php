<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'user_id','name','phone','alt_phone', 'address', 'city', 'pincode'
    ];
}
