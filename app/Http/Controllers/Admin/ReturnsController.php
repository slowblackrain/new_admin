<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnsController extends Controller
{
    public function catalog(Request $request)
    {
        $status = $request->input('status');
        $keyword = $request->input('keyword');

        $query = DB::table('fm_order_return as r')
            ->join('fm_order as o', 'r.order_seq', '=', 'o.order_seq')
            ->leftJoin('fm_member as m', 'o.member_seq', '=', 'm.member_seq')
            ->select(
                'r.*',
                'o.order_id',
                'o.order_user_name',
                'm.userid',
                'm.user_name'
            )
            ->orderBy('r.regist_date', 'desc');

        if ($status) {
            $query->where('r.status', $status);
        }

        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('r.return_code', 'like', "%{$keyword}%")
                  ->orWhere('o.order_id', 'like', "%{$keyword}%")
                  ->orWhere('o.order_user_name', 'like', "%{$keyword}%");
            });
        }

        $returns = $query->paginate(20);

        return view('admin.returns.catalog', compact('returns', 'status', 'keyword'));
    }
}
