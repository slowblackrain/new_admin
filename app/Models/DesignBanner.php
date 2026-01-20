<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignBanner extends Model
{
    protected $table = 'fm_design_banner';
    protected $primaryKey = 'banner_seq';
    public $timestamps = false; // Legacy table doesn't use standard timestamps

    protected $fillable = [
        'skin',
        'platform',
        'style',
        'name',
        'height',
        'slide_event',
    ];

    public function items()
    {
        return $this->hasMany(DesignBannerItem::class, 'banner_seq', 'banner_seq')
                    ->orderBy('banner_item_seq', 'asc');
    }
}
