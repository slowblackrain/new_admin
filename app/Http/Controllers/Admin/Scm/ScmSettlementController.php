<?php
namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScmSettlementController extends Controller
{
    public function trader_monthly(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $target_date = "{$year}-{$month}";

        // 1. Calculate Monthly Purchase per Trader from fm_offer
        // Criteria: Step = 11 (Stocked), stock_date like 'YYYY-MM%'
        $purchases = DB::table('fm_offer as o')
            ->join('fm_scm_trader as t', 'o.trader_seq', '=', 't.trader_seq')
            ->where('o.step', 11)
            ->where('o.stock_date', 'like', "{$target_date}%")
            ->select(
                't.trader_seq',
                't.trader_name',
                't.trader_id as trader_code',
                DB::raw('COUNT(o.sno) as offer_count'),
                DB::raw('SUM(CAST(o.ord_tot_price AS UNSIGNED)) as total_purchase_amount')
            )
            ->groupBy('t.trader_seq', 't.trader_name', 't.trader_id')
            ->get();

        // 2. Fetch Existing Settlement Data (if any) from fm_scm_trader_account
        // Check if settlement exists for this month (using last day of month as act_date or just checking existence)
        // We will just show calculated data for now.

        return view('admin.scm.settlement.trader_monthly', compact('purchases', 'year', 'month'));
    }
    
    // Todo: Save Settlement
}
