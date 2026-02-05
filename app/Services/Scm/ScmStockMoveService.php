<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class ScmStockMoveService
{
    protected $ledgerService;

    public function __construct(ScmLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Get Stock Move List
     */
    public function getScmStockMoveList(array $filters = [])
    {
        $query = DB::table('fm_scm_stock_move')
            ->select(
                'fm_scm_stock_move.*',
                // Subquery for Total EA if not stored in header? 
                // Header has move_code, out_wh, in_wh. 
                // fm_scm_stock_move_goods has details.
                // Assuming we want to show total ea on list.
                DB::raw('(SELECT SUM(ea) FROM fm_scm_stock_move_goods WHERE fm_scm_stock_move_goods.move_seq = fm_scm_stock_move.move_seq) as total_ea')
            );

        if (!empty($filters['keyword'])) {
             $query->where('move_code', 'like', '%' . $filters['keyword'] . '%');
        }

        // Add Date Range Filter logic if needed (Legacy usually has sc_date)
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
             $query->whereBetween('regist_date', [$filters['start_date'] . ' 00:00:00', $filters['end_date'] . ' 23:59:59']);
        }

        return $query->orderBy('regist_date', 'desc')->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Create/Execute Stock Move
     * 
     * @param int $outWhSeq Source Warehouse
     * @param int $inWhSeq Target Warehouse
     * @param array $items Array of ['goods_seq', 'option_seq', 'option_type', 'ea']
     * @param string|null $adminMemo
     * @return int Created Move Sequence
     */
    public function processStockMove(int $outWhSeq, int $inWhSeq, array $items, ?string $adminMemo = '')
    {
        return DB::transaction(function () use ($outWhSeq, $inWhSeq, $items, $adminMemo) {
            // 1. Generate Code
            $moveSeq = DB::table('fm_scm_stock_move')->max('move_seq') + 1;
            $moveCode = 'SM' . date('YmdHis') . $moveSeq;

            // 2. Create Header
            $moveSeq = DB::table('fm_scm_stock_move')->insertGetId([
                'move_code' => $moveCode,
                'out_wh_seq' => $outWhSeq,
                'in_wh_seq' => $inWhSeq,
                'regist_date' => Carbon::now(),
                'admin_memo' => $adminMemo,
                'move_status' => '1',
                'complete_date' => Carbon::now(),
            ]);

            $outLedgerTargets = [];
            $inLedgerTargets = [];

            foreach ($items as $item) {
                $ea = (int)$item['ea'];
                
                // 3. Create Details
                DB::table('fm_scm_stock_move_goods')->insert([
                    'move_seq' => $moveSeq,
                    'goods_seq' => $item['goods_seq'],
                    'option_seq' => $item['option_seq'],
                    'option_type' => $item['option_type'] ?? 'option',
                    'ea' => $ea,
                    // Basic info placeholders
                    'goods_name' => '', 
                    'goods_code' => '',
                    'option_name' => '',
                    'supply_price' => 0,
                    'krw_supply_price' => 0
                ]);

                // 4. Update Stock (Per Warehouse)
                // Source (Out) -> Decrement
                $this->updateWarehouseStock($outWhSeq, $item, -$ea);
                
                // Target (In) -> Increment
                $this->updateWarehouseStock($inWhSeq, $item, $ea);

                // Prepare Ledger Targets
                $common = [
                    'goods_seq' => $item['goods_seq'], 
                    'option_seq' => $item['option_seq'], 
                    'option_type' => $item['option_type'] ?? 'option'
                ];
                $outLedgerTargets[] = $common;
                $inLedgerTargets[] = $common;
            }

            // 5. Update Ledger
            if (!empty($outLedgerTargets)) {
                $this->ledgerService->updateDailyLedger($outWhSeq, $outLedgerTargets);
            }
            if (!empty($inLedgerTargets)) {
                $this->ledgerService->updateDailyLedger($inWhSeq, $inLedgerTargets);
            }

            return $moveSeq;
        });
    }

    /**
     * Update Stock in fm_scm_location_link (Warehouse specific stock)
     */
    protected function updateWarehouseStock($whSeq, $item, $eaChange)
    {
        // Try to find existing record
        $exists = DB::table('fm_scm_location_link')
            ->where('wh_seq', $whSeq)
            ->where('goods_seq', $item['goods_seq'])
            ->where('option_seq', $item['option_seq'])
            ->exists();

        if ($exists) {
            if ($eaChange > 0) {
                DB::table('fm_scm_location_link')
                    ->where('wh_seq', $whSeq)
                    ->where('goods_seq', $item['goods_seq'])
                    ->where('option_seq', $item['option_seq'])
                    ->increment('ea', $eaChange);
            } else {
                DB::table('fm_scm_location_link')
                    ->where('wh_seq', $whSeq)
                    ->where('goods_seq', $item['goods_seq'])
                    ->where('option_seq', $item['option_seq'])
                    ->decrement('ea', abs($eaChange));
            }
        } else {
            // Insert new record if positive change (Incoming)
            if ($eaChange > 0) {
                DB::table('fm_scm_location_link')->insert([
                    'wh_seq' => $whSeq,
                    'goods_seq' => $item['goods_seq'],
                    'option_seq' => $item['option_seq'],
                    'option_type' => $item['option_type'] ?? 'option',
                    'ea' => $eaChange,
                    'bad_ea' => 0,
                    'location_position' => '',
                    'location_code' => ''
                ]);
            } else {
                // Warning: Decrementing non-existent stock?
                // Legacy system might allow negative stock or create record with negative ea
                DB::table('fm_scm_location_link')->insert([
                    'wh_seq' => $whSeq,
                    'goods_seq' => $item['goods_seq'],
                    'option_seq' => $item['option_seq'],
                    'option_type' => $item['option_type'] ?? 'option',
                    'ea' => $eaChange,
                    'bad_ea' => 0,
                    'location_position' => '',
                    'location_code' => ''
                ]);
            }
        }
    }
}
