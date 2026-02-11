<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class SellerStatisticController extends Controller
{
    public function index()
    {
        return redirect()->route('seller.statistics.sales_monthly');
    }

    public function sales_monthly(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $year = $request->input('year', date('Y'));

        // Get member_seq for the seller (provider)
        // Legacy: $member_seq = $this->providermodel->get_provider_member_seq($this->providerInfo['provider_seq']);
        // Current: Match provider_id to fm_member.userid
        $member = DB::table('fm_member')->where('userid', $seller->provider_id)->first();
        if (!$member) {
             return back()->with('error', 'Seller member account not found.');
        }
        $member_seq = $member->member_seq;

        // Special handling for specific member (legacy logic)
        // if($member_seq['member_seq'] == '248604'){ $member_seq['member_seq'] = 253840; }
        if ($member_seq == '248604') {
            $member_seq = 253840;
        }

        // Logic from statistic_playauto_sales.php
        $statsData = [];
        
        // 1. Sales Data (Settle price, Enuri, Emoney, Cash, Shipping, etc.)
        // Filter by deposit_yn='y', provider='N' (or null), and specific goods exclusions if any
        // Legacy query groups by month
        
        $salesQuery = DB::table('fm_order as a')
            ->selectRaw('
                YEAR(a.deposit_date) as stats_year,
                MONTH(a.deposit_date) as stats_month,
                SUM(a.settleprice) as settleprice_sum,
                SUM(a.enuri) as enuri_sum,
                SUM(a.emoney) as emoney_use_sum,
                SUM(a.cash) as cash_use_sum,
                SUM(a.international_cost + a.shipping_cost) as shipping_cost_sum,
                COUNT(*) as count_sum
            ')
            ->where('a.deposit_yn', 'y')
            ->whereYear('a.deposit_date', $year)
            ->where('a.member_seq', $member_seq)
            ->where(function($q) {
                $q->where('a.provider', 'N')->orWhereNull('a.provider');
            })
            ->whereBetween('a.step', [25, 75]) // Based on legacy: a.step > 24 and a.step < 76
            ->groupByRaw('MONTH(a.deposit_date)');

        $salesResults = $salesQuery->get();

        // Initialize stats array for 12 months
        for ($i = 1; $i <= 12; $i++) {
            $statsData[$i] = [
                'month' => $i,
                'order_price' => 0,
                'discount_price' => 0,
                'sales_price' => 0,
                'refund_price' => 0,
                'interests' => 0,
                'settleprice_sum' => 0,
                'count_sum' => 0
            ];
        }

        foreach ($salesResults as $row) {
            $m = $row->stats_month;
            $statsData[$m]['settleprice_sum'] = $row->settleprice_sum;
            $statsData[$m]['order_price'] = $row->settleprice_sum + $row->emoney_use_sum + $row->cash_use_sum;
            $statsData[$m]['discount_price'] = $row->enuri_sum; // + other discounts if needed
            $statsData[$m]['count_sum'] = $row->count_sum;
            // Initial sales price (will subtract refunds later)
            $statsData[$m]['sales_price'] = $statsData[$m]['order_price']; 
        }

        // 2. Refund Data
        $refundQuery = DB::table('fm_order_refund as a')
            ->join('fm_order as b', 'a.order_seq', '=', 'b.order_seq')
            ->selectRaw('
                MONTH(a.refund_date) as stats_month,
                SUM(a.refund_price) as refund_price_sum
            ')
            ->whereYear('a.refund_date', $year)
            ->where('b.member_seq', $member_seq)
            ->groupByRaw('MONTH(a.refund_date)');

        $refundResults = $refundQuery->get();

        foreach ($refundResults as $row) {
            $m = $row->stats_month;
            $statsData[$m]['refund_price'] = $row->refund_price_sum;
            $statsData[$m]['sales_price'] -= $row->refund_price_sum;
        }

        // Calculate totals for table footer
        $totals = [
            'order_price' => 0,
            'sales_price' => 0,
            'refund_price' => 0,
            'count_sum' => 0
        ];
        foreach ($statsData as $Data) {
            $totals['order_price'] += $Data['order_price'];
            $totals['sales_price'] += $Data['sales_price'];
            $totals['refund_price'] += $Data['refund_price'];
            $totals['count_sum'] += $Data['count_sum'];
        }

        return view('seller.statistics.index', compact('statsData', 'totals', 'year'));
    }

    public function sales_daily(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        $member = DB::table('fm_member')->where('userid', $seller->provider_id)->first();
        if (!$member) return back();
        $member_seq = $member->member_seq;
        if ($member_seq == '248604') $member_seq = 253840;

        $lastDay = date('t', strtotime("$year-$month-01"));
        $statsData = [];
        for ($i = 1; $i <= $lastDay; $i++) {
            $statsData[$i] = [
                'day' => $i,
                'sales_price' => 0,
                'count_sum' => 0,
                'refund_price' => 0,
            ];
        }

        // Sales Query
        $salesQuery = DB::table('fm_order as a')
            ->selectRaw('
                DAY(a.deposit_date) as stats_day,
                SUM(a.settleprice + a.emoney + a.cash) as total_sales,
                COUNT(*) as count_sum
            ')
            ->where('a.deposit_yn', 'y')
            ->whereYear('a.deposit_date', $year)
            ->whereMonth('a.deposit_date', $month)
            ->where('a.member_seq', $member_seq)
            ->where(function($q) {
                 $q->where('a.provider', 'N')->orWhereNull('a.provider');
            })
            ->whereBetween('a.step', [25, 75])
            ->groupByRaw('DAY(a.deposit_date)');
        
        foreach ($salesQuery->get() as $row) {
            $d = $row->stats_day;
            $statsData[$d]['sales_price'] += $row->total_sales;
            $statsData[$d]['count_sum'] += $row->count_sum;
        }

        // Refund Query
        $refundQuery = DB::table('fm_order_refund as a')
            ->join('fm_order as b', 'a.order_seq', '=', 'b.order_seq')
             ->selectRaw('
                DAY(a.refund_date) as stats_day,
                SUM(a.refund_price) as refund_price_sum
            ')
            ->whereYear('a.refund_date', $year)
            ->whereMonth('a.refund_date', $month)
            ->where('b.member_seq', $member_seq)
            ->groupByRaw('DAY(a.refund_date)');

        foreach ($refundQuery->get() as $row) {
            $d = $row->stats_day;
            $statsData[$d]['refund_price'] = $row->refund_price_sum;
            $statsData[$d]['sales_price'] -= $row->refund_price_sum;
        }

        return view('seller.statistics.daily', compact('statsData', 'year', 'month'));
    }

    public function goods(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        
        $sdate = $request->input('sdate', date('Y-m-d', strtotime("-7 days")));
        $edate = $request->input('edate', date('Y-m-d'));

        $member = DB::table('fm_member')->where('userid', $seller->provider_id)->first();
        if (!$member) return back();
        $member_seq = $member->member_seq;
        if ($member_seq == '248604') $member_seq = 253840;

        // Goods Sales Statistics
        // Based on logic from statistic_playauto_goods.php
        
        $query = DB::table('fm_order_item as i')
            ->join('fm_order as o', 'i.order_seq', '=', 'o.order_seq')
            ->join('fm_order_item_option as io', function($join) {
                $join->on('i.order_seq', '=', 'io.order_seq')
                     ->on('i.item_seq', '=', 'io.item_seq');
            })
            ->selectRaw('
                i.goods_seq,
                i.goods_name,
                i.image,
                SUM(io.ea) as total_ea,
                SUM(io.price * io.ea) as total_price
            ')
            ->where('o.deposit_yn', 'y')
            ->where('o.member_seq', $member_seq)
            ->whereBetween('o.deposit_date', [$sdate . ' 00:00:00', $edate . ' 23:59:59'])
            ->groupBy('i.goods_seq', 'i.goods_name', 'i.image')
            ->orderByDesc('total_price');

        $goodsStats = $query->paginate(20);

        return view('seller.statistics.goods', compact('goodsStats', 'sdate', 'edate'));
    }
}
