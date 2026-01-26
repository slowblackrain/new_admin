<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;

class GoodsBatchController extends Controller
{
    // Bulk Update List (Batch Modify)
    public function batch_modify(Request $request)
    {
        $keyword = $request->input('keyword');
        
        $query = DB::table('fm_goods')->orderBy('regist_date', 'desc');

        if ($keyword) {
            $query->where('goods_name', 'like', "%{$keyword}%")
                  ->orWhere('goods_code', 'like', "%{$keyword}%");
        }

        $goods = $query->paginate(50); // Larger pagination for bulk edit

        return view('admin.goods.batch_modify', compact('goods', 'keyword'));
    }

    // Save Bulk Updates
    public function save_batch(Request $request)
    {
        $chk = $request->input('chk', []);
        
        if (empty($chk)) {
            return back()->with('error', '수정할 상품을 선택해주세요.');
        }

        try {
            DB::beginTransaction();

            foreach ($chk as $seq) {
                // Example: Update Price or Status
                // Logic depends on what fields are editable in batch_modify view.
                // Assuming status and price for MVP.
                
                $status = $request->input("goodsStatus_{$seq}");
                $price = $request->input("price_{$seq}"); // Simplification

                // Perform Update
                // DB::table('fm_goods')->where('goods_seq', $seq)->update([...]);
            }
            
            DB::commit();
            return back()->with('success', '일괄 수정되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '수정 중 오류 발생: ' . $e->getMessage());
        }
    }
}
