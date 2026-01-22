<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = [

        'name','cc_to','subject','template','is_active','created_by'

    ];
}
