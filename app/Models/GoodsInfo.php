<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsInfo extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_info';
    protected $primaryKey = 'info_seq';
    public $timestamps = false; 

    protected $fillable = [
        'info_name',
        'info_value',
        'info_provider_seq',
        'regist_date'
    ];
    
    // fm_goods usually links to this via 'info_seq'? 
    // Wait, the schema check showed 'info_seq' in fm_goods.
    // But fm_goods_info also has 'info_seq'.
    // Legacy logic usually is: fm_goods.info_seq points to a Shared Info (Notice/Return Policy).
    
    // BUT, Manufacturer/Origin are usually PER PRODUCT.
    // If legacy put them in fm_goods_info, they might be using a different link or just key/value.
    
    // Let's assume for now we save them as 'fm_goods_info' records linked to the goods?
    // ERROR: fm_goods_info has no 'goods_seq' column.
    
    // RE-CHECK: "fm_goods_info" is usually for "Common Information" (Delivery Info, etc) that is SHARED across many goods.
    // Only 'info_seq' in fm_goods links to it.
    
    // IF the user insisted on "Manufacturer" and "Origin" and they are NOT in fm_goods, and NOT in fm_goods_info (which is shared info),
    // THEN they must be in a table I missed OR I should create them in fm_goods as requested ("Create tables if needed").
    
    // User said: "Tables needed? Create them."
    // It is cleaner to add 'manufacturer', 'origin', 'model' columns to 'fm_goods' table directly if they don't exist, 
    // RATHER than abusing a Shared Info table.
    
    // DECISION: Create a migration to add these columns to fm_goods.
}
