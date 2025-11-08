<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'shop_id','vendor_id','payment_id','invoice_no','invoice_date','due_date','total_amount','status','is_refunded'
    ];

    public function vendor()
    {
        return $this->belongsTo('App\Models\Vendor');
    }

    public function payment()
    {
        return $this->belongsTo('App\Models\Payment');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class);
    }

    // Get total amount from all items
    public function getTotalAmountAttribute()
    {
        return $this->items()->sum('gross_cost');
    }

    // Legacy support - for backward compatibility with existing data
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
