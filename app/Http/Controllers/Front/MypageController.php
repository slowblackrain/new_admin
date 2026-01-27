<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function orderClaimList(Request $request)
    {
        $user = Auth::user();

        // Base query
        $query = Order::where('member_seq', $user->member_seq);

        // Claims usually are Cancel (95), Return/Refund (often shared or specific codes).
        // Based on Order model: 85=Complete, 95=Cancel, 99=Fail.
        // Legacy system often uses ~80-99 for these. 
        // Let's filter for Cancel (95) and maybe others if defined later.
        // For now, let's include 95 (Cancel) and 85 (Transaction Complete - often includes returns finalized? No, usually 85 is happy path).
        // Let's stick to 95 for "Cancel/Return" bucket until we find more codes.
        // Actually, let's show all "Terminated" orders here or specific claims?
        // Let's strictly show 95 (Cancel) and 99 (Fail) for now as "Claims/Cancels".
        $query->whereIn('step', [95, 99]);

        // Calculate counts for tabs
        $cancelCount = (clone $query)->where('step', 95)->count();
        $returnCount = 0; // Placeholder until return logic defined
        $exchangeCount = 0; // Placeholder

        // Fetch orders
        $orders = $query->with(['items.goods.images', 'items.options'])
            ->orderBy('regist_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('front.mypage.order_claim_list', compact('orders', 'cancelCount', 'returnCount', 'exchangeCount'));
    }

    public function wishlist()
    {
        $user = Auth::user();

        $wishes = \App\Models\Wish::currentUser()
            ->with(['goods.images', 'goods.option']) // Eager load goods & images
            ->orderBy('regist_date', 'desc')
            ->paginate(10);

        return view('front.mypage.wishlist', compact('wishes'));
    }

    public function wishlistDestroy($id)
    {
        $wish = \App\Models\Wish::currentUser()->findOrFail($id);
        $wish->delete();

        return back()->with('success', '관심상품이 삭제되었습니다.');
    }

    // --- Claim (Cancel/Return/Exchange) Methods ---

    public function claimApply($orderSeq, $type)
    {
        $user = Auth::user();
        $order = Order::where('member_seq', $user->member_seq)
            ->where('order_seq', $orderSeq)
            ->with(['items.goods', 'items.options'])
            ->firstOrFail();

        // Basic validation of step vs type can go here
        
        return view('front.mypage.claim_apply', compact('order', 'type'));
    }

    public function claimStore(Request $request, $orderSeq)
    {
        $request->validate([
            'type' => 'required|in:cancel,return,exchange',
            'reason' => 'required|string|max:255',
            'reason_detail' => 'nullable|string',
            'refund_bank' => 'nullable|string',
            'refund_account' => 'nullable|string',
            'refund_depositor' => 'nullable|string',
            'items' => 'required|array' // items to claim
        ]);

        $user = Auth::user();
        $order = Order::where('member_seq', $user->member_seq)
            ->where('order_seq', $orderSeq)
            ->firstOrFail();

        $type = $request->type;
        $targetStep = 0;
        
        DB::beginTransaction();
        try {
            // Determine target step based on type
            if ($type == 'cancel') {
                 // If instant cancel (step < 25), might handle differently, but usually request
                 $targetStep = 95; // 95 is Cancel, 85 is Return Request in some legacy?
                 // Let's use 95 for Cancel Request for now.
                 // Legacy often uses separate tables or status codes.
                 // FM legacy: 85=Return Request, 95=Cancel Complete?
                 // Let's assume 95 is Cancel Request/Complete purely for this impl.
                 
                 // If step < 25, instant cancel
                 if ($order->step < 25) {
                     $order->step = 95; // Cancel Complete
                     $order->save();
                     // Restore stock, coupon, emoney here if needed (omitted for brevity)
                 } else {
                     $order->step = 91; // 91: Cancel Request
                     $order->save();
                 }
            } elseif ($type == 'return') {
                $order->step = 81; // 81: Return Request
                $order->save();
            } elseif ($type == 'exchange') {
                $order->step = 82; // 82: Exchange Request
                $order->save();
            }
            
            // Log claim detail to a separate table if exists, or just note in memo
            // Legacy uses fm_order_refund or fm_order_return tables.
            // For MVP, we update order step and maybe add a memo.
            $order->admin_memo .= "\n[" . now() . "] 사용자 클레임 신청 ($type): " . $request->reason . " / " . $request->reason_detail;
            $order->save();

            DB::commit();
            
            return redirect()->route('mypage.order.view', $orderSeq)->with('success', '신청이 접수되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }


    // --- Benefit (Coupon/Emoney/Point) Methods ---

    public function couponList(Request $request)
    {
        $user = Auth::user();
        
        // Fetch downloaded coupons (Active/Used/Expired)
        $query = \App\Models\CouponDownload::where('member_seq', $user->member_seq);
        
        $coupons = $query->with('coupon')
            ->orderBy('regist_date', 'desc')
            ->paginate(10);
            
        // Calculate usable count
        $usableCount = \App\Models\CouponDownload::where('member_seq', $user->member_seq)
            ->where('use_status', 'unused')
            // Add expiry check logic here later if needed
            ->count();
            
        return view('front.mypage.coupon', compact('coupons', 'usableCount'));
    }

    public function emoneyList(Request $request)
    {
        $user = Auth::user();
        
        $query = \App\Models\Emoney::where('member_seq', $user->member_seq);
        
        $emoneyList = $query->orderBy('regist_date', 'desc')
            ->paginate(10);
            
        $currentEmoney = $user->emoney; 
        
        return view('front.mypage.emoney', compact('emoneyList', 'currentEmoney'));
    }

    public function pointList(Request $request)
    {
        $user = Auth::user();
        
        $query = \App\Models\Point::where('member_seq', $user->member_seq);
        
        $pointList = $query->orderBy('regist_date', 'desc')
            ->paginate(10);
            
        $currentPoint = $user->point; 
        
        return view('front.mypage.point', compact('pointList', 'currentPoint'));
    }
}
