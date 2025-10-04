<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    protected $fillable = [
        'user_id','name','category_id','sub_category_id','code','description','hsn_code','price','quantity','tax_id','metric_id','discount_type','discount','image','is_active','tax_amount','is_bulk_upload','run_id'
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

     public function stock()
    {
        return $this->hasOne(Stock::class, 'product_id');
    }

    // Filtered stock for the logged-in shop & branch
    public function filteredStock()
    {
        return $this->hasOne(Stock::class, 'product_id')
            ->where('shop_id', Auth::user()->parent_id)
            ->where('branch_id', Auth::user()->id);
    }
}
