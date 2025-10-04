<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'shop_id','branch_id','model','table_id','old_value','new_value','send_on','send_by','message','url','bulk_upload_file'
    ];
}
