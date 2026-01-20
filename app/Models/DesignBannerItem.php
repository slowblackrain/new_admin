<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignBannerItem extends Model
{
    protected $table = 'fm_design_banner_item';
    protected $primaryKey = 'banner_item_seq';
    public $timestamps = false;

    protected $fillable = [
        'banner_seq',
        'skin',
        'link',
        'target',
        'image',
        'memo',
    ];

    /**
     * Get the full URL for the banner image.
     */
    public function getImageUrlAttribute()
    {
        // DB value example: images/banner/11/images_1.jpg
        // Local file path: public/images/legacy/main/banner/images_1.jpg
        // We need to extract the filename only.
        
        $filename = basename($this->image);
        $path = 'images/legacy/main/banner/' . $filename;
        
        return asset($path);
    }
}
