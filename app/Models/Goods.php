<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    use HasFactory;

    protected $table = 'fm_goods';
    protected $primaryKey = 'goods_seq';
    public $timestamps = false;

    // Dates
    protected $dates = [
        'regist_date',
        'update_date',
        'disp_date'
    ];

    protected $guarded = [];

    // Relationship to Categories
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'fm_category_link',
            'goods_seq',      // Foreign key on pivot table for this model
            'category_code',  // Foreign key on pivot table for related model
            'goods_seq',      // Local key for this model
            'category_code'   // Local key for related model
        );
    }

    public function option()
    {
        return $this->hasMany(GoodsOption::class, 'goods_seq');
    }

    public function images()
    {
        return $this->hasMany(GoodsImage::class, 'goods_seq', 'goods_seq');
    }

    public function inputs()
    {
        return $this->hasMany(GoodsInput::class, 'goods_seq', 'goods_seq')->orderBy('input_seq', 'asc');
    }

    public function subOptions()
    {
        return $this->hasMany(SubOption::class, 'goods_seq', 'goods_seq')->orderBy('suboption_seq');
    }

    public function defaultOption()
    {
        return $this->hasOne(GoodsOption::class, 'goods_seq', 'goods_seq')
            ->where('default_option', 'y');
    }

    public function brands()
    {
        return $this->belongsToMany(
            Brand::class,
            'fm_brand_link',
            'goods_seq',
            'category_code',
            'goods_seq',
            'category_code'
        );
    }

    public function locations()
    {
        return $this->belongsToMany(
            Location::class,
            'fm_location_link',
            'goods_seq',
            'location_code',
            'goods_seq',
            'location_code'
        );
    }

    public function activeIcons()
    {
        return $this->hasMany(GoodsIcon::class, 'goods_seq', 'goods_seq')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('start_date')
                      ->orWhere('start_date', '=', '0000-00-00');
                })->orWhere('start_date', '<=', date('Y-m-d'));
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '=', '0000-00-00');
                })->orWhere('end_date', '>=', date('Y-m-d'));
            });
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_seq', 'provider_seq');
    }

    public function scopeActive($query)
    {
        return $query->where('goods_view', 'look')
            ->where('goods_status', 'normal')
            ->whereHas('provider', function ($q) {
                $q->where('provider_status', 'Y');
            });
    }

    public function scopeExcludeHiddenCodes($query)
    {
        // Legacy hidden codes + MKS
        $hiddenPrefixes = ['FFF', 'MTS', 'MXT', 'OOO', 'QQQ', 'MKS'];
        return $query->where(function ($q) use ($hiddenPrefixes) {
            foreach ($hiddenPrefixes as $prefix) {
                $q->where('goods_scode', 'not like', $prefix . '%');
            }
        });
    }
}
