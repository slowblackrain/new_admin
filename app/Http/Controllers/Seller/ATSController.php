<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Seller;

use App\Services\Agency\AgencyProductService;
use Exception;

class ATSController extends Controller
{
    protected AgencyProductService $agencyService;

    public function __construct(AgencyProductService $agencyService)
    {
        $this->agencyService = $agencyService;
        // Middleware is handled in routes/seller.php
    }

    private function checkProviderPermission()
    {
        $seller = Auth::guard('seller')->user();

        // Fallback for development (newjjang3)
        if (!$seller) {
             $seller = Seller::where('provider_id', 'newjjang3')->first();
             if ($seller) {
                 Auth::guard('seller')->login($seller);
             }
        }

        if (!$seller) {
            return redirect()->route('seller.login')->send(); 
        }

        return $seller;
    }

    private function getMemberSeq($provider_id)
    {
        $member = DB::table('fm_member')->where('userid', $provider_id)->first();
        return $member ? $member->member_seq : 0; // Return 0 if null, or handle error
    }

    private function getATSStats($member_seq)
    {
        if (!$member_seq) return ['goods_ea_price' => 0, 'goods_sorder_price' => 0];

        $ats = DB::table('fm_member_ats')->where('member_seq', $member_seq)->first();
        if ($ats) {
            return [
                'goods_ea_price' => $ats->goods_ea_price,
                'goods_sorder_price' => ($ats->sorder ?? 0) + ($ats->no_sorder ?? 0)
            ];
        }
        return ['goods_ea_price' => 0, 'goods_sorder_price' => 0];
    }

    public function catalog(Request $request)
    {
        $seller = $this->checkProviderPermission();
        $member_seq = $this->getMemberSeq($seller->provider_id);
        
        // ATS Stats
        $stats = $this->getATSStats($member_seq);

        // Filters
        $statusPlus = $request->input('ATS_status_plus'); // ATS_agency, ATS_only
        $status = $request->input('status', 'all'); 
        $startDate = $request->input('sdate', date('Y-m-d', strtotime('-1 month')));
        $endDate = $request->input('edate', date('Y-m-d'));
        $keyword = $request->input('keyword');
        $type = $request->input('type', 'goods'); // goods or social

        // Base Query
        $query = DB::table('fm_goods as g')
            ->select('g.*', 'cl.category_code')
            ->leftJoin('fm_category_link as cl', 'g.goods_seq', '=', 'cl.goods_seq')
            ->where('g.provider_seq', $seller->provider_seq);

        // Type Filter (Goods vs Ticket/Coupon)
        if ($type === 'social') {
            $query->where('g.goods_kind', 'coupon');
        } else {
            $query->where('g.goods_kind', 'goods');
        }

        // ATS Category Filter
        if ($statusPlus === 'ATS_agency') {
            $query->where('cl.category_code', 'like', '0159%');
        } elseif ($statusPlus === 'ATS_only') {
            $query->where('cl.category_code', 'like', '0160%');
        }

        // Search Filters
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('g.goods_name', 'like', "%{$keyword}%")
                  ->orWhere('g.goods_code', 'like', "%{$keyword}%");
            });
        }

        // Date Filter (Optional, consistent with legacy)
        if ($startDate && $endDate) {
            $query->whereBetween('g.regist_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }
        
        // Status Filter (Legacy: ATS_status)
        // Ignoring specific legacy status details for now, focusing on display

        // Group by goods_seq to avoid duplicates from category link
        $query->groupBy('g.goods_seq');

        // Ordering
        $query->orderBy('g.regist_date', 'desc');

        $goods = $query->paginate(20)->withQueryString();

        return view('seller.ats.catalog', compact('goods', 'statusPlus', 'status', 'startDate', 'endDate', 'keyword', 'stats', 'type'));
    }

    public function social_catalog(Request $request)
    {
        $request->merge(['type' => 'social']);
        return $this->catalog($request);
    }

    public function settlement(Request $request)
    {
        $seller = $this->checkProviderPermission();
        $member_seq = $this->getMemberSeq($seller->provider_id);

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $startDate = "$year-$month-01";
        $endDate = date('Y-m-t', strtotime($startDate));
        
        // 1. Fetch Confirmed Settlement Data
        $atsData = DB::table('fm_account_provider_ats')
            ->where('member_seq', $member_seq)
            ->where('acc_date', "$year-$month")
            ->first();

        // 2. Calculate Daily Sales (Deposited Orders)
        $salesData = DB::table('fm_order as o')
            ->selectRaw('
                day(o.deposit_date) as day,
                sum(sio.price * (sio.ea - sio.refund_ea)) as settleprice_sum,
                sum((sio.ea - sio.refund_ea)) as count_ea_sum,
                count(distinct o.order_seq) as count_sum
            ')
            ->join('fm_order_item as si', 'o.order_seq', '=', 'si.order_seq')
            ->join('fm_order_item_option as sio', 'si.item_seq', '=', 'sio.item_seq')
            ->join('fm_goods as sg', 'si.goods_seq', '=', 'sg.goods_seq')
            ->where('o.deposit_yn', 'y')
            ->whereYear('o.deposit_date', $year)
            ->whereMonth('o.deposit_date', $month)
            ->where('sg.provider_member_seq', $member_seq)
            ->whereBetween('sio.step', ['25', '75'])
            ->whereRaw('(sio.ea - sio.refund_ea) > 0')
            ->groupByRaw('day')
            ->get()
            ->keyBy('day');

        // 3. Calculate Daily Refunds
        $refundData = DB::table('fm_order_refund as r')
            ->selectRaw('
                day(r.refund_date) as day,
                sum(r.refund_price) as refund_price_sum,
                count(*) as refund_count_sum
            ')
            ->join('fm_order_refund_item as ri', 'r.refund_code', '=', 'ri.refund_code')
            ->join('fm_order_item as si', 'ri.item_seq', '=', 'si.item_seq')
            ->join('fm_goods as sg', 'si.goods_seq', '=', 'sg.goods_seq')
            ->where('r.status', 'complete')
            ->whereYear('r.refund_date', $year)
            ->whereMonth('r.refund_date', $month)
            ->where('sg.provider_member_seq', $member_seq)
            ->groupByRaw('day')
            ->get()
            ->keyBy('day');

        // 4. Calculate Daily Offers (ATS Investment)
        $offerData = DB::table('fm_offer as a')
            ->selectRaw('
                day(a.regist_date) as day,
                sum(c.cash) as offer_price
            ')
            ->join('fm_cash as c', 'a.sno', '=', 'c.ordno') // Assuming sno maps to ordno for ATS cash
            ->join('fm_goods as g', 'a.goods_seq', '=', 'g.goods_seq')
            ->whereYear('a.regist_date', $year)
            ->whereMonth('a.regist_date', $month)
            ->where('a.step', '<', '12')
            ->where('g.provider_member_seq', $member_seq)
            ->groupByRaw('day')
            ->get()
            ->keyBy('day');

        // 5. Calculate Daily Cash (Deposits/Charges)
        $cashData = DB::table('fm_cash')
            ->selectRaw('
                day(regist_date) as day,
                sum(cash) as day_cash
            ')
            ->whereYear('regist_date', $year)
            ->whereMonth('regist_date', $month)
            ->where('member_seq', $member_seq)
            ->whereIn('type', ['order', 'save'])
            ->where('gb', 'plus')
            ->groupByRaw('day')
            ->get()
            ->keyBy('day');

        // 6. Aggregate Data by Day
        $statsData = [];
        $daysInMonth = date('t', strtotime($startDate));
        $totals = [
            'sell' => 0, 'refund' => 0, 'offer' => 0, 'cash' => 0, 'all' => 0
        ];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $sales = $salesData->get($d);
            $refund = $refundData->get($d);
            $offer = $offerData->get($d);
            $cash = $cashData->get($d);

            $dayStats = [
                'day' => $d,
                'settleprice_sum' => $sales->settleprice_sum ?? 0,
                'refund_price_sum' => $refund->refund_price_sum ?? 0,
                'offer_price' => $offer->offer_price ?? 0,
                'day_cash' => $cash->day_cash ?? 0,
            ];

            $totals['sell'] += $dayStats['settleprice_sum'];
            $totals['refund'] += $dayStats['refund_price_sum'];
            $totals['offer'] += $dayStats['offer_price'];
            $totals['cash'] += $dayStats['day_cash'];
            
            // Formula might need logic adjustment for margin/save_price
            // For now: Total = Sell - Refund + Cash - Offer (Abstract logic)
            // Legacy: total_all = settleprice + revision
            
            $statsData[$d] = $dayStats;
        }

        return view('seller.ats.settlement', compact('atsData', 'statsData', 'totals', 'year', 'month'));
    }

    public function requestRunout(Request $request)
    {
        $seller = $this->checkProviderPermission();
        $request->validate([
            'goods_seq' => 'required|integer'
        ]);

        $goodsSeq = $request->input('goods_seq');
        $providerId = $seller->provider_id;

        // Fetch current status
        $goods = DB::table('fm_goods')
            ->where('goods_seq', $goodsSeq)
            ->where('provider_seq', $seller->provider_seq)
            ->first();
            
        if (!$goods) {
            return response()->json(['status' => 'error', 'message' => 'Product not found or unauthorized'], 404);
        }

        // Append 'runout_order,' to goods_status_info if not exists
        $currentInfo = $goods->goods_status_info ?? '';
        if (strpos($currentInfo, 'runout_order') === false) {
            $newInfo = $currentInfo . 'runout_order,';
            
            // Append Admin Memo
            $memo = "단종요청 [$providerId]-" . date("Y-m-d H:i:s") . "\r\n";
            
            DB::table('fm_goods')->where('goods_seq', $goodsSeq)->update([
                'goods_status_info' => $newInfo,
                'admin_memo' => DB::raw("CONCAT('" . addslashes($memo) . "', IFNULL(admin_memo, ''))")
            ]);
        }

        return response()->json(['status' => 'success']);
    }


}
