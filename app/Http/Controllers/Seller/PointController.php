<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PointController extends Controller
{
    public function index()
    {
        return redirect()->route('seller.point.emoney');
    }

    public function emoney(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        
        // Emoney Logic (fm_emoney)
        // Legacy: membermodel->emoney_list
        $query = DB::table('fm_emoney')
            ->where('member_seq', $seller->provider_member_seq) // Assuming relationship exists or we find it
            ->orderBy('emoney_seq', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $logs = $query->paginate(20);

        return view('seller.point.index', [
            'logs' => $logs,
            'type' => 'emoney',
            'title' => '적립금 내역'
        ]);
    }

    public function cash(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        
        // Cash Logic (fm_cash)
        $query = DB::table('fm_cash')
            ->where('member_seq', $seller->provider_member_seq)
            ->orderBy('cash_seq', 'desc');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $logs = $query->paginate(20);

        return view('seller.point.index', [
            'logs' => $logs,
            'type' => 'cash',
            'title' => '이머니(예치금) 내역'
        ]);
    }
}
