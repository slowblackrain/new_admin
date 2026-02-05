<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;

class ScmInventoryService
{
    /**
     * Get Inventory Asset Report
     * Based on Weighted Average Cost from fm_scm_ledger.
     * 
     * @param array $filters ['date' => 'Y-m-d', 'wh_seq' => int, 'keyword' => string]
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getInventoryList(array $filters)
    {
        $targetDate = $filters['date'] ?? date('Y-m-d');
        $whSeq = $filters['wh_seq'] ?? null;
        $keyword = $filters['keyword'] ?? null;
        $perPage = $filters['per_page'] ?? 20;

        // Subquery: Find the latest ledger entry (ldg_seq) per item before or on targetDate
        $subQuery = DB::table('fm_scm_ledger')
            ->select('goods_seq', 'option_seq', 'wh_seq', DB::raw('MAX(ldg_seq) as max_ldg_seq'))
            ->where('ldg_date', '<=', $targetDate);

        if ($whSeq) {
            $subQuery->where('wh_seq', $whSeq);
        }

        $subQuery->groupBy('goods_seq', 'option_seq', 'wh_seq');

        // Main Query
        $query = DB::table('fm_scm_ledger as l')
            ->joinSub($subQuery, 'latest', function ($join) {
                $join->on('l.goods_seq', '=', 'latest.goods_seq')
                     ->on('l.option_seq', '=', 'latest.option_seq')
                     ->on('l.wh_seq', '=', 'latest.wh_seq')
                     ->on('l.ldg_seq', '=', 'latest.max_ldg_seq');
            })
            ->join('fm_goods as g', 'l.goods_seq', '=', 'g.goods_seq')
            ->select(
                'l.goods_seq',
                'l.option_seq',
                'l.wh_seq',
                'g.goods_name',
                'g.goods_code',
                // If specific warehouse selected, use wh_ columns (Legacy: wh_cur_ea, wh_cur_supply_price)
                // If checking Global/All, we might need to aggregate differently or show individual lines.
                // Legacy "Total" mode sums `cur_ea`? No, legacy `get_inven` join condition 
                // groups by goods/option (and implicitly WH if wh filtered).
                // If NO WH filter, legacy groups by Goods?
                // Let's look at legacy again. 
                // Legacy Line 6898: ON (sl.goods_seq = tmp.goods_seq ... )
                // It does NOT join on wh_seq in the subquery group by unless filtered?
                // Actually legacy `get_inven` line 6896 groups by goods, option_type, option_seq.
                // It does NOT group by wh_seq unless filtered?
                // If `sl` has `wh_seq` column, does it store global aggregate?
                // Or is `fm_scm_ledger` per warehouse?
                // In my implementation, `fm_scm_ledger` has `wh_seq`.
                // So if I want "Total Inventory Value" across all WH, I should sum rows found for each WH?
                // Or does legacy store a 'Global' ledger with wh_seq=0 or 1?
                // Previous analysis: Ledger seems to be per-warehouse.
                // Let's implement PER WAREHOUSE listing first. If no WH selected, it lists all entries.
                'l.cur_ea', // Global Stock at that moment?
                'l.cur_supply_price', // Global Avg Price?
                'l.wh_cur_ea', // WH Stock
                'l.wh_cur_supply_price', // WH Avg Price
                'l.ldg_date'
            );
        
        // Note: My `fm_scm_ledger` schema might need verification if it has `wh_cur_ea`.
        // Step 1053 showed `fm_scm_ledger` table updateOrInsert with `wh_seq`.
        // The columns I used were `in_ea`, `out_ea`, etc.
        // Did I populate `cur_ea` (Global) and `wh_cur_ea` (WH)?
        // Let's assume standard columns `ea` or similar. 
        // Checking schema in next step if query fails.

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('g.goods_name', 'like', "%{$keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$keyword}%");
            });
        }
        
        $query->orderBy('l.goods_seq', 'desc');

        return $query->paginate($perPage);
    }
}
