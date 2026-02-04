<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsRestockNotify extends Model
{
    protected $table = 'fm_goods_restock_notify';
    protected $primaryKey = 'restock_notify_seq';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'regist_date' => 'datetime',
        'notify_date' => 'datetime',
    ];

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
