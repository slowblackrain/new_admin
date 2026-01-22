<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StatisticController extends Controller
{
    private function getMemberSeq($userid) {
        return DB::table('fm_member')->where('userid', $userid)->value('member_seq');
    }

    public function index(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $mseq = $this->getMemberSeq($seller->userid);

        if (!$mseq) {
            abort(403, 'Member account not found.');
        }

        // Date Filter
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-1 month')));
        $endDate = $request->input('end_date', date('Y-m-d'));

        // Query: Sales by Product (Purchased by this seller)
        // Group by goods_seq
        $query = DB::table('fm_order_item as item')
            ->join('fm_order as ord', 'item.order_seq', '=', 'ord.order_seq')
            ->join('fm_order_item_option as opt', 'item.item_seq', '=', 'opt.item_seq')
            ->where('ord.member_seq', $mseq)
            ->where('ord.step', '>=', '25') 
            ->whereBetween('ord.regist_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->select(
                'item.goods_seq',
                DB::raw('MAX(item.goods_name) as goods_name'), // Use MAX to pick one name
                DB::raw('SUM(opt.ea) as total_ea'),
                DB::raw('SUM(opt.price * opt.ea) as total_price')
            )
            ->groupBy('item.goods_seq')
            ->orderByDesc('total_price');

        $statistics = $query->paginate(20);

        return view('seller.statistics.index', [
            'statistics' => $statistics,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}
