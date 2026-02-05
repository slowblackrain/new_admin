<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use App\Models\Goods;

class ScmInOutHistoryService
{
    /**
     * Get In/Out History Summary
     * 
     * @param array $filters ['start_date', 'end_date', 'wh_seq', 'keyword', 'per_page']
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPeriodSummary(array $filters)
    {
        $startDate = $filters['start_date'] ?? date('Y-m-01');
        $endDate = $filters['end_date'] ?? date('Y-m-d');
        $whSeq = $filters['wh_seq'] ?? null;
        $keyword = $filters['keyword'] ?? null;
        $perPage = $filters['per_page'] ?? 20;

        // 1. Base Query: Goods
        // We list Goods that have ANY ledger activity OR currently exist.
        // Actually, legacy lists ALL goods usually, or goods with stock.
        // Let's list goods matching keyword.
        $goodsQuery = Goods::query()->select('goods_seq', 'goods_code', 'goods_name');

        if ($keyword) {
            $goodsQuery->where(function($q) use ($keyword) {
                $q->where('goods_name', 'like', "%{$keyword}%")
                  ->orWhere('goods_code', 'like', "%{$keyword}%");
            });
        }
        
        // Optimize: Only include goods that have ledger entries? 
        // Or show all goods even if no activity?
        // Usually reports show active items. But requested "In/Out History" might imply only those with history.
        // However, if an item has Pre-Stock but no movement, it should appear.
        // If an item has no stock and no movement, maybe skip?
        // Let's stick to listing filtered goods for now to ensure we cover Pre-Stock items.
        // To avoid listing 10,000 dead items, maybe join with functional check?
        // For now, simple Goods pagination.

        $goodsList = $goodsQuery->orderBy('goods_seq', 'desc')->paginate($perPage);

        // 2. Fetch Data for these Goods
        $goodsSeqs = $goodsList->pluck('goods_seq')->toArray();
        if (empty($goodsSeqs)) {
            return $goodsList;
        }

        // A. Pre-Stock (Snapshot strictly BEFORE StartDate)
        // Max ldg_seq WHERE ldg_date < StartDate
        $preStockData = $this->getSnapshotData($goodsSeqs, $startDate, $whSeq, true);

        // B. Range Sums (StartDate <= ldg_date <= EndDate)
        $rangeSums = DB::table('fm_scm_ledger')
            ->whereIn('goods_seq', $goodsSeqs)
            ->whereBetween('ldg_date', [$startDate, $endDate])
            ->when($whSeq, function($q) use ($whSeq) {
                return $q->where('wh_seq', $whSeq);
            })
            ->select('goods_seq', 
                DB::raw('SUM(in_ea) as total_in'),
                DB::raw('SUM(out_ea) as total_out'),
                DB::raw('SUM(in_ea * in_supply_price) as total_in_amt'), // Approx? in_supply_price is per unit
                // fm_scm_ledger has `in_supply_price`. 
                // Wait, if multiple entries, `in_ea * in_supply_price` works for each row?
                // No, I need SUM(in_ea * in_supply_price) which is conceptually SUM(in_amount).
                // fm_scm_ledger doesn't have `in_amount` column?
                // Checking schema Step 1275: `in_ea`, `in_supply_price`. 
                // So I should do SUM(in_ea * in_supply_price).
                DB::raw('SUM(out_ea * out_supply_price) as total_out_amt')
            )
            ->groupBy('goods_seq')
            ->get()
            ->keyBy('goods_seq');

        // C. Last Stock in Period (To double check or use as Cur if calculation drift risk?)
        // Actually `Cur = Pre + In - Out` is logically sound given Ledger integrity.
        // But `Cur` valuation price (WAC) changes. 
        // So getting the *Last Snapshot* in the period gives us the correct `Cur Price`.
        $curStockData = $this->getSnapshotData($goodsSeqs, $endDate, $whSeq, false);

        // 3. Merge Data
        $goodsList->getCollection()->transform(function($good) use ($preStockData, $rangeSums, $curStockData) {
            $seq = $good->goods_seq;
            
            $pre = $preStockData[$seq] ?? null;
            $sums = $rangeSums[$seq] ?? null;
            $cur = $curStockData[$seq] ?? null;

            $good->pre_ea = $pre ? ($pre->wh_cur_ea ?? $pre->cur_ea) : 0;
            $good->pre_price = $pre ? ($pre->wh_cur_supply_price ?? $pre->cur_supply_price) : 0;
            $good->pre_amt = $good->pre_ea * $good->pre_price;

            $good->in_ea = $sums->total_in ?? 0;
            $good->in_amt = $sums->total_in_amt ?? 0; // Sum of daily amounts
            $good->in_price = ($good->in_ea > 0) ? ($good->in_amt / $good->in_ea) : 0; // Avg In Price

            $good->out_ea = $sums->total_out ?? 0;
            $good->out_amt = $sums->total_out_amt ?? 0;
            $good->out_price = ($good->out_ea > 0) ? ($good->out_amt / $good->out_ea) : 0;

            // Cur Quantity: Logically Pre + In - Out
            // But we can also take specific snapshot if available
            $good->cur_ea = $good->pre_ea + $good->in_ea - $good->out_ea;
            
            // Cur Price: Takes from the Last Snapshot in the period (or Pre if no movement)
            $good->cur_price = $cur ? ($cur->wh_cur_supply_price ?? $cur->cur_supply_price) : $good->pre_price;
            
            // Recalculate Cur Amt based on Qty * Price
            $good->cur_amt = $good->cur_ea * $good->cur_price;

            return $good;
        });

        return $goodsList;
    }

    /**
     * Helper to get snapshot state
     * $isBefore: true = STRICTLY BEFORE date ( < ), false = ON OR BEFORE ( <= )
     */
    /**
     * Download In/Out History Excel
     */
    public function downloadPeriodSummary(array $filters)
    {
        $filename = 'inout_history_' . date('Ymd_His') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $callback = function() use ($filters) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                '상품번호', '상품코드', '상품명', 
                '기초재고(수량)', '기초재고(금액)', 
                '입고(수량)', '입고(금액)', 
                '출고(수량)', '출고(금액)', 
                '기말재고(수량)', '기말재고(금액)'
            ]);

            $startDate = $filters['start_date'] ?? date('Y-m-01');
            $endDate = $filters['end_date'] ?? date('Y-m-d');
            $whSeq = $filters['wh_seq'] ?? null;
            $keyword = $filters['keyword'] ?? null;

            // Query Goods (Chunking)
            $goodsQuery = Goods::query()->select('goods_seq', 'goods_code', 'goods_name');
            if ($keyword) {
                $goodsQuery->where(function($q) use ($keyword) {
                    $q->where('goods_name', 'like', "%{$keyword}%")
                      ->orWhere('goods_code', 'like', "%{$keyword}%");
                });
            }
            $goodsQuery->orderBy('goods_seq', 'desc');

            // Chunk size
            $chunkSize = 500;

            $goodsQuery->chunk($chunkSize, function($goodsChunk) use ($file, $startDate, $endDate, $whSeq) {
                $goodsSeqs = $goodsChunk->pluck('goods_seq')->toArray();
                if (empty($goodsSeqs)) return;

                // Enrich Data (Same logic as getPeriodSummary)
                // A. Pre-Stock
                $preStockData = $this->getSnapshotData($goodsSeqs, $startDate, $whSeq, true);

                // B. Range Sums
                $rangeSums = DB::table('fm_scm_ledger')
                    ->whereIn('goods_seq', $goodsSeqs)
                    ->whereBetween('ldg_date', [$startDate, $endDate])
                    ->when($whSeq, function($q) use ($whSeq) {
                        return $q->where('wh_seq', $whSeq);
                    })
                    ->select('goods_seq', 
                        DB::raw('SUM(in_ea) as total_in'),
                        DB::raw('SUM(out_ea) as total_out'),
                        DB::raw('SUM(in_ea * in_supply_price) as total_in_amt'),
                        DB::raw('SUM(out_ea * out_supply_price) as total_out_amt')
                    )
                    ->groupBy('goods_seq')
                    ->get()
                    ->keyBy('goods_seq');

                // C. Last Stock
                $curStockData = $this->getSnapshotData($goodsSeqs, $endDate, $whSeq, false);

                foreach ($goodsChunk as $good) {
                    $seq = $good->goods_seq;
                    $pre = $preStockData[$seq] ?? null;
                    $sums = $rangeSums[$seq] ?? null;
                    $cur = $curStockData[$seq] ?? null;

                    $preEa = $pre ? ($pre->wh_cur_ea ?? $pre->cur_ea) : 0;
                    $prePrice = $pre ? ($pre->wh_cur_supply_price ?? $pre->cur_supply_price) : 0;
                    $preAmt = $preEa * $prePrice;

                    $inEa = $sums->total_in ?? 0;
                    $inAmt = $sums->total_in_amt ?? 0;

                    $outEa = $sums->total_out ?? 0;
                    $outAmt = $sums->total_out_amt ?? 0;

                    $curEa = $preEa + $inEa - $outEa;
                    $curPrice = $cur ? ($cur->wh_cur_supply_price ?? $cur->cur_supply_price) : $prePrice;
                    $curAmt = $curEa * $curPrice;

                    fputcsv($file, [
                        $good->goods_seq,
                        $good->goods_code,
                        $good->goods_name,
                        $preEa, $preAmt,
                        $inEa, $inAmt,
                        $outEa, $outAmt,
                        $curEa, $curAmt
                    ]);
                }
                
                // Flush buffer
                flush();
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getSnapshotData($goodsSeqs, $date, $whSeq, $isBefore)
    {
        $op = $isBefore ? '<' : '<=';
        
        // Subquery for Max ID
        $subQuery = DB::table('fm_scm_ledger')
            ->select('goods_seq', DB::raw('MAX(ldg_seq) as max_id'))
            ->whereIn('goods_seq', $goodsSeqs)
            ->where('ldg_date', $op, $date);

        if ($whSeq) {
            $subQuery->where('wh_seq', $whSeq);
        } else {
            // Global? If we want Global Pre-Stock, we need latest Global Entry?
            // Problem: Ledger is per-warehouse?
            // If I look at `fm_scm_ledger` schema: `cur_ea` (Global), `wh_cur_ea` (WH).
            // If No WH selected:
            // I should find the latest entry for *any* warehouse?
            // Actually, `cur_ea` should be consistent across all WH entries for the same Goods/Time?
            // Assuming `cur_ea` is global stock.
            // So getting the *absolute latest* ledger entry regardless of WH gives the Global state.
        }
        
        $subQuery->groupBy('goods_seq');

        // Main Query
        $data = DB::table('fm_scm_ledger as l')
            ->joinSub($subQuery, 'latest', function ($join) {
                 $join->on('l.ldg_seq', '=', 'latest.max_id');
            })
            ->get()
            ->keyBy('goods_seq');
            
        return $data;
    }
}
