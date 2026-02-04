<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Seller;

class DashboardController extends Controller
{
    public function index()
    {
        $seller = Auth::guard('seller')->user();

        // retrieve member_seq
        $memberData = DB::table('fm_provider as P')
            ->leftJoin('fm_member as M', 'P.userid', '=', 'M.userid')
            ->where('P.provider_seq', $seller->provider_seq)
            ->select('M.member_seq', 'M.ATS_account')
            ->first();

        $memberSeq = $memberData ? $memberData->member_seq : null;

        $assetSummary = $this->getAssetSummary($memberSeq);
        $fulfillmentSummary = $this->getFulfillmentSummary($memberSeq);
        $productSummary = $this->getATSProductSummary($seller->provider_seq);
        $purchaseStats = $this->getPurchaseStatistics($memberSeq);
        $settlementSummary = $this->getSettlementSummary($memberSeq);

        // Notices (Keep existing logic)
        $notices = DB::table('fm_boarddata')
            ->where('boardid', 'gs_seller_notice')
            ->orderBy('gid', 'asc') 
            ->orderBy('m_date', 'asc') 
            ->limit(5)
            ->get(); 
            
        // Failure Alerts
        $failureAlerts = DB::table('fm_scm_order_fail_log as L')
            ->leftJoin('fm_goods as G', 'L.goods_seq', '=', 'G.goods_seq')
            ->where('L.provider_seq', $memberSeq)
            ->where('L.is_checked', 'N')
            ->orderBy('L.regist_date', 'desc')
            ->select('L.*', 'G.goods_name')
            ->get();

        return view('seller.dashboard', compact(
            'seller', 'memberData', 'assetSummary', 'fulfillmentSummary', 
            'productSummary', 'purchaseStats', 'notices', 'settlementSummary',
            'failureAlerts'
        ));
    }

    private function getAssetSummary($memberSeq)
    {
        if (!$memberSeq) return ['emoney' => 0, 'cash' => 0];

        $member = DB::table('fm_member')
            ->where('member_seq', $memberSeq)
            ->select('emoney', 'cash')
            ->first();

        return [
            'emoney' => $member->emoney ?? 0,
            'cash' => $member->cash ?? 0,
        ];
    }

    private function getSettlementSummary($memberSeq)
    {
        if (!$memberSeq) return ['margin' => 0, 'settle_amount' => 0, 'month' => date('m')];

        $currentMonth = date('Y-m');
        
        $data = DB::table('fm_account_provider_ats')
            ->where('member_seq', $memberSeq)
            ->where('acc_date', $currentMonth)
            ->first();

        return [
            'margin' => $data->margin ?? 0,
            // Settle Amount usually means what they get Paid relative to sales, 
            // but margin is their profit. Let's show Margin and Sales Volume.
            'sales_volume' => $data->sell_price ?? 0, 
            'month' => date('m')
        ];
    }

    private function getFulfillmentSummary($memberSeq)
    {
        if (!$memberSeq) return [];

        // Definition of Status Groups for Reseller Purchase Orders
        // 15: Deposit Pending (Action Required)
        // 25: Payment Confirmed (Ready to work)
        // 35-45: Preparing (Processing by Dometopia)
        // 55-65: Shipping (On the way to end customer)
        // 75: Delivered
        // Returns/Refunds: Checked via fm_order_return / fm_order_refund joined with order

        $summary = [
            'deposit_pending' => 0,
            'payment_confirmed' => 0,
            'preparing' => 0,
            'shipping' => 0,
            'completed' => 0,
            'return_refund' => 0,
        ];

        // 1. Order Counts by Step
        $orderCounts = DB::table('fm_order')
            ->where('member_seq', $memberSeq)
            ->select('step', DB::raw('count(*) as count'))
            ->groupBy('step')
            ->get()
            ->pluck('count', 'step');

        $summary['deposit_pending'] = $orderCounts->get('15', 0);
        $summary['payment_confirmed'] = $orderCounts->get('25', 0);
        
        // Processing (35, 40, 45, 50)
        $summary['preparing'] = ($orderCounts->get('35', 0) + $orderCounts->get('40', 0) + $orderCounts->get('45', 0) + $orderCounts->get('50', 0));
        
        // Shipping (55, 60, 65, 70)
        $summary['shipping'] = ($orderCounts->get('55', 0) + $orderCounts->get('60', 0) + $orderCounts->get('65', 0) + $orderCounts->get('70', 0) - $orderCounts->get('75', 0)); // 70 might be delivered too depending on legacy. 
        // Re-read Order Model: 70=Delivered? No, 75=PurchaseConfirm, 70=Delivered usually.
        // Order Model: 55=Shipped, 65=InTransit, 70=Delivered(sometimes), 75=PurchaseConfirm.
        // Let's stick to safe groups.
        $summary['shipping'] = ($orderCounts->get('55', 0) + $orderCounts->get('60', 0) + $orderCounts->get('65', 0));
        
        // Completed (70, 75)
        $summary['completed'] = ($orderCounts->get('70', 0) + $orderCounts->get('75', 0));

        // 2. Return/Refund Counts (Simplified check for any active return/refund linked to orders)
        // Ideally join fm_order_return -> fm_order where fm_order.member_seq = $memberSeq
        $returnCount = DB::table('fm_order_return as r')
            ->join('fm_order as o', 'r.order_seq', '=', 'o.order_seq')
            ->where('o.member_seq', $memberSeq)
            ->whereIn('r.status', ['request', 'ing'])
            ->count();
            
        $refundCount = DB::table('fm_order_refund as r')
             ->join('fm_order as o', 'r.order_seq', '=', 'o.order_seq')
             ->where('o.member_seq', $memberSeq)
             ->whereIn('r.status', ['request', 'ing'])
             ->count();

        $summary['return_refund'] = $returnCount + $refundCount;

        return $summary;
    }

    private function getATSProductSummary($providerSeq)
    {
        // ATS Goods supplied to this provider
        // fm_goods.provider_seq is the generic logic, but for ATS, goods are linked via category or registered directly.
        // Assuming standard goods ownership or ATS link. 
        // Based on ATSController, we check provider_seq in fm_goods.
        
        $stats = DB::table('fm_goods')
            ->where('provider_seq', $providerSeq)
            ->select('goods_status', DB::raw('count(*) as count'))
            ->groupBy('goods_status')
            ->get()
            ->pluck('count', 'goods_status');

        return [
            'normal' => $stats->get('normal', 0),
            'runout' => $stats->get('runout', 0),
            'stop' => $stats->get('stop', 0),
        ];
    }

    private function getPurchaseStatistics($memberSeq)
    {
        if (!$memberSeq) return [];

        // Daily Purchase Amount (Deposit Confirmed) logic
        // Past 7 days
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $endDate = date('Y-m-d');

        $dailyStats = DB::table('fm_order')
            ->where('member_seq', $memberSeq)
            ->where('deposit_yn', 'y') // Only confirmed purchases
            ->whereBetween('deposit_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                DB::raw('DATE(deposit_date) as date'),
                DB::raw('sum(settleprice) as total_amount'),
                DB::raw('count(*) as count')
            )
            ->groupBy(DB::raw('DATE(deposit_date)'))
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Fill empty days
        $chartData = [];
        $current = strtotime($startDate);
        $end = strtotime($endDate);

        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $stat = $dailyStats->get($dateStr);
            $chartData['dates'][] = date('m-d', $current);
            $chartData['amounts'][] = $stat ? $stat->total_amount : 0;
            $chartData['counts'][] = $stat ? $stat->count : 0;
            $current = strtotime('+1 day', $current);
        }

        return $chartData;
    }
}
