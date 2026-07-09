<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueStock extends Model
{
    protected $fillable = [
        'type','from','to','product_id','quantity','initiated_on','initiated_by','updated_on','updated_by','status','price','unique_id','imei','variation'
    ];

    public function From()
    {
        return $this->belongsTo(User::class, 'from');
    }

    public function To()
    {
        return $this->belongsTo(User::class, 'to');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
