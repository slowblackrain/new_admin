<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'fm_order';
    protected $primaryKey = 'order_seq';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];

    // Order Steps (주문 상태)
    const STEP_ORDER_RECEIVED = 15;      // 주문접수
    const STEP_PAYMENT_CONFIRMED = 25;   // 결제확인
    const STEP_INVOICE_PRINTED = 35;     // 송장출력
    const STEP_PRODUCT_PREPARATION = 45; // 상품준비
    const STEP_SHIPPED = 55;             // 출고완료
    const STEP_IN_TRANSIT = 65;          // 배송중
    const STEP_DELIVERED = 75;           // 배송완료

    // Relationships
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_seq', 'order_seq');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_seq', 'member_seq');
    }
}
