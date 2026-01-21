<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ATSController extends Controller
{
    public function __construct()
    {
        // Middleware is handled in routes/seller.php or manually checks
    }

    private function checkProviderPermission()
    {
        $seller = Auth::guard('seller')->user();
        if (!$seller) {
            abort(401, 'Unauthenticated.');
        }

        $member = DB::table('fm_member')->where('userid', $seller->provider_id)->first();

        if (!$member || $member->provider_YN !== 'Y') {
            abort(403, '접근 권한이 없습니다. (투자 권한 필요)');
        }
    }

    public function catalog(Request $request)
    {
        $this->checkProviderPermission();
        // Filter logic based on ATS_status_plus
        $statusPlus = $request->input('ATS_status_plus');
        $query = DB::table('fm_goods')
                    ->where('provider_seq', Auth::guard('seller')->user()->provider_seq);

        if ($statusPlus === 'ATS_agency') {
            // Agency Products logic (Legacy: category1 = 0159)
            // $query->where('category1', '0159'); // Example mapping
        } elseif ($statusPlus === 'ATS_only') {
            // Exclusive Products logic (Legacy: category1 = 0160)
            // $query->where('category1', '0160'); // Example mapping
        }

        // Apply filters and pagination (Placeholder)
        $goods = $query->paginate(20);

        return view('seller.ats.catalog', compact('goods', 'statusPlus'));
    }

    public function social_catalog(Request $request)
    {
        $this->checkProviderPermission();
        // Ticket/Coupon logic
        return view('seller.ats.catalog', ['type' => 'social']);
    }

    public function settlement(Request $request)
    {
        $this->checkProviderPermission();
        // Legacy: sales_ATS_seller
        // Date handling
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));
        $date = "$year-$month-01";

        $seller = Auth::guard('seller')->user();
        $member = DB::table('fm_member')->where('userid', $seller->provider_id)->first();
        $memberSeq = $member ? $member->member_seq : 0;

        // Fetch ATS Account Data
        $atsData = DB::table('fm_account_provider_ats')
                    ->where('member_seq', $memberSeq)
                    ->where('acc_date', "$year-$month")
                    ->first();

        // Additional Settlement Logic to be implemented (fm_order query, etc.)
        // For now, pass basic data
        
        return view('seller.ats.settlement', compact('atsData', 'year', 'month'));
    }

    public function requestRunout(Request $request)
    {
        $this->checkProviderPermission();
        $request->validate([
            'goods_seq' => 'required|integer'
        ]);

        $goodsSeq = $request->input('goods_seq');
        $providerId = Auth::guard('seller')->user()->provider_id;

        // Fetch current status
        $goods = DB::table('fm_goods')->where('goods_seq', $goodsSeq)->first();
        if (!$goods) {
            return response()->json(['status' => 'error', 'message' => 'Product not found'], 404);
        }

        // Append 'runout_order,' to goods_status_info
        $currentInfo = $goods->goods_status_info ?? '';
        if (strpos($currentInfo, 'runout_order') === false) {
            $newInfo = $currentInfo . 'runout_order,';
            
            // Append Admin Memo
            $memo = "단종요청 [$providerId]-" . date("Y-m-d H:i:s") . "\r\n";
            
            DB::table('fm_goods')->where('goods_seq', $goodsSeq)->update([
                'goods_status_info' => $newInfo,
                'admin_memo' => DB::raw("CONCAT('$memo', IFNULL(admin_memo, ''))")
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
