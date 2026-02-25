<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemOption extends Model
{
    use HasFactory;

    protected $table = 'fm_order_item_option';
    protected $primaryKey = 'item_option_seq';
    public $timestamps = false;

    protected $guarded = [];

    protected $attributes = [
        'refund_ea' => 0,
        'tax' => 0,
        'option1' => '',
        'option2' => '',
        'option3' => '',
        'option4' => '',
        'option5' => '',
        'price' => 0,
        'consumer_price' => 0,
        'supply_price' => 0,
    ];

    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'item_seq', 'item_seq');
    }
}
