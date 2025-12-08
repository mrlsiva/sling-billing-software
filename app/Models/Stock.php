<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'shop_id','branch_id','category_id','sub_category_id','product_id','quantity','is_active','imei'
    ];

    public function shop()
    {
        return $this->belongsTo('App\Models\User','shop_id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Models\User','branch_id');
    }

    public function sub_category()
    {
        return $this->belongsTo('App\Models\SubCategory');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variations()
    {
        return $this->hasMany(StockVariation::class, 'stock_id');
    }

}
