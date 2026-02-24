<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsSortcd extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_sortcd';
    protected $primaryKey = 'sortcd_seq';
    public $timestamps = false; 

    protected $fillable = [
        'goods_sortcd',
        'goods_scode',
        'goods_memo'
    ];

    /**
     * 상품 테이블(fm_goods)과 scode를 통한 릴레이션 (옵션)
     */
    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_scode', 'goods_scode');
    }
}
