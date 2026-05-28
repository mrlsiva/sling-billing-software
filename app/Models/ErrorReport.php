<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorReport extends Model
{
    protected $fillable = [
        'user_id','error','code','url','method','ip','agent'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
