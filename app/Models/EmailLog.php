<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'user_id','email','subject','body','attachment','msg','failed_on','send_on','is_send'
    ];
}
