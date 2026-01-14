<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    protected $fillable = [
        'from','to','category_id','sub_category_id','product_id','quantity','transfer_on','transfer_by','invoice'
    ];

    public function transfer_from()
    {
        return $this->belongsTo('App\Models\User','from');
    }

    public function transfer_to()
    {
        return $this->belongsTo('App\Models\User','to');
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
