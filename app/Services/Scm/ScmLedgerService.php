<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScmLedgerService
{
    /**
     * Update Daily Ledger for specific goods
     * Replicates save_ledger_today logic by aggregating today's movements.
     * 
     * @param int $whSeq Warehouse Sequence
     * @param array $targets Array of ['goods_seq', 'option_seq', 'option_type']
     */
    public function updateDailyLedger($whSeq, array $targets)
    {
        $today = Carbon::now();
        $todayDate = $today->format('Y-m-d');
        $year = $today->year;
        $month = $today->format('m'); // char(10) in schema? usually '01', '02'

        foreach ($targets as $target) {
            $goodsSeq = $target['goods_seq'];
            $optionSeq = $target['option_seq'];
            $optionType = $target['option_type'] ?? 'option';

            // 1. Calculate Today's IN (Warehousing)
            // Query fm_scm_warehousing_goods joined with fm_scm_warehousing
            $inStats = DB::table('fm_scm_warehousing_goods as wg')
                ->join('fm_scm_warehousing as w', 'wg.whs_seq', '=', 'w.whs_seq')
                ->where('w.wh_seq', $whSeq) // Filter by Warehouse
                ->where('wg.goods_seq', $goodsSeq)
                ->where('wg.option_seq', $optionSeq)
                ->whereDate('w.regist_date', $todayDate) // Use regist_date or complete_date? Legacy implies execution time.
                ->selectRaw('SUM(wg.ea) as total_ea, SUM(wg.supply_price * wg.ea) as total_price')
                ->first();
            
            $inEa = $inStats->total_ea ?? 0;
            $inPrice = $inStats->total_price ?? 0;

            // 2. Calculate Today's OUT (Carrying Out / Release)
            $outStats = DB::table('fm_scm_carryingout_goods as cg')
                ->join('fm_scm_carryingout as c', 'cg.cro_seq', '=', 'c.cro_seq')
                ->where('c.wh_seq', $whSeq)
                ->where('cg.goods_seq', $goodsSeq)
                ->where('cg.option_seq', $optionSeq)
                ->whereDate('c.regist_date', $todayDate)
                ->selectRaw('SUM(cg.ea) as total_ea, SUM(cg.supply_price * cg.ea) as total_price')
                ->first();
            
            $outEa = $outStats->total_ea ?? 0; 
            $outPrice = $outStats->total_price ?? 0;

            // 3. Calculate Today's Revision (Adjustment)
            // Positive -> IN, Negative -> OUT
            // Use fm_scm_stock_revision_goods (ea column is signed)
            $revStats = DB::table('fm_scm_stock_revision_goods as rg')
                ->join('fm_scm_stock_revision as r', 'rg.revision_seq', '=', 'r.revision_seq')
                ->where('r.wh_seq', $whSeq)
                ->where('rg.goods_seq', $goodsSeq)
                ->where('rg.option_seq', $optionSeq)
                ->whereDate('r.regist_date', $todayDate)
                ->selectRaw('
                    SUM(CASE WHEN rg.ea > 0 THEN rg.ea ELSE 0 END) as plus_ea,
                    SUM(CASE WHEN rg.ea < 0 THEN ABS(rg.ea) ELSE 0 END) as minus_ea
                ')
                ->first();
            
            $inEa += $revStats->plus_ea ?? 0;
            $outEa += $revStats->minus_ea ?? 0;

            // 4. Calculate Today's Stock Move
            // Move In (From other WH)
            $moveInStats = DB::table('fm_scm_stock_move_goods as mg')
                ->join('fm_scm_stock_move as m', 'mg.move_seq', '=', 'm.move_seq')
                ->where('m.in_wh_seq', $whSeq)
                ->where('mg.goods_seq', $goodsSeq)
                ->where('mg.option_seq', $optionSeq)
                ->whereDate('m.complete_date', $todayDate)
                ->selectRaw('SUM(mg.ea) as total_ea')
                ->first();
            $inEa += $moveInStats->total_ea ?? 0;

            // Move Out (To other WH)
            $moveOutStats = DB::table('fm_scm_stock_move_goods as mg')
                ->join('fm_scm_stock_move as m', 'mg.move_seq', '=', 'm.move_seq')
                ->where('m.out_wh_seq', $whSeq)
                ->where('mg.goods_seq', $goodsSeq)
                ->where('mg.option_seq', $optionSeq)
                ->whereDate('m.complete_date', $todayDate)
                ->selectRaw('SUM(mg.ea) as total_ea')
                ->first();
            $outEa += $moveOutStats->total_ea ?? 0;

            // 3. Get Current Stock (Cur)
            // Assuming wh_seq=1 is main stock or mapped to fm_goods_supply
            // fm_goods_supply tracks Global Stock. 
            // If wh_seq is specific, we might need another table, but for now assuming fm_goods_supply is the source of truth for total.
            
            $supply = DB::table('fm_goods_supply')
                ->where('goods_seq', $goodsSeq)
                ->where('option_seq', $optionSeq)
                ->first();
            
            $curEa = $supply->stock ?? 0;
            // $curSupplyPrice = $supply->supply_price ?? 0; // Unit price
            // Ledger stores Total Supply Price? "pre_supply_price" type decimal(11,2).
            // Schema: in_supply_price, out_supply_price, wh_pre_supply_price...
            // Likely value amounts.

            // 4. Calculate Previous Stock (Pre = Cur - In + Out)
            $preEa = $curEa - $inEa + $outEa;

            // 5. Upsert Ledger
            // Keys: ldg_date, goods_seq, option_seq, wh_seq
            DB::table('fm_scm_ledger')->updateOrInsert(
                [
                    'ldg_date' => $todayDate,
                    'goods_seq' => $goodsSeq,
                    'option_seq' => $optionSeq,
                    'wh_seq' => $whSeq,
                ],
                [
                    'ldg_year' => $year,
                    'ldg_month' => $month,
                    'goods_code' => '', // Optional to fill
                    'goods_name' => '', // Optional
                    'option_type' => $optionType,
                    'option_name' => '', // Optional
                    'in_ea' => $inEa,
                    'out_ea' => $outEa,
                    'in_supply_price' => $inPrice,
                    'out_supply_price' => $outPrice,
                    
                    // Warehouse Specific (Assuming same as Global for single WH setup)
                    'wh_pre_ea' => $preEa, 
                    'wh_cur_ea' => $curEa,
                    'wh_pre_supply_price' => 0, // Todo: calc
                    'wh_cur_supply_price' => 0, // Todo: calc

                    // Global Specific (Redundant if single WH)
                    'pre_ea' => $preEa,
                    'cur_ea' => $curEa,
                    'pre_supply_price' => 0,
                    'cur_supply_price' => 0,
                    'regist_date' => Carbon::now(),
                ]
            );
        }
    }
    /**
     * Get Ledger List with calculated fields (Weighted Average Cost)
     * Matches legacy get_ledger logic.
     */
    public function getLedgerList(array $filters)
    {
        $query = DB::table('fm_scm_ledger as l')
            ->leftJoin('fm_goods as g', 'l.goods_seq', '=', 'g.goods_seq') // for details
            ->select(
                'l.*',
                'g.goods_name',
                'g.goods_code',
                'g.scm_category'
            );

        // Date Filter
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('l.ldg_date', [$filters['start_date'], $filters['end_date']]);
        }

        // Warehouse Filter
        if (!empty($filters['wh_seq'])) {
            $query->where('l.wh_seq', $filters['wh_seq']);
        }

        // Keyword Filter
        if (!empty($filters['keyword'])) {
             $query->where(function ($q) use ($filters) {
                $q->where('g.goods_name', 'like', "%{$filters['keyword']}%")
                  ->orWhere('g.goods_code', 'like', "%{$filters['keyword']}%");
            });
        }

        $query->orderBy('l.ldg_date', 'desc')->orderBy('l.goods_seq', 'asc');

        $paginator = $query->paginate(20);

        // Calculate fields (Legacy Logic)
        $paginator->getCollection()->transform(function ($item) {
            // Pre Price (Total Value of Previous Stock)
            $preEa = $item->pre_ea;
            $preSupplyPrice = $item->pre_supply_price; // Historically averaged unit price
            $prePrice = $preEa * $preSupplyPrice;

            // In Price (Total Value of Inbound)
            // stored as total value? Schema check: in_supply_price usually total or unit? 
            // Legacy: $ldgData['in_supply_price'] seems to be Total In Value in some contexts or Unit?
            // In updateDailyLedger: 'in_supply_price' => $inPrice (Start line 41: selectRaw SUM(supply*ea) as total_price).
            // So 'in_supply_price' in DB IS TOTAL VALUE.
            $inTotalValue = $item->in_supply_price; 
            $inEa = $item->in_ea;
            
            // Out Price calculation (Weighted Average)
            // AvgCost = (PreValue + InValue) / (PreQty + InQty)
            $denominator = ($preEa + $inEa);
            $avgCost = 0;
            if ($denominator != 0) {
                $avgCost = ($prePrice + $inTotalValue) / $denominator;
            }

            $outEa = $item->out_ea;
            $outTotalValue = $outEa * $avgCost; // Calculated Out Value

            // Current (Closing)
            $curEa = $item->cur_ea;
            $curTotalValue = $curEa * $avgCost; // Valued at Avg Cost

            // Attach calculated fields to object
            $item->calc_pre_price = $prePrice;
            $item->calc_in_price = $inTotalValue;
            
            $item->calc_out_unit_price = $avgCost;
            $item->calc_out_price = $outTotalValue;
            
            $item->calc_cur_unit_price = $avgCost;
            $item->calc_cur_price = $curTotalValue;

            return $item;
        });

        return $paginator;
    }
}
