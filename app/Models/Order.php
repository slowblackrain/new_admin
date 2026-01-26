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
    protected $keyType = 'string';
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

    public function logs()
    {
        return $this->hasMany(OrderLog::class, 'order_seq', 'order_seq')->orderBy('regist_date', 'desc');
    }

    public static function getStepName($step)
    {
        $steps = [
            15 => '주문접수',
            25 => '결제확인',
            35 => '상품준비',
            40 => '부분출고준비',
            45 => '상품준비', // Sometimes 45 is used for partial
            50 => '출고준비',
            55 => '출고완료',
            60 => '배송중',
            65 => '배송중',
            70 => '배송완료',
            75 => '구매확정',
            85 => '거래완료',
            95 => '주문취소',
            99 => '결제실패'
        ];
        return $steps[$step] ?? $step;
    }

    public static function getStepColor($step)
    {
        $colors = [
            15 => '#d75e00', // 주문접수 (Orange)
            25 => '#577e19', // 결제확인 (Green)
            35 => '#005d0f', // 상품준비 (Dark Green)
            40 => '#0083db', // 부분출고준비 (Light Blue)
            45 => '#0083db', // 상품준비 (Light Blue)
            50 => '#09429d', // 출고준비 (Dark Blue)
            55 => '#09429d', // 출고완료 (Dark Blue)
            60 => '#4c24ab', // 배송중 (Purple)
            65 => '#4c24ab', // 배송중 (Purple)
            70 => '#c63765', // 배송완료 (Pink/Magenta)
            75 => '#c63765', // 구매확정 (Pink/Magenta)
            85 => '#000000', // 거래완료 (Black)
            95 => '#000000', // 주문취소 (Black)
            99 => '#000000', // 결제실패 (Black)
        ];
        return $colors[$step] ?? '#6c757d'; // Default secondary
    }
}
