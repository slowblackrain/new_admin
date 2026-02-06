<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmAnalysisService;

class ScmAnalysisController extends Controller
{
    protected $scmAnalysisService;

    public function __construct(ScmAnalysisService $scmAnalysisService)
    {
        $this->scmAnalysisService = $scmAnalysisService;
    }

    /**
     * 월별 매입 분석 리포트
     */
    public function monthly(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $stats = $this->scmAnalysisService->getMonthlyPurchaseStats($year);

        return view('admin.scm.analysis.monthly', compact('stats', 'year'));
    }

    /**
     * 거래처별 매입 분석 리포트
     */
    public function trader(Request $request)
    {
        $startDate = $request->input('sc_sdate', date('Y-m-01'));
        $endDate = $request->input('sc_edate', date('Y-m-d'));

        $list = $this->scmAnalysisService->getTraderPurchaseStats($startDate, $endDate);

        return view('admin.scm.analysis.trader', compact('list', 'startDate', 'endDate'));
    }
}
