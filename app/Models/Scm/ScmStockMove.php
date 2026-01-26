<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Model;

class ScmStockMove extends Model
{
    protected $table = 'fm_scm_stock_move';
    protected $primaryKey = 'move_seq';
    public $timestamps = false;

    // Legacy 'regist_date' is likely handled manually or via DB default
    protected $fillable = [
        'move_code',
        'move_status', // 1: Request, 2: Complete? Need to verify legacy consts
        'in_wh_seq',
        'out_wh_seq',
        'complete_date',
        'admin_memo',
        'total_ea',
        'krw_total_supply_price',
        'krw_total_supply_tax',
        'krw_total_price',
        'chg_log',
        'regist_date',
    ];

    public function goods()
    {
        return $this->hasMany(ScmStockMoveGoods::class, 'move_seq', 'move_seq');
    }
}
