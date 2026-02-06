<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsSet extends Model
{
    use HasFactory;

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

    // Dates
    protected $dates = [
        'regdate',
    ];

    // Relationship to Child Goods (The component item)
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }

    // Relationship to Parent Goods (The Set itself)
    public function parent()
    {
        return $this->belongsTo(Goods::class, 'main_seq', 'goods_seq');
    }
}
