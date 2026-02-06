<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmWarehousingService;
use App\Services\Scm\ScmOrderService;
use Illuminate\Support\Facades\DB;

class ScmWarehousingController extends Controller
{
    protected $scmWarehousingService;
    protected $scmOrderService;

    public function __construct(ScmWarehousingService $scmWarehousingService, ScmOrderService $scmOrderService)
    {
        $this->scmWarehousingService = $scmWarehousingService;
        $this->scmOrderService = $scmOrderService;
    }

    /**
     * 입고 목록 (Review/History)
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['sc_sdate'] = $request->input('sc_sdate', date('Y-m-d'));
        $filters['sc_edate'] = $request->input('sc_edate', date('Y-m-d'));

        $list = $this->scmWarehousingService->getWarehousingList($filters);
        $traders = DB::table('fm_scm_trader')->get();
        // Warehouses - assuming single warehouse for now as per legacy default
        $warehouses = DB::table('fm_scm_warehouse')->get();

        return view('admin.scm.warehousing.index', compact('list', 'filters', 'traders', 'warehouses'));
    }

    /**
     * 입고 등록 폼
     * Supports both New (Exception) and Loading from Order (Standard)
     */
    public function create(Request $request)
    {
        $whsSeq = $request->input('whs_seq');
        $sorderSeq = $request->input('sorder_seq');
        $whs = null;
        $order = null;

        if ($whsSeq) {
             $whs = $this->scmWarehousingService->getWarehousingData($whsSeq);
        } elseif ($sorderSeq) {
             // Load Data from Order for Standard Warehousing
             $order = $this->scmOrderService->getOrderData($sorderSeq);
             // Transform order items to warehousing items structure if needed
        }

        $traders = DB::table('fm_scm_trader')->orderBy('trader_name')->get();
        $warehouses = DB::table('fm_scm_warehouse')->get();

        return view('admin.scm.warehousing.form', compact('whs', 'order', 'traders', 'warehouses'));
    }

    /**
     * 입고 저장 (Process)
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $id = $this->scmWarehousingService->saveWarehousing($data);

            return redirect()->route('admin.scm_warehousing.create', ['whs_seq' => $id])
                             ->with('success', '입고 처리가 완료되었습니다.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
