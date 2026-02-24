<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\OrderReturn;
use App\Models\OrderRefund;
use App\Models\Order;

class ClaimController extends Controller
{
    /**
     * 클레임 통합 리스트
     */
    public function index(Request $request)
    {
        // 탭 구분: all(전체), returns(반품/교환), refund(환불/결제취소)
        $tab = $request->input('tab', 'all');
        $keyword = $request->input('keyword', '');
        $status = $request->input('status', []); // array of statuses e.g. ['request', 'ing', 'complete']

        $perPage = 20;

        $returns = collect();
        $refunds = collect();

        // 1. 반품/교환 데이터 (fm_order_return)
        if (in_array($tab, ['all', 'returns'])) {
            $query = OrderReturn::with('order')->orderBy('regist_date', 'desc');

            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('return_code', 'like', "%{$keyword}%")
                      ->orWhere('order_seq', 'like', "%{$keyword}%");
                });
            }
            if (!empty($status)) {
                $query->whereIn('status', $status);
            }

            $returns = $query->paginate($perPage, ['*'], 'return_page')->appends(request()->query());
        }

        // 2. 환불/결제취소 데이터 (fm_order_refund)
        if (in_array($tab, ['all', 'refund'])) {
            $query = OrderRefund::with('order')->orderBy('regist_date', 'desc');

            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('refund_code', 'like', "%{$keyword}%")
                      ->orWhere('order_seq', 'like', "%{$keyword}%");
                });
            }
            if (!empty($status)) {
                $query->whereIn('status', $status);
            }

            $refunds = $query->paginate($perPage, ['*'], 'refund_page')->appends(request()->query());
        }

        return view('admin.order.claim_list', compact('tab', 'returns', 'refunds', 'keyword', 'status'));
    }

    /**
     * 선택된 클레임 내역 일괄 상태 처리 (AJAX)
     */
    public function process(Request $request)
    {
        $type = $request->input('type'); // 'return' or 'refund'
        $codes = $request->input('codes', []);
        $newStatus = $request->input('status'); // 'request', 'ing', 'complete'

        if (empty($codes) || !$newStatus || !in_array($type, ['return', 'refund'])) {
            return response()->json(['success' => false, 'message' => '잘못된 요청입니다.']);
        }

        try {
            DB::beginTransaction();

            if ($type == 'return') {
                OrderReturn::whereIn('return_code', $codes)->update([
                    'status' => $newStatus,
                    'status_date' => now()
                ]);
            } else {
                // refund
                OrderRefund::whereIn('refund_code', $codes)->update([
                    'status' => $newStatus,
                    'status_date' => now()
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => '상태 값이 성공적으로 변경되었습니다.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => '처리에 실패했습니다: ' . $e->getMessage()]);
        }
    }
}
