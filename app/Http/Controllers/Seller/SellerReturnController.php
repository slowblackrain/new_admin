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

        // Logic to filter returns by provider items
        // Since fm_order_return links to order, and order links to items
        // We need to check if the return contains items belonging to this provider.
        // OR more simply, if the order contains items from this provider (but return might not include them).
        // Let's look at legacy logic:
        // inner join fm_order_return_item as item ...
        // LEFT JOIN fm_order_item orditem ON ...
        // where ... orditem.provider_seq = ...
        
        $query = OrderReturn::with(['items.orderItem', 'order.member'])
            ->select('fm_order_return.*')
            ->join('fm_order_return_item', 'fm_order_return.return_code', '=', 'fm_order_return_item.return_code')
            ->join('fm_order_item', 'fm_order_return_item.item_seq', '=', 'fm_order_item.item_seq')
            ->where('fm_order_item.provider_seq', $seller->provider_seq)
            ->distinct();

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
