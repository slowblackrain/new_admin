<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsIcon extends Model
{
    protected $table = 'fm_goods_icon';
    public $timestamps = false;

    protected $fillable = [
        'goods_seq',
        'codecd',
        'start_date',
        'end_date',
    ];

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
