<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmTraderAccountService;
use Exception;

class ScmTraderAccountController extends Controller
{
    protected $traderAccountService;

    public function __construct(ScmTraderAccountService $traderAccountService)
    {
        $this->traderAccountService = $traderAccountService;
    }

    /**
     * 정산 리스트 (잔액 현황)
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        // Defaults
        if (!isset($filters['sc_sdate'])) $filters['sc_sdate'] = date('Y-m-d', strtotime('-1 month')); // Default 1 month? Or empty for specific behavior?
        if (!isset($filters['sc_edate'])) $filters['sc_edate'] = date('Y-m-d');

        $list = $this->traderAccountService->getTraderAccountList($filters);

        return view('admin.scm.trader_account.index', compact('list', 'filters'));
    }

    /**
     * 정산 상세 내역 (원장)
     */
    public function show(Request $request, $traderSeq)
    {
        $filters = $request->all();
        $filters['trader_seq'] = $traderSeq;
        if (!isset($filters['sc_sdate'])) $filters['sc_sdate'] = date('Y-m-01');
        if (!isset($filters['sc_edate'])) $filters['sc_edate'] = date('Y-m-d');

        $detailList = $this->traderAccountService->getTraderAccountDetail($filters);
        
        // Trader Name fetching could be done in Service or here
        $trader = \App\Models\Scm\ScmTrader::find($traderSeq); // Assuming model exists or via DB query
        if (!$trader) {
            // Fallback if model not ready
            $trader = \Illuminate\Support\Facades\DB::table('fm_scm_trader')->where('trader_seq', $traderSeq)->first();
        }

        return view('admin.scm.trader_account.show', compact('detailList', 'filters', 'trader', 'traderSeq'));
    }

    /**
     * 정산 내역 저장 (지급/조정)
     */
    public function store(Request $request)
    {
        $data = $request->all();
        
        try {
            $this->traderAccountService->saveTraderAccount($data);
            return redirect()->back()->with('success', '성공적으로 저장되었습니다.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', '저장 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}
