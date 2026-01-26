<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrandLink extends Model
{
    use HasFactory;

    protected $table = 'fm_brand_link';
    protected $primaryKey = 'brand_link_seq';
    public $timestamps = false; // Legacy table usually no timestamps or handled manually

    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'category_code', 'category_code'); // Brand uses category_code structure
    }

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
