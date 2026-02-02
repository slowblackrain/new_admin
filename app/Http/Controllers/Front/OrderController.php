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

        $shippingCost = 3000;
        $freeShippingThreshold = 50000;
        $packagingCost = 300;

        $shipping = 0;
        if ($totalPrice > 0 && $totalPrice < $freeShippingThreshold) {
            $shipping = $shippingCost;
        }

        $tax = floor($totalPrice * 0.1);
        $finalPrice = $totalPrice + $shipping + $tax + $packagingCost;

        // 5. Fetch Usable Coupons
        $coupons = [];
        if ($user) {
            $coupons = DB::table('fm_download')
                ->join('fm_coupon', 'fm_download.coupon_seq', '=', 'fm_coupon.coupon_seq')
                ->where('fm_download.member_seq', $user->member_seq)
                ->where('fm_download.use_status', 'unused') // Verify 'unused' is correct value. Usually 'used'/'unused' or '1'/'0'. Let's assume 'unused' based on 'use_status' column name often string enum. Check Schema if possible or try both.
                ->where('fm_download.issue_enddate', '>=', now())
                ->select('fm_download.*', 'fm_coupon.coupon_name', 'fm_coupon.coupon_seq as master_coupon_seq', 'fm_coupon.sale_type', 'fm_coupon.percent_goods_sale', 'fm_coupon.won_goods_sale', 'fm_coupon.max_percent_goods_sale')
                ->get();
        }

        return view('front.order.order', compact('cartItems', 'user', 'totalPrice', 'user', 'cart_seqs', 'tax', 'finalPrice', 'shipping', 'packagingCost', 'coupons'));
    }

    public function store(Request $request)
    {
        file_put_contents('c:/dometopia/new_admin/debug_order.txt', "Store Called at " . now() . "\n", FILE_APPEND);
        
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
                // ... (Calculation Logic) ...
                $option = $cItem->options->first();
                $ea = $option->ea ?? 1;
                
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

                // Stock Validation
                if ($matchedOption) {
                    $supply = DB::table('fm_goods_supply')
                        ->where('option_seq', $matchedOption->option_seq)
                        ->first();
                    $currentStock = $supply->stock ?? 0;
                    if ($currentStock < $ea) {
                        throw new \Exception("상품 '{$goods->goods_name}'의 선택된 옵션 재고가 부족합니다. (현재: {$currentStock}, 요청: {$ea})");
                    }
                } else {
                    $supply = DB::table('fm_goods_supply')
                        ->where('goods_seq', $goods->goods_seq)
                        ->first();
                    $currentStock = $supply->stock ?? 0;
                    if ($currentStock < $ea) {
                        throw new \Exception("상품 '{$goods->goods_name}'의 재고가 부족합니다. (현재: {$currentStock}, 요청: {$ea})");
                    }
                }
            }

            // Create Order Header
            $order = new \App\Models\Order();
            $order->order_seq = $this->generateOrderSeq();
            
            $order->order_user_name = $request->order_user_name;
            $order->order_cellphone = $request->order_cellphone;
            $order->order_phone = $request->order_cellphone; 
            $order->order_email = $request->order_email;
            $order->recipient_user_name = $request->recipient_user_name;
            $order->recipient_cellphone = $request->recipient_cellphone; 
            $order->recipient_phone = $request->recipient_cellphone; 
            $order->recipient_zipcode = substr($request->recipient_zipcode, 0, 7); 
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

            // Point/Emoney Usage
            $useEmoney = $request->input('use_emoney', 0);
            $usePoint = $request->input('use_point', 0);

            if ($useEmoney > 0) {
                if ($user->emoney < $useEmoney) {
                    throw new \Exception("보유 예치금이 부족합니다.");
                }
                if ($useEmoney > $finalSettlePrice) {
                    throw new \Exception("결제 금액보다 많은 예치금을 사용할 수 없습니다.");
                }
                $finalSettlePrice -= $useEmoney;
                $user->decrement('emoney', $useEmoney);
                $order->emoney = $useEmoney; // Assuming 'emoney' column holds USED emoney
            } else {
                 $order->emoney = 0;
            }

            if ($usePoint > 0) {
                 if ($user->point < $usePoint) {
                    throw new \Exception("보유 포인트가 부족합니다.");
                }
                 if ($usePoint > $finalSettlePrice) {
                    throw new \Exception("결제 금액보다 많은 포인트를 사용할 수 없습니다.");
                }
                $finalSettlePrice -= $usePoint;
                $user->decrement('point', $usePoint);
                $order->cash = $usePoint; 
            } else {
                $order->cash = 0;
            }

            // Coupon Usage
            $downloadSeq = $request->input('download_seq');
            
            $couponDiscount = 0;
            if ($downloadSeq) {
                // ... fetch download ...
                $download = DB::table('fm_download')
                    ->where('download_seq', $downloadSeq)
                    ->where('member_seq', $user->member_seq)
                    ->first();
                
                if (!$download) {
                     throw new \Exception("쿠폰이 존재하지 않거나 유효하지 않습니다.");
                }
                
                if ($download->use_status !== 'unused') {
                     throw new \Exception("이미 사용된 쿠폰입니다.");
                }

                // ... fetch coupon ...
                $coupon = DB::table('fm_coupon')->where('coupon_seq', $download->coupon_seq)->first();
                
                // Logic for Percent vs Amount
                if ($coupon->sale_type == 'percent') {
                     $calcDiscount = floor($totalPrice * ($coupon->percent_goods_sale / 100));
                     if ($coupon->max_percent_goods_sale > 0 && $calcDiscount > $coupon->max_percent_goods_sale) {
                         $calcDiscount = $coupon->max_percent_goods_sale;
                     }
                     $couponDiscount = $calcDiscount;
                } elseif ($coupon->sale_type == 'won') {
                    $couponDiscount = $coupon->won_goods_sale;
                }
                
                if ($couponDiscount > $finalSettlePrice) {
                    $couponDiscount = $finalSettlePrice; // Cap at remaining price
                }
                
                $finalSettlePrice -= $couponDiscount;

                // Mark Used
                DB::table('fm_download')
                    ->where('download_seq', $downloadSeq)
                    ->update([
                        'use_status' => 'used', 
                        'use_date' => now()
                    ]);
                
                $order->download_seq = $downloadSeq; 
                $order->coupon_sale = $couponDiscount;
            } else {
                $order->coupon_sale = 0;
            }

            $order->settleprice = $finalSettlePrice;
            $order->enuri = 0;
            $order->tax = $tax;
            $order->shipping_cost = $shipping;

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
                $order->step = \App\Models\Order::STEP_PAYMENT_CONFIRMED;
                $order->deposit_yn = 'y'; 
                $order->bundle_yn = 'n';
            }

            if ($user) {
                $order->member_seq = $user->member_seq;
            } else {
                $order->member_seq = 0; // Guest
            }

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
                $orderItem->goods_code = $goods->goods_code; 
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

                // Stock Deduction Logic
                if ($matchedOption) {
                    $supply = DB::table('fm_goods_supply')
                        ->where('option_seq', $matchedOption->option_seq)
                        ->first();
                    
                    if ($supply) {
                        DB::table('fm_goods_supply')
                            ->where('supply_seq', $supply->supply_seq)
                            ->decrement('stock', $ea);

                        // SCM Stock Deduction (wh_seq = 1)
                        DB::table('fm_scm_location_link')
                            ->where('option_seq', $matchedOption->option_seq)
                            ->where('wh_seq', 1)
                            ->decrement('ea', $ea);
                    }
                } else {
                    DB::table('fm_goods_supply')
                        ->where('goods_seq', $goods->goods_seq)
                        ->decrement('stock', $ea);

                    // SCM Stock Deduction (wh_seq = 1)
                    DB::table('fm_scm_location_link')
                        ->where('goods_seq', $goods->goods_seq)
                        ->where('wh_seq', 1)
                        ->decrement('ea', $ea);
                }

                // Total Stock Deduction
                DB::table('fm_goods')
                    ->where('goods_seq', $goods->goods_seq)
                    ->decrement('tot_stock', $ea);
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
