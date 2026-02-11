<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderRefund;
use Illuminate\Support\Facades\Auth;

class SellerRefundController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-1 month')));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $status = $request->input('status');
        $keyword = $request->input('keyword');

        $seller = Auth::guard('seller')->user();

        // Join structure similar to Returns
        $query = OrderRefund::with(['items.orderItem', 'order.member'])
            ->select('fm_order_refund.*')
            ->join('fm_order_refund_item', 'fm_order_refund.refund_code', '=', 'fm_order_refund_item.refund_code')
            ->join('fm_order_item', 'fm_order_refund_item.item_seq', '=', 'fm_order_item.item_seq')
            ->where('fm_order_item.provider_seq', $seller->provider_seq)
            ->distinct();

        if ($startDate && $endDate) {
            $query->whereBetween('fm_order_refund.regist_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        if ($status) {
            $query->where('fm_order_refund.status', $status);
        }

        if ($keyword) {
             $query->where(function($q) use ($keyword) {
                $q->where('fm_order_refund.refund_code', 'like', "%{$keyword}%")
                  ->orWhere('fm_order_refund.order_seq', 'like', "%{$keyword}%");
             });
        }

        $refunds = $query->orderBy('fm_order_refund.regist_date', 'desc')->paginate(20);

        return view('seller.refund.index', compact('refunds', 'startDate', 'endDate'));
    }

    public function show($id)
    {
        $refund = OrderRefund::with(['items.orderItem', 'order'])->findOrFail($id);
        return view('seller.refund.show', compact('refund'));
    }
}
