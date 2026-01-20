<?php

namespace App\Models;
use App\Models\Size;
use App\Models\Colour;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'shop_id','vendor_id','payment_id','invoice_no','invoice_date','due_date','category_id','sub_category_id','product_id','imei','metric_id','quantity','price_per_unit','tax','discount','net_cost','gross_cost','status','is_refunded','size','colour'
    ];

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
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

    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    public function getSizeNamesAttribute()
    {
        if (empty($this->size)) {
            return '-';
        }

        return Size::whereIn('id', explode(',', $this->size))
            ->pluck('name')
            ->implode(', ');
    }

    public function getColourNamesAttribute()
    {
        if (empty($this->colour)) {
            return '-';
        }

        return Colour::whereIn('id', explode(',', $this->colour))
            ->pluck('name')
            ->implode(', ');
    }


}
