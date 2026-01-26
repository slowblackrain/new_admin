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

        // 1. Step Counts Calculation
        // We need counts for all tabs regardless of current filter
        $stepCounts = [
            'total' => Order::count(),
            '15' => Order::where('step', 15)->count(), // 주문접수
            '25' => Order::where('step', 25)->count(), // 결제확인
            '35_45' => Order::whereBetween('step', [35, 45])->count(), // 상품준비
            '50_55' => Order::whereBetween('step', [50, 55])->count(), // 출고
            '60_65' => Order::whereBetween('step', [60, 65])->count(), // 배송중
            '70' => Order::where('step', 70)->count(), // 배송완료
            '75' => Order::where('step', 75)->count(), // 구매확정
            '85' => Order::where('step', 85)->count(), // 거래완료
            '95' => Order::where('step', 95)->count(), // 주문취소
        ];

        // 2. Status Filter
        if ($request->filled('step')) {
            $step = $request->step;
            if (strpos($step, '_') !== false) {
                // Range filter (e.g., 35_45)
                [$min, $max] = explode('_', $step);
                $query->whereBetween('step', [$min, $max]);
            } else {
                // Single step filter
                $query->where('step', $step);
            }
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('order_seq', 'like', "%{$keyword}%")
                  ->orWhere('order_user_name', 'like', "%{$keyword}%");
            });
        }

        // 4. Date Range Filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('regist_date', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $orders = $query->orderBy('regist_date', 'desc')->paginate(20);
        $currentStep = $request->step;

        return view('admin.order.catalog', compact('orders', 'stepCounts', 'currentStep'));
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
        $order = Order::with([
            'items.options', 
            'items.goods.images', 
            'member'
        ])->where('order_seq', $order_seq)->firstOrFail();
        
        return view('admin.order.view', compact('order'));
    }
}
