<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRefund extends Model
{
    use HasFactory;

    protected $table = 'fm_order_refund';
    protected $primaryKey = 'refund_seq';
    public $timestamps = false;

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
        return $this->hasMany(OrderRefundItem::class, 'refund_code', 'refund_code');
    }

    public function getStatusLabelAttribute()
    {
        $statusMap = [
            'request' => '환불신청',
            'ing' => '환불처리중',
            'complete' => '환불완료',
            'cancel' => '환불취소', // If applicable
        ];
        return $statusMap[$this->status] ?? $this->status;
    }
}
