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
        // 1. Fetch Categories for Sidebar (Depth 1)
        $categories = Category::whereRaw('length(category_code) = 4')
            ->orderBy('position')
            ->limit(20)
            ->get();

        // 2. Determine Current Category & Sub-categories
        $code = $request->input('code');
        $currentCategory = null;
        $childCategories = collect();
        $categoryCode = 'All';

        if ($code) {
            $currentCategory = Category::where('category_code', $code)->first();
            $categoryCode = $currentCategory ? ($currentCategory->title ?? $code) : $code;

            // Fetch children (Next Depth) relative to current code
            // Logic: length = current length + 4, and starts with current code
            $nextDepthLen = strlen($code) + 4;
            $childCategories = Category::where('category_code', 'like', $code . '%')
                ->whereRaw('length(category_code) = ?', [$nextDepthLen])
                ->where('hide', '!=', '1')
                ->orderBy('position')
                ->get();
        }

        // 3. Build Goods Query
        $query = Goods::active()->excludeHiddenCodes()->with(['option', 'images']);

        // [Parity] Filter private/ATS goods
        // Legacy logic: if member_seq is set, show (ATS=0 OR ATS=me). If guest, show ATS=0 only.
        $memberSeq = auth()->check() ? auth()->user()->member_seq : 0;
        if ($memberSeq) {
            $query->where(function ($q) use ($memberSeq) {
                $q->where('ATS_member_seq', 0)
                  ->orWhere('ATS_member_seq', $memberSeq);
            });
        } else {
            $query->where('ATS_member_seq', 0);
        }

        if ($code) {
            $query->whereHas('categories', function ($q) use ($code) {
                $q->where('fm_category.category_code', 'like', $code . '%');
            });
        }

        // [New] Search within Category
        $keyword = $request->input('search_text');
        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('goods_name', 'like', "%{$keyword}%")
                  ->orWhere('goods_code', 'like', "%{$keyword}%")
                  ->orWhere('goods_scode', 'like', "%{$keyword}%");
            });
        }



        // 4. Apply Sorting (Legacy Logic)
        $sort = $request->input('sort', '');
        switch ($sort) {
            case 'A': // Box Goods
                $query->where('goods_scode', 'like', 'A%')
                      ->orderBy('goods_seq', 'desc');
                break;
            case 'G': // Single Goods
                $query->where('goods_scode', 'like', 'G%')
                      ->orderBy('goods_seq', 'desc');
                break;
            case 'price_asc':
                $query->select('fm_goods.*')
                      ->leftJoin('fm_goods_option', 'fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
                      ->orderBy('fm_goods_option.price', 'asc')
                      ->distinct();
                break;
            case 'price_desc':
                $query->select('fm_goods.*')
                      ->leftJoin('fm_goods_option', 'fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
                      ->orderBy('fm_goods_option.price', 'desc')
                      ->distinct();
                break;
            case 'new': 
                $query->orderBy('regist_date', 'desc');
                break;
            default:
                $query->orderBy('goods_seq', 'desc');
                break;
        }

        $goods = $query->paginate(20)->withQueryString();

        return view('front.goods.catalog', compact(
            'categories', 
            'goods', 
            'categoryCode', 
            'currentCategory', 
            'childCategories',
            'sort',
            'keyword'
        ));
    }

    public function view(Request $request, \App\Services\PricingService $pricingService, \App\Services\ShippingService $shippingService)
    {
        $no = $request->query('no');

        // Fetch product or fail
        // In legacy, 'no' maps to 'goods_seq'
        $product = Goods::active()->with(['option', 'images', 'inputs', 'subOptions'])->where('goods_seq', $no)->firstOrFail();

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

        // [New] Recent Viewed Items Logic (Cookie)
        $todayGoods = json_decode($request->cookie('goods_today', '[]'), true);
        if (!is_array($todayGoods)) {
            $todayGoods = [];
        }
        // Prepend current goods_seq
        array_unshift($todayGoods, $no);
        // Remove duplicates
        $todayGoods = array_unique($todayGoods);
        // Keep max 20
        $todayGoods = array_slice($todayGoods, 0, 20);
        // Create Cookie (1 day)
        $cookie = cookie('goods_today', json_encode($todayGoods), 1440);

        return response()->view('front.goods.view', compact(
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
        ))->withCookie($cookie);
    }

    public function search(Request $request, \App\Contracts\SearchIntentionInterface $aiSearchService)
    {
        // 1. Fetch Categories for Sidebar
        // Use default connection
        $categories = Category::whereRaw('length(category_code) = 4')->orderBy('position')->limit(20)->get();

        // 2. Get Search Terms & Parameters
        $keyword = $request->input('search_text') ?? $request->input('s_search') ?? $request->input('keyword');
        $subText = $request->input('sub_text');
        $subSearchType = $request->input('sub_search', 'I'); // I: Include, E: Exclude
        
        $manualStartPrice = $request->input('start_price');
        $manualEndPrice = $request->input('end_price');
        $fmDate = $request->input('fm_date');
        $toDate = $request->input('to_date');
        $manualSort = $request->input('sort'); // Check raw input to see if user manually selected

        // 3. AI Analysis (Supplementary)
        $aiAnalysis = $aiSearchService->analyze($keyword ?? '');

        // 4. Determine Effective Parameters (Manual > AI)
        $startPrice = $manualStartPrice ?? $aiAnalysis['filters']['price_min'] ?? null;
        $endPrice = $manualEndPrice ?? $aiAnalysis['filters']['price_max'] ?? null;
        
        // Sort: User Input > AI Suggestion > Smart Default (Relevance for search, Newest for browsing)
        $defaultSort = $keyword ? 'accuracy' : 'new';
        $sort = $manualSort ?? $aiAnalysis['sort'] ?? $defaultSort;

        // SYNC UI: Merge effective values back into Request so Blade helpers work
        $request->merge([
            'start_price' => $startPrice,
            'end_price' => $endPrice,
            'sort' => $sort
        ]);

        // 5. Build Query (on Production)
        // Use default connection which handles environment automatically
        $query = Goods::active()->excludeHiddenCodes()->with(['option', 'images']);

        // [Parity] Filter private/ATS goods (Same as catalog)
        $memberSeq = auth()->check() ? auth()->user()->member_seq : 0;
        if ($memberSeq) {
            $query->where(function ($q) use ($memberSeq) {
                $q->where('ATS_member_seq', 0)
                  ->orWhere('ATS_member_seq', $memberSeq);
            });
        } else {
            $query->where('ATS_member_seq', 0);
        }

        // Keyword Search (Main)
        if ($keyword) {
            $query->where(function ($q) use ($keyword, $aiAnalysis) {
                // Use AI synonyms mixed with original keyword
                // Strategy: Search (Original OR Synonyms)
                $terms = !empty($aiAnalysis['keywords']) ? $aiAnalysis['keywords'] : [$keyword];
                
                foreach ($terms as $term) {
                    $q->orWhere('goods_name', 'like', "%{$term}%")
                      ->orWhere('goods_code', 'like', "%{$term}%")
                      ->orWhere('goods_scode', 'like', "%{$term}%")
                      ->orWhere('keyword', 'like', "%{$term}%");
                }
            });
        }

        // Sub-Search
        if ($subText) {
            $subTerms = explode(' ', $subText);
            if ($subSearchType == 'I') { 
                $query->where(function ($q) use ($subTerms) {
                    foreach ($subTerms as $term) {
                        $q->where('goods_name', 'like', "%{$term}%");
                    }
                });
            } elseif ($subSearchType == 'E') {
                $query->where(function ($q) use ($subTerms) {
                    foreach ($subTerms as $term) {
                        $q->where('goods_name', 'not like', "%{$term}%");
                    }
                });
            }
        }

        // Price Filter (Effective)
        if ($startPrice) {
            $query->whereHas('option', function ($q) use ($startPrice) {
                $q->where('price', '>=', $startPrice);
            });
        }
        if ($endPrice) {
            $query->whereHas('option', function ($q) use ($endPrice) {
                $q->where('price', '<=', $endPrice);
            });
        }

        // Date Filter
        if ($fmDate) {
            $query->where('regist_date', '>=', $fmDate . ' 00:00:00');
        }
        if ($toDate) {
            $query->where('regist_date', '<=', $toDate . ' 23:59:59');
        }

        // Sorting (Effective)
        switch ($sort) {
            case 'price_asc':
                $query->select('fm_goods.*')
                      ->leftJoin('fm_goods_option', 'fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
                      ->orderBy('fm_goods_option.price', 'asc')
                      ->distinct();
                break;
            case 'price_desc':
                $query->select('fm_goods.*')
                      ->leftJoin('fm_goods_option', 'fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
                      ->orderBy('fm_goods_option.price', 'desc')
                      ->distinct();
                break;
            case 'popular': 
                // AI inference 'popular' maps to hit count widely
                $query->orderBy('hit', 'desc');
                break;
            case 'popular_sales': 
                $query->orderBy('runout_type', 'asc')->orderBy('hit', 'desc'); 
                break;
            case 'accuracy':
                if ($keyword) {
                    $query->orderByRaw("
                        CASE 
                            WHEN goods_name = ? THEN 1 
                            WHEN goods_name LIKE ? THEN 2 
                            WHEN goods_name LIKE ? THEN 3 
                            WHEN keyword LIKE ? THEN 4
                            ELSE 5 
                        END
                    ", [$keyword, $keyword.'%', '%'.$keyword.'%', '%'.$keyword.'%']);
                }
                $query->orderBy('regist_date', 'desc');
                break;
            case 'new':
            default:
                $query->orderBy('regist_date', 'desc');
                break;
        }

        // 5. Execute Query
        $goods = $query->paginate(20)->appends($request->all());

        // 6. View Data Preparation (Price Ranges, Date Ranges for UI)
        $priceList = [
            ['title'=>'~1만원', 'min'=>0, 'max'=>10000],
            ['title'=>'1~5만원', 'min'=>10000, 'max'=>50000],
            ['title'=>'5~15만원', 'min'=>50000, 'max'=>150000],
            ['title'=>'15~30만원', 'min'=>150000, 'max'=>300000],
            ['title'=>'30만원~', 'min'=>300000, 'max'=>null],
        ];

        $today = date('Y-m-d');
        $yesterday = date("Y-m-d", strtotime("-1 day"));
        $week_start = date("Y-m-d", strtotime("-1 week"));
        $month_start = date("Y-m-d", strtotime("-1 month"));

        $dayList = [
            ['title'=>'오늘', 'from'=>$today, 'to'=>$today],
            ['title'=>'어제', 'from'=>$yesterday, 'to'=>$yesterday],
            ['title'=>'일주일', 'from'=>$week_start, 'to'=>$today],
            ['title'=>'이번달', 'from'=>date("Y-m-01"), 'to'=>$today],
            ['title'=>'지난달', 'from'=>date("Y-m-01", strtotime("-1 month")), 'to'=>date("Y-m-t", strtotime("-1 month"))],
        ];

        $categoryCode = '상품검색';

        return view('front.goods.search', compact(
            'categories', 'goods', 'categoryCode', 'keyword', 
            'aiAnalysis', 'priceList', 'dayList', 'sort'
        ));
    }
}
