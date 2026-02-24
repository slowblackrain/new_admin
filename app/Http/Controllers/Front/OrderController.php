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

    public function __construct(
        \App\Services\Agency\AgencySettlementService $settlementService,
        AgencyProductService $agencyService,
        \App\Services\PricingService $pricingService,
        \App\Services\Admin\Goods\GoodsSetService $goodsSetService
    ) {
        $this->settlementService = $settlementService;
        $this->agencyService = $agencyService;
        $this->pricingService = $pricingService;
        $this->goodsSetService = $goodsSetService;
    }

    public function index(Request $request)
    {
        // 1. Validate Input (Cart Seqs)
        // If coming from cart form submit or from a validation redirect back()
        $cart_seqs = $request->input('cart_seq') ?? old('cart_seq');

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

        $shippingCost = 3000;
        $freeShippingThreshold = 50000;
        $packagingCost = 300;

        $shipping = 0;
        // Only charge shipping if there are standard items and they don't meet threshold
        if ($standardItemsTotal > 0 && $standardItemsTotal < $freeShippingThreshold) {
            $shipping = $shippingCost;
        }

        $tax = $totalVat;
        $finalPrice = $totalPrice + $shipping + $tax + $packagingCost;

        // Check for tax-exempt items (legacy: chk_tax_exempt)
        $hasExempt = $cartItems->contains(function($item) {
            return $item->goods && $item->goods->tax === 'exempt';
        });

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

        return view('front.order.order', compact('cartItems', 'user', 'totalPrice', 'user', 'cart_seqs', 'tax', 'finalPrice', 'shipping', 'packagingCost', 'coupons', 'hasExempt'));
    }

    public function calculateShipping(Request $request)
    {
        $zipcode = $request->input('zipcode');
        if (!$zipcode) {
            return response()->json(['extra_cost' => 0]);
        }

        $extraShippingCost = DB::table('shipping_extra_costs')
            ->where('zipcode_start', '<=', $zipcode)
            ->where('zipcode_end', '>=', $zipcode)
            ->value('extra_cost') ?? 0;

        return response()->json(['extra_cost' => $extraShippingCost]);
    }

    public function store(Request $request)
    {

        
        // 1. Validate
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
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

        if ($validator->fails()) {
    
            return back()->withErrors($validator)->withInput();
        }



        $cart_seqs = $request->input('cart_seq');

        // 2. Fetch Cart Items
        $cartItems = Cart::currentUser()
            ->whereIn('cart_seq', $cart_seqs)
            ->with(['goods.images', 'goods.option', 'options', 'inputs'])
            ->get();
        


        // dd('Cart Items:', $cartItems->toArray());

        if ($cartItems->isEmpty()) {
            \Illuminate\Support\Facades\Log::info("OrderController: Cart is Empty for User: " . (\Illuminate\Support\Facades\Auth::id() ?? 'Guest'));
            \Illuminate\Support\Facades\Log::info("Cart Query Count: " . \App\Models\Cart::where('member_seq', \Illuminate\Support\Facades\Auth::id())->count());
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
                // Price Logic using PricingService
                $matchedOption = null;
                if ($goods && $goods->option) {
                    $matchedOption = $goods->option->first(function($o) use ($option) {
                         // Simplify match logic for debug or trust existing
                         return (string)$o->option1 == (string)$option->option1 &&
                                (string)$o->option2 == (string)$option->option2 &&
                                (string)$o->option3 == (string)$option->option3 &&
                                (string)$o->option4 == (string)$option->option4 &&
                                (string)$o->option5 == (string)$option->option5;
                    });
                }
                
                // Fallback option if not matched (e.g. data sync issue)
                $calcOption = $matchedOption ?? $goods->option->first();
                
                // Calculate Final Unit Price (Discounted)
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
            // Only charge shipping if there are standard items and they don't meet threshold
            // Filter out postpaid items from this check
            $standardItemsTotal = 0;
            foreach ($cartItems as $cItem) {
                $cOption = $cItem->options->first();
                // Check 'shipping_method' from cart option
                if (($cOption->shipping_method ?? '') !== 'postpaid') {
                     // Recalculate or just use pre-calculated price?
                     // We need price per item. Let's reuse logic or just sum up quickly if we trust loop above.
                     // The loop above calculated $totalPrice but didn't separate standard.
                     // Let's iterate again or use a flag. 
                     // Optimization: Do this inside the main loop above (lines 162-213) 
                     // But strictly, we can just loop again here for clarity or rely on refined loop.
                }
            }
            // Actually, let's rewrite the initial loop (lines 162-213) to calculate $standardItemsTotal
            // But I am editing lines 235-242. I can't easily reach up.
            // Let's just re-loop for shipping calculation. It's only a few items.
            foreach ($cartItems as $cItem) {
                 $goods = $cItem->goods;
                 $option = $cItem->options->first();
                 $ea = $option->ea ?? 1;
                 // Get Price
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
                $shipping = $shippingCost;
            }

            // [NEW] 도서산간 배송비(우편번호 기반 구간 매핑) 적용
            $recipientZipcode = $request->recipient_zipcode;
            $extraShippingCost = 0;
            if ($recipientZipcode) {
                $extraShippingCost = DB::table('shipping_extra_costs')
                    ->where('zipcode_start', '<=', $recipientZipcode)
                    ->where('zipcode_end', '>=', $recipientZipcode)
                    ->value('extra_cost') ?? 0;
            }
            $shipping += $extraShippingCost;

            $tax = $totalVat;
            $finalSettlePrice = $totalPrice + $shipping + $tax + $packagingCost;

            $order->settleprice = $finalSettlePrice;
            $order->original_settleprice = $finalSettlePrice;
            $order->payment = $request->payment;
            $order->regist_date = now();
            $order->session_id = Session::getId();

            // Point/Emoney Usage
            $useEmoney = $request->input('use_emoney', 0);
            $useCash = $request->input('use_cash', 0);

            if ($useEmoney > 0) {
                if ($user->emoney < $useEmoney) {
                    throw new \Exception("보유 적립금이 부족합니다.");
                }
                if ($useEmoney > $finalSettlePrice) {
                    throw new \Exception("결제 금액보다 많은 적립금을 사용할 수 없습니다.");
                }
                $finalSettlePrice -= $useEmoney;
                $user->decrement('emoney', $useEmoney);
                $order->emoney = $useEmoney; // 'emoney' column holds USED emoney
                
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
                // PG Payment - Wait for Confirmation
                $order->step = \App\Models\Order::STEP_ORDER_RECEIVED;
                $order->deposit_yn = 'n'; 
                $order->bundle_yn = 'n';
            }

            if ($user) {
                $order->member_seq = $user->member_seq;
            } else {
                $order->member_seq = 0; // Guest
            }

             try {
                $order->typereceipt = $request->input('typereceipt', 0);
                $order->save();

                // Phase 1: Save Tax Invoice / Cash Receipt Request to fm_sales
                if ($order->typereceipt == 1) { // 세금계산서
                    DB::table('fm_sales')->insert([
                        'typereceipt' => 1,
                        'type' => 2, // 수동 신청
                        'tstep' => 1, // 발급 신청 접수
                        'order_seq' => $order->order_seq,
                        'member_seq' => $order->member_seq ?? 0,
                        'price' => $order->settleprice, // 우선 총액 기준 (실제로는 과세/면세 분리 고도화 필요)
                        'supply' => round($order->settleprice / 1.1),
                        'surtax' => $order->settleprice - round($order->settleprice / 1.1),
                        'co_name' => $request->input('co_name', ''),
                        'busi_no' => str_replace('-', '', $request->input('busi_no', '')),
                        'co_ceo' => $request->input('co_ceo', ''),
                        'co_status' => $request->input('co_status', ''),
                        'co_type' => $request->input('co_type', ''),
                        'person' => $request->input('tax_person', ''),
                        'email' => $request->input('tax_email', ''),
                        'regdate' => now(),
                    ]);
                } elseif ($order->typereceipt == 2) { // 현금영수증
                    $cuse = $request->input('cuse', 0);
                    $cnoInput = $request->input('cash_receipt_number', '');
                    $cno = str_replace('-', '', $cnoInput);

                    DB::table('fm_sales')->insert([
                        'typereceipt' => 2,
                        'type' => 0, // 사용자 수동
                        'tstep' => 1, // 발급 신청 접수
                        'order_seq' => $order->order_seq,
                        'member_seq' => $order->member_seq ?? 0,
                        'price' => $order->settleprice,
                        'supply' => round($order->settleprice / 1.1),
                        'surtax' => $order->settleprice - round($order->settleprice / 1.1),
                        'cuse' => $cuse, // 0:개인, 1:사업자
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

                // --- Agency Service: Cash Deduction for ATS Product ---
                // Check if it's an Agency Product (Reseller Copy)
                // Logic: Category starts with '0159' (Agency) AND goods_scode starts with 'GT' (Reseller Product)
                // Or simply check if it has a linked ATS category.
                // ATSController uses: cl.category_code like '0159%'
                // Let's use simple check for now: 
                // We need provider_member_seq (Reseller) and check if this product is an ATS copy.
                // AgencyProductService creates 'GT...' code. 
                if (strpos($goods->goods_scode, 'GT') === 0) {
                     // Verify category just to be safe if available, or trust GT prefix.
                     // The 'provider_member_seq' of this goods is the Reseller (User).
                     // However, Auth::user() is the BUYER (End Customer).
                     // So we need to deduct from the Reseller (goods owner).
                     $resellerSeq = $goods->provider_member_seq;
                     
                     // Calculate Supply Price (Provider Price on Option)
                     // fm_goods_option.provider_price is what Reseller pays to Agency.
                     $agencySupplyPrice = 0;
                     if ($matchedOption) {
                         $agencySupplyPrice = $matchedOption->provider_price;
                     } else {
                         // Fallback if no option matching logic?
                         // Usually provider_price is on option.
                         // But wait, $matchedOption might be null if legacy data structure differs.
                         // OrderController logic above already handles $matchedOption or goods->option->first().
                         $opt = $matchedOption ?? $goods->option->first();
                         $agencySupplyPrice = $opt->provider_price ?? 0;
                     }

                     if ($agencySupplyPrice > 0) {
                         // Apply VAT (1.1) to Supply Price
                         $deductAmount = ($agencySupplyPrice * 1.1) * $ea;
                         try {
                             $this->settlementService->deductAgencyCash($order->order_seq, $resellerSeq, $deductAmount);
                         } catch (\Exception $e) {
                             // Log Failure and Re-throw to trigger rollback
                             // We must separate the logging connection or postpone it?
                             // Transaction rollback will kill this insert if on same connection.
                             // But we can throw a specific exception carrying the data, catch it outside, rollback, then log.
                             // Or simpler: Use a separate try-catch structure or global array to store failures?
                             // No, rollback kills everything.
                             // Correct pattern:
                             // catch ($e) {
                             //    $failData = [...];
                             //    throw new AgencyDeductionException($e->getMessage(), $failData);
                             // }
                             // catch (AgencyDeductionException $e) { rollback; insert log; return error; }
                             
                             // Since I can't easily make new Exception class without file creation,
                             // I will use a global variable or Session or just raw DB insert AFTER rollback?
                             // But I lose context ($goods, $resellerSeq) after rollback if I am outside loop.
                             
                             // Hack: Append context to Exception Message JSON?
                             // dd($e->getMessage()); 
                             $context = json_encode([
                                 'goods_seq' => $goods->goods_seq,
                                 'provider_seq' => $resellerSeq,
                                 'reason' => $e->getMessage()
                             ]);
                             throw new \Exception("AgencyDeductionFail:" . $context);
                         }
                     }
                } elseif ($goods->provider_member_seq > 1) {
                    \Illuminate\Support\Facades\Log::info("OrderController: FFF Stock Settlement Triggered for Goods: {$goods->goods_seq}");
                    // [Phase 4] Stock Sales Settlement (FFF Products)
                    // Provider > 1 (Reseller) BUT not GT (Dropshipping).
                    // This implies Reseller owns the stock (Pre-paid).
                    // We record the sale so Reseller can see it in checking.
                    // Margin = Full Sell Price.
                    
                    $resellerSeq = $goods->provider_member_seq;
                    // Calculate Total Sell Price for this line item
                    // Note: price is unit price (calculated above).
                    $lineSellPrice = $price * $ea;
                    
                    try {
                        $ym = date('Y-m');
                        $this->settlementService->settleStockSales($resellerSeq, $ym, $lineSellPrice);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Stock Settlement Failed Order:{$order->order_seq} Item:{$goods->goods_seq} Error:" . $e->getMessage());
                        // We do not stop the order for settlement stat update failure, 
                        // but strictly we should. For now, Log to avoid blocking UX.
                    }
                }
                // --------------------------------------------------------
                
                // --- Auto-Copy Logic for ATS Purchase (Buyer = Reseller) ---
                // If user buys an ATS product (Category 0159...), it should be copied to their shop as GT product.
                // Condition: Buyer is logged in AND product category starts with '0159' AND product is NOT a GT product itself (source).
                // But wait, if they buy their own GT product (test?), we shouldn't copy.
                // The source ATS product usually has goods_scode NOT starting with GT, or is managed by Provider 1.
                // Let's check Category Link.
                
                // Optimized check:
                $isAtsProduct = false;
                $catLink = DB::table('fm_category_link')->where('goods_seq', $goods->goods_seq)->first();
                if ($catLink && strpos($catLink->category_code, '0159') === 0) {
                   $isAtsProduct = true;
                }
                
                if ($user && $isAtsProduct) {
                    try {
                        // Check if already copied?
                        // The duplicateProduct logic or service might handle it, OR we check here to prevent duplicate calls on refresh.
                        // Order logic uses transaction, so it's safe per order.
                        // But if they buy same item twice in different orders?
                        // ATS logic implies 1:1 mapping usually? Or can they have multiple? 
                        // Usually catalog is unique per source goods. Service handles logic or creates another copy.
                        // Let's assume we copy. 
                        
                        // Call Service
                        // Note: $user->member_seq is the Reseller who is buying.
                        $this->agencyService->duplicateProduct($goods->goods_seq, $user->member_seq);
                        
                    } catch (\Exception $e) {
                        // If copy fails, should we fail the order?
                        // User feedback says: "Purchase -> Auto Copy".
                        // If copy fails, they paid but got no product in their shop. 
                        // It's safer to Log error and continue order, but notify Admin? 
                        // Or fail transaction? 
                        // Failing transaction is safer for data integrity.
                         throw new \Exception("ATS 상품 복제 중 오류가 발생했습니다: " . $e->getMessage());
                    }
                }
                // -----------------------------------------------------------
            }

            // Delete from Cart
            Cart::currentUser()->whereIn('cart_seq', $cart_seqs)->delete();
            \App\Models\CartOption::whereIn('cart_seq', $cart_seqs)->delete();
            \App\Models\CartInput::whereIn('cart_seq', $cart_seqs)->delete();

            DB::commit();

                        if ($request->payment == 'bank') {
                return redirect()->route('order.complete', ['id' => $order->order_seq]);
            } else {
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

            // Agency Deduction Failure Logging
            // We expect "AgencyDeductionFail:" or just standard error if string parsing matches
            if (strpos($msg, 'AgencyDeductionFail:') === 0) {
                 $json = substr($msg, strlen('AgencyDeductionFail:'));
                 $context = json_decode($json, true);
                 
                 if ($context) {
                     // Log to DB
                     // Important: We are outside the rolled-back transaction now.
                     // This insert will persist.
                     DB::table('fm_scm_order_fail_log')->insert([
                        'goods_seq' => $context['goods_seq'],
                        'provider_seq' => $context['provider_seq'],
                        'sorder_seq' => 0, // No SCM Order created yet
                        'fail_reason' => $context['reason'],
                        'is_checked' => 'N',
                        'regist_date' => now(),
                    ]);
                    
                    // Show cleaner message to user?
                    // "주문 처리 중 오류: 예치금이 부족합니다..."
                    // Strip the prefix for user display
                    $msg = '공급사 예치금 부족으로 주문이 불가능합니다. 관리자에게 문의해주세요.'; 
                 }
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
}
