<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScmStockRevisionGoods extends Model
{
    use HasFactory;

    protected $table = 'fm_scm_stock_revision_goods';
    public $timestamps = false;
    // Composite Primary Key handling might be needed if Laravel acts up, but usually fine for insert only
    
    protected $fillable = [
        'revision_seq',
        'goods_seq',
        'goods_name',
        'goods_code',
        'use_tax',
        'option_type',
        'option_seq',
        'option_name',
        'ea',
        'supply_price_type',
        'exchange_price',
        'supply_price',
        'krw_supply_price',
        'supply_tax',
        'krw_supply_tax'
    ];

    public function revision()
    {
        return $this->belongsTo(ScmStockRevision::class, 'revision_seq', 'revision_seq');
    }
}
