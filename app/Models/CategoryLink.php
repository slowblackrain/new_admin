<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryLink extends Model
{
    use HasFactory;

    protected $table = 'fm_category_link';
    protected $primaryKey = 'category_link_seq';
    public $timestamps = false;

    protected $guarded = [];

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_seq', 'goods_seq');
    }
}
