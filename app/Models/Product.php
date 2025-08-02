<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name','category_id','sub_category_id','code','description','hsn_code','price','tax_id','metric_id','discount_type','discount','image','is_active'
    ];

    public function sub_category()
    {
        return $this->belongsTo('App\Models\SubCategory');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }
}
