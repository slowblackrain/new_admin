<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use Exception;

class ScmTraderAccountService
{
    /**
     * 거래처 정산 목록 (일별 요약 + 현재 잔액 포함)
     * Replicates `get_traderaccount_list` from scmmodel.php
     */
    public function getTraderAccountList(array $filters)
    {
        $startDate = $filters['sc_sdate'] ?? null;
        $endDate = $filters['sc_edate'] ?? null;
        $traderSeq = $filters['trader_seq'] ?? null;
        $keyword = $filters['keyword'] ?? null;
        $perPage = $filters['per_page'] ?? 20;

        // Subquery 1: Traders with Balance (Latest Balance)
        $balanceSub = DB::table('fm_scm_trader_account as ta')
            ->select('trader_seq', 'act_balance as balance')
            ->whereIn('act_seq', function($q) {
                $q->select(DB::raw('MAX(act_seq)'))
                  ->from('fm_scm_trader_account')
                  ->groupBy('trader_seq');
            });

        // Subquery 2: Period Sums
        $periodSub = DB::table('fm_scm_trader_account')
            ->select(
                'trader_seq',
                DB::raw('SUM(act_in_price) as sum_in'), // Payable (입고금액)
                DB::raw('SUM(act_out_price) as sum_out'), // Paid (지급금액)
                DB::raw('MIN(act_date) as min_date'),
                DB::raw('MAX(act_date) as max_date')
            )
            ->where('act_seq', '>', 0);

        if ($startDate) $periodSub->where('act_date', '>=', $startDate);
        if ($endDate) $periodSub->where('act_date', '<=', $endDate);
        
        $periodSub->groupBy('trader_seq');

        // Main Query
        $query = DB::table('fm_scm_trader as t')
            ->leftJoinSub($balanceSub, 'bal', 't.trader_seq', '=', 'bal.trader_seq')
            ->leftJoinSub($periodSub, 'per', 't.trader_seq', '=', 'per.trader_seq')
            ->select(
                't.trader_seq',
                't.trader_name',
                't.trader_id',
                'bal.balance',
                DB::raw('IFNULL(per.sum_in, 0) as act_in_price'),
                DB::raw('IFNULL(per.sum_out, 0) as act_out_price')
            )
            ->where('t.trader_seq', '>', 0);

        if ($traderSeq) {
            $query->where('t.trader_seq', $traderSeq);
        }
        if ($keyword) {
            $query->where('t.trader_name', 'like', "%{$keyword}%");
        }

        // Calculate 'Carried Over' (Previous Balance)
        // Previous = Current Balance + Out(Period) - In(Period)
        // Note: This logic assumes 'balance' is the CURRENT global balance.
        // If we want period-specific balance, we need more complex logic.
        // Legacy code: (balance + act_out_price - act_in_price) as carriedover
        $query->addSelect(DB::raw('(IFNULL(bal.balance, 0) + IFNULL(per.sum_out, 0) - IFNULL(per.sum_in, 0)) as carriedover'));

        return $query->orderBy('t.trader_name')->paginate($perPage);
    }

    /**
     * 거래처 정산 상세 내역
     * Replicates `get_traderaccount_detail`
     */
    public function getTraderAccountDetail(array $filters)
    {
        $traderSeq = $filters['trader_seq'];
        $startDate = $filters['sc_sdate'] ?? null;
        $endDate = $filters['sc_edate'] ?? null;
        $perPage = $filters['per_page'] ?? 20;

        $query = DB::table('fm_scm_trader_account_detail')
            ->where('trader_seq', $traderSeq);

        if ($startDate) $query->where('act_date', '>=', $startDate);
        if ($endDate) $query->where('act_date', '<=', $endDate);

        return $query->orderBy('regist_date', 'desc')->paginate($perPage);
    }

    /**
     * 정산 내역 저장 (지급/조정 등)
     * Replicates `save_traderaccount`
     */
    public function saveTraderAccount(array $data)
    {
        // 1. Calculate Balance
        $traderSeq = $data['trader_seq'];
        
        // Disable strict mode for this check if needed, or just get latest
        $lastDetail = DB::table('fm_scm_trader_account_detail')
            ->where('trader_seq', $traderSeq)
            ->orderBy('acdt_seq', 'desc')
            ->first();

        $carriedOver = $lastDetail ? $lastDetail->act_balance : 0;
        $actPrice = $data['act_price'];
        $actType = $data['act_type']; // 'pay'(지급), 'def'(이월), 'cal'(정산-수동?), 'carryingout'

        // Balance Logic: 
        // Pay (Out) -> Balance decreases
        // CarryingOut (Out) -> Balance decreases? Wait, CarryingOut is stock out.
        // If CarryingOut is a RETURN to provider, we get money back (or debt reduces)?
        // Legacy: if (pay || carryingout) balance = prev - price. 
        // So CarryingOut (Return) is treated as we receiving value (debt reduction). Correct.
        // Other (In - Warehousing) -> Balance increases (We owe more).

        if (in_array($actType, ['pay', 'carryingout'])) {
            $actBalance = $carriedOver - $actPrice;
        } else {
            // 'def' (Deferred?), 'in' (Warehousing)
            $actBalance = $carriedOver + $actPrice;
        }

        $actDate = $data['act_date'] ?? date('Y-m-d');

        $insertData = [
            'trader_seq' => $traderSeq,
            'act_type' => $actType,
            'act_date' => $actDate,
            'act_price' => $actPrice,
            'act_carriedover' => $carriedOver,
            'act_balance' => $actBalance,
            'act_memo' => $data['act_memo'] ?? '',
            'regist_date' => now(),
            // Additional fields if needed
            'org_type' => 'KRW',
            'org_price' => $data['org_price'] ?? 0,
            'exchange_price' => $data['exchange_price'] ?? 0,
            'changer' => auth()->id() ?? 0,
        ];

        $id = DB::table('fm_scm_trader_account_detail')->insertGetId($insertData);

        // Update Act Code if Pay
        if (in_array($actType, ['pay', 'def'])) {
            $actCode = strtoupper($actType) . date('YmdHis') . $id;
            DB::table('fm_scm_trader_account_detail')->where('acdt_seq', $id)->update(['act_code' => $actCode]);
        }

        // 2. Recalculate Daily Summary
        $this->calculateTraderAccount($traderSeq, $actDate);

        return $id;
    }

    /**
     * 일별 정산 집계 재계산
     * Replicates `calculate_traderaccount`
     */
    public function calculateTraderAccount($traderSeq, $date)
    {
        // Aggregate Details for the Date
        $summary = DB::table('fm_scm_trader_account_detail')
            ->where('trader_seq', $traderSeq)
            ->where('act_date', $date)
            ->select(
                DB::raw("SUM(IF(act_type='def', act_carriedover, 0)) as def_act_carriedover"),
                DB::raw("SUM(IF(act_type='pay', 0, act_price)) as act_in_price"), // Others = In (Debt Increase)
                DB::raw("SUM(IF(act_type='pay', act_price, 0)) as act_out_price") // Pay = Out (Debt Decrease)
            )
            ->first();

        // Get Latest Daily Account BEFORE this date/seq to determine carryover
        // Actually, legacy tries to find the previous day's balance or previous seq.
        // Simplified: Use the latest account record.
        
        $prevAccount = DB::table('fm_scm_trader_account')
            ->where('trader_seq', $traderSeq)
            ->where('act_date', '<', $date) // Strictly before
            ->orderBy('act_seq', 'desc')
            ->first();

        $carriedOver = $prevAccount ? $prevAccount->act_balance : 0;
        if (!$carriedOver && $summary->def_act_carriedover > 0) {
            $carriedOver = $summary->def_act_carriedover;
        }

        $inPrice = $summary->act_in_price ?? 0;
        $outPrice = $summary->act_out_price ?? 0;
        $balance = $carriedOver + $inPrice - $outPrice;

        // Check if entry exists for this date
        $exists = DB::table('fm_scm_trader_account')
            ->where('trader_seq', $traderSeq)
            ->where('act_date', $date)
            ->first();

        if ($exists) {
            DB::table('fm_scm_trader_account')
                ->where('act_seq', $exists->act_seq)
                ->update([
                    'act_in_price' => $inPrice,
                    'act_out_price' => $outPrice,
                    'act_balance' => $balance,
                ]);
        } else {
            DB::table('fm_scm_trader_account')->insert([
                'trader_seq' => $traderSeq,
                'act_date' => $date,
                'act_carriedover' => $carriedOver,
                'act_in_price' => $inPrice,
                'act_out_price' => $outPrice,
                'act_balance' => $balance,
                'regist_date' => now(),
            ]);
        }
    }
}
