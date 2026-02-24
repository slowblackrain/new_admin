<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * 이벤트/기획전 리스트 조회 (admin/event/catalog)
     */
    public function catalog(Request $request)
    {
        // Legacy eventmodel.php merges fm_event and fm_gift
        $perPage = $request->input('perpage', 10);
        $searchKeyword = $request->input('keyword');

        // Subquery approach or merging collections based on legacy 'UNION' logic
        // For simplicity, we are retrieving fm_event first, expanding later if needed.
        $query = DB::table('fm_event');

        if ($searchKeyword) {
            $query->where('title', 'like', '%' . $searchKeyword . '%');
        }

        // Handle Event Status Filters (Ing, Before, End)
        /*
        We will rely on simple date clauses for MVP parity
        $today = now();
        $query->where('start_date', '<=', $today)->where('end_date', '>=', $today);
        */

        $events = $query->orderBy('start_date', 'desc')->paginate($perPage);

        return view('admin.event.catalog', compact('events'));
    }

    /**
     * 이벤트 등록/수정 폼
     */
    public function regist(Request $request)
    {
        $no = $request->input('no');
        $event = null;
        if ($no) {
            $event = DB::table('fm_event')->where('event_seq', $no)->first();
        }

        return view('admin.event.regist', compact('event'));
    }

    /**
     * 이벤트 등록/수정 처리
     */
    public function process(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            DB::beginTransaction();

            $eventData = [
                'title' => $request->input('title'),
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'event_introduce' => $request->input('event_introduce', ''),
                'update_date' => now(),
                'display' => $request->input('display', 'y'),
                'event_view' => $request->input('event_view', 'y'),
            ];

            if ($request->has('event_seq') && $request->input('event_seq')) {
                DB::table('fm_event')->where('event_seq', $request->input('event_seq'))->update($eventData);
            } else {
                $eventData['regist_date'] = now();
                DB::table('fm_event')->insert($eventData);
            }

            DB::commit();

            return redirect()->route('admin.event.catalog')->with('success', '이벤트가 성공적으로 저장되었습니다.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Event Process Error: ' . $e->getMessage());
            return back()->withInput()->with('error', '이벤트 저장 중 오류가 발생했습니다.');
        }
    }
}
