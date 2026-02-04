<?php
namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScmSettlementController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $target_date = "{$year}-{$month}";

        $query = DB::table('fm_account_provider_ats as a')
            ->leftJoin('fm_member as m', 'a.member_seq', '=', 'm.member_seq')
            ->select(
                'a.*',
                'm.userid',
                'm.user_name'
            )
            ->orderBy('a.acc_date', 'desc')
            ->orderBy('a.seq', 'desc');

        // Filter by Month
        if ($request->year && $request->month) {
             $query->where('a.acc_date', $target_date);
        }

        // Filter by Provider
        if ($request->keyword) {
             $query->where(function($q) use ($request) {
                  $q->where('m.userid', 'like', "%{$request->keyword}%")
                    ->orWhere('m.user_name', 'like', "%{$request->keyword}%");
             });
        }

        $settlements = $query->paginate(20);

        return view('admin.scm.settlement.index', compact('settlements', 'year', 'month'));
    }

    // Trader Monthly Stats (Existing)
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
    // Manual Adjustment (Update)
    public function update(Request $request, $seq)
    {
        // 1. Validate
        $request->validate([
            'offer_price' => 'required|numeric|min:0',
            'margin' => 'required|numeric',
        ]);

        $settlement = DB::table('fm_account_provider_ats')->where('seq', $seq)->first();

        if (!$settlement) {
            return response()->json(['message' => 'Settlement record not found.'], 404);
        }

        if ($settlement->acc_status === 'complete') {
            return response()->json(['message' => 'Cannot modify completed settlement.'], 403);
        }

        // 2. Update
        DB::table('fm_account_provider_ats')
            ->where('seq', $seq)
            ->update([
                'offer_price' => $request->offer_price,
                'margin' => $request->margin,
                // 'sell_price' is generally not editable to maintain consistency with order total
            ]);

        return response()->json(['message' => 'Settlement updated successfully.']);
    }
}
