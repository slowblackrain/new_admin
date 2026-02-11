<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturn extends Model
{
    use HasFactory;

    protected $table = 'fm_order_return';
    protected $primaryKey = 'return_seq';
    public $timestamps = false; // Legacy table mostly uses regist_date

    protected $guarded = [];

    protected $dates = [
        'regist_date',
        'status_date'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_seq', 'order_seq');
    }

    public function items()
    {
        return $this->hasMany(OrderReturnItem::class, 'return_code', 'return_code');
    }

    // Status accessor for convenience
    public function getStatusLabelAttribute()
    {
        $statusMap = [
            'request' => '반품신청',
            'ing' => '반품처리중',
            'complete' => '반품완료',
            'cancel' => '반품취소',
        ];
        return $statusMap[$this->status] ?? $this->status;
    }
}
