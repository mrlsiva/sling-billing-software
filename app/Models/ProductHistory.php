<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    protected $fillable = [
        'shop_id','branch_id','category_id','sub_category_id','product_id','quantity','transfer_on','transfer_by'
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
        return $this->belongsTo('App\Models\Product');
    }

    public function transferBy()
    {
        return $this->belongsTo('App\Models\User','transfer_by');
    }
     
}
