<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = [
        'user_id','address','gst','payment_method','payment_date','primary_colour','secondary_colour','bill_type','is_scan_avaiable','is_bill_enabled','plan_start','plan_end'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function billType()
    {
        return $this->belongsTo('App\Models\PrinterType','bill_type');
    }
}
