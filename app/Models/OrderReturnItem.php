<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderReturnItem extends Model
{
    use HasFactory;

    protected $table = 'fm_order_return_item';
    
    // Legacy table might not have a single simple PK or it might be 'item_seq' (ambiguous with order_item) or 'return_item_seq'.
    // Checking legacy schema is tricky without direct DB access, but typically these association tables have a PK.
    // Based on standard simple naming, let's assume `return_item_seq`. If not, we might need to adjust.
    // However, looking at legacy code `inner join fm_order_return_item as item on ref.return_code=item.return_code`, it links by return_code.
    
    protected $primaryKey = 'return_item_seq'; 
    public $timestamps = false;

    protected $guarded = [];

    public function return()
    {
        return $this->belongsTo(OrderReturn::class, 'return_code', 'return_code');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'item_seq', 'item_seq'); // item_seq in return_item usually refers to order_item_seq or similar
    }
    
    // Note: item_seq in fm_order_return_item maps to fm_order_item.item_seq
}
