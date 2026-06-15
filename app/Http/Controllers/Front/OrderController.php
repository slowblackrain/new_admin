<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

use App\Services\Agency\AgencyProductService;
use App\Models\CategoryLink;

class OrderController extends Controller
{
    protected $settlementService;
    protected AgencyProductService $agencyService;
    protected \App\Services\PricingService $pricingService;
    protected \App\Services\Admin\Goods\GoodsSetService $goodsSetService;
    protected \App\Services\ShippingService $shippingService;

    public function __construct(
        \App\Services\Agency\AgencySettlementService $settlementService,
        AgencyProductService $agencyService,
        \App\Services\PricingService $pricingService,
        \App\Services\Admin\Goods\GoodsSetService $goodsSetService,
        \App\Services\ShippingService $shippingService
    ) {
        $this->settlementService = $settlementService;
        $this->agencyService = $agencyService;
        $this->pricingService = $pricingService;
        $this->goodsSetService = $goodsSetService;
        $this->shippingService = $shippingService;
    }

    public function index(Request $request)
    {
        // [비회원 가드] 로그인하지 않은 유저가 guest 파라미터 없이 결제창에 진입하면 로그인창으로 리다이렉트
        if (!Auth::check() && !$request->has('guest')) {
            $returnUrl = route('order.form_get', ['cart_seq' => $request->input('cart_seq') ?? []]);
            return redirect()->route('login', ['return_url' => $returnUrl]);
        }

        // 1. Validate Input (Cart Seqs)
        // If coming from cart form submit or from a validation redirect back()
        $cart_seqs = $request->input('cart_seq') ?? old('cart_seq');

        if (!$cart_seqs || !is_array($cart_seqs)) {
            return redirect()->route('cart.index')->withErrors(['msg' => '선택된 상품이 없습니다.']);
        }

        // 2. Fetch Cart Items
        // 비회원 세션 유실 방어: 명시적인 cart_seqs가 전달된 경우, 세션 ID 불일치 이슈를 우회하기 위해
        // 비회원(member_seq = 0) 데이터이거나 로그인한 본인 데이터라면 안전하게 조회를 허용합니다.
        $query = Cart::whereIn('cart_seq', $cart_seqs);
        
        if (Auth::check()) {
            $query->where('member_seq', Auth::id());
        } else {
            $query->where(function($q) {
                $q->where('session_id', \Illuminate\Support\Facades\Session::getId())
                  ->orWhere('member_seq', 0);
            });
        }

        $cartItems = $query->with(['goods.images', 'goods.option', 'options'])->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['msg' => '선택된 상품이 존재하지 않습니다.']);
        }

        // 3. Prepare User Info
        $user = Auth::user();

        // 4. Calculate Total (Initial calculation for verification, view will also Calc)
        $totalPrice = 0;
        $standardItemsTotal = 0;
        $totalVat = 0;

        foreach ($cartItems as $item) {
            $goods = $item->goods;
            $option = $item->options->first();
            $ea = $option->ea ?? 1;

            if ($goods && $goods->option) {
                $matchedOption = $goods->option->first(function($o) use ($option) {
                        return (string)$o->option1 == (string)$option->option1 &&
                            (string)$o->option2 == (string)$option->option2 &&
                            (string)$o->option3 == (string)$option->option3 &&
                            (string)$o->option4 == (string)$option->option4 &&
                            (string)$o->option5 == (string)$option->option5;
                });
                $calcOption = $matchedOption ?? $goods->option->first();
            } else {
                $calcOption = null;
            }

            $pricing = $this->pricingService->calculatePrice($goods, $calcOption, $ea);
            $totalPrice += $pricing['total_price'];

            if ($goods && $goods->tax === 'tax') {
                $totalVat += floor($pricing['total_price'] * 0.1);
            }

            // Check if standard shipping (not postpaid)
            // Note: fm_cart_option has shipping_method column.
            if (($option->shipping_method ?? '') !== 'postpaid') {
                $standardItemsTotal += $pricing['total_price'];
            }
        }

        $baseShipping = config('shop.shipping.base_cost', 2500);
        $freeShippingThreshold = config('shop.shipping.free_threshold', 150000);
        $packagingCost = config('shop.shipping.packaging_cost', 300);

        // 1. Calculate Jeju/Island Mountainous Extra Shipping Fee based on default user address
        $userAddress = '';
        $userAddressStreet = '';
        if (Auth::check() && $user) {
            $userAddress = $user->address;
            $userAddressStreet = $user->address_street;
            $defaultAddr = DB::table('fm_delivery_address')
                ->where('member_seq', $user->member_seq)
                ->where('default', 'Y')
                ->first();
            if ($defaultAddr) {
                $userAddress = $defaultAddr->recipient_address;
                $userAddressStreet = $defaultAddr->recipient_address_street;
            }
        }
        
        $extraCost = $this->getExtraShippingCost($userAddress, $userAddressStreet);

        // 2. Compute Accumulative Dropship Fees & Standard HQ shipping
        $hqTotal = 0;
        $dropshipCost = 0;

        foreach ($cartItems as $item) {
            $goods = $item->goods;
            $option = $item->options->first();
            $ea = $option->ea ?? 1;

            if (($option->shipping_method ?? '') === 'postpaid') {
                continue; // postpaid/ATS items have zero prepaid shipping
            }

            // Options mapping logic
            $matchedOption = null;
            if ($goods && $goods->option) {
                $matchedOption = $goods->option->first(function($o) use ($option) {
                    return (string)$o->option1 == (string)$option->option1 &&
                        (string)$o->option2 == (string)$option->option2 &&
                        (string)$o->option3 == (string)$option->option3 &&
                        (string)$o->option4 == (string)$option->option4 &&
                        (string)$o->option5 == (string)$option->option5;
                });
            }
            $calcOption = $matchedOption ?? ($goods->option ? $goods->option->first() : null);
            $pricing = $this->pricingService->calculatePrice($goods, $calcOption, $ea);

            if ($goods && $goods->shipping_policy === 'goods') {
                // Dropship item
                $dropshipCost += $this->shippingService->calculateProductShipping($goods, $ea);
            } else {
                // Standard Shop item
                $hqTotal += $pricing['total_price'];
            }
        }

        $shipping = $dropshipCost;
        if ($hqTotal > 0 && $hqTotal < $freeShippingThreshold) {
            $shipping += $baseShipping;
        }

        // Accumulate island/mountainous region extra shipping costs
        $shipping += $extraCost;

        $tax = floor($totalVat / 10) * 10;
        $finalPrice = $totalPrice + $shipping + $tax + $packagingCost;
        $finalPrice = floor($finalPrice / 10) * 10;

        // Check for tax-exempt items (legacy: chk_tax_exempt)
        $hasExempt = $cartItems->contains(function($item) {
            return $item->goods && $item->goods->tax === 'exempt';
        });

        // [NEW] 쿠폰/적립금 사용 제한 상품 및 3% 카드 수수료 할증 상품 플래그 추가
        $limitSeqs = ['64931', '67659', '9891', '16046', '10327', '192328', '205052', '204693'];
        $hasSaveEmoneyLimit = $cartItems->contains(function($item) use ($limitSeqs) {
            return $item->goods && in_array((string)$item->goods->goods_seq, $limitSeqs);
        });

        $bbbSeqs = ['64931', '67659', '64972', '16046', '9891', '10327', '192328', '205052', '204693'];
        $isBbbType = $cartItems->contains(function($item) use ($bbbSeqs) {
            return $item->goods && in_array((string)$item->goods->goods_seq, $bbbSeqs);
        });

        // 5. Fetch Usable Coupons
        $coupons = [];
        if ($user) {
            $coupons = DB::table('fm_download')
                ->join('fm_coupon', 'fm_download.coupon_seq', '=', 'fm_coupon.coupon_seq')
                ->where('fm_download.member_seq', $user->member_seq)
                ->where('fm_download.use_status', 'unused') 
                ->where('fm_download.issue_enddate', '>=', now())
                ->select('fm_download.*', 'fm_coupon.coupon_name', 'fm_coupon.coupon_seq as master_coupon_seq', 'fm_coupon.sale_type', 'fm_coupon.percent_goods_sale', 'fm_coupon.won_goods_sale', 'fm_coupon.max_percent_goods_sale')
                ->get();
        }

        // [NEW] 적립금(E-money) 한도 산출 (레거시 get_usable_emoney 100% 포팅)
        $usableEmoney = 0;
        $errReserve = "";
        if (Auth::check() && $user) {
            if ($hasSaveEmoneyLimit) {
                $usableEmoney = 0;
                $errReserve = "제한 상품이 포함되어 적립금을 사용할 수 없습니다.";
            } else {
                $reserves = DB::table('fm_config')->where('groupcd', 'reserve')->get()->pluck('value', 'codecd')->toArray();
                $minEmoney = (int) ($reserves['min_emoney'] ?? 100);          // 최소 사용 적립금
                $useLimit = (int) ($reserves['emoney_use_limit'] ?? 100);     // 최소 보유 적립금
                $maxPolicy = $reserves['max_emoney_policy'] ?? 'unlimit';
                
                $memberEmoney = (int) $user->emoney;
                $settlePrice = $finalPrice; // 최종 결제 예정 금액
                
                $usableEmoney = $memberEmoney;
                if ($usableEmoney > $settlePrice) {
                    $usableEmoney = $settlePrice;
                }
                
                if ($memberEmoney >= $useLimit) {
                    if ($maxPolicy === 'percent_limit' && isset($reserves['max_emoney_percent'])) {
                        $maxEmoney = (int) ($settlePrice * (int)$reserves['max_emoney_percent'] / 100);
                    } else if ($maxPolicy === 'price_limit' && isset($reserves['max_emoney'])) {
                        $maxEmoney = (int) $reserves['max_emoney'];
                    } else {
                        $maxEmoney = $settlePrice;
                    }
                    
                    if ($maxEmoney > $settlePrice) {
                        $maxEmoney = $settlePrice;
                    }
                    
                    if ($usableEmoney < $minEmoney) {
                        $usableEmoney = 0;
                        $errReserve = "적립금은 최소 " . number_format($minEmoney) . "원부터 사용가능 합니다.";
                    }
                    
                    if ($usableEmoney > $maxEmoney && $maxPolicy !== 'unlimit') {
                        $usableEmoney = $maxEmoney;
                    }
                } else {
                    $usableEmoney = 0;
                    $errReserve = number_format($useLimit) . "원 이상 적립하여야 합니다.";
                }
            }
        }

        return view('front.order.order', compact('cartItems', 'user', 'totalPrice', 'cart_seqs', 'tax', 'finalPrice', 'shipping', 'packagingCost', 'coupons', 'hasExempt', 'usableEmoney', 'errReserve', 'extraCost', 'hasSaveEmoneyLimit', 'isBbbType'));
    }

    public function calculateShipping(Request $request)
    {
        $address = $request->input('address');
        $addressStreet = $request->input('address_street');
        
        $extraShippingCost = $this->getExtraShippingCost($address, $addressStreet);

        return response()->json(['extra_cost' => $extraShippingCost]);
    }

    public function store(Request $request)
    {
        // 3분할 연락처 병합 처리
        $orderPhone = is_array($request->order_phone) ? implode('-', array_filter($request->order_phone)) : $request->order_phone;
        $orderCellphone = is_array($request->order_cellphone) ? implode('-', array_filter($request->order_cellphone)) : $request->order_cellphone;
        $recipientPhone = is_array($request->recipient_phone) ? implode('-', array_filter($request->recipient_phone)) : $request->recipient_phone;
        $recipientCellphone = is_array($request->recipient_cellphone) ? implode('-', array_filter($request->recipient_cellphone)) : $request->recipient_cellphone;
        
        $request->merge([
            'order_phone_merged' => $orderPhone,
            'order_cellphone_merged' => $orderCellphone,
            'recipient_phone_merged' => $recipientPhone,
            'recipient_cellphone_merged' => $recipientCellphone,
        ]);
        
        // 1. Validate
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'cart_seq' => 'required|array',
            'order_user_name' => 'required',
            'order_cellphone_merged' => 'required',
            'order_email' => 'required|email',
            'recipient_user_name' => 'required',
            'recipient_cellphone_merged' => 'required',
            'recipient_zipcode' => 'required',
            'recipient_address' => 'required',
            'payment' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cart_seqs = $request->input('cart_seq');

        // 2. Fetch Cart Items
        $cartItems = Cart::currentUser()
            ->whereIn('cart_seq', $cart_seqs)
            ->with(['goods.images', 'goods.option', 'options', 'inputs'])
            ->get();

        if ($cartItems->isEmpty()) {
            \Illuminate\Support\Facades\Log::info("OrderController: Cart is Empty for User: " . (\Illuminate\Support\Facades\Auth::id() ?? 'Guest'));
            return back()->withErrors(['msg' => '선택된 상품이 없습니다.']);
        }

        // 3. Process Order in Transaction
        DB::beginTransaction();
        try {
            $user = Auth::user();
            
            $totalPrice = 0;
            $totalEa = 0;
            $totalVat = 0;
            $kinds = 0;

            // Calculate Total
            foreach ($cartItems as $cItem) {
                $goods = $cItem->goods;
                $option = $cItem->options->first();
                $ea = $option->ea ?? 1;
                
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
                }
                
                $calcOption = $matchedOption ?? $goods->option->first();
                $pricingInfo = $this->pricingService->calculatePrice($goods, $calcOption, $ea);
                $price = $pricingInfo['unit_price'];

                $totalPrice += ($price * $ea);
                
                if ($goods && $goods->tax === 'tax') {
                    $totalVat += floor(($price * $ea) * 0.1);
                }

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

            // [NEW] 결제 및 할인 제한 검증
            $hasExempt = false;
            $hasSaveEmoneyLimit = false;
            $isBbbType = false;
            
            $limitSeqs = ['64931', '67659', '9891', '16046', '10327', '192328', '205052', '204693'];
            $bbbSeqs = ['64931', '67659', '64972', '16046', '9891', '10327', '192328', '205052', '204693'];
            
            foreach ($cartItems as $cItem) {
                $goods = $cItem->goods;
                if ($goods) {
                    if ($goods->tax === 'exempt') $hasExempt = true;
                    if (in_array((string)$goods->goods_seq, $limitSeqs)) $hasSaveEmoneyLimit = true;
                    if (in_array((string)$goods->goods_seq, $bbbSeqs)) $isBbbType = true;
                }
            }

            if ($hasExempt) {
                if ($request->payment === 'card') {
                    throw new \Exception("면세(비과세) 상품이 포함된 경우 신용카드 결제를 이용할 수 없습니다.");
                }
                if ((int)$request->input('typereceipt', 0) > 0) {
                    throw new \Exception("면세(비과세) 상품이 포함된 경우 증빙서류 발급 신청을 할 수 없습니다.");
                }
            }

            $useEmoney = (int)$request->input('use_emoney', 0);
            $downloadSeq = $request->input('download_seq');
            if ($hasSaveEmoneyLimit) {
                if ($useEmoney > 0 || $downloadSeq) {
                    throw new \Exception("제한 상품이 포함된 경우 쿠폰 및 적립금을 사용할 수 없습니다.");
                }
            }

            // Create Order Header
            $order = new \App\Models\Order();
            $order->order_seq = $this->generateOrderSeq();
            
            $order->order_user_name = $request->order_user_name;
            $order->order_cellphone = $orderCellphone;
            $order->order_phone = $orderPhone; 
            $order->order_email = $request->order_email;
            $order->recipient_user_name = $request->recipient_user_name;
            $order->recipient_cellphone = $recipientCellphone; 
            $order->recipient_phone = $recipientPhone; 
            $order->recipient_zipcode = substr($request->recipient_zipcode, 0, 7); 
            $order->recipient_address_type = $request->recipient_address_type ?? 'zibun';
            $order->recipient_address = $request->recipient_address;
            $order->recipient_address_street = $request->recipient_address_street;
            $order->recipient_address_detail = $request->recipient_address_detail;
            $order->memo = $request->memo;

            $baseShipping = config('shop.shipping.base_cost', 2500);
            $freeShippingThreshold = config('shop.shipping.free_threshold', 150000);
            $packagingCost = config('shop.shipping.packaging_cost', 300);

            $shipping = 0;
            $standardItemsTotal = 0;
            foreach ($cartItems as $cItem) {
                 $goods = $cItem->goods;
                 $option = $cItem->options->first();
                 $ea = $option->ea ?? 1;
                 
                 $matchedOption = null;
                 if ($goods && $goods->option) {
                     $matchedOption = $goods->option->first(function($o) use ($option) {
                          return (string)$o->option1 == (string)$option->option1 &&
                                 (string)$o->option2 == (string)$option->option2 &&
                                 (string)$o->option3 == (string)$option->option3 &&
                                 (string)$o->option4 == (string)$option->option4 &&
                                 (string)$o->option5 == (string)$option->option5;
                     });
                 }
                 $calcOption = $matchedOption ?? $goods->option->first();
                 $pricingInfo = $this->pricingService->calculatePrice($goods, $calcOption, $ea);
                 
                 if (($option->shipping_method ?? '') !== 'postpaid') {
                     $standardItemsTotal += ($pricingInfo['unit_price'] * $ea);
                 }
            }

            if ($standardItemsTotal > 0 && $standardItemsTotal < $freeShippingThreshold) {
                $shipping = $baseShipping;
            }

            $recipientAddress = $request->recipient_address;
            $recipientAddressStreet = $request->recipient_address_street;
            $extraShippingCost = $this->getExtraShippingCost($recipientAddress, $recipientAddressStreet);
            $shipping += $extraShippingCost;

            $tax = $totalVat;

            // [NEW] 3% 카드 수수료 할증 서버 계산
            $cardVat = 0;
            if ($isBbbType && $request->payment === 'card') {
                $cardVat = floor(($totalPrice + $tax) * 0.03);
            }

            $finalSettlePrice = $totalPrice + $shipping + $tax + $packagingCost + $cardVat;

            $order->settleprice = $finalSettlePrice;
            $order->original_settleprice = $finalSettlePrice;
            $order->payment = $request->payment;
            $order->regist_date = now();
            $order->session_id = Session::getId();

            // Point/Emoney Usage
            $useEmoney = $request->input('use_emoney', 0);
            $useCash = $request->input('use_cash', 0);

            if ($useEmoney > 0) {
                if (!$user) {
                    throw new \Exception("회원만 적립금을 사용할 수 있습니다.");
                }
                
                $reserves = DB::table('fm_config')->where('groupcd', 'reserve')->get()->pluck('value', 'codecd')->toArray();
                $minEmoney = (int) ($reserves['min_emoney'] ?? 100);          
                $useLimit = (int) ($reserves['emoney_use_limit'] ?? 100);     
                $maxPolicy = $reserves['max_emoney_policy'] ?? 'unlimit';
                
                $memberEmoney = (int) $user->emoney;
                
                if ($memberEmoney < $useLimit) {
                    throw new \Exception(number_format($useLimit) . "원 이상 적립하여야 합니다.");
                }
                if ($useEmoney < $minEmoney) {
                    throw new \Exception("적립금은 최소 " . number_format($minEmoney) . "원부터 사용가능 합니다.");
                }
                if ($user->emoney < $useEmoney) {
                    throw new \Exception("보유 적립금이 부족합니다.");
                }
                
                if ($maxPolicy === 'percent_limit' && isset($reserves['max_emoney_percent'])) {
                    $maxEmoney = (int) ($finalSettlePrice * (int)$reserves['max_emoney_percent'] / 100);
                } else if ($maxPolicy === 'price_limit' && isset($reserves['max_emoney'])) {
                    $maxEmoney = (int) $reserves['max_emoney'];
                } else {
                    $maxEmoney = $finalSettlePrice;
                }
                
                if ($maxEmoney > $finalSettlePrice) {
                    $maxEmoney = $finalSettlePrice;
                }
                
                if ($useEmoney > $maxEmoney) {
                    if ($maxPolicy === 'unlimit') {
                        throw new \Exception("결제 금액보다 많은 적립금을 사용할 수 없습니다.");
                    } else {
                        throw new \Exception("사용 가능한 최대 적립금(" . number_format($maxEmoney) . "원)을 초과하였습니다.");
                    }
                }
                
                $finalSettlePrice -= $useEmoney;
                $user->decrement('emoney', $useEmoney);
                $order->emoney = $useEmoney; 
                
                DB::table('fm_emoney')->insert([
                    'member_seq' => $user->member_seq,
                    'type' => 'order',
                    'gb' => 'minus',
                    'emoney' => $useEmoney,
                    'ordno' => $order->order_seq,
                    'memo' => "[차감]주문 ({$order->order_seq})에 의한 적립금 차감",
                    'regist_date' => now(),
                ]);
            } else {
                 $order->emoney = 0;
            }

            if ($useCash > 0) {
                 if ($user->cash < $useCash) {
                    throw new \Exception("보유 예치금이 부족합니다.");
                }
                 if ($useCash > $finalSettlePrice) {
                    throw new \Exception("결제 금액보다 많은 예치금을 사용할 수 없습니다.");
                }
                $finalSettlePrice -= $useCash;
                $user->decrement('cash', $useCash);
                $order->cash = $useCash; 
                
                DB::table('fm_cash')->insert([
                    'member_seq' => $user->member_seq,
                    'type' => 'order',
                    'gb' => 'minus',
                    'cash' => $useCash,
                    'ordno' => $order->order_seq,
                    'memo' => "[차감]주문 ({$order->order_seq})에 의한 예치금 차감",
                    'regist_date' => now(),
                ]);
            } else {
                $order->cash = 0;
            }

            // Coupon Usage
            $downloadSeq = $request->input('download_seq');
            $couponDiscount = 0;
            if ($downloadSeq) {
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

                $coupon = DB::table('fm_coupon')->where('coupon_seq', $download->coupon_seq)->first();
                
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
                    $couponDiscount = $finalSettlePrice; 
                }
                
                $finalSettlePrice -= $couponDiscount;

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

            // 해외배송 영문 주소 저장 처리
            if ($request->input('international') === 'international' || $request->filled('international_address')) {
                $order->international = 'international';
                $order->international_address = $request->input('international_address');
                $order->international_town_city = $request->input('international_town_city');
                $order->international_county = $request->input('international_county');
                $order->international_postcode = $request->input('international_postcode');
                $order->international_country = $request->input('international_country');
            } else {
                $order->international = 'domestic';
                $order->international_address = '';
                $order->international_town_city = '';
                $order->international_county = '';
                $order->international_postcode = '';
                $order->international_country = '';
            }

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

                // 환불 계좌 log 컬럼 저장
                if ($request->filled('refund_bank') && $request->filled('refund_acount')) {
                    $refundName = $request->input('refund_name', $request->order_user_name);
                    $order->log = "[{$request->refund_bank}] {$request->refund_acount} - {$refundName}";
                }
            } else {
                $order->step = \App\Models\Order::STEP_ORDER_RECEIVED;
                $order->deposit_yn = 'n'; 
                $order->bundle_yn = 'n';
            }

            if ($user) {
                $order->member_seq = $user->member_seq;
            } else {
                $order->member_seq = 0; 
            }

            // 기본 배송지 저장 처리
            if (Auth::check() && $request->input('save_delivery_address') == 1) {
                DB::table('fm_delivery_address')
                    ->where('member_seq', $user->member_seq)
                    ->where('default', 'Y')
                    ->update(['default' => 'N']);
                    
                DB::table('fm_delivery_address')->insert([
                    'member_seq' => $user->member_seq,
                    'recipient_user_name' => $request->recipient_user_name,
                    'recipient_phone' => $recipientPhone,
                    'recipient_cellphone' => $recipientCellphone,
                    'recipient_zipcode' => $request->recipient_zipcode,
                    'recipient_address_type' => $request->recipient_address_type ?? 'zibun',
                    'recipient_address' => $request->recipient_address,
                    'recipient_address_street' => $request->recipient_address_street,
                    'recipient_address_detail' => $request->recipient_address_detail,
                    'default' => 'Y',
                    'address_group' => '기본',
                    'regist_date' => now(),
                ]);
            }

            try {
                $order->typereceipt = $request->input('typereceipt', 0);
                $order->save();

                // Phase 1: Save Tax Invoice / Cash Receipt Request to fm_sales
                if ($order->typereceipt == 1) { 
                    DB::table('fm_sales')->insert([
                        'typereceipt' => 1,
                        'type' => '2', 
                        'tstep' => 1, 
                        'order_seq' => $order->order_seq,
                        'member_seq' => $order->member_seq ?? 0,
                        'price' => $order->settleprice, 
                        'supply' => round($order->settleprice / 1.1),
                        'surtax' => $order->settleprice - round($order->settleprice / 1.1),
                        'co_name' => $request->input('co_name', ''),
                        'busi_no' => str_replace('-', '', $request->input('busi_no', '')),
                        'co_ceo' => $request->input('co_ceo', ''),
                        'co_status' => $request->input('co_status', ''),
                        'co_type' => $request->input('co_type', ''),
                        
                        // 주소 필드 매핑
                        'zipcode' => $request->input('co_new_zipcode', ''),
                        'address_type' => $request->input('co_address_type', 'zibun'),
                        'address' => $request->input('co_address', ''),
                        'address_street' => $request->input('co_address_street', ''),
                        'address_detail' => $request->input('co_address_detail', ''),
                        
                        // 담당자 정보 매핑
                        'person' => $request->input('person', ''),
                        'phone' => $request->input('phone', ''),
                        'email' => $request->input('email', ''),
                        
                        'regdate' => now(),
                    ]);
                } elseif ($order->typereceipt == 2) { 
                    $cuse = $request->input('cuse', 0);
                    $cnoArr = $request->input('creceipt_number');
                    $cno = '';
                    if (is_array($cnoArr)) {
                        $cno = ($cuse == 0) ? ($cnoArr[0] ?? '') : ($cnoArr[1] ?? '');
                    } else {
                        $cno = $cnoArr;
                    }
                    $cno = str_replace('-', '', $cno);

                    DB::table('fm_sales')->insert([
                        'typereceipt' => 2,
                        'type' => '0', 
                        'tstep' => 1, 
                        'order_seq' => $order->order_seq,
                        'member_seq' => $order->member_seq ?? 0,
                        'price' => $order->settleprice,
                        'supply' => round($order->settleprice / 1.1),
                        'surtax' => $order->settleprice - round($order->settleprice / 1.1),
                        'cuse' => $cuse, 
                        'creceipt_number' => $cno,
                        'person' => $request->input('order_user_name', ''),
                        'email' => $request->input('order_email', ''),
                        'regdate' => now(),
                    ]);
                }
        
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

                 // Price Logic Again using PricingService
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
                }
                
                $calcOption = $matchedOption ?? $goods->option->first();
                $pricingInfo = $this->pricingService->calculatePrice($goods, $calcOption, $ea);
                $price = $pricingInfo['unit_price'];

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
                
                // Check if Postpaid
                if (($option->shipping_method ?? '') === 'postpaid') {
                    $itemOption->title1 = ($option->title1 ?? '옵션') . ' [착불]';
                    $orderItem->goods_shipping_cost = 0; // Ensure 0
                    $orderItem->save(); // Save update
                } else {
                    $itemOption->title1 = $option->title1 ?? '옵션';
                }
                
                $itemOption->option1 = $option->option1 ?? '';
                $itemOption->option2 = $option->option2 ?? '';
                $itemOption->option2 = $option->option2 ?? '';
                $itemOption->save();

                // Save Order Item Inputs (from Cart Inputs)
                if ($cItem->inputs && $cItem->inputs->count() > 0) {
                    foreach ($cItem->inputs as $cInput) {
                        $orderInput = new \App\Models\OrderItemInput();
                        $orderInput->order_seq = $order->order_seq;
                        $orderInput->item_seq = $orderItem->item_seq;
                        $orderInput->item_option_seq = $itemOption->item_option_seq; // Assuming relation exists or 0
                        $orderInput->type = $cInput->type;
                        $orderInput->title = $cInput->input_title;
                        $orderInput->value = $cInput->input_value;
                        $orderInput->save();
                    }
                }

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

                // --- Set Product Stock Deduction ---
                // If this is a Set Product, deduct stock of components
                $this->goodsSetService->deductStockForSet($order->order_seq, $goods->goods_seq, $orderItem->item_seq);


            }

            // Delete from Cart (Deferred if PG payment)
            if ($request->payment == 'bank') {
                Cart::currentUser()->whereIn('cart_seq', $cart_seqs)->delete();
                \App\Models\CartOption::whereIn('cart_seq', $cart_seqs)->delete();
                \App\Models\CartInput::whereIn('cart_seq', $cart_seqs)->delete();
                
                DB::commit();
                return redirect()->route('order.complete', ['id' => $order->order_seq]);
            } else {
                // Save to Cache for deletion upon successful payment callback (Handles S2S missing session)
                \Illuminate\Support\Facades\Cache::put("order_cart_seqs_{$order->order_seq}", $cart_seqs, now()->addHours(2));
                
                DB::commit();
                return redirect()->route('payment.request', ['order_seq' => $order->order_seq]);
            }

        } catch (\Exception $e) {
            $msg = $e->getMessage();
            Log::error('Order Store Failed', ['error' => $msg, 'trace' => $e->getTraceAsString()]);

            try {
                DB::rollBack();
            } catch (\Exception $rollbackEx) {
                Log::error('DB Rollback Failed', ['error' => $rollbackEx->getMessage()]);
            }



            // return back()->withErrors(['msg' => '주문 처리 중 오류 발생: ' . $msg]);
            echo "<script>alert('주문 처리 중 오류가 발생했습니다: " . addslashes($msg) . "');history.back();</script>";
            return;
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

    private function getExtraShippingCost($address, $addressStreet)
    {
        if (!$address && !$addressStreet) {
            return 0;
        }

        $row = DB::table('fm_provider_shipping')->where('provider_seq', 1)->first();
        if (!$row || !$row->add_delivery_cost) {
            return 0;
        }

        $addressWords = array_filter(explode(" ", trim($address)));
        $addressStreetWords = array_filter(explode(" ", trim($addressStreet)));

        $maxCost = 0;
        $rules = explode("|", $row->add_delivery_cost);

        foreach ($rules as $rule) {
            $tmps = explode(":", $rule);
            $tmpCount = count($tmps);
            if ($tmpCount === 3) {
                $jibunArea = trim($tmps[0]);
                $streetArea = trim($tmps[1]);
                $cost = (int)trim($tmps[2]);

                $jibunWords = array_filter(explode(" ", $jibunArea));
                $isJibunMatch = !empty($jibunWords) && (count(array_intersect($jibunWords, $addressWords)) === count($jibunWords));

                $streetWords = array_filter(explode(" ", $streetArea));
                $isStreetMatch = !empty($streetWords) && (count(array_intersect($streetWords, $addressStreetWords)) === count($streetWords));

                if ($isJibunMatch || $isStreetMatch) {
                    if ($cost > $maxCost) {
                        $maxCost = $cost;
                    }
                }
            } else if ($tmpCount === 2) {
                $area = trim($tmps[0]);
                $cost = (int)trim($tmps[1]);

                $areaWords = array_filter(explode(" ", $area));
                $isJibunMatch = !empty($areaWords) && (count(array_intersect($areaWords, $addressWords)) === count($areaWords));
                $isStreetMatch = !empty($areaWords) && (count(array_intersect($areaWords, $addressStreetWords)) === count($areaWords));

                if ($isJibunMatch || $isStreetMatch) {
                    if ($cost > $maxCost) {
                        $maxCost = $cost;
                    }
                }
            }
        }

        return $maxCost;
    }
}
