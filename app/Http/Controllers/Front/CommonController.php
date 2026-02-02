<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Cart; 
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    /**
     * Get Right Floating Menu Display (Recent Items List)
     * Mirrors legacy /common/get_right_display
     */
    public function getRightDisplay(Request $request)
    {
        $type = $request->input('type'); // e.g., 'right_item_recent'
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 5);

        if ($type === 'right_item_recent') {
            // 1. Get Cookie Data
            $todayGoods = json_decode($request->cookie('goods_today', '[]'), true);
            
            // Legacy / Serialization Fallback
            if (!is_array($todayGoods)) {
                 $unserialized = @unserialize($request->cookie('goods_today'));
                 if ($unserialized !== false) {
                     $todayGoods = $unserialized;
                 } else {
                     $todayGoods = [];
                 }
            }

            // 2. Pagination
            $offset = ($page - 1) * $limit;
            $slicedIds = array_slice($todayGoods, $offset, $limit);

            if (empty($slicedIds)) {
                return ''; 
            }

            // 3. Fetch Data with Eager Loading
            $orderStr = implode(',', $slicedIds);
            if (empty($orderStr)) {
                 $goodsList = collect([]);
            } else {
                $goodsList = Goods::whereIn('goods_seq', $slicedIds)
                ->with('images') 
                ->orderByRaw("FIELD(goods_seq, $orderStr)")
                ->get();
            }

            return view('front.layouts.quick_item_list', compact('goodsList'));
        }

        // Other types (Wishlist, Cart)
        return '';
    }

    /**
     * Get Total Counts for Right Menu
     * Mirrors legacy /common/get_right_total
     */
    public function getRightTotal(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'right_item_recent') {
            $todayGoods = json_decode($request->cookie('goods_today', '[]'), true);
            return count($todayGoods);
        }

        if ($type === 'right_item_cart') {
             // Logic to count cart items
             // Assuming Cart model or session based cart
             // This needs to match existing CartController logic
             // For now, simpler implementation:
             $cartCount = 0;
             if (Auth::check()) {
                 $cartCount = DB::table('fm_cart')->where('member_seq', Auth::id())->count();
             } else {
                 $cartCount = DB::table('fm_cart')->where('session_id', session()->getId())->count();
             }
             return $cartCount;
        }

        if ($type === 'right_item_wish') {
            if (Auth::check()) {
                return DB::table('fm_goods_wish')->where('member_seq', Auth::id())->count();
            }
            return 0;
        }

        return 0;
    }

    /**
     * Delete Recent Item
     * Mirrors legacy /goods/goods_recent_del
     */
    public function deleteRecentItem(Request $request)
    {
        $goodsSeq = $request->input('goods_seq');
        $todayGoods = json_decode($request->cookie('goods_today', '[]'), true);
        
        // Remove item
        $todayGoods = array_values(array_diff($todayGoods, [$goodsSeq]));
        
        // Update Cookie
        $cookie = cookie('goods_today', json_encode($todayGoods), 1440);

        return response()->json(['msg' => 'ok', 'totalcnt' => count($todayGoods)])->withCookie($cookie);
    }
}
