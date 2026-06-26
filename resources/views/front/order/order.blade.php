@extends('layouts.front')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="/css/order.css?v={{ time() }}">
        <style>
            /* 헤더 아이콘 복원 */
            .icon_sq_n, .icon_sq_w, .icon_sq_p, .icon_sq_s, .icon_sq_c {
                display: inline-block;
                width: 16px;
                height: 16px;
                line-height: 16px;
                text-align: center;
                background: #4a90e2;
                color: #fff;
                font-size: 10px;
                font-weight: bold;
                border-radius: 2px;
                margin-right: 5px;
            }
            /* 필수 배지 복원 (necessa class 호환) */
            .necessa:after {  
                content: ""; 
                background: url(https://dometopia.com/data/skin/beauty/images/asset/necessary.png) no-repeat; 
                width: 27px; 
                height: 18px; 
                vertical-align: middle; 
                margin-left: 7px; 
                display: inline-block; 
            }
            .fleft { float: left; }
            .fright { float: right; }
            .clearbox:after { content: ""; display: block; clear: both; }
            .relative { position: relative; }
            
            /* 레거시 인풋 및 폼 스타일 이식 */
            #order { font-family: 'Malgun Gothic', '맑은고딕'; }
            #order .order-info-wrap h2:before { 
                content: ""; 
                background: url(https://dometopia.com/data/skin/beauty/images/asset/x_pattern.png) repeat-x; 
                display: inline-block; 
                width: 100%; 
                height: 10px; 
                padding-top: 40px; 
            }
            #order .order-info-wrap h2 { 
                font-size: 16px; 
                font-weight: bold; 
                color: #222; 
                border-bottom: 3px solid #222; 
                padding-bottom: 12px; 
                line-height: 1; 
                margin-top: 60px; 
                margin-bottom: 0;
            }
            #order .order-info-wrap h2 strong {  
                font-size: 14px; 
                font-weight: 300; 
                color: #666; 
                margin-left: 10px;
            }
            .order-info input[type='text'], .order-info input[type='email'], .order-info input[type='number'] { 
                border: 1px solid #e9ecef; 
                height: 50px; 
                padding: 0 1em; 
                font-size: 14px; 
                font-weight: 500; 
                color: #333; 
                outline: none; 
                box-sizing: border-box;
            }
            textarea { 
                border: 1px solid #e9ecef; 
                height: 50px; 
                padding: 1em; 
                font-size: 14px; 
                font-weight: 500; 
                color: #333; 
                outline: none; 
                box-sizing: border-box;
            }
            button.blue_line { 
                border: 1px solid #3ba0ff; 
                color: #3ba0ff; 
                background: #fff;
                height: 50px; 
                width: 111px; 
                line-height: 48px; 
                font-weight: bold;
                cursor: pointer;
                border-radius: 2px;
            }
            button.blue_line:hover {
                background: #f4f9ff;
            }
            .order-info .order-row { 
                display: flex; 
                font-size: 14px; 
                margin-bottom: 10px; 
                align-items: center;
            }
            .order-info .order-input-title { 
                display: flex; 
                align-items: center; 
                width: 138px; 
                min-height: 50px; 
                font-size: 14px; 
                font-weight: bold; 
                color: #222;
            }
            .order-info .order-input-wrap { 
                display: flex; 
                align-items: center; 
                flex: 1; 
                height: 50px; 
            }
            .order-info { 
                border-bottom: 1px solid #e9ecef; 
                overflow: hidden; 
                background: #fff;
            }
            .order-info .addr-load {  
                height: 88px; 
                border-bottom: 1px solid #e9ecef; 
                padding: 25px 29px; 
                box-sizing: border-box;
            }
            .order-info .addr-load span, .order-info .addr-load label { 
                display: inline-block; 
                width: 141px; 
                height: 37px; 
                background: #f7f8f9; 
                border: 1px solid #e9ecef; 
                font-size: 13px; 
                font-weight: bold; 
                border-radius: 2px; 
                line-height: 35px; 
                text-align: center; 
                cursor: pointer; 
                vertical-align: top;
                box-sizing: border-box;
            }
            .order-info .addr-load label input { display: none; }
            .order-info .order-select-wrap { 
                padding: 30px 40px 0 17px; 
                box-sizing: border-box;
            }
            .order-select-wrap div { margin-left: 12px; margin-bottom: 25px; }
            .order-select-wrap select { 
                height: 35px; 
                width: 100%; 
                margin-top: 10px; 
                border: 1px solid #e9ecef; 
                padding-left: 8px; 
            }
            .order-select-wrap input { display: inline-block; margin: 0; vertical-align: middle; }
            .order-select-wrap label { 
                color: #888; 
                font-size: 13px; 
                line-height: 1; 
                margin-left: 5px; 
                vertical-align: middle;
                cursor: pointer;
            }
            .order-select-wrap input[type="radio"]:checked + label { 
                font-weight: bold; 
                color: #000; 
            }
            
            /* 주의사항 스타일 복원 */
            .doto-order-info-alert { 
                background: #f7f8f9; 
                padding: 20px 25px; 
                position: relative; 
                width: 622px; 
                box-sizing: border-box;
                border: 1px solid #e9ecef;
                margin: 0 !important;
            }
            .doto-order-info-alert h3 { 
                width: 100%; 
                padding-bottom: 10px; 
                position: absolute; 
                top: 0; 
                height: 45px; 
                line-height: 42px; 
                left: 0; 
                padding-left: 25px; 
                background: #e4eff9; 
                font-size: 14px; 
                font-weight: bold; 
                margin: 0;
                box-sizing: border-box;
            }
            .doto-order-info-alert h3:after { 
                content: ""; 
                width: 100%; 
                background: url(https://dometopia.com/data/skin/beauty/images/asset/alert_pattern.png); 
                position: absolute; 
                height: 2px; 
                bottom: 0; 
                left: 0; 
            }
            .doto-order-info-alert h3:before { 
                content: ""; 
                width: 22px; 
                height: 22px; 
                background: url(https://dometopia.com/data/skin/beauty/images/asset/exclamation_mark.png) no-repeat; 
                display: inline-block; 
                vertical-align: middle; 
                margin-right: 5px; 
            }
            .doto-order-info-alert ul { margin-top: 45px; padding-left: 0; list-style: none; }
            .doto-order-info-alert ul li { line-height: 1.3; font-size: 13px; color: #666; }
            .doto-order-info-alert ul li + li { margin-top: 10px; }
            
            .hide { display: none !important; }
            .ml5 { margin-left: 5px; }
            .ml10 { margin-left: 10px; }
            .mr5 { margin-right: 5px; }
            .wx480 { width: 480px; }

            /* 최종 결제 금액 확인 우측 영역 및 결제 레이아웃 CSS */
            .order-bottom-container {
                width: 1200px;
                margin: 0 auto;
                margin-top: 40px;
                padding: 0;
                overflow: hidden;
                background: transparent;
            }
            .order-bottom-container .fleft {
                float: left;
                width: 738px;
            }
            .order-bottom-container .fright {
                float: right;
                width: 441px;
                margin-left: 6px;
            }



            /* 배송비 정책 동의 */
            .shipping-agree-wrap {
                margin-top: 20px;
            }
            .shipping-agree-wrap .shipping-agree {
                background: #f7f8f9;
                border: 1px solid #cfd5da;
                border-radius: 30px;
                height: 56px;
                line-height: 54px;
                text-align: center;
                box-shadow: inset 3px 3px 10px rgba(0,0,0,.03);
                margin-bottom: 20px;
            }
            .shipping-agree-wrap .shipping-agree label {
                font-size: 16px;
                font-weight: bold;
                color: #222;
                cursor: pointer;
            }
            .shipping-agree-wrap .shipping-agree input {
                width: 18px;
                height: 18px;
                vertical-align: middle;
                margin-right: 8px;
            }
            .shipping-agree-wrap ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }
            .shipping-agree-wrap ul li {
                font-size: 12px;
                color: #666;
                line-height: 1.6;
                margin-bottom: 8px;
            }

            /* 결제 버튼 */
            .btn-settle-submit {
                display: block;
                width: 100%;
                height: 60px;
                line-height: 60px;
                background: #f44336;
                color: #fff;
                font-size: 22px;
                font-weight: bold;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 20px;
                text-align: center;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .btn-settle-submit:hover {
                background: #d32f2f;
            }

            .order-pay-box {  
                background: #FFF; 
                border: 1px solid #cfd5da; 
                padding: 30px;
                margin-bottom: 20px;
                font-family: 'Malgun Gothic', '맑은고딕', sans-serif;
            }
            .order-pay-box h4 { 
                font-size: 18px; 
                font-weight: bold;
                border-bottom: 2px solid #222; 
                padding-bottom: 15px; 
                line-height: 1; 
                margin-top: 0;
                margin-bottom: 20px;
                color: #222;
            }
            .order-pay-box .order-row {
                display: flex;
                font-size: 14px;
                margin-bottom: 15px;
                align-items: center;
            }
            .order-pay-box .order-input-title {
                width: 120px;
                font-weight: bold;
                color: #222;
            }
            .order-pay-box .order-input-wrap {
                flex: 1;
                display: flex;
                align-items: center;
            }
            .order-pay-box input[type="text"], .order-pay-box select {
                border: 1px solid #e9ecef;
                height: 40px;
                padding: 0 10px;
                box-sizing: border-box;
                font-size: 13px;
            }
            .order-pay-box .reserve-info {
                list-style: none;
                padding-left: 120px;
                margin: 10px 0 0 0;
            }
            .order-pay-box .reserve-info li {
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
            }
            .order-pay-box .reserve-info li strong {
                color: #f44336;
            }
        </style>
    @endpush
    <div class="order_header_v2">
        <div class="order_header_inner clearbox">
            <!-- Left: Title with Icon -->
            <div class="title_area">
                <h2>주문/결제<i><img src="https://dometopia.com/data/skin/beauty/images/icon/order_card.png" alt="Card Icon"></i></h2>
            </div>
            
            <!-- Right: Step Indicator -->
            <div class="step_area">
                <ul>
                    <li><span class="num">1</span> <span class="txt">장바구니</span></li>
                    <li class="on"><span class="num">2</span> <span class="txt">주문/결제</span></li>
                    <li><span class="num">3</span> <span class="txt">주문 완료</span></li>
                </ul>
            </div>
        </div>
    </div>    <!-- Subtitle or other content if needed -->

        @if ($errors->any())
            <div
                style="background: #fee; border: 1px solid #d00; color: #d00; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <ul style="list-style: none; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @inject('pricingService', 'App\Services\PricingService')
        <div class="cart_list_area">
            <h4>주문 리스트 확인</h4>
            <table class="cart_table">
                <colgroup>
                    <col width="*" />
                    <col width="60" />
                    <col width="100" />
                    <col width="100" />
                    <col width="100" />
                    <col width="120" />
                    <col width="100" />
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">주문상품</th>
                        <th scope="col"><span class="icon_sq_n">N</span> 수량</th>
                        <th scope="col"><span class="icon_sq_w">W</span> 단가</th>
                        <th scope="col"><span class="icon_sq_p">%</span> 할인</th>
                        <th scope="col"><span class="icon_sq_w">W</span> 주문금액</th>
                        <th scope="col"><span class="icon_sq_s">S</span> 배송비</th>
                        <th scope="col"><span class="icon_sq_c">C</span> 쿠폰</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cartItems as $item)
                        @php
                            $goods = $item->goods;
                            $option = $item->options->first();
                            
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
                            $ea = $option->ea ?? 1;

                            $pricing = $pricingService->calculatePrice($goods, $calcOption, $ea);
                            $unitPrice = $pricing['unit_price'];
                            $itemPrice = $pricing['total_price'];

                            $addSale = $pricing['domae_price'] - $unitPrice;
                            $saleText = $addSale > 0 ? '-' . number_format($addSale * $ea) . '원' : '-';

                            $mainImage = $goods->images->where('image_type', 'list1')->first();
                            $imagePath = $mainImage ? $mainImage->image : '';
                            if ($imagePath && strpos($imagePath, '/data/goods/') === 0) {
                                $imgSrc = "http://dometopia.com" . $imagePath;
                            } elseif ($imagePath && strpos($imagePath, 'goods_img') !== false) {
                                    $suffix = substr($imagePath, strpos($imagePath, 'goods_img') + 9);
                                    $imgSrc = "https://dmtusr.vipweb.kr/goods_img" . $suffix;
                            } elseif ($imagePath) {
                                if (Str::startsWith($imagePath, 'http')) {
                                    $imgSrc = $imagePath;
                                } else {
                                    $imgSrc = "http://dometopia.com/data/goods/" . $imagePath;
                                }
                            } else {
                                $imgSrc = '/images/no_image.gif';
                            }
                        @endphp
                        <tr>
                            <td class="info_cell" style="text-align: left; padding: 10px;">
                                <div style="float: left; margin-right: 15px;">
                                    <img src="{{ $imgSrc }}" alt="{{ $goods->goods_name }}" width="60" style="border: 1px solid #ddd;">
                                </div>
                                <div style="float: left; padding-top: 5px;">
                                    <div style="margin-bottom: 5px;">
                                        <a href="/goods/view?goods_seq={{ $goods->goods_seq }}" style="color: #0088ff; font-weight: bold; text-decoration: none;">{{ $goods->goods_scode }}</a>
                                    </div>
                                    <div class="g_name" style="font-weight: bold; color: #333;">{{ $goods->goods_name }}</div>
                                    @if($option->option1 && $option->option1 !== '기본' && $option->option1 !== '')
                                        <div class="g_opt" style="color: #666; font-size: 11px; margin-top: 3px;">옵션: {{ $option->option1 }}</div>
                                    @endif
                                </div>
                                <div style="clear: both;"></div>
                            </td>
                            <td>{{ $ea }}</td>
                            <td>{{ number_format($unitPrice) }}원</td>
                            <td>{{ $saleText }}</td>
                            <td class="price_bold">{{ number_format($itemPrice) }}원</td>
                            <td>
                                @if(($option->shipping_method ?? '') === 'postpaid')
                                    본사<br>착불
                                @else
                                    본사<br>택배(선불)<br>{{ number_format(config('shop.shipping.base_cost', 2500)) }}
                                    <div style="margin-top: 5px;">
                                        <button type="button" class="btn_change_shipping" style="background: #0088ff; color: #fff; border: none; padding: 2px 8px; font-size: 11px; cursor: pointer; border-radius: 2px;">변경</button>
                                    </div>
                                    <div style="color: #888; font-size: 11px; margin-top: 3px;">+ {{ number_format(config('shop.shipping.packaging_cost', 300)) }}원</div>
                                @endif
                            </td>
                            <td>-</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="text-align: right; margin-top: 10px; font-size: 12px; color: #333;">
                기본배송비 : <strong>{{ number_format(config('shop.shipping.base_cost', 2500)) }}원</strong> 
                추가배송비 : <strong>{{ number_format(config('shop.shipping.packaging_cost', 300)) }}원</strong>
            </div>

            <div class="cart_total_area_legacy">
                <div class="total_left">
                    <div class="total_row">
                        <span class="th">총 상품:</span>
                        <span class="td"><strong>{{ count($cartItems) }}</strong></span>
                    </div>
                    <div class="total_row">
                        <span class="th">총 수량:</span>
                        <span class="td"><strong>{{ $cartItems->sum(function($item){ return $item->options->first()->ea ?? 0; }) }}</strong></span>
                    </div>
                </div>
                <div class="total_right">
                    <div class="calc_box">
                        <span class="item">총 상품 금액: <strong>{{ number_format($totalPrice) }}</strong></span>
                        <span class="op plus">+</span>
                        <span class="item">배송비: <strong id="total_shipping_display_text">{{ number_format($shipping + $packagingCost) }}</strong></span>
                        <span class="op minus">-</span>
                        <span class="item">총 할인: <strong id="coupon_discount_display_text">0</strong></span>
                        <span class="op plus">+</span>
                        <span class="item">총 부가세: <strong>{{ number_format($tax) }}</strong></span>
                        <span class="item ml10">예상포인트: 0</span>
                        <span class="op equal">=</span>
                        <span class="item total">총 결제 금액: <strong class="final_price">{{ number_format($finalPrice) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <form name="orderForm" id="orderForm" method="post" action="{{ route('order.store') }}">
                @csrf
                @if(isset($cart_seqs))
                    @foreach($cart_seqs as $seq)
                        <input type="hidden" name="cart_seq[]" value="{{ $seq }}">
                    @endforeach
                @endif
                {{-- 주문서 처리 로직은 다음 Phase에서 구현 --}}

            <div id="order">
                <div class="order-info-wrap relative">
                    <h2>주문자 정보 <strong>회원 정보를 입력하세요.</strong></h2>
                    <div class="order-info clearbox" style="padding: 25px 0; overflow: hidden;">
                        <!-- fleft -->
                        <div class="fleft" style="width: 468px;">
                            <div class="order-row name">
                                <div class="order-input-title necessa">이름</div>
                                <div class="order-input-wrap">
                                    <input type="text" name="order_user_name" value="{{ old('order_user_name', $user->user_name ?? '') }}" required>
                                </div>
                            </div>
                            @php
                                $orderPhoneArr = explode('-', old('order_phone', $user->phone ?? ''));
                                $orderCellphoneArr = explode('-', old('order_cellphone', $user->cellphone ?? ''));
                            @endphp
                            <div class="order-row phone">
                                <div class="order-input-title necessa">주문자 핸드폰</div>
                                <div class="order-input-wrap">
                                    <input type="text" style="width:30%; text-align: center;" name="order_cellphone[]" value="{{ $orderCellphoneArr[0] ?? '' }}" required>
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" style="width:30%; text-align: center;" name="order_cellphone[]" value="{{ $orderCellphoneArr[1] ?? '' }}" required>
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" style="width:30%; text-align: center;" name="order_cellphone[]" value="{{ $orderCellphoneArr[2] ?? '' }}" required>
                                </div>
                            </div>
                            <div class="order-row tel">
                                <div class="order-input-title">주문자 전화번호</div>
                                <div class="order-input-wrap">
                                    <input type="text" style="width:30%; text-align: center;" name="order_phone[]" value="{{ $orderPhoneArr[0] ?? '' }}">
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" style="width:30%; text-align: center;" name="order_phone[]" value="{{ $orderPhoneArr[1] ?? '' }}">
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" style="width:30%; text-align: center;" name="order_phone[]" value="{{ $orderPhoneArr[2] ?? '' }}">
                                </div>
                            </div>
                            <div class="order-row email">
                                <div class="order-input-title necessa">주문자 이메일</div>
                                <div class="order-input-wrap">
                                    <input type="email" name="order_email" value="{{ old('order_email', $user->email ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                        <div class="fright" style="width: 622px;">
                            <div class="doto-order-info-alert" style="height: 230px; margin: 0 !important;">
                                <h3>주의사항</h3>
                                <ul style="margin-top: 55px;">
                                    <li>ㆍ비회원의 주문배송조회를 위한 로그인은 주문번호와 이메일 정보로 확인할 수 있습니다.</li>
                                    <li>ㆍ구매 내역은 이메일과 SMS로 발송됩니다.</li>
                                    <li>ㆍ정확한 이메일과 휴대폰번호를 입력해 주십시오.</li>
                                    <li>ㆍ주문자 정보는 운송장에 표기되지 않으며, 주문 확인용으로만 사용됩니다.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-info-wrap relative">
                    <h2>배송지 입력<strong>배송내역 확인 후 주문하여 주시기 바랍니다.</strong></h2>
                    <div class="order-info clearbox" style="overflow: unset;">
                        <!-- fleft -->
                        <div class="fleft" style="width: 384px;">
                            <div class="addr-load">
                                <label>
                                    주문자 정보 불러오기
                                    <input type="checkbox" id="copy_user_info">
                                </label>
                                @if(auth()->check())
                                <span class="ml10" onclick="openAddressModal()">주소록에서 불러오기</span>
                                @endif
                            </div>
                            @if(auth()->check())
                            <div class="order-select-wrap">
                                <div>
                                    <input type="radio" name="chkQuickAddress" value="often" id="chkQuick_often"><label for="chkQuick_often">자주쓰는 배송지</label>
                                </div>
                                <div>
                                    <input type="radio" name="chkQuickAddress" value="new" id="chkQuick_new" checked><label for="chkQuick_new">새로운 배송지</label>
                                </div>
                                <div>
                                    <input type="radio" name="chkQuickAddress" value="member" id="chkQuick_member"><label for="chkQuick_member">회원정보주소</label>
                                </div>
                                <div>
                                    <input type="radio" name="chkQuickAddress" value="lately" id="chkQuick_lately"><label for="chkQuick_lately">최근 배송지</label>
                                </div>
                                <select name="chkQuickAddressLately" id="chkQuickAddressLately" style="display:none; min-width: 180px;">
                                    <option value="">최근 배송지를 선택하세요</option>
                                </select>
                            </div>
                            @endif
                        </div>

                        <!-- fright -->
                        <div class="fleft" style="border-left: 1px solid #cfd5da; padding: 29px 25px; overflow: unset; width: 730px;">
                            <div class="order-row name wx480">
                                <div class="order-input-title necessa">받는분 성함</div>
                                <div class="order-input-wrap">
                                    <input type="text" name="recipient_user_name" id="recipient_user_name" value="{{ old('recipient_user_name') }}" required>
                                </div>
                            </div>
                            <div class="order-row phone wx480">
                                <div class="order-input-title necessa">받는분 핸드폰</div>
                                <div class="order-input-wrap">
                                    <input type="text" name="recipient_cellphone[]" id="recipient_cellphone_0" value="" style="width:30%; text-align: center;" required>
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" name="recipient_cellphone[]" id="recipient_cellphone_1" value="" style="width:30%; text-align: center;" required>
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" name="recipient_cellphone[]" id="recipient_cellphone_2" value="" style="width:30%; text-align: center;" required>
                                </div>
                            </div>
                            <div class="order-row tel wx480">
                                <div class="order-input-title">받는분 전화 번호</div>
                                <div class="order-input-wrap">
                                    <input type="text" name="recipient_phone[]" id="recipient_phone_0" value="" style="width:30%; text-align: center;">
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" name="recipient_phone[]" id="recipient_phone_1" value="" style="width:30%; text-align: center;">
                                    <span style="margin: 0px 5px;">-</span>
                                    <input type="text" name="recipient_phone[]" id="recipient_phone_2" value="" style="width:30%; text-align: center;">
                                </div>
                            </div>
                            
                            <!-- domestic -->
                            <div class="domestic goods_delivery_info">
                                <div class="order-row address wx480">
                                    <div class="order-input-title necessa">주소입력</div>
                                    <div class="order-input-wrap">
                                        <button type="button" class="blue_line mr5" onclick="openDaumPostcode(); return false;">우편번호찾기</button>
                                        <input type="text" name="recipient_new_zipcode" id="recipient_zipcode" value="{{ old('recipient_new_zipcode') }}" placeholder="우편번호" readonly style="width: 150px;">
                                    </div>
                                </div>
                                <div class="order-row address2" style="width: 722px;">
                                    <div class="order-input-title" style="visibility: hidden">주소상세</div>
                                    <div class="order-input-wrap" style="height: auto; flex-wrap: wrap; gap: 5px;">
                                        <input type="hidden" name="recipient_address_type" id="recipient_address_type" value="{{ old('recipient_address_type') }}">
                                        <input type="text" name="recipient_address_street" id="recipient_address_street" value="{{ old('recipient_address_street') }}" class="hide" style="flex: none; width:342px;" readonly>
                                        <input type="text" name="recipient_address" id="recipient_address" value="{{ old('recipient_address') }}" style="flex: none; width:342px;" readonly>
                                        <input type="text" name="recipient_address_detail" id="recipient_address_detail" value="{{ old('recipient_address_detail') }}" placeholder="나머지 주소" style="width:232px;">
                                        
                                        {{-- Display only --}}
                                        <input type="text" id="recipient_address_display" value="{{ old('recipient_address_type') == 'street' ? old('recipient_address_street') : old('recipient_address') }}" style="width: 342px; display: none;" placeholder="기본주소" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- international -->
                            <div id="international_address_row" class="international goods_delivery_info" style="display:none;">
                                <div class="order-row">
                                    <div class="order-input-title">주소</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="international_address" id="international_address" value="">
                                    </div>
                                </div>
                                <div class="order-row">
                                    <div class="order-input-title">시도</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="international_town_city" id="international_town_city" value="">
                                    </div>
                                </div>
                                <div class="order-row">
                                    <div class="order-input-title">주</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="international_county" id="international_county" value="">
                                    </div>
                                </div>
                                <div class="order-row">
                                    <div class="order-input-title">우편번호</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="international_postcode" id="international_postcode" value="">
                                    </div>
                                </div>
                                <div class="order-row">
                                    <div class="order-input-title">국가</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="international_country" id="international_country" value="">
                                    </div>
                                </div>
                            </div>

                            <!-- memo -->
                            <div class="order-row goods_delivery_info" style="margin-top: 15px;">
                                <div class="order-input-title">배송 메시지</div>
                                <div class="order-input-wrap" style="height: auto;">
                                    <textarea name="memo" cols="70" rows="3" style="width: 100%; height: 80px; border: 1px solid #e9ecef; padding: 10px; box-sizing: border-box; resize: none;" placeholder="배송 메시지를 입력해주세요."></textarea>
                                </div>
                            </div>
                            
                            @if(auth()->check())
                            <div style="margin-left: 138px; margin-top: 5px;">
                                <input type="checkbox" name="save_delivery_address" id="save_delivery_address" value="1" style="height: 18px; margin: 0; vertical-align: middle;">
                                <label for="save_delivery_address" style="color: #3ba0ff; line-height: 1.4; font-size: 13px; margin-left: 5px; cursor: pointer; vertical-align: middle;">기본 배송지로 저장</label>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- order-bottom-container : 2단 분할 결제 정보 레이아웃 -->
                <div class="order-bottom-container">
                    <!-- fleft: 입력 영역 (혜택/결제수단/적립금/매출증빙) -->
                <div class="fleft">
                    <!-- 1. 쿠폰/혜택 사용하기 -->
                    <div class="order-pay-box">
                        <h4>1. 쿠폰/혜택 사용하기</h4>
                        <div class="order-row" id="coupon_use_row" style="margin-bottom: 20px;">
                            <div class="order-input-title">쿠폰 할인 금액</div>
                            <div class="order-input-wrap">
                                <select name="download_seq" id="download_seq" style="min-width: 250px; height: 40px; border: 1px solid #e9ecef; padding-left: 8px;">
                                    <option value="">쿠폰을 선택하세요</option>
                                    @foreach($coupons as $coupon)
                                        <option value="{{ $coupon->download_seq }}" {{ old('download_seq') == $coupon->download_seq ? 'selected' : '' }}
                                            data-type="{{ $coupon->sale_type }}"
                                            data-percent="{{ $coupon->percent_goods_sale }}"
                                            data-max="{{ $coupon->max_percent_goods_sale }}"
                                            data-won="{{ $coupon->won_goods_sale }}">
                                            {{ $coupon->coupon_name }} 
                                            ({{ $coupon->sale_type == 'percent' ? $coupon->percent_goods_sale . '%' : number_format($coupon->won_goods_sale) . '원' }} 할인)
                                        </option>
                                    @endforeach
                                </select>
                                <span id="coupon_discount_display" style="color: #d00; font-weight: bold; margin-left: 10px;"></span>
                            </div>
                        </div>
                        <div class="order-row">
                            <div class="order-input-title">코드 할인</div>
                            <div class="order-input-wrap">
                                <input type="text" name="coupon_code" placeholder="할인 코드 입력" style="width: 200px; height: 40px; border:1px solid #e9ecef; padding-left:10px;">
                                <button type="button" class="blue_line ml5" style="height: 40px; line-height: 38px; width: 80px; cursor:pointer;">코드적용</button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. 결제 수단 선택 -->
                    <div class="order-pay-box payment_type">
                        <h4>2. 결제 수단 선택</h4>
                        <div style="margin-bottom: 20px; font-size: 14px;">
                            <label style="cursor: pointer; margin-right: 25px; font-weight: bold; display: inline-block; vertical-align: middle;">
                                <input type="radio" name="payment" value="bank" {{ old('payment', 'bank') == 'bank' ? 'checked' : '' }} style="width:16px; height:16px; vertical-align:middle; margin-right:5px;"> 무통장입금
                            </label>
                            <label style="cursor: pointer; margin-right: 25px; font-weight: bold; display: inline-block; vertical-align: middle;" id="card_payment_label">
                                <input type="radio" name="payment" value="card" {{ old('payment') == 'card' ? 'checked' : '' }} style="width:16px; height:16px; vertical-align:middle; margin-right:5px;"> 신용카드
                            </label>
                        </div>

                        <!-- 무통장 입금 정보 입력 박스 -->
                        <div class="pay-bank-wrap bank" id="bank_info_row" style="height: auto; border: 2px solid #0088ff; padding: 20px; background: #fff; margin-bottom: 15px;">
                            <h6 style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #0088ff; border-bottom: 1px solid #e9ecef; padding-bottom: 10px; text-align: left; height: auto; line-height: 1;">무통장 입금 정보 입력</h6>
                            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px; font-size: 13px;">
                                <label style="font-weight: bold; width: 70px;">입금자명</label>
                                <input type="text" name="depositor" value="{{ old('depositor') }}" style="width: 150px; height: 35px; border: 1px solid #e9ecef; padding: 0 8px;">
                                <label style="font-weight: bold; width: 70px; margin-left: 15px;">입금은행</label>
                                <select name="bank_account" style="flex: 1; height: 35px; border: 1px solid #e9ecef; padding-left: 8px;">
                                    <option value="국민은행 123-456-7890 도매토피아" {{ old('bank_account') == '국민은행 123-456-7890 도매토피아' ? 'selected' : '' }}>국민은행 123-456-7890 도매토피아</option>
                                    <option value="농협 098-765-4321 도매토피아" {{ old('bank_account') == '농협 098-765-4321 도매토피아' ? 'selected' : '' }}>농협 098-765-4321 도매토피아</option>
                                </select>
                            </div>
                            <div style="background: #0088ff; color: #fff; font-size: 12px; padding: 8px 12px; font-weight: bold; line-height:1.4;">
                                ※선택한 계좌로 입금 부탁드리며, 입금시 입금자명 및 금액이 작성된 내용과 다를시 자동처리가 안되니 주의바랍니다.
                            </div>
                        </div>

                        <!-- 환불시 입금 정보 입력 -->
                        <div class="pay-bank-wrap bank" id="refund_info_row" style="height: auto; border: 1px solid #cfd5da; padding: 20px; background: #fff; display: none;">
                            <h6 style="margin: 0 0 15px 0; font-size: 14px; font-weight: bold; color: #222; border-bottom: 1px solid #e9ecef; padding-bottom: 10px; text-align: left; height: auto; line-height: 1;">환불시 입금 정보 입력(선택사항)</h6>
                            <div style="font-size: 13px;">
                                <div style="margin-bottom: 10px; display: flex; align-items: center;">
                                    <span style="display: inline-block; width: 100px; font-weight: bold;">예금주명</span>
                                    <input type="text" name="refund_name" id="refund_name" value="{{ old('refund_name') }}" style="width: 150px; height: 35px; border: 1px solid #e9ecef; padding: 0 8px;">
                                </div>
                                <div style="margin-bottom: 10px; display: flex; align-items: center;">
                                    <span style="display: inline-block; width: 100px; font-weight: bold;">입금은행</span>
                                    <input type="text" name="refund_bank" id="refund_bank" value="{{ old('refund_bank') }}" style="width: 150px; height: 35px; border: 1px solid #e9ecef; padding: 0 8px;">
                                </div>
                                <div style="margin-bottom: 10px; display: flex; align-items: center;">
                                    <span style="display: inline-block; width: 100px; font-weight: bold;">입금계좌</span>
                                    <input type="text" name="refund_acount" id="refund_acount" value="{{ old('refund_acount') }}" style="width: 250px; height: 35px; border: 1px solid #e9ecef; padding: 0 8px;" placeholder="'-'없이 입력">
                                    <span style="color:#888; font-size:11px; margin-left:10px;">( 하이픈 [ - ] 없이 입력 )</span>
                                </div>
                                <div style="background: #f7f8f9; color: #666; font-size: 11px; padding: 8px 12px; margin-top: 5px; border: 1px solid #e9ecef; line-height:1.4;">
                                    ※ 환불은 작성하신 계좌로 입금 됩니다. 입금자명 및 계좌를 정확히 입력해주세요.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. 적립금 및 캐시 사용 -->
                    <div class="order-pay-box reserve">
                        <h4>3. 적립금 및 캐시 사용</h4>
                        <div class="order-row" id="emoney_use_row">
                            <div class="order-input-title">적립금</div>
                            <div class="order-input-wrap">
                                <input type="number" name="use_emoney_view" id="use_emoney_view" value="{{ old('use_emoney', 0) }}" style="text-align:right; width: 120px; height: 40px; border:1px solid #e9ecef;">
                                <span style="margin-left: 5px; font-weight: bold;">원</span>
                                <input type="hidden" name="use_emoney" id="use_emoney" value="{{ old('use_emoney', 0) }}">
                                
                                <button type="button" class="blue_line ml10" onclick="useEmoneyBtn()" style="height: 40px; line-height: 38px; width: 80px; cursor:pointer;">입력</button>
                                <button type="button" class="blue_line ml5" onclick="useAllEmoneyBtn()" style="height: 40px; line-height: 38px; width: 80px; cursor:pointer;">전액사용</button>
                                <button type="button" class="blue_line ml5" id="emoney_cancel_btn" onclick="cancelEmoneyBtn()" style="height: 40px; line-height: 38px; width: 80px; display:none; cursor:pointer;">초기화</button>
                            </div>
                        </div>
                        <ul class="reserve-info" style="margin-bottom: 20px;">
                            <li>보유한 적립금: <strong>{{ number_format($user->emoney ?? 0) }}</strong>원</li>
                            @if(isset($errReserve) && $errReserve)
                                <li style="color: #d00;">※ {{ $errReserve }}</li>
                            @else
                                <li>※ 적립금은 100원 이상 보유 시, 최소 100원부터 사용 가능합니다.</li>
                            @endif
                        </ul>

                        <div class="order-row" id="cash_use_row">
                            <div class="order-input-title">캐시</div>
                            <div class="order-input-wrap">
                                <input type="number" name="use_cash_view" id="use_cash_view" value="{{ old('use_cash', 0) }}" style="text-align:right; width: 120px; height: 40px; border:1px solid #e9ecef;">
                                <span style="margin-left: 5px; font-weight: bold;">원</span>
                                <input type="hidden" name="use_cash" id="use_cash" value="{{ old('use_cash', 0) }}">
                                
                                <button type="button" class="blue_line ml10" onclick="useCashBtn()" style="height: 40px; line-height: 38px; width: 80px; cursor:pointer;">입력</button>
                                <button type="button" class="blue_line ml5" onclick="useAllCashBtn()" style="height: 40px; line-height: 38px; width: 80px; cursor:pointer;">전액사용</button>
                                <button type="button" class="blue_line ml5" id="cash_cancel_btn" onclick="cancelCashBtn()" style="height: 40px; line-height: 38px; width: 80px; display:none; cursor:pointer;">초기화</button>
                            </div>
                        </div>
                        <ul class="reserve-info">
                            <li>보유한 캐시: <strong>{{ number_format($user->cash ?? 0) }}</strong>원</li>
                        </ul>
                    </div>

                    <!-- 4. 매출 증빙 신청 -->
                    <div class="order-pay-box receipt" id="receipt_request_table">
                        <h4>4. 매출 증빙 신청</h4>
                        <div class="order-row" id="typereceipt_selection_row">
                            <div class="order-input-title">신청 선택</div>
                            <div class="order-input-wrap">
                                <label style="cursor: pointer; margin-right: 15px; display: inline-block; vertical-align: middle;"><input type="radio" name="typereceipt" value="0" {{ old('typereceipt', '0') == '0' ? 'checked' : '' }} onclick="toggleReceipt(0)" style="width:15px; height:15px; vertical-align:middle; margin-right:3px;"> 신청안함</label>
                                <label style="cursor: pointer; margin-right: 15px; display: inline-block; vertical-align: middle;" id="tax_invoice_label"><input type="radio" name="typereceipt" value="1" {{ old('typereceipt') == '1' ? 'checked' : '' }} onclick="toggleReceipt(1)" style="width:15px; height:15px; vertical-align:middle; margin-right:3px;"> 세금계산서</label>
                                <label style="cursor: pointer; margin-right: 15px; display: inline-block; vertical-align: middle;" id="cash_receipt_label"><input type="radio" name="typereceipt" value="2" {{ old('typereceipt') == '2' ? 'checked' : '' }} onclick="toggleReceipt(2)" style="width:15px; height:15px; vertical-align:middle; margin-right:3px;"> 현금영수증</label>
                                <span id="tax_exempt_warning" style="color:#d00; font-size:12px; margin-left:10px; display:none;">(비과세 상품 포함시 증빙서류 발급 불가)</span>
                            </div>
                        </div>

                        <div id="proof-content" style="margin-top: 15px; border-top: 1px dashed #cfd5da; padding-top: 15px;">
                            {{-- 세금계산서 --}}
                            <div id="tax_form" class="hide">
                                <div class="order-row"><div class="order-input-title">상호명</div><div class="order-input-wrap"><input type="text" name="co_name" id="co_name" value="{{ old('co_name') }}"></div></div>
                                <div class="order-row"><div class="order-input-title">사업자번호</div><div class="order-input-wrap"><input type="text" name="busi_no" id="busi_no" value="{{ old('busi_no') }}" placeholder="'-'없이 입력"></div></div>
                                <div class="order-row"><div class="order-input-title">대표자명</div><div class="order-input-wrap"><input type="text" name="co_ceo" id="co_ceo" value="{{ old('co_ceo') }}"></div></div>
                                <div class="order-row"><div class="order-input-title">업태</div><div class="order-input-wrap"><input type="text" name="co_status" id="co_status" value="{{ old('co_status') }}"></div></div>
                                <div class="order-row"><div class="order-input-title">종목</div><div class="order-input-wrap"><input type="text" name="co_type" id="co_type" value="{{ old('co_type') }}"></div></div>
                                
                                <div class="order-row">
                                    <div class="order-input-title">주소입력</div>
                                    <div class="order-input-wrap">
                                        <button type="button" class="blue_line" style="height: 40px; line-height: 38px; width: 110px; cursor:pointer;" onclick="openTaxDaumPostcode()">우편번호찾기</button>
                                        <input type="text" name="co_new_zipcode" id="co_new_zipcode" value="{{ old('co_new_zipcode') }}" style="width: 200px; margin-left: 10px; height:40px;" readonly>
                                    </div>
                                </div>
                                <div class="order-row">
                                    <div class="order-input-title" style="visibility:hidden;">기본주소</div>
                                    <div class="order-input-wrap">
                                        <input type="hidden" name="co_address_type" id="co_address_type" value="{{ old('co_address_type', 'zibun') }}">
                                        <input type="text" name="co_address" id="co_address" value="{{ old('co_address') }}" placeholder="지번주소" readonly>
                                        <input type="text" name="co_address_street" id="co_address_street" value="{{ old('co_address_street') }}" class="hide" placeholder="도로명주소" readonly>
                                    </div>
                                </div>
                                <div class="order-row">
                                    <div class="order-input-title" style="visibility:hidden;">상세주소</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="co_address_detail" id="co_address_detail" value="{{ old('co_address_detail') }}" placeholder="상세주소">
                                    </div>
                                </div>
                                
                                <div style="margin-top: 15px; border-top: 1px dashed #cfd5da; padding-top: 15px;">
                                    <div class="order-row"><div class="order-input-title">담당자명</div><div class="order-input-wrap"><input type="text" name="person" id="person" value="{{ old('person') }}"></div></div>
                                    <div class="order-row"><div class="order-input-title">담당자 연락처</div><div class="order-input-wrap"><input type="text" name="phone" id="phone" value="{{ old('phone') }}"></div></div>
                                    <div class="order-row"><div class="order-input-title">담당자 메일</div><div class="order-input-wrap"><input type="text" name="email" id="email" value="{{ old('email') }}"></div></div>
                                </div>
                            </div>
                            
                            {{-- 현금영수증 --}}
                            <div id="cash_form" class="hide">
                                <div class="order-row">
                                    <div class="order-input-title">발행용도</div>
                                    <div class="order-input-wrap">
                                        <label style="cursor: pointer; margin-right: 15px; display:inline-block; vertical-align:middle;"><input type="radio" name="cuse" value="0" {{ old('cuse', '0') == '0' ? 'checked' : '' }} onclick="toggleCashReceiptType(0)" style="width:15px; height:15px; vertical-align:middle; margin-right:3px;"> 개인 소득공제용</label>
                                        <label style="cursor: pointer; margin-right: 15px; display:inline-block; vertical-align:middle;"><input type="radio" name="cuse" value="1" {{ old('cuse') == '1' ? 'checked' : '' }} onclick="toggleCashReceiptType(1)" style="width:15px; height:15px; vertical-align:middle; margin-right:3px;"> 사업자지출 증빙용</label>
                                    </div>
                                </div>
                                <div class="order-row" id="personal_receipt_row">
                                    <div class="order-input-title">휴대폰번호</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="creceipt_number[]" value="{{ old('creceipt_number') ? (old('creceipt_number')[0] ?? '') : '' }}" placeholder="'-'없이 입력">
                                    </div>
                                </div>
                                <div class="order-row" id="business_receipt_row" style="display:none;">
                                    <div class="order-input-title">사업자번호</div>
                                    <div class="order-input-wrap">
                                        <input type="text" name="creceipt_number[]" value="{{ old('creceipt_number') ? (old('creceipt_number')[1] ?? '') : '' }}" placeholder="'-'없이 입력">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- fright: 고정 요약 및 동의 영역 -->
                <div class="fright" style="position: sticky; top: 10px;">
                    <!-- 최종 결제 금액 확인 -->
                    <div class="total-pay-price-wrap">
                        <h3>최종 결제 금액 확인</h3>
                        <div class="total-pay-price">
                            <div style="clear: both; overflow: hidden; height: 50px; line-height: 50px; position: relative;">
                                <span class="pay-title" style="float: left;">총 주문금액</span>
                                <span class="pay-contents plus" style="float: right;"><span>{{ number_format($totalPrice) }}</span></span>
                            </div>
                            <div style="clear: both; overflow: hidden; height: 50px; line-height: 50px; position: relative;">
                                <span class="pay-title" style="float: left;">총 부가세</span>
                                <span class="pay-contents plus" style="float: right;">
                                    <span class="total_vat_price">{{ number_format($tax) }}</span>
                                </span>
                            </div>
                            <div id="card_surcharge_row" style="display:none; clear: both; overflow: hidden; height: 50px; line-height: 50px; position: relative;">
                                <span class="pay-title" style="float: left;">카드 수수료(3%)</span>
                                <span class="pay-contents plus" style="float: right;"><span id="card_surcharge_amount">0</span></span>
                            </div>
                            <div class="goods_delivery_info" style="clear: both; height: 50px; line-height: 50px; position: relative; overflow: visible;">
                                <span class="pay-title" style="float: left;">총 배송비</span>
                                <span class="pay-contents plus" style="float: right;"><span id="total_shipping_display_text_2">{{ number_format($shipping + $packagingCost) }}</span></span>
                                <img src="/data/skin/beauty/images/icon/order_detail.png" alt="상세내역" class="price_area hand total_org_shipping_price_btn" style="cursor:pointer; float: right; margin-right: 10px; margin-top: 13px; vertical-align: middle;" onclick="$('#delivery_detail_layer').toggleClass('hide')">
                                <div id="delivery_detail_layer" class="absolute sale_price_layer hide doto_sale_layer" style="background:#fff; border:1px solid #ddd; padding:10px; z-index:100; right: 0; top:35px; width:200px; box-shadow:0 2px 5px rgba(0,0,0,0.15); line-height: 1.4; text-align: left;">
                                    <h6 style="margin:0 0 5px 0; font-size:12px; font-weight:bold;">배송비내역</h6>
                                    <table width="100%" style="font-size:11px; border-collapse:collapse;">
                                        <tr><td style="padding:3px 0;">기본배송비</td><td style="text-align:right;">{{ number_format(config('shop.shipping.base_cost', 2500)) }}원</td></tr>
                                        <tr><td style="padding:3px 0;">추가배송비</td><td style="text-align:right;">{{ number_format(config('shop.shipping.packaging_cost', 300)) }}원</td></tr>
                                    </table>
                                </div>
                            </div>
                            <div style="clear: both; overflow: hidden; height: 50px; line-height: 50px; position: relative;">
                                <span class="pay-title" style="float: left;">총 할인 금액</span>
                                <span class="pay-contents minus" style="float: right;"><span id="coupon_discount_display_text_2">0</span></span>
                            </div>
                            <div style="clear: both; overflow: hidden; height: 50px; line-height: 50px; position: relative;">
                                <span class="pay-title" style="float: left;">사용한 적립금</span>
                                <span class="pay-contents minus" style="float: right;"><span id="use_emoney_display">0</span></span>
                            </div>
                            <div style="clear: both; overflow: hidden; height: 50px; line-height: 50px; position: relative;">
                                <span class="pay-title" style="float: left;">사용 캐시</span>
                                <span class="pay-contents minus" style="float: right;"><span id="use_cash_display">0</span></span>
                            </div>
                            <div class="order-settle-price" style="clear: both; overflow: hidden; height: 77px; line-height: 77px; position: relative;">
                                <span class="pay-title" style="float: left;">총 결제금액</span>
                                <span class="pay-contents" style="float: right;"><span class="settle_price final_price">{{ number_format($finalPrice) }}</span></span>
                            </div>
                            <div class="total-pay-price-alert goods_delivery_info" style="clear: both; display: table; padding: 10px 0; height: 79px; box-sizing: content-box;">
                                <p style="line-height: 1.5; font-size: 14px; font-weight: normal; vertical-align: middle; display: table-cell;">
                                    ※배송비는 구매 금액에 상관없이 <strong>박스당 2,500원이 우선 청구</strong>되며
                                    <strong style="color: #f44336;">추가 요금 발생시 착불로 청구</strong>됩니다.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 배송비 정책 동의 -->
                    <div class="shipping-agree-wrap">
                        <div class="shipping-agree">
                            <label><input type="checkbox" name="delivery_chk" value="Y" required style="width:16px; height:16px; vertical-align:middle; margin-right:5px;"> 배송비 정책에 동의합니다.</label>
                        </div>
                        <ul style="list-style: none; padding-left: 0; font-size: 13px; color: #555;">
                            <li style="margin-bottom: 5px;">1. 배송비는 15만원 이상 구매하면 무료입니다. (도서산간/제주 추가배송비 별도)</li>
                            <li style="margin-bottom: 5px;">2. 선불/착불 선택 가능하며 선불은 기본 1박스만 선결제 됩니다.</li>
                            <li style="margin-bottom: 5px;">3. 1박스를 선불로 결제하더라도 추가 박스 발생시 착불로 배송됩니다.<br>(전량 선불 결제를 원할 시 고객센터로 연락주세요.)</li>
                            <li style="margin-bottom: 5px;">4. 궁금한 점은 고객센터로 연락주시기 바랍니다.</li>
                        </ul>
                    </div>

                    <!-- 개인정보 동의(필수) -->
                    <div style="margin-top: 15px; background: #fff; border: 1px solid #cfd5da; padding: 15px;">
                        <h6 style="margin:0 0 10px 0; font-size:13px; font-weight:bold;">개인정보 동의(필수)</h6>
                        <div style="font-size: 11px; color: #666; height: 120px; overflow-y: scroll; border: 1px solid #e9ecef; background: #fff; padding: 10px; box-sizing: border-box; line-height: 1.4; margin-bottom: 10px;">
                            도매토피아는 회원님께 최대한으로 최적화되고 맞춤화된 서비스를 제공하기 위하여 다음과 같은 목적으로 개인정보를 수집하고 있습니다.
                            <table class="privacy_table" style="width: 100%; font-size: 11px; border-collapse: collapse; margin-top: 10px; border: 1px solid #ddd;">
                                <thead>
                                    <tr style="background: #f7f8f9;">
                                        <th style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">목적</th>
                                        <th style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">항목</th>
                                        <th style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">보유기간</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr align="center">
                                        <td style="border: 1px solid #ddd; padding: 5px;">상품 주문내역 안내, 상품배송</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">
                                            @if(isset($Overseas) && $Overseas == 'Y')
                                                이름, 핸드폰번호, 주문자 이메일, 받는분 성함, 받는분 핸드폰, 받는분 주소
                                            @else
                                                주문자 정보(이름,핸드폰,이메일) 배송지정보(이름, 핸드폰, 주소)
                                            @endif
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">개인정보는 서비스 제공 기간동안 보유 및 이용하며, 탈퇴 시 즉시 파기됩니다.</td>
                                    </tr>
                                    @if(isset($Overseas) && $Overseas == 'Y')
                                    <tr align="center">
                                        <td style="border: 1px solid #ddd; padding: 5px;">해외 직배송 상품 통관업무처리</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">개인통관번호</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">개인정보는 서비스 제공 기간동안 보유 및 이용하며, 탈퇴 시 즉시 파기됩니다.</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                            @if(!isset($Overseas) || $Overseas != 'Y')
                                <div style="margin-top: 10px;">
                                    개인정보 수집이용을 거부할 권리가 있습니다. <b>단, 거부시 상품구매 서비스를 이용하실 수 없습니다.</b>
                                </div>
                            @endif
                        </div>
                        <div style="font-size: 12px; text-align: center;">
                            <label style="cursor:pointer;"><input type="radio" name="privacy_agree" value="Y"> 개인정보 수집ㆍ이용에 동의</label>
                            <label style="cursor:pointer; margin-left:15px;"><input type="radio" name="privacy_agree" value="N" checked> 개인정보 수집ㆍ이용에 동의하지 않음</label>
                        </div>
                    </div>

                    <!-- 개인정보 동의(선택) -->
                    <div style="margin-top: 15px; background: #fff; border: 1px solid #cfd5da; padding: 15px;">
                        <h6 style="margin:0 0 10px 0; font-size:13px; font-weight:bold;">개인정보 동의(선택)</h6>
                        <div style="font-size: 11px; color: #666; height: 120px; overflow-y: scroll; border: 1px solid #e9ecef; background: #fff; padding: 10px; box-sizing: border-box; line-height: 1.4; margin-bottom: 10px;">
                            도매토피아는 회원님께 최대한으로 최적화되고 맞춤화된 서비스를 제공하기 위하여 다음과 같은 목적으로 개인정보를 수집하고 있습니다.
                            <table class="privacy_table" style="width: 100%; font-size: 11px; border-collapse: collapse; margin-top: 10px; border: 1px solid #ddd;">
                                <thead>
                                    <tr style="background: #f7f8f9;">
                                        <th style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">목적</th>
                                        <th style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">항목</th>
                                        <th style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">보유기간</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr align="center">
                                        <td style="border: 1px solid #ddd; padding: 5px;">상품 주문내역 안내, 상품배송</td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">
                                            @if(isset($Overseas) && $Overseas == 'Y')
                                                주문자 전화 번호, 받는분 전화번호
                                            @else
                                                주문자 정보(전화번호) 배송지정보(전화번호)
                                            @endif
                                        </td>
                                        <td style="border: 1px solid #ddd; padding: 5px;">
                                            @if(isset($Overseas) && $Overseas == 'Y')
                                                개인정보는 서비스제공기간동안 보유 및 이용하며, 탈퇴시 즉시 파기됩니다.
                                            @else
                                                개인정보는 서비스 제공 기간동안 보유 및 이용하며, 탈퇴 시 즉시 파기됩니다.
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            @if(!isset($Overseas) || $Overseas != 'Y')
                                <div style="margin-top: 10px;">
                                    개인정보 수집이용을 거부할 권리가 있습니다. 단, 거부시 서비스 이용에는 제한이 없습니다.
                                </div>
                            @endif
                        </div>
                        <div style="font-size: 12px; text-align: center;">
                            <label style="cursor:pointer;"><input type="radio" name="privacy_agree_2" value="Y"> 개인정보 수집ㆍ이용에 동의</label>
                            <label style="cursor:pointer; margin-left:15px;"><input type="radio" name="privacy_agree_2" value="N" checked> 개인정보 수집ㆍ이용에 동의하지 않음</label>
                        </div>
                    </div>

                    <div class="btn_area_center" style="margin-top: 20px;">
                        <button type="submit" class="btn-settle-submit">바로구매</button>
                        <a href="{{ route('cart.index') }}" class="btn_cancel" style="display:block; width:100%; margin-top:10px; height:45px; line-height:43px; box-sizing:border-box; background:#555; color:#fff; text-align:center; text-decoration:none; font-weight:bold; border-radius:4px;">장바구니 돌아가기</a>
                    </div>
                </div>
            </div>

                @foreach($cart_seqs as $seq)
                    <input type="hidden" name="cart_seq[]" value="{{ $seq }}">
                @endforeach
            </form>
        </div>
    </div>

    <div id="addressModal" class="modal_overlay" style="display:none;">
        <div class="modal_content">
            <div class="modal_header">
                <h3>나의 배송지 목록</h3>
                <button type="button" class="btn_close_modal" onclick="closeAddressModal()">X</button>
            </div>
            <div class="modal_body" style="background:#fff; padding:15px; max-height: 400px; overflow-y: auto;">
                <div style="margin-bottom: 15px; text-align: right;">
                    <button type="button" class="btn_base" onclick="toggleNewAddressForm()">+ 신규 배송지 등록</button>
                </div>
                
                {{-- 신규 배송지 입력 폼 (기본 숨김) --}}
                <div id="newAddressFormArea" style="display:none; background:#f9f9f9; padding:15px; border:1px solid #ddd; margin-bottom: 20px;">
                    <h4 style="margin-bottom:10px; font-size:14px;">새로운 배송지 입력</h4>
                    <table class="form_table">
                        <colgroup>
                            <col width="100">
                            <col width="*">
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>수령인 *</th>
                                <td><input type="text" id="new_addr_name" class="input_text full_width"></td>
                            </tr>
                            <tr>
                                <th>휴대전화 *</th>
                                <td><input type="text" id="new_addr_mobile" class="input_text full_width"></td>
                            </tr>
                            <tr>
                                <th>배송지명</th>
                                <td><input type="text" id="new_addr_group" class="input_text full_width" placeholder="예: 집, 회사"></td>
                            </tr>
                            <tr>
                                <th>주소 *</th>
                                <td>
                                    <input type="text" id="new_addr_zipcode" class="input_text" style="width: 80px;" readonly>
                                    <button type="button" class="btn_base" onclick="openModalDaumPostcode()">검색</button><br>
                                    <input type="text" id="new_addr_address" class="input_text full_width mt5" readonly><br>
                                    <input type="hidden" id="new_addr_street">
                                    <input type="hidden" id="new_addr_type">
                                    <input type="text" id="new_addr_detail" class="input_text full_width mt5" placeholder="상세주소를 입력해주세요">
                                </td>
                            </tr>
                            <tr>
                                <th>기본배송지</th>
                                <td><label><input type="checkbox" id="new_addr_default" value="Y"> 기본 배송지로 설정</label></td>
                            </tr>
                        </tbody>
                    </table>
                    <div style="text-align:center; margin-top:15px;">
                        <button type="button" class="btn_base btn_black" onclick="saveNewAddress()">저장하기</button>
                        <button type="button" class="btn_base" onclick="toggleNewAddressForm()">취소</button>
                    </div>
                </div>

                <ul id="addressList" class="address_list" style="list-style:none; padding:0; margin:0;">
                    <!-- Loaded via AJAX -->
                </ul>
            </div>
        </div>
    </div>

    {{-- Daum Address API --}}
    <script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script>
        document.getElementById('copy_user_info').addEventListener('change', function () {
            if (this.checked) {
                document.getElementById('recipient_user_name').value = document.getElementsByName('order_user_name')[0].value;
                const userPhone = document.getElementsByName('order_cellphone')[0].value;
                document.getElementById('recipient_cellphone').value = userPhone;
            } else {
                document.getElementById('recipient_user_name').value = '';
                document.getElementById('recipient_cellphone').value = '';
            }
        });

        // Toggle bank info based on payment method
        const paymentRadios = document.querySelectorAll('input[name="payment"]');
        const bankInfoRow = document.getElementById('bank_info_row');

        paymentRadios.forEach(radio => {
            radio.addEventListener('change', function () {
        if (this.value === 'bank') {
            bankInfoRow.style.display = 'table-row';
            refundInfoRow.style.display = 'table-row';
            
            // 무통장 선택 시 세금계산서 자동 선택 및 토글 트리거
            if (!hasExempt) {
                const taxRadio = document.querySelector('input[name="typereceipt"][value="1"]');
                if (taxRadio) {
                    taxRadio.checked = true;
                    toggleReceipt(1);
                }
            }
        } else {
            bankInfoRow.style.display = 'none';
            refundInfoRow.style.display = 'none';
            
            // 신용카드 선택 시 증빙서류 '신청안함' 자동 선택
            const noReceiptRadio = document.querySelector('input[name="typereceipt"][value="0"]');
            if (noReceiptRadio) {
                noReceiptRadio.checked = true;
                toggleReceipt(0);
            }
        }
        updateFinalPrice();
    });
});

// Address Modal Functions
function openAddressModal() {
    const listEl = document.getElementById('addressList');
    listEl.innerHTML = '<li>로딩중...</li>';
    document.getElementById('addressModal').style.display = 'block';

    fetch("{{ route('mypage.delivery_address.json') }}")
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                listEl.innerHTML = '';
                if (data.data.length === 0) {
                    listEl.innerHTML = '<li>등록된 배송지가 없습니다.</li>';
                    return;
                }
                data.data.forEach(addr => {
                    const li = document.createElement('li');
                    li.style.cssText = "border:1px solid #ddd; padding:15px; margin-bottom:10px; cursor:pointer;";
                    li.onclick = function() { selectAddress(addr); };
                    li.innerHTML = `
                        <div class="addr_item">
                            <strong style="color:#000; font-size:14px;">${addr.recipient_user_name}</strong>
                            <span style="color:#888; font-size:12px; margin-left:5px;">[${addr.address_group || '기본'}]</span>
                            ${addr.default === 'Y' ? '<span style="display:inline-block; padding:2px 5px; background:#d00; color:#fff; font-size:11px; margin-left:5px; border-radius:3px;">기본</span>' : ''}
                            <p style="margin:5px 0 0 0; color:#555; font-size:13px;">${addr.recipient_address} ${addr.recipient_address_detail || ''}</p>
                            <p style="margin:2px 0 0 0; color:#888; font-size:12px;">${addr.recipient_mobile || addr.recipient_cellphone || ''}</p>
                        </div>
                    `;
                    listEl.appendChild(li);
                });
            } else {
                alert(data.message);
                closeAddressModal();
            }
        })
        .catch(err => {
            console.error(err);
            listEl.innerHTML = '<li>불러오기 실패</li>';
        });
}

function closeAddressModal() {
    document.getElementById('addressModal').style.display = 'none';
}

function selectAddress(addr) {
    document.getElementById('recipient_user_name').value = addr.recipient_user_name;
    
    // 연락처 3분할 대입
    const cellphone = (addr.recipient_mobile || addr.recipient_cellphone || '').split('-');
    const phone = (addr.recipient_phone || '').split('-');
    for(let i=0; i<3; i++) {
        document.getElementById('recipient_phone_' + i).value = phone[i] || '';
        document.getElementById('recipient_cellphone_' + i).value = cellphone[i] || '';
    }
    
    document.getElementById('recipient_zipcode').value = addr.recipient_zipcode;
    document.getElementById('recipient_address').value = addr.recipient_address;
    document.getElementById('recipient_address_street').value = addr.recipient_address_street || '';
    document.getElementById('recipient_address_display').value = addr.recipient_address_type === 'street' ? (addr.recipient_address_street || addr.recipient_address) : addr.recipient_address;
    document.getElementById('recipient_address_detail').value = addr.recipient_address_detail || '';
    document.getElementById('recipient_address_type').value = addr.recipient_address_type || 'zibun';
    
    closeAddressModal();
    fetchShippingExtraCost(addr.recipient_zipcode, addr.recipient_address, addr.recipient_address_street);
}

// 새 배송지 폼 토글
function toggleNewAddressForm() {
    const formArea = document.getElementById('newAddressFormArea');
    if (formArea.style.display === 'none') {
        formArea.style.display = 'block';
        // Reset inputs
        document.getElementById('new_addr_name').value = '';
        document.getElementById('new_addr_mobile').value = '';
        document.getElementById('new_addr_group').value = '';
        document.getElementById('new_addr_zipcode').value = '';
        document.getElementById('new_addr_address').value = '';
        document.getElementById('new_addr_detail').value = '';
        document.getElementById('new_addr_default').checked = false;
    } else {
        formArea.style.display = 'none';
    }
}

// 새 배송지 저장요청 (AJAX)
function saveNewAddress() {
    const data = {
        recipient_user_name: document.getElementById('new_addr_name').value,
        recipient_mobile: document.getElementById('new_addr_mobile').value,
        address_group: document.getElementById('new_addr_group').value,
        recipient_zipcode: document.getElementById('new_addr_zipcode').value,
        recipient_address: document.getElementById('new_addr_address').value,
        recipient_address_street: document.getElementById('new_addr_street').value,
        recipient_address_detail: document.getElementById('new_addr_detail').value,
        recipient_address_type: document.getElementById('new_addr_type').value || 'zibun',
        default: document.getElementById('new_addr_default').checked ? 'Y' : 'N'
    };

    if(!data.recipient_user_name || !data.recipient_mobile || !data.recipient_zipcode) {
        alert('필수 사항을 모두 입력해주세요.');
        return;
    }

    fetch("{{ route('mypage.delivery_address.store') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(resData => {
        if(resData.status === 'success') {
            alert('새 배송지가 등록되었습니다.');
            toggleNewAddressForm();
            openAddressModal();
        } else {
            alert('등록 중 오류가 발생했습니다.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('요청 실패');
    });
}

// Initial PHP values passing to JS
const initialFinalPrice = {{ $totalPrice + $shipping + $packagingCost + $tax }};
const initialGoodsPrice = {{ $totalPrice }};
const initialTax = {{ $tax }};
const maxEmoney = {{ $usableEmoney ?? 0 }};
const maxCash = {{ $user->cash ?? 0 }};
const hasExempt = {{ $hasExempt ? 'true' : 'false' }};
const hasSaveEmoneyLimit = {{ $hasSaveEmoneyLimit ? 'true' : 'false' }};
const isBbbType = {{ $isBbbType ? 'true' : 'false' }};
let extraShippingCost = {{ $extraCost ?? 0 }}; 

// Initialize constraints on page load
window.addEventListener('DOMContentLoaded', () => {
    // 1. 비과세 제한 처리
    if (hasExempt) {
        const cardRadio = document.querySelector('input[name="payment"][value="card"]');
        if (cardRadio) {
            cardRadio.disabled = true;
            if (cardRadio.checked) {
                document.querySelector('input[name="payment"][value="bank"]').checked = true;
            }
        }
        const cardLabel = document.getElementById('card_payment_label');
        if (cardLabel) cardLabel.style.display = 'none';
        
        document.querySelectorAll('input[name="typereceipt"]').forEach(radio => {
            if (radio.value !== '0') {
                radio.disabled = true;
            } else {
                radio.checked = true;
            }
        });
        document.getElementById('tax_exempt_warning').style.display = 'inline';
        toggleReceipt(0);
    }
    
    // 2. 제한 상품 쿠폰/적립금 영역 숨김
    if (hasSaveEmoneyLimit) {
        const couponRow = document.getElementById('coupon_use_row');
        if (couponRow) couponRow.style.display = 'none';
        
        const emoneyRow = document.getElementById('emoney_use_row');
        if (emoneyRow) emoneyRow.style.display = 'none';
        
        document.getElementById('download_seq').value = '';
        document.getElementById('use_emoney').value = 0;
        document.getElementById('use_emoney_view').value = 0;
    }

    const wrap = document.getElementById('extra_shipping_wrap');
    const display = document.getElementById('extra_shipping_display');
    if (extraShippingCost > 0) {
        wrap.style.display = 'inline-block';
        display.innerText = new Intl.NumberFormat().format(extraShippingCost);
    }
    updateFinalPrice();
});

// 비동기 추가 배송비 조회 로직
function fetchShippingExtraCost(zipcode, address = '', addressStreet = '') {
    const resolvedAddress = address || document.getElementById('recipient_address').value || '';
    const resolvedAddressStreet = addressStreet || document.getElementById('recipient_address_street').value || '';
    
    fetch("{{ route('order.calculate-shipping') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ 
            zipcode: zipcode, 
            address: resolvedAddress, 
            address_street: resolvedAddressStreet 
        })
    })
    .then(res => res.json())
    .then(data => {
        extraShippingCost = parseInt(data.extra_cost) || 0;
        const wrap = document.getElementById('extra_shipping_wrap');
        const display = document.getElementById('extra_shipping_display');
        
        if (extraShippingCost > 0) {
            wrap.style.display = 'inline-block';
            display.innerText = new Intl.NumberFormat().format(extraShippingCost);
        } else {
            wrap.style.display = 'none';
            display.innerText = '0';
        }
        
        updateFinalPrice();
    })
    .catch(err => console.error("Shipping Calculation Error:", err));
}

// 적립금/캐시 동적 버튼 함수
function useEmoneyBtn() {
    const viewVal = parseInt(document.getElementById('use_emoney_view').value) || 0;
    const userEmoney = {{ $user->emoney ?? 0 }};
    
    if (hasSaveEmoneyLimit) {
        alert('제한 상품이 포함되어 적립금을 사용할 수 없습니다.');
        cancelEmoneyBtn();
        return;
    }
    if (viewVal <= 0) {
        alert('사용하실 금액을 입력해주세요.');
        return;
    }
    
    const currentTotal = calculateCurrentTotal('emoney');
    let useVal = viewVal;
    
    if (useVal > userEmoney) {
        alert('보유 적립금액을 초과할 수 없습니다.');
        useVal = userEmoney;
    }
    if (useVal > currentTotal) {
        useVal = currentTotal;
    }
    
    const minEmoney = 100;
    const emoneyUseLimit = 100;
    if (userEmoney < emoneyUseLimit) {
        alert(new Intl.NumberFormat().format(emoneyUseLimit) + '원 이상 적립하여야 합니다.');
        cancelEmoneyBtn();
        return;
    }
    if (useVal < minEmoney) {
        alert('적립금은 최소 ' + new Intl.NumberFormat().format(minEmoney) + '원부터 사용가능 합니다.');
        cancelEmoneyBtn();
        return;
    }
    
    document.getElementById('use_emoney_view').value = useVal;
    document.getElementById('use_emoney').value = useVal;
    document.getElementById('use_emoney_view').readOnly = true;
    
    document.querySelector('.emoney_input_button').style.display = 'none';
    document.querySelector('.emoney_all_input_button').style.display = 'none';
    document.querySelector('.emoney_cancel_button').style.display = 'inline-block';
    
    updateFinalPrice();
}

function useAllEmoneyBtn() {
    if (hasSaveEmoneyLimit) {
        alert('제한 상품이 포함되어 적립금을 사용할 수 없습니다.');
        return;
    }
    const userEmoney = {{ $user->emoney ?? 0 }};
    const currentTotal = calculateCurrentTotal('emoney');
    let useVal = userEmoney;
    if (useVal > currentTotal) {
        useVal = currentTotal;
    }
    document.getElementById('use_emoney_view').value = useVal;
    useEmoneyBtn();
}

function cancelEmoneyBtn() {
    document.getElementById('use_emoney_view').value = 0;
    document.getElementById('use_emoney').value = 0;
    document.getElementById('use_emoney_view').readOnly = false;
    
    document.querySelector('.emoney_input_button').style.display = 'inline-block';
    document.querySelector('.emoney_all_input_button').style.display = 'inline-block';
    document.querySelector('.emoney_cancel_button').style.display = 'none';
    
    updateFinalPrice();
}

function useCashBtn() {
    const viewVal = parseInt(document.getElementById('use_cash_view').value) || 0;
    const userCash = {{ $user->cash ?? 0 }};
    
    if (viewVal <= 0) {
        alert('사용하실 캐시 금액을 입력해주세요.');
        return;
    }
    
    const currentTotal = calculateCurrentTotal('cash');
    let useVal = viewVal;
    
    if (useVal > userCash) {
        alert('보유 캐시를 초과할 수 없습니다.');
        useVal = userCash;
    }
    if (useVal > currentTotal) {
        useVal = currentTotal;
    }
    
    document.getElementById('use_cash_view').value = useVal;
    document.getElementById('use_cash').value = useVal;
    document.getElementById('use_cash_view').readOnly = true;
    
    document.querySelector('.cash_input_button').style.display = 'none';
    document.querySelector('.cash_all_input_button').style.display = 'none';
    document.querySelector('.cash_cancel_button').style.display = 'inline-block';
    
    updateFinalPrice();
}

function useAllCashBtn() {
    const userCash = {{ $user->cash ?? 0 }};
    const currentTotal = calculateCurrentTotal('cash');
    let useVal = userCash;
    if (useVal > currentTotal) {
        useVal = currentTotal;
    }
    document.getElementById('use_cash_view').value = useVal;
    useCashBtn();
}

function cancelCashBtn() {
    document.getElementById('use_cash_view').value = 0;
    document.getElementById('use_cash').value = 0;
    document.getElementById('use_cash_view').readOnly = false;
    
    document.querySelector('.cash_input_button').style.display = 'inline-block';
    document.querySelector('.cash_all_input_button').style.display = 'inline-block';
    document.querySelector('.cash_cancel_button').style.display = 'none';
    
    updateFinalPrice();
}

function calculateCurrentTotal(excludeType) {
    let total = initialFinalPrice + extraShippingCost - calculateCouponDiscount();
    if (excludeType !== 'emoney') total -= parseInt(document.getElementById('use_emoney').value || 0);
    if (excludeType !== 'cash') total -= parseInt(document.getElementById('use_cash').value || 0);
    return total;
}

function calculateCouponDiscount() {
    const select = document.getElementById('download_seq');
    const option = select.options[select.selectedIndex];
    if (!option || !option.value) return 0;

    let discount = 0;
    const type = option.dataset.type;
    
    if (type === 'percent') {
        const percent = parseFloat(option.dataset.percent);
        const max = parseFloat(option.dataset.max);
        discount = Math.floor(initialGoodsPrice * (percent / 100));
        if (max > 0 && discount > max) discount = max;
    } else if (type === 'won') {
        discount = parseFloat(option.dataset.won);
    }
    if (discount > initialFinalPrice) discount = initialFinalPrice;
    return discount;
}

function updateFinalPrice() {
    let useEmoney = parseInt(document.getElementById('use_emoney').value || 0);
    let useCash = parseInt(document.getElementById('use_cash').value || 0);
    let couponDiscount = calculateCouponDiscount();

    if (couponDiscount > 0) {
         document.getElementById('coupon_discount_display').innerText = '-' + new Intl.NumberFormat().format(couponDiscount) + '원';
         document.getElementById('coupon_discount_display_text').innerText = new Intl.NumberFormat().format(couponDiscount);
    } else {
         document.getElementById('coupon_discount_display').innerText = '';
         document.getElementById('coupon_discount_display_text').innerText = '0';
    }

    let availableForPoints = initialFinalPrice + extraShippingCost - couponDiscount;

    if (useEmoney > availableForPoints) {
         useEmoney = availableForPoints;
         document.getElementById('use_emoney').value = useEmoney;
    }
    availableForPoints -= useEmoney;

    if (useCash > availableForPoints) {
         useCash = availableForPoints;
         document.getElementById('use_cash').value = useCash;
    }

    // 3% 카드 수수료 할증 실시간 계산
    const paymentVal = document.querySelector('input[name="payment"]:checked')?.value || 'bank';
    let cardVat = 0;
    if (isBbbType && paymentVal === 'card') {
        const baseAmount = initialGoodsPrice + initialTax;
        cardVat = Math.floor(baseAmount * 0.03);
    }

    let finalPrice = initialFinalPrice + extraShippingCost - couponDiscount - useEmoney - useCash + cardVat;
    if (finalPrice < 0) finalPrice = 0;

    // 모든 .final_price 클래스를 가진 엘리먼트 갱신
    document.querySelectorAll('.final_price').forEach(el => {
        el.innerText = new Intl.NumberFormat().format(finalPrice);
    });
    
    const totalShipping = {{ $shipping + $packagingCost }} + extraShippingCost;
    const shippingText = new Intl.NumberFormat().format(totalShipping);
    
    // 배송비 및 개별 할인/적립금 항목 실시간 갱신
    const ship1 = document.getElementById('total_shipping_display_text');
    if (ship1) ship1.innerText = shippingText;
    const ship2 = document.getElementById('total_shipping_display_text_2');
    if (ship2) ship2.innerText = shippingText;

    const emoneyDisp = document.getElementById('use_emoney_display');
    if (emoneyDisp) emoneyDisp.innerText = new Intl.NumberFormat().format(useEmoney);
    const cashDisp = document.getElementById('use_cash_display');
    if (cashDisp) cashDisp.innerText = new Intl.NumberFormat().format(useCash);

    const couponDisp2 = document.getElementById('coupon_discount_display_text_2');
    if (couponDisp2) couponDisp2.innerText = new Intl.NumberFormat().format(couponDiscount);

    // 카드 수수료 가산 표기 제어
    const surchargeRow = document.getElementById('card_surcharge_row');
    const surchargeAmt = document.getElementById('card_surcharge_amount');
    if (surchargeRow && surchargeAmt) {
        if (cardVat > 0) {
            surchargeRow.style.display = 'flex';
            surchargeAmt.innerText = new Intl.NumberFormat().format(cardVat);
        } else {
            surchargeRow.style.display = 'none';
            surchargeAmt.innerText = '0';
        }
    }
}

document.getElementById('download_seq').addEventListener('change', updateFinalPrice);

// Toggle Receipt Forms
function toggleReceipt(type) {
    const row = document.getElementById('receipt_form_row');
    const taxForm = document.getElementById('tax_form');
    const cashForm = document.getElementById('cash_form');

    if (type == 0) {
        row.style.display = 'none';
        taxForm.style.display = 'none';
        cashForm.style.display = 'none';
    } else if (type == 1) { 
        row.style.display = 'block';
        taxForm.style.display = 'block';
        cashForm.style.display = 'none';
    } else if (type == 2) { 
        row.style.display = 'block';
        taxForm.style.display = 'none';
        cashForm.style.display = 'block';
    }
}

// 현금영수증 타입 교차 노출
function toggleCashReceiptType(type) {
    const personalRow = document.getElementById('personal_receipt_row');
    const businessRow = document.getElementById('business_receipt_row');
    if (type == 0) {
        personalRow.style.display = 'block';
        businessRow.style.display = 'none';
    } else {
        personalRow.style.display = 'none';
        businessRow.style.display = 'block';
    }
}

// Quick Address Radios change bindings
document.querySelectorAll('input[name="chkQuickAddress"]').forEach(el => {
    el.addEventListener('change', function() {
        const latelySelect = document.getElementById('chkQuickAddressLately');
        if (this.value === 'member') {
            latelySelect.style.display = 'none';
            document.getElementById('recipient_user_name').value = "{{ $user->user_name ?? '' }}";
            
            const phone = "{{ $user->phone ?? '' }}".split('-');
            const cellphone = "{{ $user->cellphone ?? '' }}".split('-');
            for(let i=0; i<3; i++) {
                document.getElementById('recipient_phone_' + i).value = phone[i] || '';
                document.getElementById('recipient_cellphone_' + i).value = cellphone[i] || '';
            }
            
            document.getElementById('recipient_new_zipcode').value = "{{ $user->zipcode ?? '' }}";
            document.getElementById('recipient_address').value = "{{ $user->address ?? '' }}";
            document.getElementById('recipient_address_street').value = "{{ $user->address_street ?? '' }}";
            document.getElementById('recipient_address_display').value = "{{ ($user->address_type ?? 'zibun') == 'street' ? ($user->address_street ?? '') : ($user->address ?? '') }}";
            document.getElementById('recipient_address_detail').value = "{{ $user->address_detail ?? '' }}";
            document.getElementById('recipient_address_type').value = "{{ $user->address_type ?? 'zibun' }}";
            
            fetchShippingExtraCost("{{ $user->zipcode ?? '' }}", "{{ $user->address ?? '' }}", "{{ $user->address_street ?? '' }}");
        } else if (this.value === 'new') {
            latelySelect.style.display = 'none';
            document.getElementById('recipient_user_name').value = '';
            for(let i=0; i<3; i++) {
                document.getElementById('recipient_phone_' + i).value = '';
                document.getElementById('recipient_cellphone_' + i).value = '';
            }
            document.getElementById('recipient_new_zipcode').value = '';
            document.getElementById('recipient_address').value = '';
            document.getElementById('recipient_address_street').value = '';
            document.getElementById('recipient_address_display').value = '';
            document.getElementById('recipient_address_detail').value = '';
            document.getElementById('recipient_address_type').value = 'zibun';
            extraShippingCost = 0;
            updateFinalPrice();
        } else if (this.value === 'often') {
            latelySelect.style.display = 'none';
            openAddressModal();
        } else if (this.value === 'lately') {
            latelySelect.style.display = 'inline-block';
            loadLatelyAddresses();
        }
    });
});

function loadLatelyAddresses() {
    const select = document.getElementById('chkQuickAddressLately');
    select.innerHTML = '<option value="">로딩중...</option>';
    
    fetch("{{ route('mypage.delivery_address.json') }}")
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                select.innerHTML = '<option value="">최근 배송지를 선택하세요</option>';
                data.data.forEach(addr => {
                    const option = document.createElement('option');
                    option.value = JSON.stringify(addr);
                    option.innerText = `${addr.recipient_user_name} (${addr.recipient_address})`;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">불러오기 실패</option>';
            }
        })
        .catch(err => {
            console.error(err);
            select.innerHTML = '<option value="">불러오기 에러</option>';
        });
}

document.getElementById('chkQuickAddressLately').addEventListener('change', function() {
    if (!this.value) return;
    const addr = JSON.parse(this.value);
    
    document.getElementById('recipient_user_name').value = addr.recipient_user_name;
    
    const cellphone = (addr.recipient_mobile || addr.recipient_cellphone || '').split('-');
    const phone = (addr.recipient_phone || '').split('-');
    for(let i=0; i<3; i++) {
        document.getElementById('recipient_phone_' + i).value = phone[i] || '';
        document.getElementById('recipient_cellphone_' + i).value = cellphone[i] || '';
    }
    
    document.getElementById('recipient_zipcode').value = addr.recipient_zipcode;
    document.getElementById('recipient_address').value = addr.recipient_address;
    document.getElementById('recipient_address_street').value = addr.recipient_address_street || '';
    document.getElementById('recipient_address_display').value = addr.recipient_address_type === 'street' ? (addr.recipient_address_street || addr.recipient_address) : addr.recipient_address;
    document.getElementById('recipient_address_detail').value = addr.recipient_address_detail || '';
    document.getElementById('recipient_address_type').value = addr.recipient_address_type || 'zibun';
    
    fetchShippingExtraCost(addr.recipient_zipcode, addr.recipient_address, addr.recipient_address_street);
});

// Validate Agreements on Submit
document.getElementById('orderForm').addEventListener('submit', function(e) {
    if (!document.querySelector('input[name="delivery_chk"]:checked')) {
        alert('배송비 정책에 동의하셔야 합니다.');
        e.preventDefault();
        return false;
    }
    const privacyAgree = document.querySelector('input[name="privacy_agree"]:checked');
    if (!privacyAgree || privacyAgree.value !== 'Y') {
        alert('개인정보 수집ㆍ이용에 동의하셔야 합니다.');
        if (privacyAgree) privacyAgree.focus();
        e.preventDefault();
        return false;
    }
});

function openDaumPostcode() {
    new daum.Postcode({
        oncomplete: function (data) {
            var addr = ''; 
            if (data.userSelectedType === 'R') { 
                addr = data.roadAddress;
                document.getElementById('recipient_address_type').value = 'street';
            } else { 
                addr = data.jibunAddress;
                document.getElementById('recipient_address_type').value = 'zibun';
            }

            document.getElementById('recipient_zipcode').value = data.zonecode;
            document.getElementById("recipient_address_display").value = addr; 

            const jibunAddr = data.jibunAddress || data.autoJibunAddress || addr;
            const roadAddr = data.roadAddress || data.autoRoadAddress || '';
            document.getElementById("recipient_address").value = jibunAddr; 
            document.getElementById("recipient_address_street").value = roadAddr; 

            fetchShippingExtraCost(data.zonecode, jibunAddr, roadAddr);
            document.getElementById("recipient_address_detail").focus();
        }
    }).open();
}

function openModalDaumPostcode() {
    new daum.Postcode({
        oncomplete: function (data) {
            var addr = ''; 
            if (data.userSelectedType === 'R') { 
                addr = data.roadAddress;
                document.getElementById('new_addr_type').value = 'street';
            } else { 
                addr = data.jibunAddress;
                document.getElementById('new_addr_type').value = 'zibun';
            }

            document.getElementById('new_addr_zipcode').value = data.zonecode;
            document.getElementById("new_addr_address").value = addr;
            document.getElementById("new_addr_street").value = data.roadAddress || data.autoRoadAddress || ''; 

            document.getElementById("new_addr_detail").focus();
        }
    }).open();
}

// 세금계산서 주소 Daum API 연동
function openTaxDaumPostcode() {
    new daum.Postcode({
        oncomplete: function (data) {
            var addr = ''; 
            if (data.userSelectedType === 'R') { 
                addr = data.roadAddress;
                document.getElementById('co_address_type').value = 'street';
                document.getElementById('co_address_street').style.display = 'block';
                document.getElementById('co_address').style.display = 'none';
            } else { 
                addr = data.jibunAddress;
                document.getElementById('co_address_type').value = 'zibun';
                document.getElementById('co_address').style.display = 'block';
                document.getElementById('co_address_street').style.display = 'none';
            }

            document.getElementById('co_new_zipcode').value = data.zonecode;
            document.getElementById("co_address").value = data.jibunAddress || data.autoJibunAddress || addr;
            document.getElementById("co_address_street").value = data.roadAddress || data.autoRoadAddress || ''; 

            document.getElementById("co_address_detail").focus();
        }
    }).open();
}

    </script>

@endsection