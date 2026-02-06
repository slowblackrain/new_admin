<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Http\Controllers\Controller;
use App\Services\Goods\GoodsSetService;
use App\Models\Goods;
use Illuminate\Http\Request;

class GoodsSetController extends Controller
{
    protected $service;

    public function __construct(GoodsSetService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $sets = $this->service->getMainSets($keyword);
        
        return view('admin.goods.set.index', compact('sets', 'keyword'));
    }

    public function detail(Request $request)
    {
        $parentSeq = $request->input('gno');
        $children = $this->service->getSetChildren($parentSeq);
        
        return view('admin.goods.set.detail', compact('children'));
    }

    public function search(Request $request)
    {
        $scode = $request->input('scode');
        $goods = Goods::where('goods_scode', $scode)->first();

        if ($goods) {
            return response()->json([
                'goods_seq' => $goods->goods_seq,
                'goods_name' => $goods->goods_name,
            ]);
        } else {
            return response()->json([], 404);
        }
    }

    public function store(Request $request)
    {
        $seq = $request->input('seq'); // Child Goods ID or New Main Goods ID
        $pno = $request->input('pno'); // Parent ID (0 for Main)
        $ea = $request->input('ea', 1);

        $result = $this->service->add($pno, $seq, $ea);

        return response($result['message']); // Return 'OK', 'Double', etc. for legacy compat
    }

    public function destroy(Request $request)
    {
        $seq = $request->input('seq'); // set_seq
        $result = $this->service->remove($seq);
        
        return response($result['message']);
    }
}
