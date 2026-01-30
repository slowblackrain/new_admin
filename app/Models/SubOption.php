<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubOption extends Model
{
    protected $table = 'fm_goods_suboption';
    protected $primaryKey = 'suboption_seq';
    public $timestamps = false; // Legacy table usually doesn't have laravel timestamps

    protected $guarded = [];

    public function good()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
