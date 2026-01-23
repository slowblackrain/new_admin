<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class OrderDetailController extends Controller
{
    public function index($order_seq)
    {
        $order = Order::with([
            'items.options', 
            'items.goods.images', 
            'member'
        ])->where('order_seq', $order_seq)->firstOrFail();
        
        return view('admin.order.view', compact('order'));
    }

    public function searchGoods(Request $request)
    {
        $keyword = $request->keyword;
        if (!$keyword) return response()->json([]);

        $goods = \App\Models\Goods::with('images')
            ->where('goods_name', 'like', "%{$keyword}%")
            ->orWhere('goods_code', 'like', "%{$keyword}%")
            ->select('goods_seq', 'goods_name', 'goods_code')
            ->limit(20)
            ->get();
            
        $result = $goods->map(function($item) {
            return [
                'goods_seq' => $item->goods_seq,
                'goods_name' => $item->goods_name,
                'goods_code' => $item->goods_code,
                'image' => $item->images->first()->image ?? '/images/no_image.png',
                // Fetch price from first option or similar? 
                // Let's just say "Option Select Needed" or 0 if unknown here.
                // Or fetch min price via join? keeping simple.
                'price' => '-' 
            ];
        });

        return response()->json($result);
    }

    public function getOptions(Request $request)
    {
        $goodsSeq = $request->goods_seq;
        $options = \App\Models\GoodsOption::where('goods_seq', $goodsSeq)
            ->orderBy('option_seq')
            ->get(['option_seq', 'option1', 'price']);
            
        return response()->json($options);
    }
}
