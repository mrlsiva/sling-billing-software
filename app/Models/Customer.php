<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'user_id','branch_id','name','phone','alt_phone', 'address', 'pincode','gender_id','dob'
    ];

    public function gender()
    {
        return $this->belongsTo('App\Models\Gender');
    }
}
