<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function catalog(Request $request)
    {
        $query = Order::query();

        // Status Filter
        if ($request->filled('step')) {
            $query->where('step', $request->step);
        }

        // Keyword Filter
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('order_seq', 'like', "%{$keyword}%")
                  ->orWhere('order_user_name', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->orderBy('regist_date', 'desc')->paginate(20);

        return view('admin.order.catalog', compact('orders'));
    }

    public function bank_check()
    {
        // For Step 1 (Deposit Waiting)
        $orders = Order::where('step', 10) // 10: Deposit Waiting
                       ->orderBy('regist_date', 'asc')
                       ->paginate(20);
        
        return view('admin.order.bank_check', compact('orders'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_seq' => 'required',
            'step' => 'required|integer'
        ]);

        $order = Order::find($request->order_seq);
        if ($order) {
            $order->step = $request->step;
            $order->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Order not found']);
    }

    public function view($order_seq)
    {
        $order = Order::with('items')->where('order_seq', $order_seq)->firstOrFail();
        return view('admin.order.view', compact('order'));
    }
}
