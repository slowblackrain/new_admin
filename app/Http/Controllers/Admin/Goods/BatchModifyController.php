<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Category;
use App\Services\Goods\BatchModifyService;

class BatchModifyController extends Controller
{
    protected $batchModifyService;

    public function __construct(BatchModifyService $batchModifyService)
    {
        $this->batchModifyService = $batchModifyService;
    }

    public function index(Request $request)
    {
        $mode = $request->input('mode', 'price'); // Default mode
        
        // Search & Pagination Logic
        $query = Goods::query();

        // Apply filters (Name, Code, Category etc.)
        if ($request->filled('keyword')) {
            $query->where('goods_name', 'like', '%' . $request->keyword . '%');
        }
        if ($request->filled('category_code')) {
            $query->where('category_code', 'like', $request->category_code . '%');
        }

        $goodsList = $query->with('defaultOption')->orderBy('goods_seq', 'desc')->paginate(20);

        // Load Categories for Filter
        $categories = Category::whereRaw('length(category_code) = 4')->get();

        return view('admin.goods.batch.index', compact('goodsList', 'mode', 'categories'));
    }

    public function update(Request $request)
    {
        $mode = $request->input('mode');
        $ids = $request->input('goods_seq');
        $data = $request->all();

        $result = $this->batchModifyService->update($mode, $ids, $data);

        return redirect()->back()->with('alert', "업데이트 완료: 성공 {$result['success']}건, 실패 {$result['fail']}건");
    }
}
