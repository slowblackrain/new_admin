<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DesignPopup;
use Illuminate\Support\Facades\DB;

class PopupController extends Controller
{
    public function show(Request $request)
    {
        $popupSeq = $request->input('seq');
        $popup = DesignPopup::find($popupSeq);

        if (!$popup) {
            return '<script>self.close();</script>';
        }

        // Logic for 'contents_type'
        // ENUM('image','text','pc_style_5','pc_style_4','pc_style_3','pc_style_2')
        // In legacy, styles 2-5 are banners.
        
        $banner = null;
        $bannerItems = collect([]);

        if (!in_array($popup->contents_type, ['image', 'text']) && $popup->popup_banner_seq) {
            // It's a banner type
            $banner = DB::table('fm_design_popup_banner')->where('banner_seq', $popup->popup_banner_seq)->first();
            if ($banner) {
                // Determine skin? Legacy doesn't seem to filter by skin in popup.php designpopup logic clearly, 
                // but let's grab all items for this banner_seq as typical in legacy.
                // Wait, legacy code: $query = $this->db->query("select * from fm_design_popup_banner_item where banner_seq = ?",array($banner_seq));
                $bannerItems = DB::table('fm_design_popup_banner_item')
                    ->where('banner_seq', $popup->popup_banner_seq)
                    ->orderBy('banner_item_seq')
                    ->get();
            }
        }

        return view('front.popup.design_popup', compact('popup', 'banner', 'bannerItems'));
    }
}
