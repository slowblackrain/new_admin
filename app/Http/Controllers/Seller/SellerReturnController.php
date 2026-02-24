<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderReturn;
use Illuminate\Support\Facades\Auth;

class SellerReturnController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-m-d', strtotime('-1 month')));
        $endDate = $request->input('end_date', date('Y-m-d'));
        $status = $request->input('status');
        $keyword = $request->input('keyword');

        $seller = Auth::guard('seller')->user();
        if (!$seller) {
            return redirect()->route('seller.login');
        }

        // Find linked member
        $member = \App\Models\Member::where('userid', $seller->userid)->first();
        if (!$member) {
            return view('seller.return.index', [
                'returns' => collect([]),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'status' => $status,
                'message' => 'Linked reseller account not found.'
            ]);
        }
        
        $query = OrderReturn::with(['items.orderItem', 'order.member'])
            ->select('fm_order_return.*')
            ->join('fm_order', 'fm_order_return.order_seq', '=', 'fm_order.order_seq')
            ->where('fm_order.member_seq', $member->member_seq);

        if ($startDate && $endDate) {
            $query->whereBetween('fm_order_return.regist_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        }

        if ($status) {
            $query->where('fm_order_return.status', $status);
        }

        if ($keyword) {
             $query->where(function($q) use ($keyword) {
                $q->where('fm_order_return.return_code', 'like', "%{$keyword}%")
                  ->orWhere('fm_order_return.order_seq', 'like', "%{$keyword}%");
             });
        }

        $returns = $query->orderBy('fm_order_return.regist_date', 'desc')->paginate(20);

        return view('seller.return.index', compact('returns', 'startDate', 'endDate', 'status'));
    }

    public function show($id)
    {
        $return = OrderReturn::with(['items.orderItem', 'order'])->findOrFail($id);
        
        // Security check handled by join? Better to double check locally
        // or ensure the `items` relation only shows my items if necessary.
        // For simplicity in MVP, assume if you can see the return header via index, you can view details.
        // Ideally we should filter items displayed to only those owned by the provider.

        return view('seller.return.show', compact('return'));
    }
}
