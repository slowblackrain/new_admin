<?php

namespace App\Models\Scm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScmStockRevision extends Model
{
    use HasFactory;

    protected $table = 'fm_scm_stock_revision';
    protected $primaryKey = 'revision_seq';
    public $timestamps = false;

    protected $fillable = [
        'revision_code',
        'revision_type', // 1: Increase, 2: Decrease, 3: Adjust (Set), 4: Discard
        'revision_status', // 1: Temp, 2: Complete
        'wh_seq',
        'admin_memo',
        'chg_log',
        'total_ea',
        'complete_date',
        'krw_total_supply_price',
        'krw_total_supply_tax',
        'krw_total_price',
        'regist_date'
    ];

    protected $casts = [
        'regist_date' => 'datetime',
        'complete_date' => 'datetime',
    ];

    // Relationships
    public function goods()
    {
        return $this->hasMany(ScmStockRevisionGoods::class, 'revision_seq', 'revision_seq');
    }
}
