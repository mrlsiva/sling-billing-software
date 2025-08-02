<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'user_id','name','image','is_active'
    ];

    public function sub_categories()
    {
        return $this->hasMany(SubCategory::class, 'category_id');
    }
}
