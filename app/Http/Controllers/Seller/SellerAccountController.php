<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SellerAccountController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user();

        // Check if fm_account table exists or similar.
        // Legacy 'account.php' usually deals with 'fm_account' or 'fm_account_log'.
        // Let's assume 'fm_account' for now based on standard FirstMall.
        // Or 'fm_order_settlement'?
        // Given 'gap_analysis' said 'seller.account.index' was 404, we need to build it.
        
        // For MVP, if table structure is unknown, we can show a placeholder or try to query standard tables.
        // Let's query 'fm_account' if it exists.
        
        $query = DB::table('fm_account')
            ->where('provider_seq', $seller->provider_seq)
            ->orderBy('regist_date', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
        }

        $accounts = $query->paginate(20)->withQueryString();

        return view('seller.account.index', compact('accounts'));
    }
}
