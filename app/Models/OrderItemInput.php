<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemInput extends Model
{
    use HasFactory;

    protected $table = 'fm_order_item_input';
    protected $primaryKey = 'input_seq';
    public $timestamps = false; // Usually fm_ tables don't have standard timestamps unless specified
    protected $guarded = [];

    public function item()
    {
        return $this->belongsTo(OrderItem::class, 'item_seq', 'item_seq');
    }
}
