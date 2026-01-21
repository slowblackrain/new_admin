<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\Category;
use App\Models\GoodsImage;
// Note: Brand and Location models are not yet created in app/Models, using DB facade if needed or creating placeholders.

class ProductController extends Controller
{
    public function create()
    {
        $seller = Auth::guard('seller')->user();

        // Legacy Access Check (Only specific provider allowed)
        // Original Logic: if($this->providerInfo['provider_seq'] != '3151') ...
        if ($seller->provider_seq != 3151) {
             abort(403, '관리자 권한이 없습니다. (Access Denied)');
        }

        // 1. Generate Goods Scode (Legacy logic: QQQ + increment)
        // Check finding the last scode
        $lastGoods = DB::table('fm_goods')
            ->where('goods_scode', '!=', '')
            ->orderBy('goods_seq', 'desc')
            ->first();

        $gscode = 'QQQ10001'; // Default start
        if ($lastGoods && $lastGoods->goods_scode) {
            $lastCode = $lastGoods->goods_scode;
            // Assuming format QQQ{number}
            if (preg_match('/^QQQ(\d+)$/', $lastCode, $matches)) {
                $nextNum = intval($matches[1]) + 1;
                $gscode = 'QQQ' . $nextNum;
            }
        }

        // 2. Fetch Categories (Top level or frequent - Simplified for now)
        // Legacy: "SELECT cl.category_code FROM ... group by cl.category_code ... limit 30"
        // We will just fetch top-level categories for the dropdown for now.
        $categories = Category::whereRaw('length(category_code) = 4')->get();

        return view('seller.goods.regist', compact('seller', 'gscode', 'categories'));
    }

    public function store(Request $request)
    {
        // Basic Validation
        $request->validate([
            'goods_name' => 'required',
            'price' => 'required|numeric',
            'consumer_price' => 'nullable|numeric',
            'supply_price' => 'required|numeric',
            'category1' => 'required', // At least primary category
            // Add more as needed
        ]);

        $seller = Auth::guard('seller')->user();

        DB::beginTransaction();
        try {
            // 1. Insert fm_goods
            $goods = new Goods();
            $goods->goods_name = $request->goods_name;
            $goods->summary = $request->summary;
            $goods->goods_status = $request->goods_status ?? 'normal';
            $goods->goods_view = $request->goods_view ?? 'look';
            $goods->goods_kind = 'goods'; // Default
            $goods->provider_seq = $seller->provider_seq;
            $goods->goods_scode = $request->goods_scode;
            $goods->keyword = $request->keyword;
            $goods->regist_date = now();
            $goods->update_date = now();
            
            // Legacy defaults
            $goods->shipping_policy = 'shop'; 
            
            $goods->save();
            $goodsSeq = $goods->goods_seq;

            // 2. Insert fm_goods_option (Default Option)
            // Legacy creates a default option with user's price info
            // table: fm_goods_option
            $optionId = DB::table('fm_goods_option')->insertGetId([
                'goods_seq' => $goodsSeq,
                'default_option' => 'y',
                'option_title' => '', // Empty for default option often
                'consumer_price' => $request->consumer_price ?? 0,
                'price' => $request->price,
            ]);

            // 3. Insert fm_goods_supply (Supply Info)
            // table: fm_goods_supply
            DB::table('fm_goods_supply')->insert([
                'goods_seq' => $goodsSeq,
                'option_seq' => $optionId,
                'supply_price' => $request->supply_price,
                'stock' => $request->stock ?? 0,
                'badstock' => 0,
                'safe_stock' => 0,
                'reservation15' => 0,
                'reservation25' => 0,
            ]);

            // 4. Update fm_goods total stock
            $goods->tot_stock = $request->stock ?? 0;
            $goods->save();

            // 5. Category Link
            // Linking the selected category
            // Form usually sends category1, category2, etc. We take the deepest selected.
            $categoryCode = $request->category4 ?: ($request->category3 ?: ($request->category2 ?: $request->category1));
            
            if ($categoryCode) {
                 DB::table('fm_category_link')->insert([
                    'category_code' => $categoryCode,
                    'goods_seq' => $goodsSeq,
                    'link' => 1, // Primary link
                    'regist_date' => now()
                ]);
            }

            // 6. Image Uploads (Simplified)
            // Handling 'image' file input
            if ($request->hasFile('image')) {
                // In a real scenario, this involves resizing and creating _l, _m, _s versions
                // For this MVP, we might upload one and use it.
                // Legacy often saves relative paths like '/data/goods/...'
                // Let's defer complex image processing and just save the path if possible or stub it.
                // NOTE: Will implement Laravel basic storage for now.
                
                /*
                $path = $request->file('image')->store('goods', 'public');
                // Update goods table with this path (legacy expects specific columns)
                $goods->image = $path; // Legacy column might be different?
                // Legacy uses `fm_goods_image` table too?
                // Legacy uses columns img_i, img_s, img_m, img_l in fm_goods OR separate table
                // Analysis showed `fm_goods_image` insert in goods_process.php
                
                DB::table('fm_goods_image')->insert([
                    'goods_seq' => $goodsSeq,
                    'image' => $path,
                    'cut_number' => 1
                ]);
                */
            }

            DB::commit();

            return redirect()->route('seller.goods.regist')->with('success', '상품이 성공적으로 등록되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '상품 등록 중 오류가 발생했습니다: ' . $e->getMessage())->withInput();
        }
    }
}
