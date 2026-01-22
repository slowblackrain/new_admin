<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mosms extends Model
{
    protected $table = 'fm_mosms';
    protected $primaryKey = 'idx';
    public $timestamps = false;

    protected $fillable = [
        'pg_status',
        'in_bank',
        'in_price',
        'in_name',
        'memo',
        'order_seq',
        'update_time'
    ];
}
