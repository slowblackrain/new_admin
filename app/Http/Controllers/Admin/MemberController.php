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
            ->leftJoin('fm_member_group', 'fm_member.group_seq', '=', 'fm_member_group.group_seq')
            ->addSelect([
                'fm_member.*',
                'fm_member_group.group_name',
                DB::raw("AES_DECRYPT(UNHEX(email), '{$key}') as email"),
                DB::raw("AES_DECRYPT(UNHEX(phone), '{$key}') as phone"),
                DB::raw("AES_DECRYPT(UNHEX(cellphone), '{$key}') as cellphone")
            ]);
            // Columns like cash, order_cash, referer, business_seq are already in fm_member.* 


        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('userid', 'like', "%{$keyword}%")
                  ->orWhere('user_name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [
                $request->start_date,
                $request->end_date
            ]);
        }

        if ($request->filled('lastlogin_start') && $request->filled('lastlogin_end')) {
            $query->whereBetween('lastlogin_date', [
                $request->lastlogin_start,
                $request->lastlogin_end
            ]);
        }

        if ($request->filled('group_seq')) {
            $query->where('fm_member.group_seq', $request->group_seq);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('sms')) {
            $query->where('sms', $request->sms);
        }

        if ($request->filled('mailing')) {
            $query->where('mailing', $request->mailing);
        }

        $members = $query->orderBy('regist_date', 'desc')->paginate(20);
        $groups = DB::table('fm_member_group')->select('group_seq', 'group_name')->get();

        return view('admin.member.catalog', compact('members', 'groups'));
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
