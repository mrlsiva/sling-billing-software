<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockVariation extends Model
{
    protected $fillable = [
        'stock_id','product_id','size_id','colour_id','quantity','price'
    ];


    public function stock()
    {
        return $this->belongsTo('App\Models\Stock','stock_id');
    }

    public function size() 
    {
        return $this->belongsTo(Size::class);
    }

    public function colour() 
    {
        return $this->belongsTo(Colour::class);
    }

}
