<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignPopup extends Model
{
    protected $table = 'fm_design_popup';
    protected $primaryKey = 'popup_seq';
    public $timestamps = false; // Legacy table may not use standard timestamps or uses custom names

    protected $guarded = [];

    // Constants for Enums
    const STYLE_WINDOW = 'window';
    const STYLE_LAYER = 'layer';
    
    // Relationships if needed
    public function banner()
    {
        return $this->belongsTo(DesignPopupBanner::class, 'popup_banner_seq', 'banner_seq');
    }
}
