<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = [
        'user_id','category_id','name','image','is_active','is_bulk_upload','run_id'
    ];

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }
}
