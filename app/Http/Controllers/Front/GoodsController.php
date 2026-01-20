<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Category;

class GoodsController extends Controller
{
    public function catalog(Request $request)
    {
        // 1. Fetch Categories for Sidebar
        $categories = Category::whereRaw('length(category_code) = 4')->orderBy('position')->limit(20)->get();

        // 2. Fetch Goods with Pagination
        $query = Goods::active()->with(['option', 'images']);

        $categoryCode = 'All';

        // Filter by Category Code if present
        if ($request->has('code') && $request->code) {
            $code = $request->code;
            $query->whereHas('categories', function ($q) use ($code) {
                // Category code hierarchy: 0001, 00010001 ...
                // LIKE '0001%' matches all subcategories
                $q->where('fm_category.category_code', 'like', $code . '%');
            });

            // Fetch Current Category Name for display
            $currentCategory = Category::where('category_code', $code)->first();
            $categoryCode = $currentCategory ? ($currentCategory->title ?? $code) : $code;
        }

        $goods = $query->orderBy('regist_date', 'desc')
            ->paginate(12);

        return view('front.goods.catalog', compact('categories', 'goods', 'categoryCode'));
    }

    public function view(Request $request, \App\Services\PricingService $pricingService, \App\Services\ShippingService $shippingService)
    {
        $no = $request->query('no');

        // Fetch product or fail
        // In legacy, 'no' maps to 'goods_seq'
        $product = Goods::active()->with(['option', 'images', 'inputs'])->where('goods_seq', $no)->firstOrFail();

        // 1. Calculate Prices for Display (Tiered Pricing) using Service
        $priceInfo = $pricingService->getProductPricingInfo($product);

        // 2. Calculate Shipping Info using Service
        $shippingInfo = $shippingService->calculateShipping($product, 0); // View page assumes 0 total initially for threshold info

        // 3. Parse Product Specs (goods_contents2)
        // Legacy: $content2_arr = explode('|',$goods_contents2);
        $contentArr = explode('|', $product->goods_contents2 ?? '');
        $contentInfo = [
            'material' => $contentArr[0] ?? '',
            'packing' => $contentArr[1] ?? '',
            'size' => $contentArr[2] ?? '',
            'color' => $contentArr[3] ?? '',
            'box_quantity' => $contentArr[4] ?? '',
            'delivery_period' => $contentArr[5] ?? '',
            'safe_num' => $contentArr[6] ?? '',
            'usage' => $contentArr[7] ?? '',
            'bar_code' => $contentArr[8] ?? '',
            'weight' => $contentArr[9] ?? '',
        ];

        // Manufacturer Name
        $makerName = $product->maker_name ?? '';

        // Fix Detail Images Paths (common_contents, contents)
        $product->common_contents = str_replace('src="/data/', 'src="http://dometopia.com/data/', $product->common_contents ?? '');
        $product->contents = str_replace('src="/data/', 'src="http://dometopia.com/data/', $product->contents ?? '');

        // Prepare Detailed Image from img_contents column
        $detailImgMap = '';
        if (!empty($product->img_contents)) {
            $imgSrc = $product->img_contents;
            if (!str_starts_with($imgSrc, 'http')) {
                $imgSrc = 'http://dometopia.com' . $imgSrc;
            }
            $detailImgMap = "<img src='" . $imgSrc . "' style='max-width:100%; width:auto; display:block; margin:0 auto;' />";
        }

        // 4. Determine Top Images / Banner Logic
        $goodsScode = $product->goods_scode;
        $allTopImg = '';
        if (strpos($goodsScode, "XT") !== false) {
            $allTopImg = "<img src='http://dometopia.com/data/goods/goods_img/top_xmas.jpg'>";
        } else {
            $allTopImg = "<img src='http://dometopia.com/data/goods/goods_img/all_top_img.jpg'>";
        }

        $gtdImg = '';
        $excArray = ["GDF132297", "GDF132298", "GDF132299"];
        if ((strpos($goodsScode, "GDF") !== false || strpos($goodsScode, "GDH") !== false) && !in_array($goodsScode, $excArray)) {
            $gtdImg = "<img src='http://dometopia.com/data/goods/goods_img/gtd_title.jpg'>";
        }

        // GUS Image
        $gusImg = '';
        if (strpos($goodsScode, "GUS") !== false) {
            $gusImg = "<a href='/board/?id=brand' target='_blank' ><img src='http://dometopia.com/data/goods/shes/GUS_caution.jpg'></a>";
        }

        // Fetch categories for sidebar
        $categories = Category::whereRaw('length(category_code) = 4')->orderBy('position')->limit(20)->get();

        return view('front.goods.view', compact(
            'product',
            'categories',
            'priceInfo',
            'shippingInfo',
            'contentInfo',
            'allTopImg',
            'gtdImg',
            'gusImg',
            'makerName',
            'detailImgMap'
        ));
    }

    public function search(Request $request, \App\Contracts\SearchIntentionInterface $aiSearchService)
    {
        // 1. Fetch Categories for Sidebar
        $categories = Category::whereRaw('length(category_code) = 4')->orderBy('position')->limit(20)->get();

        // 2. Get Search Term
        $keyword = $request->input('s_search') ?? $request->input('keyword') ?? $request->input('search_text');

        // 3. AI Analysis (Query Expansion)
        $aiAnalysis = $aiSearchService->analyze($keyword ?? '');

        // 4. Build Query
        $query = Goods::active()->with(['option', 'images']);

        if (!empty($aiAnalysis['keywords'])) {
            $query->where(function ($q) use ($aiAnalysis) {
                foreach ($aiAnalysis['keywords'] as $term) {
                    $q->orWhere('goods_name', 'like', "%{$term}%")
                        ->orWhere('goods_code', 'like', "%{$term}%");
                }
            });
        } elseif ($keyword) {
            // Fallback if AI returns nothing (shouldn't happen with current Mock)
            $query->where('goods_name', 'like', "%{$keyword}%");
        }

        // Apply AI Filters
        if (isset($aiAnalysis['filters']['price_max'])) {
            // Need to join options or use whereHas for price
            $query->whereHas('option', function ($q) use ($aiAnalysis) {
                $q->where('price', '<=', $aiAnalysis['filters']['price_max']);
            });
        }

        // Apply AI Sort
        if (($aiAnalysis['sort'] ?? '') === 'price_asc') {
            // Complex sort with join needed usually, but for now simple
            // This is tricky with Eloquent relations, strictly speaking requires join
            // For prototype, we settle for default or just basic sort
        }

        $goods = $query->orderBy('regist_date', 'desc')->paginate(12);

        // Append query strings to pagination links
        $goods->appends($request->all());

        $categoryCode = 'Search Results';

        return view('front.goods.search', compact('categories', 'goods', 'categoryCode', 'keyword', 'aiAnalysis'));
    }
}
