<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    public function index()
    {
        // 1. Fetch Gift Main Categories (Code starts with 0146)
        // Legacy: get_category_gift_main_view('0146') -> Level 2/3 categories
        // We'll fetch direct children of 0146 (which is length 8, since 0146 is 4? No, 0146 is 4 chars. Children are 8.)
        $categories = \App\Models\Category::where('category_code', 'like', '0146%')
            ->whereRaw('length(category_code) = 8')
            ->where('hide', '!=', '1')
            ->orderBy('position')
            ->get();

        // 2. Fetch "Best 100" for Gift Section (Simulated)
        // Legacy uses showDesignDisplay(101811)
        $bestProducts = \App\Models\Goods::active()
            ->where('goods_scode', 'like', '0146%') // Assuming 0146 is the prefix for gift goods too, or mapped
            ->orderBy('hit', 'desc')
            ->limit(20) // Limit to 20 for now
            ->get();

        // 3. Theme Products (Simulating Tabs)
        // Tab 1: Writing (01460017 - Stationery?) - Let's use 0017 for now based on index.html link
        $theme1 = \App\Models\Goods::active()->where('goods_scode', 'like', '0017%')->orderBy('hit', 'desc')->limit(8)->get();
        // Tab 2: Bottle (0042 - Kitchen?)
        $theme2 = \App\Models\Goods::active()->where('goods_scode', 'like', '0042%')->orderBy('hit', 'desc')->limit(8)->get();
        // Tab 3: Stationery (0017)
        $theme3 = \App\Models\Goods::active()->where('goods_scode', 'like', '0017%')->orderBy('regist_date', 'desc')->limit(8)->get();

        return view('front.gift.index', compact('categories', 'bestProducts', 'theme1', 'theme2', 'theme3'));
    }
}
