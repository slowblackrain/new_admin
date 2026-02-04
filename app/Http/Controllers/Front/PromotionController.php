<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Goods;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        $now = now();
        $events = Event::where('display', 'y')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->orderBy('start_date', 'desc')
            ->paginate(10);

        return view('front.promotion.index', compact('events'));
    }

    public function view(Request $request)
    {
        $eventSeq = $request->input('seq');
        if (!$eventSeq) {
             $eventSeq = $request->route('seq');
        }

        $event = Event::where('event_seq', $eventSeq)
            ->where('display', 'y')
            ->firstOrFail();

        // Product Fetching Logic (Replicated from HomeController/Legacy)
        $query = Goods::where('goods_view', 'look')
            ->where('goods_status', 'normal')
            ->with(['option', 'images', 'activeIcons']);

        if ($event->goods_rule == 'goods_view') {
            $query->join('fm_event_choice as ec', 'fm_goods.goods_seq', '=', 'ec.goods_seq')
                  ->where('ec.choice_type', 'goods')
                  ->where('ec.event_seq', $event->event_seq)
                  ->orderBy('ec.event_choice_seq', 'asc');
        } 
        elseif ($event->goods_rule == 'category') {
            $query->join('fm_category_link as l', 'fm_goods.goods_seq', '=', 'l.goods_seq')
                  ->join('fm_event_choice as ec_cat', function($join) use ($event) {
                      $join->on('l.category_code', '=', 'ec_cat.category_code')
                           ->where('ec_cat.choice_type', 'category')
                           ->where('ec_cat.event_seq', '=', $event->event_seq);
                  });
            
            // Exclusions
            $excludedGoods = DB::table('fm_event_choice')
                ->where('event_seq', $event->event_seq)
                ->where('choice_type', 'except_goods')
                ->pluck('goods_seq');
            
            if ($excludedGoods->isNotEmpty()) {
                $query->whereNotIn('fm_goods.goods_seq', $excludedGoods);
            }
            
            $query->distinct()->select('fm_goods.*');
            $query->orderBy('fm_goods.goods_seq', 'desc');
        }
        else { // 'all'
             $excludedGoods = DB::table('fm_event_choice')
                ->where('event_seq', $event->event_seq)
                ->where('choice_type', 'except_goods')
                ->pluck('goods_seq');

            if ($excludedGoods->isNotEmpty()) {
                $query->whereNotIn('fm_goods.goods_seq', $excludedGoods);
            }
            $query->orderBy('fm_goods.goods_seq', 'desc');
        }

        $goodsList = $query->paginate(20);

        return view('front.promotion.view', compact('event', 'goodsList'));
    }
}
