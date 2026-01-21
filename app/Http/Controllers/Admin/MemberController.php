<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Member;

class MemberController extends Controller
{
    public function catalog(Request $request)
    {
        $key = 'FirstMall';
        $query = Member::query()
            ->addSelect('fm_member.*')
            ->addSelect(DB::raw("AES_DECRYPT(UNHEX(email), '{$key}') as email"))
            ->addSelect(DB::raw("AES_DECRYPT(UNHEX(phone), '{$key}') as phone"))
            ->addSelect(DB::raw("AES_DECRYPT(UNHEX(cellphone), '{$key}') as cellphone"));

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('userid', 'like', "%{$keyword}%")
                  ->orWhere('user_name', 'like', "%{$keyword}%");
            });
        }

        $members = $query->orderBy('regist_date', 'desc')->paginate(20);

        return view('admin.member.catalog', compact('members'));
    }

    public function view($member_seq)
    {
        $key = 'FirstMall';
        $member = DB::table('fm_member')
            ->leftJoin('fm_member_group', 'fm_member.group_seq', '=', 'fm_member_group.group_seq')
            ->where('fm_member.member_seq', $member_seq)
            ->select(
                'fm_member.*',
                'fm_member_group.group_name',
                DB::raw("AES_DECRYPT(UNHEX(fm_member.email), '{$key}') as email"),
                DB::raw("AES_DECRYPT(UNHEX(fm_member.phone), '{$key}') as phone"),
                DB::raw("AES_DECRYPT(UNHEX(fm_member.cellphone), '{$key}') as cellphone")
            )
            ->first();

        if (!$member) {
            return redirect()->route('admin.member.catalog')->with('error', '회원 정보를 찾을 수 없습니다.');
        }

        // 30-day Order Summary
        $dateRangeStart = date('Y-m-d', strtotime('-30 days'));
        $dateRangeEnd = date('Y-m-d') . ' 23:59:59';

        $orderCounts = DB::table('fm_order')
            ->select('step', DB::raw('count(*) as cnt'))
            ->where('member_seq', $member_seq)
            ->where('hidden', 'N')
            ->whereBetween('regist_date', [$dateRangeStart, $dateRangeEnd])
            ->groupBy('step')
            ->pluck('cnt', 'step')
            ->toArray();

        // Specific Lists for linking (Logic from legacy)
        // Deposit Waiting (15)
        $orderReady = DB::table('fm_order')
            ->where('member_seq', $member_seq)
            ->where('step', 15)
            ->where('hidden', 'N')
            ->whereBetween('regist_date', [$dateRangeStart, $dateRangeEnd])
            ->get();

        // Export Ready (Multi-step)
        $exportReady = DB::table('fm_order')
            ->where('member_seq', $member_seq)
            ->whereIn('step', [25, 35, 40, 50, 60, 70])
            ->where('hidden', 'N')
            ->whereBetween('regist_date', [$dateRangeStart, $dateRangeEnd])
            ->get();

        return view('admin.member.view', compact('member', 'orderCounts', 'orderReady', 'exportReady'));
    }
}
