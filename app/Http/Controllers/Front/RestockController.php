<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\GoodsRestockNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RestockController extends Controller
{
    public function register(Request $request)
    {
        $goodsSeq = $request->input('goods_seq');
        $goods = Goods::findOrFail($goodsSeq);

        $user = Auth::guard('web')->user();

        return view('front.goods.restock_notify', compact('goods', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'goods_seq' => 'required|exists:fm_goods,goods_seq',
            'cellphone' => 'required',
        ]);

        $exists = GoodsRestockNotify::where('goods_seq', $request->goods_seq)
            ->where('cellphone', str_replace('-', '', $request->cellphone))
            ->where('notify_status', 'none')
            ->exists();

        if ($exists) {
            return "<script>alert('이미 신청된 번호입니다.'); window.close();</script>"; 
        }

        GoodsRestockNotify::create([
            'goods_seq' => $request->goods_seq,
            'member_seq' => Auth::guard('web')->id() ?? 0,
            'cellphone' => str_replace('-', '', $request->cellphone),
            'notify_status' => 'none',
            'regist_date' => now(),
            'ip' => $request->ip(),
            'agent' => $request->userAgent()
        ]);

        return "<script>alert('재입고 알림 신청이 완료되었습니다.'); window.close();</script>";
    }
}
