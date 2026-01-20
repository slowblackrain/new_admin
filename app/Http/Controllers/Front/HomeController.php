<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\GoodsOption;
use App\Models\Category;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Fetch Categories for Sidebar (Level 2 or 3 usually, simplifying to top level for demo)
        // Legacy 'category_code' usually: 0001, 00010001, etc.
        // We'll just take the top 20 for display for now.
        // 1. Fetch Categories for Sidebar with Children
        $categories = Category::whereRaw('length(category_code) = 4')
            ->where('hide', '!=', '1')
            ->where('title', 'not like', '%낱개판매코너%')
            ->orderBy('position')
            ->with(['children' => function ($query) {
                $query->where('hide', '!=', '1')->orderBy('position');
            }])
            ->get();

        // 2. Fetch Best Products (Recommended) - Display ID: 7150
        $bestProducts = $this->getDisplayProducts(7150);
        
        // 3. Fetch New Products - Display ID: 7152
        $newProducts = $this->getDisplayProducts(7152);

        // 4. Fetch Main Banners (Banner ID: 11)
        $mainBannerGroup = \App\Models\DesignBanner::with('items')->find(11);
        $mainBanners = $mainBannerGroup ? $mainBannerGroup->items : collect([]);

        // Fallback for demo if DB is empty or missing (Optional, can be removed if confident in DB data)
        if ($mainBanners->isEmpty()) {
            $mainBanners = collect([
                (object)['image_url' => asset('images/legacy/main/banner/images_1.jpg'), 'link' => '/goods/catalog?code=004200130010'],
                (object)['image_url' => asset('images/legacy/main/banner/images_2.jpg'), 'link' => '/goods/catalog?code=002000230006'],
                (object)['image_url' => asset('images/legacy/main/banner/images_3.jpg'), 'link' => '/goods/catalog?code=00080021'],
                (object)['image_url' => asset('images/legacy/main/banner/images_4.jpg'), 'link' => '/goods/catalog?code=0052'],
                (object)['image_url' => asset('images/legacy/main/banner/images_5.jpg'), 'link' => '/goods/catalog?code=00280021'],
            ]);
        }

        // 5. GDF Planning (Display ID: 7160) - Random 3 items
        $gdfList = $this->getDisplayProducts(7160);
        if ($gdfList->isNotEmpty()) {
            $gdfList = $gdfList->count() > 3 ? $gdfList->random(3) : $gdfList;
        }

        // 6. Category Planning (fm_category.plan = 'y')
        // Used for "Category Best" sections or specific category focuses
        $categoryPlan = Category::where('plan', 'y')
            ->where('hide', '0')
            ->orderBy('position', 'asc')
            ->get();

        // 7. Special Rolling Banner (Display ID: 101810) - Bottom Right
        $specialRolling = $this->getDisplayProducts(101810);

        return view('front.main.index', compact('categories', 'bestProducts', 'newProducts', 'mainBanners', 'gdfList', 'categoryPlan', 'specialRolling'));
    }

    private function getDisplayProducts($displaySeq)
    {
        // 0. Get Display General Info (for limit calculation)
        $displayInfo = \Illuminate\Support\Facades\DB::table('fm_design_display')
            ->where('display_seq', $displaySeq)
            ->first();

        $limit = 20; // Default
        if ($displayInfo) {
            $countW = $displayInfo->count_w ?? 4; // Default count_w is usually 4 in legacy
            $countH = $displayInfo->count_h ?? 5; // Default count_h
             // If lattice_b style, logic might differ but generally w*h is safe
            // If lattice_b style, logic might differ but generally w*h is safe
            // Legacy logic: if perpage exists use it, else w*h
            // In legacy goodsdisplay.php:
            // if($this->count_w*$this->count_h < $this->perpage) ...
            
            // For now, let's trust count_w * count_h as the display limit
            // But usually perpage is the reliable one if set? 
            // Only fm_design_display columns shown in file view earlier were count_w, count_h. 
            // Let's assume w * h. 
            // Wait, looking at legacy code: 
            // $perpage = $this->count_w * $this->count_h;
            // So we will calculate it.
            $limit = $countW * $countH;
        }


        // 1. Get Display Tab Info
        $displayTab = \Illuminate\Support\Facades\DB::table('fm_design_display_tab')
            ->where('display_seq', $displaySeq)
            ->where('display_tab_index', 0)
            ->first();

        if (!$displayTab) {
            return collect();
        }

        // 2. Auto Display Logic (Event based)
        if ($displayTab->auto_use == 'y' && !empty($displayTab->auto_criteria)) {
            // Parse auto_criteria (e.g., "selectEvent=16,selectEventBenefits=...")
            $criteria = [];
            foreach (explode(',', $displayTab->auto_criteria) as $pair) {
                $parts = explode('=', $pair);
                if (count($parts) == 2) {
                    $criteria[$parts[0]] = urldecode($parts[1]);
                }
            }

            if (!empty($criteria['selectEvent'])) {
                $eventSeq = $criteria['selectEvent'];
                $benefitSeq = $criteria['selectEventBenefits'] ?? null;

                $event = \Illuminate\Support\Facades\DB::table('fm_event')
                    ->where('event_seq', $eventSeq)
                    ->first();

                if ($event) {
                    $query = Goods::where('goods_view', 'look')
                        ->where('goods_status', 'normal')
                        ->with(['option', 'images', 'activeIcons']);
                    
                    // Apply Event Rules
                    // 1. Specific Goods (goods_view)
                    if ($event->goods_rule == 'goods_view') {
                        $query->join('fm_event_choice as ec', 'fm_goods.goods_seq', '=', 'ec.goods_seq')
                              ->where('ec.choice_type', 'goods')
                              ->where('ec.event_seq', $eventSeq);
                        
                        if ($benefitSeq) {
                            $query->where('ec.event_benefits_seq', $benefitSeq);
                        }
                        
                        $query->orderBy('ec.event_choice_seq', 'asc');

                    } 
                    // 2. Category Based (category)
                    elseif ($event->goods_rule == 'category') {
                        // In legacy, it checks if goods are linked to the chosen categories.
                        // We need to join fm_category_link and check against fm_event_choice(category)
                        
                        $query->join('fm_category_link as l', 'fm_goods.goods_seq', '=', 'l.goods_seq')
                              ->join('fm_event_choice as ec_cat', function($join) use ($eventSeq) {
                                  $join->on('l.category_code', '=', 'ec_cat.category_code')
                                       ->where('ec_cat.choice_type', 'category')
                                       ->where('ec_cat.event_seq', '=', $eventSeq);
                              });

                        if ($benefitSeq) {
                            $query->where('ec_cat.event_benefits_seq', $benefitSeq);
                        }

                        // Apply Exclusions (except_goods, except_category)
                        // Legacy logic effectively filters out goods that match 'except_goods' type choices for this event.
                        $excludedGoods = \Illuminate\Support\Facades\DB::table('fm_event_choice')
                            ->where('event_seq', $eventSeq)
                            ->where('choice_type', 'except_goods')
                            ->pluck('goods_seq');
                        
                        if ($excludedGoods->isNotEmpty()) {
                            $query->whereNotIn('fm_goods.goods_seq', $excludedGoods);
                        }
                        
                        $query->distinct()->select('fm_goods.*');
                        $query->orderBy('fm_goods.goods_seq', 'desc');

                    } 
                    // 3. All Goods (all)
                    elseif ($event->goods_rule == 'all') {
                         // Apply Exclusions only
                        $excludedGoods = \Illuminate\Support\Facades\DB::table('fm_event_choice')
                            ->where('event_seq', $eventSeq)
                            ->where('choice_type', 'except_goods')
                            ->pluck('goods_seq');

                        if ($excludedGoods->isNotEmpty()) {
                            $query->whereNotIn('fm_goods.goods_seq', $excludedGoods);
                        }
                        
                        // Also legacy might have except_category, but it's complex to join for 'all'.
                        // Simplified to just except_goods for now as it's most common.
                        
                        $query->orderBy('fm_goods.goods_seq', 'desc');
                    }

                    return $query->limit($limit)->get();
                }
            }
        }

        // 3. Manual Display Logic (Fallback)
        $orderedIds = \Illuminate\Support\Facades\DB::table('fm_design_display_tab_item')
            ->where('display_seq', $displaySeq)
            ->where('display_tab_index', 0)
            ->orderBy('display_tab_item_seq')
            ->limit($limit)
            ->pluck('goods_seq')
            ->toArray();

        if (empty($orderedIds)) {
            return collect();
        }

        $placeholders = implode(',', array_fill(0, count($orderedIds), '?'));
        
        return Goods::where('goods_view', 'look')
            ->where('goods_status', 'normal')
            ->with(['option', 'images', 'activeIcons'])
            ->whereIn('goods_seq', $orderedIds)
            ->orderByRaw("FIELD(goods_seq, $placeholders)", $orderedIds)
            ->get();
    }
}
