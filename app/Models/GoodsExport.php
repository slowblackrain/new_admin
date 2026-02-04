<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsExport extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_export';
    protected $primaryKey = 'export_seq';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'export_date' => 'datetime',
        'complete_date' => 'datetime',
        'shipping_date' => 'datetime',
        'regist_date' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_seq', 'order_seq');
    }

    public function items()
    {
        return $this->hasMany(GoodsExportItem::class, 'export_code', 'export_code');
    }

    // Status Code Helper (Legacy: config_load('export_status'))
    public static function getStatusName($status)
    {
        $statuses = [
            '45' => '출고준비',
            '55' => '출고완료',
            '65' => '출고취소', // Inferred, verify legacy config if needed
            '75' => '반품접수',
            // Add more as needed based on legacy config
        ];
        return $statuses[$status] ?? $status;
    }
}
