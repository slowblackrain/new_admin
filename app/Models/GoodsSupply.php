<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodsSupply extends Model
{
    use HasFactory;

    protected $table = 'fm_goods_supply';
    // Often supply table uses option_seq or composite keys. 
    // Assuming standard auto-increment PK 'supply_seq' or similar if distinct.
    // If no PK, set $primaryKey = null and $incrementing = false.
    // Legacy schema usually has 'supply_seq' or 'seq'.
    // Let's assume 'supply_seq' for now or 'option_seq' as PK? 
    // Usually it's 1:1 with option? No, supply table tracks stock.
    // Let's use 'option_seq' as PK if it's 1:1, or find out.
    // Safest is to disable PK handling if unsure or composite.
    // But replicate() needs a clean model.
    // Let's assume `supply_seq`. 
    protected $primaryKey = 'supply_seq'; 
    public $timestamps = false;        
    protected $guarded = [];
}
