<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Scm\ScmStockRevision;
use App\Models\Scm\ScmStockRevisionGoods;
use Carbon\Carbon;

class ScmManageController extends Controller
{
    /**
     * SCM Stock Revision Management
     * Corresponds to legacy 'revision', 'revision_regist'
     */

    // Revision List
    public function revision(Request $request)
    {
        $query = ScmStockRevision::with('warehouse');

        if ($request->keyword) {
            $query->where('revision_code', 'like', "%{$request->keyword}%");
        }
        
        if ($request->sc_sdate && $request->sc_edate) {
             $query->whereBetween('regist_date', [$request->sc_sdate . ' 00:00:00', $request->sc_edate . ' 23:59:59']);
        }
        
        if ($request->wh_seq) {
             $query->where('wh_seq', $request->wh_seq);
        }

        $revisions = $query->orderBy('regist_date', 'desc')->paginate(20);
        $warehouses = \App\Models\Scm\ScmWarehouse::all();

        return view('admin.scm.manage.revision', compact('revisions', 'warehouses'));
    }

    // Revision Registration Form
    public function revision_regist(Request $request)
    {
        $warehouses = \App\Models\Scm\ScmWarehouse::all();
        $revision = null;

        if ($request->rsno) {
            $revision = ScmStockRevision::with(['goods' => function($q) {
                // $q->with('goods'); // If relation exists
            }])->where('revision_seq', $request->rsno)->first();
        }

        return view('admin.scm.manage.revision_regist', compact('warehouses', 'revision'));
    }

    // Save Revision (Process)
    public function revision_save(Request $request, \App\Services\Scm\ScmRevisionService $revisionService)
    {
        // Validation
        $data = $request->validate([
            'wh_seq' => 'required|integer',
            'admin_memo' => 'nullable|string',
            'goods_seq' => 'required|array',
            'option_seq' => 'required|array',
            'ea' => 'required|array',
            'reason' => 'nullable|array', // Allow array of reasons corresponding to items? Or single reason?
                                          // Legacy UI usually has one reason or per item.
                                          // Service expects array of items with 'ea'.
        ]);

        // Transform Request to Service Item Format
        $items = [];
        foreach ($data['goods_seq'] as $i => $goodsSeq) {
            $items[] = [
                'goods_seq' => $goodsSeq,
                'option_seq' => $data['option_seq'][$i],
                'option_type' => 'option', // Assuming basic option type for now
                'ea' => $data['ea'][$i],
                // 'reason' => $data['reason'][$i] ?? '' // If per-item reason exists
            ];
        }
        
        try {
            $revSeq = $revisionService->processRevision(
                $data['wh_seq'],
                $items,
                $data['admin_memo']
            );

            return redirect()->route('admin.scm.manage.revision.regist', ['rsno' => $revSeq])
                             ->with('message', '재고 조정이 완료되었습니다.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    // Stock Movement Management
    
    // Stock Move List (Using Service Logic Structure)
    public function stockmove(Request $request) {
        $query = DB::table('fm_scm_stock_move as m')
            ->join('fm_scm_warehouse as ow', 'm.out_wh_seq', '=', 'ow.wh_seq')
            ->join('fm_scm_warehouse as iw', 'm.in_wh_seq', '=', 'iw.wh_seq')
            ->select('m.*', 'ow.wh_name as out_wh_name', 'iw.wh_name as in_wh_name')
            ->orderBy('m.move_seq', 'desc');

        $moves = $query->paginate(20);

        return view('admin.scm.manage.stockmove.list', compact('moves'));
    }

    // Stock Move Regist Form
    public function stockmove_regist(Request $request) {
        $warehouses = \App\Models\Scm\ScmWarehouse::all();
        $move = null;
        if ($request->smno) {
            $move = DB::table('fm_scm_stock_move')->where('move_seq', $request->smno)->first();
            // TODO: load details ...
        }
        return view('admin.scm.manage.stockmove.regist', compact('warehouses', 'move'));
    }

    // Stock Move Save (Process)
    public function stockmove_save(Request $request, \App\Services\Scm\ScmStockMoveService $moveService) {
        $data = $request->validate([
            'out_wh_seq' => 'required|integer',
            'in_wh_seq' => 'required|integer|different:out_wh_seq',
            'admin_memo' => 'nullable|string',
            'goods_seq' => 'required|array',
            'option_seq' => 'required|array',
            'ea' => 'required|array',
        ]);

        $items = [];
        foreach ($data['goods_seq'] as $i => $goodsSeq) {
            $items[] = [
                'goods_seq' => $goodsSeq,
                'option_seq' => $data['option_seq'][$i],
                'option_type' => 'option', // simplified
                'ea' => $data['ea'][$i]
            ];
        }

        $moveSeq = $moveService->processStockMove(
            $data['out_wh_seq'],
            $data['in_wh_seq'],
            $items,
            $data['admin_memo']
        );

        return redirect()->route('admin.scm_manage.stockmove.regist', ['smno' => $moveSeq])
                         ->with('message', '재고 이동이 완료되었습니다.');
    }

    // Stock Ledger
    public function ledger(Request $request, \App\Services\Scm\ScmLedgerService $ledgerService) {
        $filters = [
            'start_date' => $request->sc_sdate ?? date('Y-m-01'),
            'end_date' => $request->sc_edate ?? date('Y-m-d'),
            'wh_seq' => $request->wh_seq,
            'keyword' => $request->keyword
        ];

        $logs = $ledgerService->getLedgerList($filters);
        $warehouses = \App\Models\Scm\ScmWarehouse::all();

        return view('admin.scm.manage.ledger', compact('logs', 'warehouses', 'filters'));
    }

    // Inventory Asset Report (Inven)
    public function inven(Request $request, \App\Services\Scm\ScmInventoryService $inventoryService) {
        $filters = [
            'date' => $request->sc_date ?? date('Y-m-d'),
            'wh_seq' => $request->wh_seq,
            'keyword' => $request->keyword,
            'per_page' => $request->per_page ?? 20
        ];

        $inventory = $inventoryService->getInventoryList($filters);
        $warehouses = \App\Models\Scm\ScmWarehouse::all();

        return view('admin.scm.manage.inven', compact('inventory', 'warehouses', 'filters'));
    }

    // Inventory In/Out History (Period Summary)
    public function inout_catalog(Request $request, \App\Services\Scm\ScmInOutHistoryService $inOutService) {
        $filters = [
            'start_date' => $request->sc_sdate ?? date('Y-m-01'),
            'end_date' => $request->sc_edate ?? date('Y-m-d'),
            'wh_seq' => $request->wh_seq,
            'keyword' => $request->keyword,
            'per_page' => $request->per_page ?? 20
        ];

        $history = $inOutService->getPeriodSummary($filters);
        $warehouses = \App\Models\Scm\ScmWarehouse::all();

        return view('admin.scm.manage.inout_catalog', compact('history', 'warehouses', 'filters'));
    }

    // In/Out History Excel Download
    public function inout_catalog_excel(Request $request, \App\Services\Scm\ScmInOutHistoryService $inOutService) {
        $filters = [
            'start_date' => $request->sc_sdate ?? date('Y-m-01'),
            'end_date' => $request->sc_edate ?? date('Y-m-d'),
            'wh_seq' => $request->wh_seq,
            'keyword' => $request->keyword,
        ];
        
        return $inOutService->downloadPeriodSummary($filters);
    }

    // SCM Goods Management (Safe Stock, Warnings)
    public function goods(Request $request, \App\Services\Scm\ScmGoodsService $goodsService) {
        $filters = [
            'wh_seq' => $request->wh_seq,
            'keyword' => $request->keyword,
            'warning_only' => $request->has('warning_only'),
            'per_page' => $request->per_page ?? 20,
            'trader_seq' => $request->trader_seq,
            'trader_group' => $request->trader_group,
            'category_code' => $request->category_code,
        ];

        $goods = $goodsService->getScmGoodsList($filters);
        $warehouses = \App\Models\Scm\ScmWarehouse::all();
        
        // Master Data for Filters (Legacy Parity)
        $traders = \App\Models\Scm\ScmTrader::orderBy('trader_name')->get();
        $traderGroups = \App\Models\Scm\ScmTrader::select('trader_group')->distinct()->whereNotNull('trader_group')->get();
        // Categories (Depth 1)
        $categories = \App\Models\Category::where('category_depth', 1)->orderBy('category_sort')->get();

        return view('admin.scm.manage.goods', compact('goods', 'warehouses', 'filters', 'traders', 'traderGroups', 'categories'));
    }

    // Ledger Detail View (Drill-down)
    public function ledger_detail(Request $request, \App\Services\Scm\ScmLedgerDetailService $detailService) {
        $goodsSeq = $request->goods_seq;
        if (!$goodsSeq) {
            return back()->with('error', '상품 번호가 필요합니다.');
        }

        $filters = [
            'start_date' => $request->sc_sdate ?? date('Y-m-01'),
            'end_date' => $request->sc_edate ?? date('Y-m-d'),
            'wh_seq' => $request->wh_seq,
            'option_seq' => $request->option_seq
        ];

        $data = $detailService->getHistory($goodsSeq, $filters);
        
        if (!$data) {
             return back()->with('error', '상품 정보를 찾을 수 없습니다.');
        }

        $warehouses = \App\Models\Scm\ScmWarehouse::all();

        return view('admin.scm.manage.ledger_detail', array_merge($data, compact('warehouses', 'filters', 'goodsSeq')));
    }

    // Process Auto Order Registration
    public function process_auto_order(Request $request, \App\Services\Scm\ScmGoodsService $goodsService) {
        $data = $request->validate([
            'goods_seq_list' => 'required|string',
            'add_ea_type' => 'required|in:direct,auto',
            'direct_ea' => 'nullable|integer',
            'store_seq' => 'nullable|integer',
            'warehouse_seq' => 'nullable|integer',
        ]);

        $goodsSeqs = explode(',', $data['goods_seq_list']);
        $count = $goodsService->createAutoOrder($goodsSeqs, $data);

        return redirect()->back()->with('message', "총 {$count}개의 상품이 자동발주상품에 등록되었습니다.");
    }

    // Goods Excel Download
    public function goods_excel_download(Request $request, \App\Services\Scm\ScmGoodsService $goodsService) {
        // Simple CSV Download using Service
        $target = $request->target; // 'select' or 'list'
        $type = $request->type; // 'supply', 'shop', 'stock'
        
        $filters = $request->all(); // Pass all filters
        
        if ($target == 'select' && !$request->goods_seq) {
            return back()->with('error', '선택된 상품이 없습니다.');
        }

        return $goodsService->downloadExcel($target, $type, $filters);
    }

    // Revision Excel Sample Download
    public function revision_excel_sample(\App\Services\Scm\ScmRevisionService $revisionService) {
        return $revisionService->downloadExcelSample();
    }

    // Revision Excel Upload
    public function save_revision_excel(Request $request, \App\Services\Scm\ScmRevisionService $revisionService) {
        $request->validate([
            'revision_excel_file' => 'required|file|mimes:xls,xlsx,csv'
        ]);

        try {
            $count = $revisionService->importRevisionExcel($request->file('revision_excel_file'));
            return redirect()->back()->with('message', "{$count}건의 재고가 조정되었습니다.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '엑셀 처리 중 오류가 발생했습니다: ' . $e->getMessage());
            return redirect()->back()->with('error', '엑셀 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    // Ledger Print View
    public function ledger_print(Request $request, \App\Services\Scm\ScmLedgerService $ledgerService) {
        $filters = [
            'start_date' => $request->sc_sdate ?? date('Y-m-d'),
            'end_date' => $request->sc_edate ?? date('Y-m-d'),
            'wh_seq' => $request->wh_seq,
            'keyword' => $request->keyword,
            // Print usually shows ALL or same pagination? Legacy uses _ledger which paginates. 
            // Let's assume it respects pagination but maybe larger limit?
            // Legacy ledger_print usually just prints the current page.
            'per_page' => $request->per_page ?? 20,
        ];

        // Reuse existing service method
        $data = $ledgerService->getLedgerList($filters);
        // getLedgerList returns Paginator directly, not array with logs key?
        // Let's check getLedgerList return type in Service.
        // It returns $paginator (Step 768, line 233).
        // So $logs = $data;
        $logs = $data;

        // Totals Calculation (Simple sum for the current page/view)
        // If we want total of ALL pages, we need a separate query. 
        // Legacy ledger_print usually prints what's on screen (page).
        // Legacy calculated totals in controller loop. We can do same or just let view handle if simple.
        
        return view('admin.scm.manage.ledger_print', compact('logs', 'filters'));
    }
}
