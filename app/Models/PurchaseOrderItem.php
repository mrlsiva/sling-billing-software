<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id','category_id','sub_category_id','product_id','imei','metric_id','quantity','price_per_unit','tax','discount','net_cost','gross_cost'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function category()
    {
        return $this->belongsTo('App\Models\Category');
    }

    public function sub_category()
    {
        return $this->belongsTo('App\Models\SubCategory');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function metric()
    {
        return $this->belongsTo('App\Models\Metric');
    }
}