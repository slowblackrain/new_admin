<?php

namespace App\Models\Admin\Goods;

use Illuminate\Database\Eloquent\Model;

class GoodsSet extends Model
{
    protected $table = 'fm_goods_set';
    protected $primaryKey = 'set_seq';
    public $timestamps = false;

    protected $fillable = [
        'main_seq',
        'goods_seq',
        'goods_ea',
        'ea_calc',
        'manager',
        'regdate',
    ];

    // Relationship to Parent Goods (The Set itself)
    public function mainGoods()
    {
        return $this->belongsTo(\App\Models\Goods::class, 'main_seq', 'goods_seq');
    }

    // Relationship to Child Goods (The Item in the Set)
    public function subGoods()
    {
        return $this->belongsTo(\App\Models\Goods::class, 'goods_seq', 'goods_seq');
    }
}
