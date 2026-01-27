<?php

namespace App\Http\Controllers\Admin\Goods;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\GoodsExport;
use App\Imports\GoodsImport;

class GoodsBatchController extends Controller
{
    protected $batchService;

    public function __construct(\App\Services\Goods\BatchService $batchService)
    {
        $this->batchService = $batchService;
    }

    // Excel Download
    public function excel_download(Request $request) 
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 600); // 10 minutes
        return Excel::download(new \App\Exports\GoodsExport($request->all()), 'goods_list_'.date('Ymd').'.xlsx');
    }

    // Excel Form (Upload UI)
    public function excel_form()
    {
        return view('admin.goods.excel_form');
    }

    // Excel Upload (Process)
    public function excel_upload(Request $request)
    {
        // 1. Validate file
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv',
            'mode' => 'required|in:regist,update'
        ]);

        try {
            // 2. Process Import
            // We pass 'mode' to the Import class constructor
            Excel::import(new \App\Imports\GoodsImport($request->input('mode')), $request->file('excel_file'));

            return redirect()->back()->with('success', 'Excel processed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error during import: ' . $e->getMessage());
        }
    }

    // Batch Modify Redirect or Logic
    // If the view is submitting to 'batch_save', we need to align routes.
    // Assuming the view submits to route('admin.goods.batch.modify')
    // Batch Modify Logic (Integrated)
    public function batch_modify(Request $request)
    {
        $ids = $request->input('chk');
        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', '선택된 상품이 없습니다.');
        }

        $mode = $request->input('mode', 'status_update'); // Default simple status
        $count = 0;

        try {
            DB::beginTransaction();

            foreach ($ids as $id) {
                $status = $request->input('goodsStatus_'.$id);
                // Simple Per-Row Status Update from List View
                if ($status) {
                     DB::table('fm_goods')->where('goods_seq', $id)->update(['goods_status' => $status]);
                     $count++;
                }
                
                // Future Extension: Global Batch Update (e.g. Set all to 'stop')
                // if ($request->filled('batch_status')) { ... }
            }

            // Batch Price / Stock Logic (if submitted from dedicated batch form)
            // Implementation pending specific UI request, currently supporting list-row-update.

            DB::commit();
            return redirect()->back()->with('success', $count . '건의 상품이 수정되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', '일괄 수정 중 오류: ' . $e->getMessage());
        }
    }
    // JSON Search for SCM (Uses Default Connection -> Read: Live, Write: Local)
    public function search_json(Request $request) {
        $keyword = $request->input('keyword');
        
        if (!$keyword) return response()->json([]);

        $goods = DB::table('fm_goods')
            ->where('goods_name', 'like', "%{$keyword}%")
            ->orWhere('goods_code', 'like', "%{$keyword}%")
            ->select('goods_seq', 'goods_name', 'goods_code')
            ->limit(20)
            ->get();
            
        return response()->json($goods);
    }
}
