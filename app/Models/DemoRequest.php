<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    protected $fillable = ['name', 'mobile', 'email', 'shop_name', 'business_type'];
}
