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
            'order_user_name' => 'required',
            'order_cellphone' => 'required',
            'order_email' => 'required|email',
            'recipient_user_name' => 'required',
            'recipient_cellphone' => 'required',
            'recipient_zipcode' => 'required',
            'recipient_address' => 'required',
            'payment' => 'required',
        ]);

        $cart_seqs = $request->input('cart_seq');

        // 2. Fetch Cart Items
        $cartItems = Cart::currentUser()
            ->whereIn('cart_seq', $cart_seqs)
            ->with(['goods.images', 'goods.option', 'options', 'inputs'])
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
                $ea = $option->ea ?? 1;
                
                // Logic to find price (same as cart)
                $price = 0;
                $goods = $cItem->goods;
                if ($goods && $goods->option) {
                    $matchedOption = $goods->option->first(function($o) use ($option) {
                         return (string)$o->option1 == (string)$option->option1 &&
                                (string)$o->option2 == (string)$option->option2 &&
                                (string)$o->option3 == (string)$option->option3 &&
                                (string)$o->option4 == (string)$option->option4 &&
                                (string)$o->option5 == (string)$option->option5;
                    });
                    if ($matchedOption) {
                        $price = $matchedOption->price;
                    } else {
                         $price = $goods->option->first()->price ?? 0;
                    }
                }
                
                $totalPrice += ($price * $ea);
                $totalEa += $ea;
                $kinds++;
            }

            // Create Order Header
            $order = new \App\Models\Order();
            $order->order_seq = $this->generateOrderSeq();
            $order->order_user_name = $request->order_user_name;
            $order->order_cellphone = $request->order_cellphone;
            $order->order_phone = $request->order_cellphone; // Map cellphone to phone if phone empty
            $order->order_email = $request->order_email;
            $order->recipient_user_name = $request->recipient_user_name;
            $order->recipient_cellphone = $request->recipient_cellphone; // Fix field name mismatch
            $order->recipient_phone = $request->recipient_cellphone; 
            $order->recipient_zipcode = substr($request->recipient_zipcode, 0, 7); // Ensure length
            $order->recipient_address_type = $request->recipient_address_type ?? 'zibun';
            $order->recipient_address = $request->recipient_address;
            $order->recipient_address_street = $request->recipient_address_street;
            $order->recipient_address_detail = $request->recipient_address_detail;
            $order->memo = $request->memo;

            $shippingCost = 3000;
            $freeShippingThreshold = 50000;
            $packagingCost = 300;

            $shipping = 0;
            if ($totalPrice > 0 && $totalPrice < $freeShippingThreshold) {
                $shipping = $shippingCost;
            }

            $tax = floor($totalPrice * 0.1);
            $finalSettlePrice = $totalPrice + $shipping + $tax + $packagingCost;

            $order->settleprice = $finalSettlePrice;
            $order->original_settleprice = $finalSettlePrice;
            $order->payment = $request->payment;
            $order->regist_date = now();
            $order->session_id = Session::getId();

            // Default/Required Legacy Fields
            $order->enuri = 0;
            $order->tax = $tax;
            $order->shipping_cost = $shipping;
            // $order->delivery_cost = $packagingCost; // Optional: store packaging here if needed? Leaving 0 for safety.

            $order->international = 'domestic';
            $order->international_cost = 0;
            $order->total_ea = $totalEa;
            $order->total_type = $kinds;
            $order->mode = 'order'; 
            $order->sitetype = 'P'; 
            $order->skintype = 'P';
            $order->important = '0';
            $order->hidden = 'N';
            $order->admin_order = '';
            $order->cash_receipts_no = '';
            $order->virtual_date = '0000-00-00 00:00:00'; 
            $order->ip = $request->ip();

            if ($request->payment == 'bank') {
                $order->bank_account = $request->bank_account;
                $order->depositor = $request->depositor;
                $order->step = \App\Models\Order::STEP_ORDER_RECEIVED;
                $order->deposit_yn = 'n';
                $order->bundle_yn = 'n';
            } else {
                // Card payment placeholder
                $order->step = \App\Models\Order::STEP_PAYMENT_CONFIRMED;
                $order->deposit_yn = 'y'; 
                $order->bundle_yn = 'n';
            }

            if ($user) {
                $order->member_seq = $user->member_seq;
            } else {
                $order->member_seq = 0; // Guest
            }

            // Handle strict mode date issue by using raw query if eloquent fails?
            // Or just try specific format.
            // For now, let's try standard save, but catch strict date errors
             try {
                $order->save();
            } catch (\Exception $e) {
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
                $ea = $option->ea ?? 1;

                 // Price Logic Again
                $price = 0;
                $matchedOption = null;
                if ($goods && $goods->option) {
                    $matchedOption = $goods->option->first(function($o) use ($option) {
                         return (string)$o->option1 == (string)$option->option1 &&
                                (string)$o->option2 == (string)$option->option2 &&
                                (string)$o->option3 == (string)$option->option3 &&
                                (string)$o->option4 == (string)$option->option4 &&
                                (string)$o->option5 == (string)$option->option5;
                    });
                     if ($matchedOption) {
                        $price = $matchedOption->price;
                    } else {
                         $price = $goods->option->first()->price ?? 0;
                    }
                }

                $orderItem = new \App\Models\OrderItem();
                $orderItem->order_seq = $order->order_seq;
                $orderItem->goods_seq = $goods->goods_seq;
                $orderItem->goods_name = $goods->goods_name;
                $orderItem->goods_shipping_cost = 0;
                $orderItem->basic_shipping_cost = 0;
                $orderItem->goods_code = $goods->goods_code; // Populate goods_code
                $orderItem->image = $goods->images->where('image_type', 'list1')->first()->image ?? '';
                $orderItem->save();

                $itemOption = new \App\Models\OrderItemOption();
                $itemOption->order_seq = $order->order_seq;
                $itemOption->item_seq = $orderItem->item_seq;
                $itemOption->price = $price;
                $itemOption->ea = $ea;
                $itemOption->step = $order->step;
                $itemOption->option1 = $option->option1 ?? '';
                $itemOption->option2 = $option->option2 ?? '';
                $itemOption->title1 = $option->title1 ?? '옵션';
                $itemOption->save();
            }

            // Delete from Cart
            Cart::currentUser()->whereIn('cart_seq', $cart_seqs)->delete();
            \App\Models\CartOption::whereIn('cart_seq', $cart_seqs)->delete();
            \App\Models\CartInput::whereIn('cart_seq', $cart_seqs)->delete();

            DB::commit();

            return redirect()->route('order.complete', ['id' => $order->order_seq]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['msg' => '주문 처리 중 오류 발생: ' . $e->getMessage() . ' line:' . $e->getLine()]);
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
