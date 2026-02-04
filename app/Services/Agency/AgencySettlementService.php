<?php

namespace App\Services\Agency;

use Illuminate\Support\Facades\DB;
use Exception;

class AgencySettlementService
{
    /**
     * Deduct Agency Cash (Investment) when an order is placed.
     * This represents the Agency Seller buying the product from Dometopia (ATS).
     * 
     * @param string $orderSeq
     * @param int $memberSeq Agency Seller Member Seq
     * @param int $amount Total Provider Price (Supply Price * Qty)
     * @return int Inserted Cash Seq
     */
    public function deductAgencyCash($orderSeq, $memberSeq, $amount)
    {
        // 1. Check current cash
        $currentCash = $this->getCurrentCash($memberSeq);
        
        if ($currentCash < $amount) {
            throw new Exception("예치금이 부족합니다. (필요: {$amount}, 보유: {$currentCash})");
        }

        // 2. Deduct Cash
        $cashSeq = DB::table('fm_cash')->insertGetId([
            'member_seq' => $memberSeq,
            'type' => 'order', // Using 'order' as generic deduction type for now
            'ordno' => $orderSeq,
            'gb' => 'minus',
            'cash' => $amount,
            'remain' => $currentCash - $amount,
            'memo' => "ATS 상품 발주 차감 (주문번호: {$orderSeq})",
            'regist_date' => now(),
        ]);

        // 3. Update Member Cash Balance
        DB::table('fm_member')->where('member_seq', $memberSeq)->decrement('cash', $amount);

        return $cashSeq;
    }

    /**
     * Refund Agency Cash when an order is cancelled/refunded.
     * 
     * @param string $orderSeq
     * @param int $memberSeq
     * @param int $amount
     * @return int Inserted Cash Seq
     */
    public function refundAgencyCash($orderSeq, $memberSeq, $amount)
    {
        $currentCash = $this->getCurrentCash($memberSeq);

        $cashSeq = DB::table('fm_cash')->insertGetId([
            'member_seq' => $memberSeq,
            'type' => 'order', // Refund is also 'order' type in legacy often, or 'cancel'
            'ordno' => $orderSeq,
            'gb' => 'plus',
            'cash' => $amount,
            'remain' => $currentCash + $amount,
            'memo' => "ATS 상품 발주 취소 환불 (주문번호: {$orderSeq})",
            'regist_date' => now(),
        ]);

        // 4. Update Member Cash Balance (Increment)
        DB::table('fm_member')->where('member_seq', $memberSeq)->increment('cash', $amount);

        return $cashSeq;
    }

    /**
     * Calculate and update monthly settlement margin.
     * This is usually called when order is confirmed.
     * 
     * @param int $memberSeq
     * @param string $yearMonth 'YYYY-MM'
     * @param int $sellPrice End user payment
     * @param int $providerPrice Agency cost
     */
    public function settleAgencyMargin($memberSeq, $yearMonth, $sellPrice, $providerPrice)
    {
        // Check if record exists
        $record = DB::table('fm_account_provider_ats')
            ->where('member_seq', $memberSeq)
            ->where('acc_date', $yearMonth)
            ->first();

        $margin = $sellPrice - $providerPrice;

        if ($record) {
            DB::table('fm_account_provider_ats')
                ->where('seq', $record->seq)
                ->update([
                    'sell_price' => DB::raw("sell_price + {$sellPrice}"),
                    'offer_price' => DB::raw("offer_price + {$providerPrice}"), // Assuming offer_price is cost
                    'margin' => DB::raw("margin + {$margin}"),
                    'sell_ea' => DB::raw("sell_ea + 1"), // Simplified count
                    // update_date? Schema didn't show it but good to have
                ]);
        } else {
            DB::table('fm_account_provider_ats')->insert([
                'acc_date' => $yearMonth,
                'acc_status' => 'none',
                'member_seq' => $memberSeq,
                'sell_price' => $sellPrice,
                'offer_price' => $providerPrice,
                'margin' => $margin,
                'sell_ea' => 1,
                'regist_date' => now(),
            ]);
        }
    }

    private function getCurrentCash($memberSeq)
    {
        // Get last remain cash
        $lastCash = DB::table('fm_cash')
            ->where('member_seq', $memberSeq)
            ->orderBy('cash_seq', 'desc')
            ->value('remain');

        if ($lastCash === null) {
             // Fallback to fm_member cash if no logs found (Migration/Init case)
             return DB::table('fm_member')->where('member_seq', $memberSeq)->value('cash') ?? 0;
        }

        return $lastCash;
    }
}
