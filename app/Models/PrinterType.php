<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrinterType extends Model
{
    protected $fillable = [
        'name','blade','is_active'
    ];
}
