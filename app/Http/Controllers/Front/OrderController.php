<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // 1. Validate Input (Cart Seqs)
        // If coming from cart form submit
        $cart_seqs = $request->input('cart_seq');

        if (!$cart_seqs || !is_array($cart_seqs)) {
            return redirect()->route('cart.index')->withErrors(['msg' => '선택된 상품이 없습니다.']);
        }

        // 2. Fetch Cart Items
        $cartItems = Cart::currentUser()
            ->whereIn('cart_seq', $cart_seqs)
            ->with(['goods.images', 'goods.option', 'options'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['msg' => '선택된 상품이 존재하지 않습니다.']);
        }

        // 3. Prepare User Info
        $user = Auth::user();

        // 4. Calculate Total (Initial calculation for verification, view will also Calc)
        $totalPrice = 0;
        foreach ($cartItems as $item) {
            $option = $item->options->first();
            $price = $item->goods->option->first()->price ?? 0;
            $ea = $option->ea ?? 1;
            $totalPrice += ($price * $ea);
        }

        $tax = floor($totalPrice * 0.1);
        $finalPrice = $totalPrice + $tax;

        return view('front.order.order', compact('cartItems', 'user', 'totalPrice', 'user', 'cart_seqs', 'tax', 'finalPrice'));
    }

    public function store(Request $request)
    {
        // 1. Validate
        $request->validate([
            'cart_seq' => 'required|array',
            'recipient_user_name' => 'required',
            'recipient_cellphone' => 'required',
            'recipient_zipcode' => 'required',
            'recipient_address' => 'required',
            'recipient_address_detail' => 'nullable',
            'payment' => 'required',
        ]);

        $cart_seqs = $request->input('cart_seq');

        // 2. Fetch Cart Items
        $cartItems = Cart::currentUser()
            ->whereIn('cart_seq', $cart_seqs)
            ->with(['goods.images', 'goods.option', 'options'])
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->withErrors(['msg' => '선택된 상품이 없습니다.']);
        }

        // 3. Process Order in Transaction
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $totalPrice = 0;
            $totalEa = 0;
            $kinds = 0;

            // Calculate Total
            foreach ($cartItems as $cItem) {
                $option = $cItem->options->first();
                $price = $cItem->goods->option->first()->price ?? 0;
                $ea = $option->ea ?? 1;
                $totalPrice += ($price * $ea);
                $totalEa += $ea;
                $kinds++;
            }

            // Create Order Header
            $order = new \App\Models\Order();
            $order->order_seq = $this->generateOrderSeq();
            $order->order_user_name = $request->order_user_name;
            $order->order_cellphone = $request->order_cellphone;
            $order->order_email = $request->order_email;
            $order->recipient_user_name = $request->recipient_user_name;
            $order->recipient_cellphone = $request->recipient_cellphone;
            $order->recipient_zipcode = $request->recipient_zipcode;
            $order->recipient_address_type = $request->recipient_address_type ?? 'zibun';
            $order->recipient_address = $request->recipient_address; // This should be Jibun address
            $order->recipient_address_street = $request->recipient_address_street; // This should be Road address
            $order->recipient_address_detail = $request->recipient_address_detail;
            $order->memo = $request->memo;

            $order->settleprice = $totalPrice;
            $order->original_settleprice = $totalPrice;
            $order->step = \App\Models\Order::STEP_ORDER_RECEIVED;
            $order->payment = $request->payment;
            $order->regist_date = now();
            $order->session_id = Session::getId();

            // Default/Required Legacy Fields
            $order->enuri = 0;
            $order->tax = 0;
            $order->shipping_cost = 0;
            $order->international = 'domestic';
            $order->international_cost = 0;
            $order->total_ea = $totalEa;
            $order->total_type = $kinds;
            $order->mode = 'order'; // specific to legacy, assumed 'order'
            $order->sitetype = 'P'; // PC
            $order->skintype = 'P';
            $order->important = '0';
            $order->hidden = 'N';
            $order->admin_order = '';
            $order->cash_receipts_no = '';
            $order->virtual_date = '0000-00-00 00:00:00'; // Or valid date if strict mode

            if ($request->payment == 'bank') {
                $order->bank_account = $request->bank_account;
                $order->depositor = $request->depositor;
                $order->step = \App\Models\Order::STEP_ORDER_RECEIVED;
                $order->deposit_yn = 'n';
                $order->bundle_yn = 'n';
            } else {
                $order->step = \App\Models\Order::STEP_PAYMENT_CONFIRMED;
                $order->deposit_yn = 'y'; // Assumed paid
                $order->bundle_yn = 'n';
            }

            if ($user) {
                $order->member_seq = $user->member_seq;
            }

            // Handle strict mode for 0000-00-00
            try {
                $order->save();
            } catch (\Exception $e) {
                // Check if date error, retry with now() if strict mode
                if (strpos($e->getMessage(), 'virtual_date') !== false) {
                    $order->virtual_date = now();
                    $order->save();
                } else {
                    throw $e;
                }
            }

            // Create Order Items
            foreach ($cartItems as $cItem) {
                $goods = $cItem->goods;
                $option = $cItem->options->first();
                $price = $goods->option->first()->price ?? 0;
                $ea = $option->ea ?? 1;

                $orderItem = new \App\Models\OrderItem();
                $orderItem->order_seq = $order->order_seq;
                $orderItem->goods_seq = $goods->goods_seq;
                $orderItem->goods_name = $goods->goods_name;
                $orderItem->goods_shipping_cost = 0;
                $orderItem->save();

                $itemOption = new \App\Models\OrderItemOption();
                $itemOption->order_seq = $order->order_seq;
                $itemOption->item_seq = $orderItem->item_seq;
                $itemOption->price = $price;
                $itemOption->ea = $ea;
                $itemOption->step = $order->step;
                $itemOption->option1 = $option->option1 ?? '기본';
                $itemOption->save();
            }

            // Delete from Cart
            Cart::currentUser()->whereIn('cart_seq', $cart_seqs)->delete();

            DB::commit();

            return redirect()->route('order.complete', ['id' => $order->order_seq]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => '주문 처리 중 오류 발생: ' . $e->getMessage()]);
        }
    }

    public function complete($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        return view('front.order.complete', compact('order'));
    }

    private function generateOrderSeq()
    {
        $today = date('Y-m-d');
        $exists = DB::table('fm_order_sequence')->where('regist_date', $today)->exists();

        if (!$exists) {
            DB::statement('TRUNCATE TABLE fm_order_sequence');
            DB::statement('ALTER TABLE fm_order_sequence AUTO_INCREMENT = 17530');
        }

        $id = DB::table('fm_order_sequence')->insertGetId([
            'regist_date' => $today
        ]);

        return date('YmdHis') . $id;
    }
}
