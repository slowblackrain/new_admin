<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsOption extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_option';
    protected $primaryKey = 'option_seq';
    public $timestamps = false;

    protected $guarded = [];

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
