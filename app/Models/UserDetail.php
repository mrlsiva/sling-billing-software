<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id','address','gst','primary_colour','secondary_colour'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
