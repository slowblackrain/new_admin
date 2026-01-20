<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'fm_category';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function goods()
    {
        return $this->belongsToMany(
            Goods::class,
            'fm_category_link',
            'category_code', // FK on pivot for Category
            'goods_seq',     // FK on pivot for Goods
            'category_code', // Local key on Category
            'goods_seq'      // Local key on Goods
        );
    }
}
