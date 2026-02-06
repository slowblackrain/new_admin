<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Scm\ScmCarryingOutService;
use Illuminate\Support\Facades\DB;

class ScmCarryingOutController extends Controller
{
    protected $scmCarryingOutService;

    public function __construct(ScmCarryingOutService $scmCarryingOutService)
    {
        $this->scmCarryingOutService = $scmCarryingOutService;
    }

    /**
     * 반출 목록
     */
    public function index(Request $request)
    {
        $filters = $request->all();
        $filters['sc_sdate'] = $request->input('sc_sdate', date('Y-m-d'));
        $filters['sc_edate'] = $request->input('sc_edate', date('Y-m-d'));

        $list = $this->scmCarryingOutService->getCarryingOutList($filters);
        $traders = DB::table('fm_scm_trader')->get();
        $warehouses = DB::table('fm_scm_warehouse')->get();

        return view('admin.scm.carryingout.index', compact('list', 'filters', 'traders', 'warehouses'));
    }

    /**
     * 반출 등록 폼
     */
    public function create(Request $request)
    {
        $croSeq = $request->input('cro_seq');
        $cro = null;

        if ($croSeq) {
             $cro = $this->scmCarryingOutService->getCarryingOutData($croSeq);
        }

        $traders = DB::table('fm_scm_trader')->orderBy('trader_name')->get();
        $warehouses = DB::table('fm_scm_warehouse')->get();

        return view('admin.scm.carryingout.form', compact('cro', 'traders', 'warehouses'));
    }

    /**
     * 반출 저장
     */
    public function store(Request $request)
    {
        try {
            $data = $request->all();
            $id = $this->scmCarryingOutService->saveCarryingOut($data);

            return redirect()->route('admin.scm_carryingout.create', ['cro_seq' => $id])
                             ->with('success', '반출 처리가 완료되었습니다.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * 반출 삭제 (재고 복구)
     */
    public function destroy(Request $request)
    {
        try {
            $croSeq = $request->input('cro_seq');
            if(!$croSeq) throw new \Exception("Invalid Request");

            $this->scmCarryingOutService->deleteCarryingOut($croSeq);

            return redirect()->route('admin.scm_carryingout.index')
                             ->with('success', '반출 내역이 삭제되었습니다.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
