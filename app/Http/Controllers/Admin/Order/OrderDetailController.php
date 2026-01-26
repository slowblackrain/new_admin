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
            'member',
            'logs'
        ])->where('order_seq', $order_seq)->firstOrFail();
        
        return view('admin.order.view', compact('order'));
    }

    public function updateRecipient(Request $request) {
        $request->validate([
            'order_seq' => 'required',
            'recipient_user_name' => 'required',
            'recipient_phone' => 'required',
            'recipient_zipcode' => 'required',
            'recipient_address' => 'required',
            'recipient_address_detail' => 'required'
        ]);

        $order = Order::where('order_seq', $request->order_seq)->firstOrFail();
        
        // Log changes
        $old_data = sprintf(
            "이름:%s / 연락처:%s / 주소:(%s) %s %s", 
            $order->recipient_user_name, 
            $order->recipient_phone,
            $order->recipient_zipcode,
            $order->recipient_address,
            $order->recipient_address_detail
        );

        $order->update([
            'recipient_user_name' => $request->recipient_user_name,
            'recipient_phone' => $request->recipient_phone,
            'recipient_zipcode' => $request->recipient_zipcode,
            'recipient_address' => $request->recipient_address,
            'recipient_address_detail' => $request->recipient_address_detail
        ]);

        // Create Log
        \App\Models\OrderLog::create([
            'order_seq' => $order->order_seq,
            'type' => 'process',
            'actor' => '관리자', // Ideally auth()->user()->name
            'title' => '배송정보수정',
            'detail' => "이전정보: {$old_data}",
            'regist_date' => now(),
            'mtype' => 'm', // Manager
            'mseq' => 1 // Temporary until Auth implemented properly
        ]);

        return response()->json(['success' => true]);
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
