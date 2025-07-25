<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'unique_id','user_id','module','model','table','table_id','action','old_value','new_value','status','comment','url','method','ip','agent'
    ];
}
