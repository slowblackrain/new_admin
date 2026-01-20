<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'fm_order_item';
    protected $primaryKey = 'item_seq';
    public $timestamps = false;

    protected $guarded = [];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_seq', 'order_seq');
    }

    public function options()
    {
        return $this->hasMany(OrderItemOption::class, 'item_seq', 'item_seq');
    }

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
