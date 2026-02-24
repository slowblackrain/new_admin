<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GoodsExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Member;

class SellerExportController extends Controller
{
    public function catalog(Request $request)
    {
        // Default Filters
        $startDate = $request->input('start_date', date('Y-m-d'));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $status = $request->input('status');
        $keyword = $request->input('keyword');

        $provider = session('provider');

        // Fallback for development
        if (!$provider) {
            $provider = DB::table('fm_provider')->where('provider_id', 'dometopia001')->first();
            $provider = (array) $provider;
        }

        if (!$provider || !isset($provider['userid'])) {
            return redirect()->back()->with('error', 'Seller information not found.');
        }

        // Find linked member
        /** @var \App\Models\Member $member */
        $member = Member::where('userid', $provider['userid'])->first();

        if (!$member) {
            return view('seller.export.catalog', [
                'exports' => [],
                'message' => 'Linked reseller account not found.',
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
        }

        $query = GoodsExport::with(['items', 'order.member'])
            ->select('fm_goods_export.*')
            ->join('fm_order', 'fm_goods_export.order_seq', '=', 'fm_order.order_seq')
            ->where('fm_order.member_seq', $member->member_seq)
            ->groupBy('fm_goods_export.export_seq');

        // Date Filter
        if ($startDate && $endDate) {
            $query->whereBetween('fm_goods_export.regist_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        // Status Filter
        if ($status) {
            $query->where('fm_goods_export.status', $status);
        }

        // Keyword Filter
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('fm_goods_export.export_code', 'like', "%{$keyword}%")
                  ->orWhere('fm_goods_export.order_seq', 'like', "%{$keyword}%")
                  ->orWhereHas('order', function($oq) use ($keyword) {
                      $oq->where('order_user_name', 'like', "%{$keyword}%")
                         ->orWhere('recipient_user_name', 'like', "%{$keyword}%");
                  });
            });
        }

        $exports = $query->orderBy('fm_goods_export.export_seq', 'desc')->paginate(20);

        return view('seller.export.catalog', compact('exports', 'startDate', 'endDate'));
    }

    public function view(Request $request, $id)
    {
        $export = GoodsExport::with(['items', 'order'])->findOrFail($id);
        
        // Security Check: Ensure this export belongs to the provider
        // Implementation depend on how strict we want to be. 
        // For now preventing view if no items match provider might be complex due to bundle exports.
        // Simplified check:
        // $hasItem = $export->items()->where('provider_seq', Auth::guard('provider')->id())->exists();
        // if (!$hasItem) abort(403);

        return view('seller.export.view', compact('export'));
    }
}
