<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;

class GoodsBatchController extends Controller
{
    public function index(Request $request)
    {
        // Simple Search
        // Join with default option to get price
        $query = DB::table('fm_goods as g')
            ->leftJoin('fm_goods_option as o', function($join) {
                $join->on('g.goods_seq', '=', 'o.goods_seq')
                     ->where('o.default_option', 'y');
            })
            ->select('g.*', 'o.price', 'o.consumer_price', 'o.supply_price', 'o.option_seq');
        
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function($q) use ($keyword) {
                $q->where('g.goods_name', 'like', "%{$keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$keyword}%");
            });
        }
        
        if ($request->filled('goods_status')) {
            $query->where('g.goods_status', $request->input('goods_status'));
        }

        $items = $query->orderBy('g.goods_seq', 'desc')->paginate(50);
        
        return view('admin.goods.batch_modify', compact('items'));
    }

    public function update(Request $request)
    {
        $selected = $request->input('goods_seq', []);
        if (empty($selected)) {
            return back()->with('error', '선택된 상품이 없습니다.');
        }

        $updates = $request->input('updates', []);
        
        DB::beginTransaction();
        try {
            foreach ($selected as $seq) {
                if (isset($updates[$seq])) {
                    $data = $updates[$seq];
                    
                    // Goods Table Updates
                    $goodsData = [];
                    if(isset($data['goods_name'])) $goodsData['goods_name'] = $data['goods_name'];
                    if(isset($data['goods_status'])) $goodsData['goods_status'] = $data['goods_status'];
                    if(isset($data['goods_view'])) $goodsData['goods_view'] = $data['goods_view'];
                    
                    if (!empty($goodsData)) {
                        DB::table('fm_goods')->where('goods_seq', $seq)->update($goodsData);
                    }
                    
                    // Option Table Updates (Prices)
                    // We update ALL options for this goods_seq because typical batch edit implies master price change
                    // Or we could just update default_option. 
                    // Let's update ALL options to be safe for now (or consistent).
                    $optionData = [];
                    if(isset($data['price'])) $optionData['price'] = str_replace(',', '', $data['price']);
                    if(isset($data['consumer_price'])) $optionData['consumer_price'] = str_replace(',', '', $data['consumer_price']);
                    if(isset($data['supply_price'])) $optionData['supply_price'] = str_replace(',', '', $data['supply_price']);
                    
                    if (!empty($optionData)) {
                        DB::table('fm_goods_option')->where('goods_seq', $seq)->update($optionData);
                    }
                }
            }
            DB::commit();
            return back()->with('success', count($selected) . '개 상품이 수정되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '수정 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}
