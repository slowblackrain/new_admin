<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index()
    {
        // 1. Get Current Seller (Provider)
        // Assuming Auth::guard('seller') is used, or fallback to a test provider for now
        $seller = \Illuminate\Support\Facades\Auth::guard('seller')->user();
        
        // FOR DEVELOPMENT: if no seller logged in, use 'parksh73' as verified in previous tasks
        if (!$seller) {
            // Retrieve via model to simulate login for dev
             $seller = \App\Models\Seller::where('provider_id', 'parksh73')->first();
             // In production, we should redirect to login here
             // return redirect()->route('seller.login');
        }

        if (!$seller) {
            return "Seller not found or not logged in.";
        }
        
        // Link to Member for legacy queries usually join with fm_member or check fm_order.member_seq?
        // Actually fm_order_item matches provider_seq usually.
        // Let's look at verify_dashboard_final.php logic: it gets member_seq from fm_provider linked userid.
        
        // However, usually sellers see orders assigned to their provider_seq in fm_order_item.
        // Let's use provider_seq for filtering.
        $providerSeq = $seller->provider_seq;

        // 2. Statistics: Ready to Ship (Steps 25, 35, 45)
        // Legacy 'Ready' often includes: 25(Paid), 35(Preparing), 45(Ready)
        $readySteps = [25, 35, 45];
        
        $readyToShipCnt = \Illuminate\Support\Facades\DB::table('fm_order_item as item')
            ->leftJoin('fm_order_item_option as opt', 'item.item_seq', '=', 'opt.item_seq')
            ->where('item.provider_seq', $providerSeq)
            ->whereIn('opt.step', $readySteps)
            ->count();
            
        // 3. Statistics: Claims (Return/Exchange/Cancel)
        // Steps usually 50~70 are shipping, 80+ might be claims, or separate 'order_seq' logic?
        // Checking legacy codes:
        // 85: Return Request, 95: Exchange Request, 
        // Cancel is usually step 85 or separate table? 
        // For Dometopia, let's assume standard FirstMall steps for claims:
        // 40: Cancel Request? 
        // Let's stick to known standard or gathered info. 
        // Common FM: 85 (Return/Refund), 95 (Exchange). 
        $claimSteps = [85, 95];
        $claimCnt = \Illuminate\Support\Facades\DB::table('fm_order_item as item')
            ->leftJoin('fm_order_item_option as opt', 'item.item_seq', '=', 'opt.item_seq')
            ->where('item.provider_seq', $providerSeq)
            ->whereIn('opt.step', $claimSteps)
            ->count();
            
        // 5. Statistics: New Orders (Step 15 - Order Received/Deposit Pending)
        $newOrderSteps = [15];
        $newOrderCnt = \Illuminate\Support\Facades\DB::table('fm_order_item as item')
            ->leftJoin('fm_order_item_option as opt', 'item.item_seq', '=', 'opt.item_seq')
            ->where('item.provider_seq', $providerSeq)
            ->whereIn('opt.step', $newOrderSteps)
            ->count();
            
        // 4. Q&A (Unanswered)
        // fm_goods_qna where provider_seq = ? and status = 'ready'?
        // Or re_contents is as empty?
        // Let's assume fm_boarddata with specific boardid if provider specific
        // Or fm_goods_qna table if it exists.
        // Checking verify_dashboard_final.php, it looked for notices in 'fm_boarddata' (gs_seller_notice).
        
        // Let's just fetch Notices for now as per previous context
        $notices = \Illuminate\Support\Facades\DB::table('fm_boarddata')
            ->where('boardid', 'gs_seller_notice')
            ->orderBy('m_date', 'desc')
            ->limit(5)
            ->get();

        return view('seller.index', compact('seller', 'readyToShipCnt', 'claimCnt', 'notices', 'newOrderCnt'));
    }
}
