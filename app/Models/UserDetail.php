<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id','address','gst','payment_method','payment_date','primary_colour','secondary_colour','bill_type'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
