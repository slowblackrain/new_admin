<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerMemo;

class CustomerMemoController extends Controller
{
    /**
     * 상담 메모 관리 리스트 화면 (SPA 형식의 CRUD)
     */
    public function index()
    {
        $memos = CustomerMemo::orderBy('sort', 'asc')->orderBy('memo_idx', 'desc')->get();
        return view('admin.order.customer_memo.index', compact('memos'));
    }

    /**
     * 새로운 메모 저장
     */
    public function store(Request $request)
    {
        $request->validate([
            'memo' => 'required|string|max:255',
            'sort' => 'nullable|integer',
        ]);

        CustomerMemo::create([
            'memo' => $request->input('memo'),
            'sort' => $request->input('sort', 0),
            'point' => $request->has('point') && $request->input('point') == 'y' ? 'y' : 'n',
            'update_time' => now()
        ]);

        return redirect()->route('admin.order.customer_memo.index')->with('success', '상담 메모가 추가되었습니다.');
    }

    /**
     * 메모 수정 (Ajax 대응 처리)
     */
    public function update(Request $request)
    {
        $request->validate([
            'memo_idx' => 'required|exists:fm_customer_memo,memo_idx',
            'memo' => 'required|string|max:255',
            'sort' => 'nullable|integer',
        ]);

        $memo = CustomerMemo::findOrFail($request->input('memo_idx'));
        $memo->update([
            'memo' => $request->input('memo'),
            'sort' => $request->input('sort', 0),
            'point' => $request->has('point') && $request->input('point') == 'y' ? 'y' : 'n',
            'update_time' => now()
        ]);

        if($request->ajax()) {
            return response()->json(['success' => true, 'message' => '수정되었습니다.']);
        }

        return redirect()->route('admin.order.customer_memo.index')->with('success', '상담 메모가 수정되었습니다.');
    }

    /**
     * 메모 삭제
     */
    public function destroy(Request $request)
    {
        $memo_idx = $request->input('memo_idx');
        if (!$memo_idx) {
             return response()->json(['success' => false, 'message' => '잘못된 요청입니다.']);
        }

        CustomerMemo::where('memo_idx', $memo_idx)->delete();

        return response()->json(['success' => true, 'message' => '삭제되었습니다.']);
    }

    /**
     * 주문 상세에서 메모 가져오기용 팝업 뷰
     */
    public function popup()
    {
        $memos = CustomerMemo::orderBy('sort', 'asc')->orderBy('memo_idx', 'desc')->get();
        return view('admin.order.customer_memo.popup', compact('memos'));
    }
}
