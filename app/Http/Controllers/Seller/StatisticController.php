<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatisticController extends Controller
{
    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $providerSeq = $seller->provider_seq;

        // Default Date: This Month
        $startDate = $request->input('start_date', date('Y-m-01'));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // Query: Sales by Goods for this Provider
        // Table: fm_order_item (oi) join fm_order (o)
        // Check step: 25, 35, 45, 55, 65, 75 (Payment Completed ~ Delivery Completed)
        // Exclude cancelled/refunded if needed, but usually stats include valid orders.
        // Legacy often checks `step` >= 25.

        $query = DB::table('fm_order_item as oi')
            ->join('fm_order as o', 'oi.order_seq', '=', 'o.order_seq')
            ->join('fm_order_item_option as oio', 'oi.item_seq', '=', 'oio.item_seq')
            ->select(
                'oi.goods_seq',
                'oi.goods_name',
                'oi.image',
                DB::raw('COUNT(DISTINCT o.order_seq) as order_count'),
                DB::raw('SUM(oio.ea) as total_ea'),
                DB::raw('SUM(oio.price * oio.ea) as total_price')
            )
            ->where('oio.provider_seq', $providerSeq)
            ->whereBetween('o.regist_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('oio.step', ['25', '35', '40', '45', '50', '55', '60', '65', '70', '75']) // Valid steps
            ->groupBy('oi.goods_seq', 'oi.goods_name', 'oi.image')
            ->orderByDesc('total_price');

        if ($request->filled('keyword')) {
            $query->where('oi.goods_name', 'like', '%' . $request->keyword . '%');
        }

        $statistics = $query->paginate(20);

        return view('seller.statistics.index', [
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
