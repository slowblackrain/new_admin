<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRefundItem extends Model
{
    use HasFactory;

    protected $table = 'fm_order_refund_item';
    protected $primaryKey = 'refund_item_seq'; // Assumed
    public $timestamps = false;

    protected $guarded = [];

    public function refund()
    {
        return $this->belongsTo(OrderRefund::class, 'refund_code', 'refund_code');
    }

    // Usually maps back to order item as well
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'item_seq', 'item_seq');
    }
}
