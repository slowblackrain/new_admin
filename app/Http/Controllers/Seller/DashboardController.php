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

        // Retrieve the linked member sequence for the provider
        // Legacy: $member_seq = $this->providermodel->get_provider_member_seq($this->providerInfo['provider_seq']);
        // Query: select M.member_seq, M.ATS_account from fm_provider as P left join fm_member as M on (P.userid = M.userid) where provider_seq=?
        $memberData = DB::table('fm_provider as P')
            ->leftJoin('fm_member as M', 'P.userid', '=', 'M.userid')
            ->where('P.provider_seq', $seller->provider_seq)
            ->select('M.member_seq', 'M.ATS_account')
            ->first();

        $memberSeq = $memberData ? $memberData->member_seq : null;
        $params = [
            'member_seq' => $memberSeq,
            // 'provider_seq' => $seller->provider_seq, // Unused in current logic but available
        ];

        // 1. Order Summary (Ready to Ship / Processing)
        // Replicating legacy query logic from _print_main_order_summary
        $orderSummary = [];
        
        if ($memberSeq) {
            // "출고준비건" (Ready to Ship / Preparing)
            // Steps: 25(Payment Confirmed?), 35, 40(Preparing), 50, 60, 70
            $steps = ['25', '35', '40', '50', '60', '70'];
            
            $readyToShipCnt = DB::table('fm_order_item as b')
                ->leftJoin('fm_order_item_option as c', 'b.item_seq', '=', 'c.item_seq')
                ->leftJoin('fm_order_item_suboption as d', 'b.item_seq', '=', 'd.item_seq')
                ->leftJoin('fm_order as e', 'b.order_seq', '=', 'e.order_seq')
                ->where('e.member_seq', $memberSeq)
                ->where(function($q) use ($steps) {
                    $q->whereIn('c.step', $steps) // Check option step
                      ->orWhereIn('d.step', $steps); // Check suboption step
                })
                ->distinct('b.order_seq') // group by b.order_seq and count
                ->count('b.order_seq');
                
            $orderSummary['ready_to_ship'] = [
                'title' => '출고준비건',
                'count' => $readyToShipCnt,
                'link'  => '/selleradmin/order_playauto/catalog?chk_step[25]=1&chk_step[35]=1&chk_step[40]=1&chk_step[50]=1&chk_step[60]=1&chk_step[70]=1'
            ];

            // Other statuses like Export/Delivery/Return require more complex joins with fm_goods_export.
            // For this iteration, we focus on the main "Ready to Ship" metric as a proof of concept.
            // We can add the others ( 배송준비중, 배송중, 반품진행중 ) iteratively.
            
            // NOTE: The legacy code calculates these using subqueries or complex joins. 
            // We will implement them if requested or in the next iteration.
        }

        // 2. Seller Summary (Notices)
        // Fetching "gs_seller_notice" - generic seller notices
        // Using basic DB query for now instead of migrating the entire Board system models immediately
        // 2. Seller Summary (Notices)
        // Fetching "gs_seller_notice" - generic seller notices
        $notices = DB::table('fm_boarddata')
            ->where('boardid', 'gs_seller_notice')
            ->orderBy('gid', 'asc') // Legacy: gid asc, m_date asc
            ->orderBy('m_date', 'asc') 
            ->limit(5)
            ->get(); 

        return view('seller.dashboard', compact('seller', 'memberData', 'orderSummary', 'notices'));
    }
}
