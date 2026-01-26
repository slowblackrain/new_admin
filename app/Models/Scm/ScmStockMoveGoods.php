<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;

class ScmStockMoveGoods extends Model
{
    protected $table = 'fm_scm_stock_move_goods';
    public $timestamps = false;

    // Likely no single PK or composite.
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'move_seq',
        'goods_seq',
        'goods_code',
        'goods_name',
        'option_type',
        'option_seq',
        'option_name',
        'ea',
        'supply_price',
        // Add other fields as discovered if needed
    ];
}
