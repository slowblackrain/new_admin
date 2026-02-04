<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsExportItem extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_export_item';
    protected $primaryKey = 'item_seq'; // Verify if this is the correct PK, logic usually suggests unique item_seq per export item row
    public $timestamps = false;

    protected $guarded = [];

    public function export()
    {
        return $this->belongsTo(GoodsExport::class, 'export_code', 'export_code');
    }

    public function orderItem()
    {
        // Assuming there is a link back to the original order item if needed, though export item has most data
        // fm_goods_export_item usually copies data from fm_order_item
        return $this->belongsTo(OrderItem::class, 'item_seq', 'item_seq'); // Note: item_seq might be shared or different. Legacy schema check recommended if strict FK needed.
        // In legacy, fm_goods_export_item.item_seq often matches fm_order_item.item_seq or is a copy.
        // Let's assume it links to OrderItem via 'item_seq' or 'option_seq' combination if needed.
        // For now, we will define it as belonging to an export.
    }
}
