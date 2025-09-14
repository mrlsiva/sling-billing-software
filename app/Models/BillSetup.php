<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillSetup extends Model
{
    protected $fillable = [
        'shop_id','branch_id','bill_number','setup_on','is_active'
    ];

    public function branch()
    {
        return $this->belongsTo('App\Models\User','branch_id');
    }

    public function shop()
    {
        return $this->belongsTo('App\Models\User','shop_id');
    }


}
