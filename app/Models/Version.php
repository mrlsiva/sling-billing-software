<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $fillable = [
        'component','release_date','version','release_type','change_log','status','updated_at'
    ];

    const Software = 1;
    const Database = 2;

    const Scheduled = 1;
    const Hotfix = 2;
}
