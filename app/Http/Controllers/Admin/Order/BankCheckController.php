<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mosms;
use App\Models\Order;
use App\Models\OrderOption;
use App\Models\OrderSubOption;
use App\Models\Goods;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BankCheckController extends Controller
{
    public function index(Request $request)
    {
        $query = Mosms::query()->orderBy('pg_status', 'desc')->orderBy('idx', 'desc');

        // Keywords (Depositor, Memo)
        if ($request->keyword) {
            $query->where(function ($q) use ($request) {
                $q->where('in_name', 'like', '%' . $request->keyword . '%')
                  ->orWhere('memo', 'like', '%' . $request->keyword . '%');
            });
        }

        // Unmatched Only (pg_status = 'R')
        if ($request->deli_ck) {
            $query->where('pg_status', 'R');
        }

        // Bank Filter
        if ($request->bank && is_array($request->bank)) {
            $query->whereIn('in_bank', $request->bank);
        }

        // Date Filter
        if ($request->sdate && $request->edate) {
            $query->whereBetween('update_time', [$request->sdate . ' 00:00:00', $request->edate . ' 23:59:59']);
        } elseif ($request->sdate) {
            $query->where('update_time', '>=', $request->sdate . ' 00:00:00');
        } elseif ($request->edate) {
            $query->where('update_time', '<=', $request->edate . ' 23:59:59');
        }

        $data = $query->paginate($request->perpage ?? 10);

        return view('admin.order.bank_check', compact('data'));
    }

    // Show Candidate Orders for Matching
    public function matchCandidates(Request $request)
    {
        $mosmsIdx = $request->idx;
        $sms = Mosms::find($mosmsIdx);

        if (!$sms || $sms->pg_status != 'R' || $sms->order_seq) {
            return response()->json(['error' => 'Invalid Request or Already Matched']);
        }

        // Legacy Loop: 72 hours window
        // Step < 25 (Waiting), Payment = 'bank'
        $candidates = Order::where('step', '<', 25)
            ->where('step', '>', 0)
            ->where('payment', 'bank')
            ->where('regist_date', '>=', Carbon::now()->subHours(72))
            ->orderBy('regist_date', 'desc')
            ->get();

        return view('admin.order.match_candidates', compact('sms', 'candidates'));
    }

    // Execute Matching
    public function processMatch(Request $request)
    {
        $request->validate([
            'mosms_idx' => 'required',
            'order_seq' => 'required'
        ]);

        $sms = Mosms::find($request->mosms_idx);
        $order = Order::find($request->order_seq);

        if (!$sms || !$order) {
            return response()->json(['success' => false, 'message' => 'Record not found']);
        }

        if ($order->step == 15) { // 'Waiting for Deposit'
            DB::beginTransaction();
            try {
                // 1. Update Order Step to 25
                $order->step = 25;
                $order->save();

                // 2. Log
                // In a real scenario, we use a logging service. For now just DB update.

                // 3. Update Inventory (Reservation)
                // Simplified: Logic to update reservation would go here.
                // For this task, we focus on status updates as per legacy analysis.

                // 4. Update Mosms
                $sms->pg_status = 'M';
                $sms->order_seq = $order->order_seq;
                $sms->memo = "수동매칭 완료 - [" . Auth::guard('admin')->user()->mname . "]";
                $sms->update_time = now();
                $sms->save();

                DB::commit();
                return response()->json(['success' => true, 'message' => '매칭 처리가 완료 되었습니다.']);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => false, 'message' => '주문 상태가 올바르지 않습니다.']);
    }
}
