<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundDetail extends Model
{
    protected $fillable = [
        'refund_id','product_id','name','quantity','price','tax_amount', 'tax_percent','imei','size_id','colour_id'
    ];

    public function size() 
    {
        return $this->belongsTo(Size::class);
    }

    public function colour() 
    {
        return $this->belongsTo(Colour::class);
    }
}
