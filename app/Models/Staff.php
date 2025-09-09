<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staffs';
    
    protected $fillable = [
        'branch_id','name','phone','role','is_active'
    ];

    public function branch()
    {
        return $this->belongsTo('App\Models\User','branch_id');
    }
}
