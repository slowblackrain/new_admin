<?php

namespace App\Http\Controllers\Admin\Scm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScmOrderFailController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('fm_scm_order_fail_log as l')
            ->leftJoin('fm_member as m', 'l.provider_seq', '=', 'm.member_seq')
            ->leftJoin('fm_goods as g', 'l.goods_seq', '=', 'g.goods_seq')
            ->select(
                'l.*',
                'm.userid',
                'm.user_name',
                'g.goods_name'
            )
            ->orderBy('l.regist_date', 'desc');

        if ($request->keyword) {
            $query->where(function($q) use ($request) {
                $q->where('m.userid', 'like', "%{$request->keyword}%")
                  ->orWhere('m.user_name', 'like', "%{$request->keyword}%")
                  ->orWhere('g.goods_name', 'like', "%{$request->keyword}%");
            });
        }

        if ($request->status) {
            $query->where('l.is_checked', $request->status); // 'N' or 'Y'
        }

        $logs = $query->paginate(20);

        return view('admin.scm.order.fail_log_index', compact('logs'));
    }
}
