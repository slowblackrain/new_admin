<?php

namespace App\Services\Scm;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScmAnalysisService
{
    /**
     * 월별 매입 통계 (Monthly Purchase Stats)
     * Aggregates completed warehousing (status=1) data by month.
     * 
     * @param string $year (YYYY)
     * @return \Illuminate\Support\Collection
     */
    public function getMonthlyPurchaseStats($year)
    {
        $startDate = "{$year}-01-01 00:00:00";
        $endDate = "{$year}-12-31 23:59:59";

        // Aggregate from fm_scm_warehousing (Header)
        // Adjust logic if krw_total_price is not fully reliable (but it should be)
        $stats = DB::table('fm_scm_warehousing')
            ->select(
                DB::raw("DATE_FORMAT(complete_date, '%m') as month"),
                DB::raw("COUNT(*) as cnt"),
                DB::raw("SUM(krw_total_price) as total_amt"),
                DB::raw("SUM(krw_total_supply_tax) as total_tax")
            )
            ->where('whs_status', '1') // Complete only
            ->whereBetween('complete_date', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        // Fill empty months
        $result = collect([]);
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = sprintf('%02d', $m);
            $data = $stats[$monthKey] ?? null;
            $result->push([
                'month' => $monthKey,
                'cnt' => $data ? $data->cnt : 0,
                'total_amt' => $data ? $data->total_amt : 0,
                'total_tax' => $data ? $data->total_tax : 0,
            ]);
        }

        return $result;
    }

    /**
     * 거래처별 매입 통계 (Trader Purchase Stats)
     * Aggregates by Trader for a given period.
     * 
     * @param string $startDate (YYYY-MM-DD)
     * @param string $endDate (YYYY-MM-DD)
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getTraderPurchaseStats($startDate, $endDate, $perPage = 20)
    {
        return DB::table('fm_scm_warehousing as w')
            ->join('fm_scm_trader as t', 'w.trader_seq', '=', 't.trader_seq')
            ->select(
                't.trader_seq',
                't.trader_name',
                DB::raw("COUNT(w.whs_seq) as cnt"),
                DB::raw("SUM(w.krw_total_price) as total_amt"),
                DB::raw("SUM(w.krw_total_supply_tax) as total_tax")
            )
            ->where('w.whs_status', '1')
            ->whereBetween('w.complete_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('t.trader_seq', 't.trader_name')
            ->orderByDesc('total_amt')
            ->paginate($perPage);
    }
}
