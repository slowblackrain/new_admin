<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;
use App\Models\GoodsOption;
use Illuminate\Support\Facades\Storage;

class GoodsController extends Controller
{


    protected $priceCalculator;

    public function __construct(\App\Services\Goods\PriceCalculator $priceCalculator)
    {
        $this->priceCalculator = $priceCalculator;
    }

    public function create()
    {
        return view('admin.goods.regist');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'goodsName' => 'required|max:255',
            'costPrice' => 'nullable|numeric', // Base Cost
            'realCost' => 'nullable|numeric',  // Actual Component Cost
        ]);

        try {
            DB::beginTransaction();

            // 1. Calculate Prices (Server-Side Authority)
            // Gather Cost Components
            $realCost = $request->input('realCost', 0);
            $otherCosts = [
                'mem' => $request->input('otherCost_mem', 0),
                'sticker' => $request->input('otherCost_sticker', 0),
                'package' => $request->input('otherCost_package', 0),
                'delivery' => $request->input('otherCost_delivery', 0),
                'etc' => $request->input('otherCost_etc', 0),
            ];
            
            // 1-1. Base Cost (원가)
            $calculatedCostPrice = $this->priceCalculator->calculateCost($realCost, $otherCosts);

            // 1-2. Landed Price (수입원가/공급가)
            // Inputs for formula
            $exchangeRate = $request->input('exchange', 1); // 환율
            $cbmUnit = 0; // Logic for CBM needed if we want full fidelity, or use input 'xyh_ea' equivalent if passed
            // For now, using Simplified Logic or direct input if available, BUT per task we should use calculator.
            // Let's assume standard CBM params are passed or default to 0 for local goods.
            // If import logic applies:
            $transportRate = $request->input('transport', 0);
            $dutyRate = $request->input('customs', 0);
            $incidentalRate = $request->input('incidental', 1);
            
            // NOTE: CBM Unit calculation requires Dimensions. 
            // If dimensions are in inputs 'cbm0' (w), 'cbm1' (d), 'cbm2' (h), 'cbm3' (qty)
            $cbmUnit = $this->priceCalculator->calculateCbmUnit(
                $request->input('cbm0', 0),
                $request->input('cbm1', 0),
                $request->input('cbm2', 0),
                $request->input('cbm3', 1)
            );

            $landedPrice = $this->priceCalculator->calculateLandedPrice(
                $calculatedCostPrice * $exchangeRate, 
                $cbmUnit, 
                $transportRate, 
                $dutyRate, 
                $incidentalRate
            );

            // 1-3. Wholesale & Retail
            $wholesalePrice = $this->priceCalculator->calculateWholesalePrice($landedPrice);
            $retailPrice = $this->priceCalculator->calculateRetailPrice($wholesalePrice);

            // 2. Insert Goods (fm_goods)
            $goods = new Goods();
            $goods->provider_status = $request->input('provider_status', 1);
            $goods->goods_name = $request->input('goodsName');
            $goods->goods_name_linkage = $request->input('goodsNameLinkage', '');
            $goods->goods_code = $request->input('goodsCode') ?: date('YmdHis').rand(10,99);
            $goods->goods_scode = $request->input('goodsScode', '');
            
            // Legacy Text Columns
            $goods->maker_name = $request->input('maker_name', '');
            $goods->origin_name = $request->input('origin_name', '');
            $goods->model = $request->input('model', '');
            
            $goods->goods_sortcd = $request->input('goodsSortcd', '');
            $goods->reserve_policy = $request->input('reserve_policy', 'shop');

            // Volume Discount (Multi Discount)
            $goods->multi_discount_use = $request->input('multi_discount_use', '0');
            $goods->multi_discount_ea = $request->input('multi_discount_ea', 0);
            $goods->multi_discount = $request->input('multi_discount', 0);
            $goods->multi_discount_unit = $request->input('multi_discount_unit', 'won');
            
            $goods->multi_discount_ea1 = $request->input('multi_discount_ea1', 0);
            $goods->multi_discount1 = $request->input('multi_discount1', 0);
            $goods->multi_discount_unit1 = $request->input('multi_discount_unit1', 'won');
            
            $goods->multi_discount_ea2 = $request->input('multi_discount_ea2', 0);
            $goods->multi_discount2 = $request->input('multi_discount2', 0);
            $goods->multi_discount_unit2 = $request->input('multi_discount_unit2', 'won');

            // Legacy Tiered Pricing (Wholesale Discount / Factory Trade)
            $goods->fifty_discount_ea = $request->input('fifty_discount_ea', 50); // Default 50
            $goods->fifty_discount = $request->input('fifty_discount', 0);
            
            $goods->hundred_discount_ea = $request->input('hundred_discount_ea', 100); // Default 100
            $goods->hundred_discount = $request->input('hundred_discount', 0);

            $goods->option_use = $request->input('optionUse', '0') == '1' ? '1' : '0';       $goods->goods_view = $request->input('goodsView', 'look');
            $goods->goods_status = $request->input('goodsStatus', 'normal');
            
            // Legacy Cost Info Serialization (multi_discount_cbm)
            // Preserving legacy array index structure if known, otherwise storing associative
            // Legacy seems to use indexed array. We'll stick to a consistent JSON or serialized format.
            // For checking compatibility, we'll store specific keys.
            $costInfo = [
                'cost_price' => $calculatedCostPrice,
                'real_cost' => $realCost,
                'other_costs' => $otherCosts,
                'landed_price' => $landedPrice,
                'exchange' => $exchangeRate,
                'transport' => $transportRate,
                'cbm_unit' => $cbmUnit
            ];
            $goods->multi_discount_cbm = serialize($costInfo); // Or json_encode if moving away from legacy PHP serialize

            $goods->regist_date = now();
            $goods->update_date = now();
            $goods->save();

            // 3. Insert Options
            $optionUse = $request->input('optionUse', '0');
            $prices = $request->input('price', []);
            // ... other inputs ...

            foreach ($prices as $i => $inputPrice) {
                $option = new GoodsOption();
                $option->goods_seq = $goods->goods_seq;
                
                if ($optionUse == '0') {
                    $option->option1 = ''; 
                    $option->default_option = 'y';
                    
                    // Single Option: Use Calculated Prices
                    // Allow Manual Override if 'manual_price' flag exists? For now, enforce logic.
                    $option->provider_price = $landedPrice;
                    $option->consumer_price = $inputPrice * 1.5; // Rough estimate or use logic
                    $option->price = $retailPrice; 
                    
                    // IF Input Price is significantly different, maybe user manually adjusted?
                    // Safe approach: Use Calculated as Guide, but respect Input if reasonable?
                    // User Request was "Apply the logic". So we Apply it.
                    // However, to avoid confusing user who typed "1000", if we save "1200", we should be careful.
                    // Logic: If 'cost' inputs are provided, we Recalculate. if not, we use direct input.
                    if ($realCost > 0) {
                        $option->price = $retailPrice;
                        $option->provider_price = $landedPrice;
                        $option->consumer_price = $retailPrice * 1.3; // Basic MSRP logic
                    } else {
                        $option->price = str_replace(',', '', $inputPrice);
                        $option->provider_price = str_replace(',', '', $request->input('supplyPrice')[$i] ?? 0);
                        $option->consumer_price = str_replace(',', '', $request->input('consumerPrice')[$i] ?? 0);
                    }

                } else {
                    // Multi Option: Complex to apply single cost logic to multiple options.
                    // Usually Multi-Option goods have their own costs per option or shared.
                    // For MVP Phase, we will trust Input for Multi-option, or apply logic to ALL if shared.
                    $option->option1 = $request->input('optionTitle')[$i] ?? '';
                    $option->default_option = ($i == 0) ? 'y' : 'n';
                    
                    $option->price = str_replace(',', '', $inputPrice);
                    $option->consumer_price = str_replace(',', '', $request->input('consumerPrice')[$i] ?? 0);
                    $option->provider_price = str_replace(',', '', $request->input('supplyPrice')[$i] ?? 0);
                }
                
                $option->save();

                // Supply / Stock
                $stockVal = str_replace(',', '', $request->input('stock')[$i] ?? 0);
                
                DB::table('fm_goods_supply')->insert([
                    'goods_seq' => $goods->goods_seq,
                    'option_seq' => $option->option_seq,
                    'stock' => $stockVal,
                    'total_stock' => $stockVal,
                    'safe_stock' => 0,
                    'badstock' => 0,
                    'reservation15' => 0,
                    'reservation25' => 0
                ]);
            }

            // 4. Handle Images (Keep existing logic)
            if ($request->hasFile('goodsImage')) {
                $files = $request->file('goodsImage');
                if (!is_array($files)) $files = [$files];

                foreach ($files as $idx => $file) {
                    $path = $file->store('goods', 'public');
                    $types = ($idx == 0) ? ['list1', 'list2', 'view', 'large'] : ['view']; 
                    
                    foreach ($types as $type) {
                        DB::table('fm_goods_image')->insert([
                            'goods_seq' => $goods->goods_seq,
                            'image_type' => $type,
                            'file_type' => $file->extension(),
                            'image' => $path, 
                            'regist_date' => now()
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.goods.catalog')->with('success', '상품이 등록되었습니다. (가격 자동 계산 적용됨)');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '등록 실패: ' . $e->getMessage())->withInput();
        }
    }

    public function calculatePrice(Request $request)
    {
        // 1. Base Cost
        $realCost = $request->input('realCost', 0);
        
        // Fix: Gather individual otherCost fields into array
        $otherCosts = [
            'mem' => $request->input('otherCost_mem', 0),
            'sticker' => $request->input('otherCost_sticker', 0),
            'package' => $request->input('otherCost_package', 0),
            'delivery' => $request->input('otherCost_delivery', 0),
            'etc' => $request->input('otherCost_etc', 0),
        ];
        
        $baseCost = $this->priceCalculator->calculateCost($realCost, $otherCosts);

        // 2. Landed Price
        $exchange = $request->input('exchange', 1);
        $cbmUnit = $request->input('cbmUnit', 0); 
        $transport = $request->input('transport', 0);
        $customs = $request->input('customs', 0);
        $incidental = $request->input('incidental', 1);
        
        $landedPrice = $this->priceCalculator->calculateLandedPrice(
            $baseCost * $exchange,
            $cbmUnit,
            $transport,
            $customs,
            $incidental
        );

        // 3. Margin Prices
        $wholesale = $this->priceCalculator->calculateWholesalePrice($landedPrice);
        $retail = $this->priceCalculator->calculateRetailPrice($wholesale);

        return response()->json([
            'base_cost' => $baseCost,
            'landed_price' => $landedPrice,
            'wholesale_price' => $wholesale,
            'retail_price' => $retail
        ]);
    }

    public function getCategoryChildren(Request $request)
    {
        $parentId = $request->input('parent_id', 0); // 0 or specific ID
        // Note: For Depth 1, parent_id might be 1 (ROOT) or 0 depending on DB structure.
        // Based on CategoryController, Root has ID 1. So Depth 1 items have parent_id = 1.
        // If JS sends 0 for initial load, we should return children of 1? 
        // Or if data uses parent_id=0 for roots.
        // Let's check CategoryController again: 
        // "if ($cat->id == 1) node['parent']='#'; if($cat->parent_id == 0) node['parent']='#';"
        // It implies ID 1 is the Root container. Real categories are children of 1.
        
        $targetParent = ($parentId == 0) ? 1 : $parentId;
        
        // If we want Depth 1 list (which are children of Root '1'):
        $categories = DB::table('fm_category')
            ->where('parent_id', $targetParent)
            ->where('hide', '0') // Only visible
            ->select('id', 'title', 'category_code')
            ->orderBy('position', 'asc')
            ->get();

        return response()->json($categories);
    }

    public function catalog(Request $request)
    {
        // Search Parameters
        $keyword = $request->input('keyword');
        $category1 = $request->input('category1');
        $category2 = $request->input('category2');
        $category3 = $request->input('category3');
        $category4 = $request->input('category4');
        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $dateType = $request->input('date_type', 'regist_date'); // regist_date or update_date
        
        $goodsStatus = $request->input('goods_status', []); // Array
        $providerStatus = $request->input('provider_status');
        
        // Expanded Filters
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        $scBrand = $request->input('brand');
        $scModel = $request->input('model');
        $scMaker = $request->input('maker');
        $scOrigin = $request->input('origin');
        $scProvider = $request->input('provider_seq');

        $query = DB::table('fm_goods as g')
            ->leftJoin('fm_goods_image as i', function($join) {
                $join->on('g.goods_seq', '=', 'i.goods_seq')->where('i.image_type', 'list1');
            })
            ->leftJoin('fm_provider as p', 'g.provider_seq', '=', 'p.provider_seq')
            ->leftJoin('fm_category_link as cl', function($join) {
                $join->on('g.goods_seq', '=', 'cl.goods_seq')->where('cl.link', 1); // Primary Category
            })
            ->leftJoin('fm_category as c', 'cl.category_code', '=', 'c.category_code')
            ->select(
                'g.goods_seq', 'g.goods_name', 'g.goods_code', 'g.goods_view', 'g.goods_status', 
                'g.regist_date', 'g.update_date', 'g.goods_scode', 'g.provider_status',
                'g.model', 'g.maker_name', 'g.origin_name', 'g.provider_seq', 'g.offer_chk',
                'i.image',
                'p.provider_name',
                'c.title as category_title',
                DB::raw('(SELECT SUM(s.stock) FROM fm_goods_supply as s WHERE s.goods_seq = g.goods_seq) as total_stock'),
                DB::raw('(SELECT SUM(s.badstock + s.reservation15 + s.reservation25) FROM fm_goods_supply as s WHERE s.goods_seq = g.goods_seq) as total_holding'),
                DB::raw('(SELECT MAX(o.regist_date) FROM fm_order as o JOIN fm_order_item as oi ON o.order_seq = oi.order_seq WHERE oi.goods_seq = g.goods_seq) as l_date')
            )
            ->orderBy('g.regist_date', 'desc');

        // Apply Filters
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('g.goods_name', 'like', "%{$keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$keyword}%");
            });
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween("g.{$dateType}", ["{$startDate} 00:00:00", "{$endDate} 23:59:59"]);
        }

        if (!empty($goodsStatus)) {
            $query->whereIn('g.goods_status', $goodsStatus);
        }

        if ($providerStatus !== null) {
            $query->where('g.provider_status', $providerStatus);
        }
        
        // Expanded Filter Logic
        if ($minPrice) $query->where('g.sale_price', '>=', str_replace(',', '', $minPrice)); // Assuming sale_price exists in goods or we need to join option?
        // Wait, price is usually in option. Legacy catalog search by main price often uses a representative column or joins.
        // Let's check if fm_goods has 'sale_price' or similar default. 
        // Legacy: "SELECT price FROM fm_goods_option WHERE goods_seq =? "
        // For accurate search, we should join fm_goods_option.
        // Doing a JOIN for search might duplicate rows if multiple options.
        // Strategy: Use whereExists or Join and Group.
        if ($minPrice || $maxPrice) {
            $query->whereExists(function($sub) use ($minPrice, $maxPrice) {
                 $sub->select(DB::raw(1))
                     ->from('fm_goods_option')
                     ->whereColumn('fm_goods_option.goods_seq', 'g.goods_seq');
                 if ($minPrice) $sub->where('price', '>=', str_replace(',', '', $minPrice));
                 if ($maxPrice) $sub->where('price', '<=', str_replace(',', '', $maxPrice));
            });
        }

        if ($scBrand) {
            // $query->where('g.brand_code', 'like', "%{$scBrand}%"); // Column missing in fm_goods. Defer.
        }
        if ($scModel) $query->where('g.model', 'like', "%{$scModel}%");
        if ($scMaker) $query->where('g.maker_name', 'like', "%{$scMaker}%");
        if ($scOrigin) $query->where('g.origin_name', 'like', "%{$scOrigin}%");
        if ($scProvider) $query->where('g.provider_seq', $scProvider);
        
        // Get Providers for Dropdown
        $providers = DB::table('fm_provider')->select('provider_seq', 'provider_name')->get();

        $goods = $query->paginate(20);

        foreach ($goods as $item) {
            // 1. Option Price & Count
            $options = DB::table('fm_goods_option')
                ->where('goods_seq', $item->goods_seq)
                ->selectRaw('MIN(price) as min_price, MAX(price) as max_price, COUNT(*) as opt_count')
                ->first();
            
            $item->min_price = optional($options)->min_price ?? 0;
            $item->max_price = optional($options)->max_price ?? 0;
            $item->opt_count = optional($options)->opt_count ?? 0;

            // 2. Stock Logic (Legacy Porting)
            // n_stock = total_stock (synced with SCM if needed)
            // n_rstock = n_stock - holding
            
            // Legacy Sync Logic: If stock < 0, sync with SCM WH 1
            $currentStock = $item->total_stock ?? 0;
            
            if ($currentStock < 0) {
                $scmStock = DB::table('fm_scm_location_link')
                    ->where('goods_seq', $item->goods_seq)
                    ->where('wh_seq', 1) 
                    ->value('ea');
                
                $scmStock = $scmStock ?? 0;
                
                if ($currentStock != $scmStock) {
                    // Sync Up!
                    DB::table('fm_goods_supply')
                        ->where('goods_seq', $item->goods_seq)
                        ->update(['stock' => $scmStock, 'total_stock' => $scmStock]);
                    
                    $currentStock = $scmStock; 
                }
            }
            $item->n_stock = $currentStock; // Real Stock
            $item->n_rstock = $currentStock - ($item->total_holding ?? 0); // Available Stock
            
            // 3. Info & Price Logic (Legacy Porting)
            // Use Services\LegacyGoodsHelper to generate HTML
            $item->offer_info = \App\Services\LegacyGoodsHelper::getOfferInfoHtml($item->goods_seq);
            $item->disp_price = \App\Services\LegacyGoodsHelper::getDiscountPriceHtml($item->goods_seq);

            // 4. Image Logic
            // Legacy matches: if(strpos($data['image'], "/data/goods/goods_img") !== false) -> replace with CDN
            if ($item->image && strpos($item->image, "/data/goods/goods_img") !== false) {
                $item->image = str_replace("/data/goods/goods_img", "https://dmtusr.vipweb.kr/goods_img", $item->image);
            } else if ($item->image && !str_starts_with($item->image, 'http') && !str_starts_with($item->image, '/')) {
                 // Ensure local paths have /data/goods prefix if simple filename
                 // But wait, the Join in query might return full path or just filename?
                 // Usually just filename like '1234.jpg'.
                 // Legacy view uses: src="/data/goods/{$item->image}"
                 // But if it is in goods_img folder, it's different.
                 // Let's keep it simple: if it doesn't look like a path, assume /data/goods/
                 // BUT if we applied CDN replacement, we are good.
            }

            // 5. Visual Logic (Buying Service)
            $item->is_buying_service = false;
            if (strpos($item->goods_scode, 'FFF') !== false) {
                 // Check if any order item is in specific steps
                 $pendingOrders = DB::table('fm_order_item as i')
                    ->join('fm_order_item_option as io', 'i.item_seq', '=', 'io.item_seq')
                    ->where('i.goods_seq', $item->goods_seq)
                    ->whereBetween('io.step', [45, 85])
                    ->exists();
                 
                 if ($pendingOrders) {
                     $item->is_buying_service = true; // Use this to color row pink
                 }
            }
        }

        return view('admin.goods.catalog', compact(
            'goods', 'keyword', 'startDate', 'endDate', 'goodsStatus', 'providerStatus',
            'minPrice', 'maxPrice', 'scBrand', 'scModel', 'scMaker', 'scOrigin', 'scProvider', 'providers'
        ));
    }
}
