<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'fm_sales';
    protected $primaryKey = 'seq';
    public $timestamps = false;

    protected $guarded = [];

    // Relationships can be defined here if needed
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_seq', 'order_seq');
    }
}
