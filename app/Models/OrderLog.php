<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    protected $table = 'fm_order_log';
    protected $primaryKey = 'log_seq';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'regist_date' => 'datetime',
    ];
}
