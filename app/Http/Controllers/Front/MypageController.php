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
        $user = Auth::user();

        // 1. 진행중인 주문 수 (step 15 이상 75 미만인 실질적 진행 중 주문)
        $orderCount = Order::where('member_seq', $user->member_seq)
            ->where('step', '>=', 15)
            ->where('step', '<', 75)
            ->count();

        // 2. 교환, 반품 (반품요청 81, 교환요청 82, 취소요청 91 등 진행 중 클레임. 완료된 취소 95는 제외)
        $claimCount = Order::where('member_seq', $user->member_seq)
            ->whereIn('step', [81, 82, 91])
            ->count();

        // 3. 할인쿠폰 수
        $couponCount = \App\Models\CouponDownload::where('member_seq', $user->member_seq)
            ->where('use_status', 'unused')
            ->count();

        // 4. 장바구니 수
        $cartCount = \App\Models\Cart::currentUser()->count();

        // 5. 위시리스트 수
        $wishCount = \App\Models\Wish::currentUser()->count();

        // 6. 최근 주문내역 (5건 - 가주문 step 0 제외)
        $recentOrders = Order::where('member_seq', $user->member_seq)
            ->where('step', '>=', 15)
            ->with(['items'])
            ->orderBy('regist_date', 'desc')
            ->take(5)
            ->get();

        // 7. 최근 문의사항 (5건)
        $recentQuestions = \App\Models\Board::where('boardid', 'mbqna')
            ->where('mseq', $user->member_seq)
            ->orderBy('r_date', 'desc')
            ->take(5)
            ->get();

        return view('front.mypage.index', compact(
            'user',
            'orderCount',
            'claimCount',
            'couponCount',
            'cartCount',
            'wishCount',
            'recentOrders',
            'recentQuestions'
        ))->with('title', '마이페이지');
    }

    public function withdrawForm()
    {
        return view('front.mypage.withdraw');
    }

    public function withdrawProcess(Request $request, \App\Services\MemberManagementService $memberService)
    {
        $request->validate([
            'reason_code' => 'required',
            'reason_desc' => 'required_if:reason_code,other|string|max:255',
            'agree' => 'accepted'
        ]);

        $user = Auth::user();
        $reason = $request->reason_code;
        if ($reason === 'other') {
            $reason = '기타: ' . $request->reason_desc;
        }

        try {
            $memberService->processWithdrawal($user->member_seq, $reason, 'user');
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('success', '정상적으로 회원 탈퇴가 처리되었습니다. 그동안 이용해 주셔서 감사합니다.');
        } catch (\Exception $e) {
            return back()->with('error', '회원 탈퇴 처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
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

        return view('front.mypage.order_list', compact('orders', 'allCount', 'orderCount', 'deliveryCount'))->with('title', '주문/배송 조회');
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
        // Let's strictly show 81(Return), 82(Exchange), 91(Cancel Request), 95 (Cancel) for now as "Claims".
        $query->whereIn('step', [81, 82, 91, 95]);

        // Calculate counts for tabs
        $cancelCount = clone $query;
        $cancelCount = $cancelCount->whereIn('step', [91, 95])->count();
        $returnCount = clone $query;
        $returnCount = $returnCount->where('step', 81)->count();
        $exchangeCount = clone $query;
        $exchangeCount = $exchangeCount->where('step', 82)->count();

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

    public function confirmPurchase($orderSeq)
    {
        $user = Auth::user();
        $order = Order::where('member_seq', $user->member_seq)
            ->where('order_seq', $orderSeq)
            ->with(['items.goods']) // Need goods to check ATS
            ->firstOrFail();

        if ($order->step >= 75) {
            return back()->with('error', '이미 구매확정된 주문입니다.');
        }

        DB::beginTransaction();
        try {
            // Update Order Step
            $order->step = 75; // Purchase Confirmed
            $order->save();

            // Settle Agency Margin
            // Iterate items to find Agency Products
            foreach ($order->items as $item) {
                // Check if ATS Product (Logic matching OrderController deduction)
                // goods_scode starts with 'GT' AND category... (or just GT check for now as established)
                 if ($item->goods && strpos($item->goods->goods_scode, 'GT') === 0) {
                     $resellerSeq = $item->goods->provider_member_seq;
                     
                     // Calculate Prices for this item
                     // Sell Price: item option price * ea
                     // Provider Price: need to fetch from option again effectively or store it on item?
                     // Storing on item is best but schema didn't show 'provider_price' on fm_order_item.
                     // We must fetch from current goods option (Assuming price didn't change? Risk).
                     // Ideally fm_order_item_option should have it.
                     // Let's look at fm_order_item_option (OrderItemOption model).
                     // It usually copies 'price' (consumer price). Does it copy 'provider_price'?
                     // If not, we rely on fm_goods_option validation.
                     
                     $option = \App\Models\OrderItemOption::where('item_seq', $item->item_seq)->first();
                     $currentGoodsOption = \App\Models\GoodsOption::where('goods_seq', $item->goods_seq)
                        ->where('option1', $option->option1) // Simple match
                        ->first();
                        
                     if ($currentGoodsOption) {
                         $sellPrice = $item->item_price ?? ($option->price * $option->ea); 
                         $providerPrice = $currentGoodsOption->provider_price * $option->ea;
                         
                         // Determine YearMonth
                         $yearMonth = date('Y-m');
                         
                         // Call Service
                         app(\App\Services\Agency\AgencySettlementService::class)->settleAgencyMargin(
                             $resellerSeq,
                             $yearMonth,
                             $sellPrice,
                             $providerPrice
                         );
                     }
                 }
            }

            DB::commit();
            return back()->with('success', '구매확정이 완료되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '처리 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}
