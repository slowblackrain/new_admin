<?php

namespace App\Services\Scm;

use App\Services\Scm\ScmInventoryService;
use Illuminate\Support\Facades\DB;
use App\Models\Goods;

class ScmLedgerDetailService
{
    protected $inventoryService;

    public function __construct(ScmInventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Get Ledger Detail History
     * Dynamically unions source tables to reconstruct history.
     * 
     * @param int $goodsSeq
     * @param array $filters ['wh_seq', 'start_date', 'end_date']
     * @return array ['pre_stock', 'history', 'cur_stock', 'goods_info']
     */
    public function getHistory(int $goodsSeq, array $filters)
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $whSeq = $filters['wh_seq'] ?? null;

        // 1. Get Goods Info
        $goodsInfo = DB::table('fm_goods')
            ->leftJoin('fm_goods_option', function($join) {
                $join->on('fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
                     ->where('fm_goods_option.default_option', 'y');
            })
            ->where('fm_goods.goods_seq', $goodsSeq)
            ->when(isset($filters['option_seq']), function($q) use ($filters) {
                $q->where('fm_goods_option.option_seq', $filters['option_seq']);
            })
            ->select('fm_goods.goods_name', 'fm_goods.goods_code', 'fm_goods_option.option1 as option_name')
            ->first();

        if (!$goodsInfo) return null;

        // 2. Calculate Pre-Stock (Snapshot < StartDate)
        // We can reuse ScmInventoryService or ScmInOutHistoryService logic.
        // But for single item, we can just sum up EVERYTHING before StartDate from Ledger?
        // Or use the Snapshot logic?
        // The most accurate "Reconstruction" is summing source tables < StartDate.
        // However, fm_scm_ledger is the daily summary source of truth.
        // Let's use fm_scm_ledger to get the "Closing Stock" of the day BEFORE StartDate.
        
        $preStock = 0;
        $prePrice = 0; // Weighted Avg Price

        // Find the latest ledger entry BEFORE StartDate
        $lastLedger = DB::table('fm_scm_ledger')
            ->where('goods_seq', $goodsSeq)
            ->when(isset($filters['option_seq']), function($q) use ($filters) {
                $q->where('option_seq', $filters['option_seq']);
            })
            ->where('ldg_date', '<', $startDate)
            ->orderBy('ldg_date', 'desc')
            ->orderBy('ldg_seq', 'desc')
            ->when($whSeq, function($q) use ($whSeq) {
                return $q->where('wh_seq', $whSeq);
            })
            ->first();

        if ($lastLedger) {
            if ($whSeq) {
                $preStock = $lastLedger->wh_cur_ea;
                $prePrice = $lastLedger->wh_cur_supply_price;
            } else {
                $preStock = $lastLedger->cur_ea;
                $prePrice = $lastLedger->cur_supply_price;
            }
        } else {
            // No ledger before start date. 
            // Check if there are ANY transactions? 
            // If strictly relying on ledger, assumed 0.
        }

        // 3. Union Queries (StartDate <= Date <= EndDate)
        
        // A. Revision
        $qRevision = DB::table('fm_scm_stock_revision as r')
            ->join('fm_scm_stock_revision_goods as rg', 'r.revision_seq', '=', 'rg.revision_seq')
            ->select(
                'r.regist_date as date',
                DB::raw("'입출고 조정' as type"),
                DB::raw("CASE WHEN r.revision_type = 'in' THEN rg.ea ELSE 0 END as in_qty"),
                DB::raw("CASE WHEN r.revision_type = 'out' THEN rg.ea ELSE 0 END as out_qty"),
                'rg.supply_price', // Use goods detail price
                'r.admin_memo as memo',
                'r.wh_seq'
            )
            ->where('rg.goods_seq', $goodsSeq)
            ->when(isset($filters['option_seq']), function($q) use ($filters) {
                $q->where('rg.option_seq', $filters['option_seq']);
            })
            ->whereBetween(DB::raw('DATE(r.regist_date)'), [$startDate, $endDate]);

        // B. Stock Move
        // B. Stock Move
        $qMove = DB::table('fm_scm_stock_move as m')
            ->join('fm_scm_stock_move_goods as mg', 'm.move_seq', '=', 'mg.move_seq');
            // Select columns in strict order: date, type, in, out, price, memo, wh

        // Build Select based on WH Filter context
        $moveSelect = [
            'm.regist_date as date',
            DB::raw("'창고 이동' as type")
        ];

        if ($whSeq) {
             $moveSelect[] = DB::raw("CASE WHEN m.in_wh_seq = {$whSeq} THEN mg.ea ELSE 0 END as in_qty");
             $moveSelect[] = DB::raw("CASE WHEN m.out_wh_seq = {$whSeq} THEN mg.ea ELSE 0 END as out_qty");
             $qMove->whereRaw("(m.out_wh_seq = {$whSeq} OR m.in_wh_seq = {$whSeq})");
        } else {
             $moveSelect[] = 'mg.ea as in_qty';
             $moveSelect[] = 'mg.ea as out_qty';
        }

        $moveSelect[] = 'mg.supply_price';
        $moveSelect[] = DB::raw("CONCAT('FROM ', m.out_wh_seq, ' TO ', m.in_wh_seq) as memo");
        $moveSelect[] = 'm.out_wh_seq as wh_seq';

        $qMove->select($moveSelect)
              ->where('mg.goods_seq', $goodsSeq)
              ->when(isset($filters['option_seq']), function($q) use ($filters) {
                  $q->where('mg.option_seq', $filters['option_seq']);
              })
              ->whereBetween(DB::raw('DATE(m.regist_date)'), [$startDate, $endDate]);

        // C. Warehousing (In)
        $qIn = DB::table('fm_scm_warehousing as w')
            ->join('fm_scm_warehousing_goods as wg', 'w.whs_seq', '=', 'wg.whs_seq')
            ->select(
                'w.regist_date as date',
                DB::raw("'입고' as type"),
                'wg.ea as in_qty',
                DB::raw("0 as out_qty"),
                'wg.supply_price',
                DB::raw("CONCAT('발주번호: ', w.sorder_seq) as memo"),
                'w.wh_seq'
            )
            ->where('wg.goods_seq', $goodsSeq)
            ->when(isset($filters['option_seq']), function($q) use ($filters) {
                $q->where('wg.option_seq', $filters['option_seq']);
            })
            ->whereBetween(DB::raw('DATE(w.regist_date)'), [$startDate, $endDate]);

        // D. Carrying Out (Out)
        $qOut = DB::table('fm_scm_carryingout as c')
            ->join('fm_scm_carryingout_goods as cg', 'c.cro_seq', '=', 'cg.cro_seq')
            ->select(
                'c.regist_date as date',
                DB::raw("'반출' as type"),
                DB::raw("0 as in_qty"),
                'cg.ea as out_qty',
                'cg.supply_price',
                'c.admin_memo as memo',
                'c.wh_seq'
            )
            ->where('cg.goods_seq', $goodsSeq)
            ->when(isset($filters['option_seq']), function($q) use ($filters) {
                $q->where('cg.option_seq', $filters['option_seq']);
            })
            ->whereBetween(DB::raw('DATE(c.regist_date)'), [$startDate, $endDate]);

        // E. Order (Out)
        // Customer Orders: fm_order -> fm_order_item -> fm_order_item_option
        $qOrder = DB::table('fm_order_item_option as oio')
            ->join('fm_order_item as oi', 'oio.item_seq', '=', 'oi.item_seq')
            ->join('fm_order as o', 'oi.order_seq', '=', 'o.order_seq')
            ->select(
                'o.regist_date as date',
                DB::raw("'주문' as type"),
                DB::raw("0 as in_qty"),
                'oio.ea as out_qty',
                'oio.supply_price',
                DB::raw("CONCAT('주문번호: ', o.order_seq) as memo"),
                DB::raw("1 as wh_seq") // Default Sales WH
            )
            ->where('oi.goods_seq', $goodsSeq)
            ->when(isset($filters['option_seq']), function($q) use ($filters) {
                // fm_order_item_option has option_seq? Assuming yes, or we join back.
                // Actually fm_order_item has option_seq? No, fm_order_item_option has it.
                // Let's check schema assumption. fm_order_item_option usually has option_seq or we infer from item_seq?
                // Standard: fm_order_item_option.option_seq exists.
                 $q->where('oio.option_seq', $filters['option_seq']);
            })
            ->whereBetween(DB::raw('DATE(o.regist_date)'), [$startDate, $endDate])
            ->whereRaw("o.step >= 25"); // Valid orders only (Raw to match bindings)

        // Apply WH Filters BEFORE Union
        if ($whSeq) {
             // Use whereRaw to prevent binding count mismatch (Keep all at 3 params)
             $qRevision->whereRaw("r.wh_seq = {$whSeq}");
             $qIn->whereRaw("w.wh_seq = {$whSeq}");
             $qOut->whereRaw("c.wh_seq = {$whSeq}");
             
             if ($whSeq == 1) {
                 // Valid for WH1 (Default Sales WH)
             } else {
                 $qOrder->whereRaw("1 = 0"); 
             }
        }

        // Union All
        // Note: Using `unionAll` for performance.
        $query = $qRevision
            ->unionAll($qMove)
            ->unionAll($qIn)
            ->unionAll($qOut)
            ->unionAll($qOrder);

        $history = $query->orderBy('date', 'asc')->get();

        // 4. Compute Running Balance
        $runningStock = $preStock;
        
        $processedHistory = $history->map(function($item) use (&$runningStock) {
            $in = (float) $item->in_qty;
            $out = (float) $item->out_qty;
            $runningStock = $runningStock + $in - $out;
            
            $item->current_stock = $runningStock;
            return $item;
        });

        return [
            'goods_info' => $goodsInfo,
            'pre_stock' => $preStock,
            'history' => $processedHistory,
            'cur_stock' => $runningStock
        ];
    }
}
