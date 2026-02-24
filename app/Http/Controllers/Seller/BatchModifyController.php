<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Category;
use App\Services\Goods\BatchModifyService;
use Illuminate\Support\Facades\Auth;

class BatchModifyController extends Controller
{
    protected $batchModifyService;

    public function __construct(BatchModifyService $batchModifyService)
    {
        $this->batchModifyService = $batchModifyService;
    }

    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $mode = $request->input('mode', 'price'); // Default mode
        
        // Search & Pagination Logic
        $query = Goods::where('provider_seq', $seller->provider_seq);

        // Apply filters (Name, Code, Category etc.)
        if ($request->filled('keyword')) {
            $query->where(function($q) use ($request) {
                $q->where('goods_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('goods_code', 'like', '%' . $request->keyword . '%');
            });
        }
        if ($request->filled('category_code')) {
            // Depending on how category link is set up. Assume simple category_code column for now
            // typically fm_category_link is used. But for simple filter:
            // $query->where('category_code', 'like', $request->category_code . '%');
            // We'll skip complex category join unless necessary for MVP
        }

        // We eagerly load supply because we need supply stock, etc.
        $goodsList = $query->with(['defaultOption.supply'])->orderBy('goods_seq', 'desc')->paginate(20);

        // Load Categories for Filter
        $categories = Category::whereRaw('length(category_code) = 4')->get();

        return view('seller.goods.batch.index', compact('goodsList', 'mode', 'categories'));
    }

    public function update(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $mode = $request->input('mode');
        $ids = $request->input('goods_seq');
        $data = $request->all();

        // Security Check: Ensure all IDs belong to this seller
        $validIds = Goods::where('provider_seq', $seller->provider_seq)
                         ->whereIn('goods_seq', $ids)
                         ->pluck('goods_seq')
                         ->toArray();

        if (count($validIds) !== count($ids)) {
            return redirect()->back()->with('error', '권한이 없는 상품이 포함되어 있습니다.');
        }

        $result = $this->batchModifyService->update($mode, $validIds, $data);

        return redirect()->back()->with('alert', "업데이트 완료: 성공 {$result['success']}건, 실패 {$result['fail']}건");
    }
}
