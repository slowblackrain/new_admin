<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class MypageController extends Controller
{
    public function index()
    {
        // For now, redirect to order list as the main dashboard feature
        return redirect()->route('mypage.order.list');
    }

    public function orderList(Request $request)
    {
        $user = Auth::user();

        // Base query
        $query = Order::where('member_seq', $user->member_seq);

        // Calculate counts
        $allCount = (clone $query)->count();
        $orderCount = (clone $query)->whereIn('step', [15, 25, 35, 45, 55])->count();
        $deliveryCount = (clone $query)->whereIn('step', [65, 75])->count();

        // Filter by step if requested
        if ($request->filled('step')) {
            if ($request->step == 'order') {
                $query->whereIn('step', [15, 25, 35, 45, 55]);
            } elseif ($request->step == 'delivery') {
                $query->whereIn('step', [65, 75]);
            }
        }

        // Fetch orders, paginated with eager loading
        $orders = $query->with(['items.goods.images', 'items.options'])
            ->orderBy('regist_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('front.mypage.order_list', compact('orders', 'allCount', 'orderCount', 'deliveryCount'));
    }

    public function orderView($id)
    {
        $user = Auth::user();

        // Fetch order with items and options, ensuring it belongs to the user
        $order = Order::where('member_seq', $user->member_seq)
            ->where('order_seq', $id)
            ->with(['items.goods', 'items.options'])
            ->firstOrFail();

        return view('front.mypage.order_view', compact('order'));
    }
}
