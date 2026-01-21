<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Goods;

class OrderPlayautoController extends Controller
{
    // public function __construct()
    // {
    //     // Middleware is handled in routes
    // }

    public function catalog(Request $request)
    {
        $seller = Auth::guard('seller')->user();
        $providerSeq = $seller->provider_seq;

        $query = Order::query();

        // Join Items to filter by provider
        $query->whereHas('items', function($q) use ($providerSeq) {
            $q->where('provider_seq', $providerSeq);
        });

        // Basic Filters derived from legacy
        // Step > 15 (Paid/Deposited)
        // Usually list shows steps 25 (Payment Confirmed) to 85 (Purchase Confirmed)
        // Legacy: if param 'chk_step' is not set, it might default.
        // For now, let's show all relevant orders (step >= 25)
        // $query->where('step', '>=', 25); 

        // Search Filters
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('order_seq', 'like', "%{$keyword}%")
                  ->orWhere('order_user_name', 'like', "%{$keyword}%")
                  ->orWhere('order_email', 'like', "%{$keyword}%")
                  ->orWhere('recipient_user_name', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $orders = $query->orderBy('regist_date', 'desc')
                        ->paginate(20)
                        ->withQueryString();

        return view('seller.order.catalog', compact('orders'));
    }
}
