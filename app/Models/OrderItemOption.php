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

    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'item_seq', 'item_seq');
    }
}
