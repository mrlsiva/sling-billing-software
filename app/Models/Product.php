<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id','name','category_id','sub_category_id','code','description','hsn_code','price','tax_id','metric_id','discount_type','discount','image','is_active'
    ];

    public function sub_category()
    {
        return $this->belongsTo('App\Models\SubCategory');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function tax()
    {
        return $this->belongsTo('App\Models\Tax');
    }

    public function metric()
    {
        return $this->belongsTo('App\Models\Metric');
    }
}
