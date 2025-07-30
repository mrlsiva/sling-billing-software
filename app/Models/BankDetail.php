<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDetail extends Model
{
    protected $fillable = [
        'user_id','name','holder_name','branch','account_no','ifsc_code'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
