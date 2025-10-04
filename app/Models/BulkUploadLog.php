<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkUploadLog extends Model
{
    protected $fillable = [
        'user_id','run_id','run_on','module','total_record','successfull_record','error_record','excel','log','error_excel','successfull_excel'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
