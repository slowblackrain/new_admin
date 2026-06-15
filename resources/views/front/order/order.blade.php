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
            /* 필수 배지 복원 */
            .badge_required {
                color: #ff3300;
                border: 1px solid #ff3300;
                padding: 1px 4px;
                font-size: 10px;
                border-radius: 2px;
                margin-left: 5px;
                font-weight: normal;
                display: inline-block;
                vertical-align: middle;
            }
            /* 주문자 정보 폼 카드화 */
            .order_info_card_container {
                display: flex;
                gap: 20px;
                align-items: flex-start;
                margin-top: 15px;
            }
            .order_info_card_left {
                flex: 1;
                border: 1px solid #ddd;
                padding: 25px;
                border-radius: 4px;
                background: #fff;
            }
            .order_info_card_right {
                width: 320px;
                border: 1px solid #e2e8f0;
                padding: 20px;
                border-radius: 4px;
                background: #f8fafc;
                font-size: 12px;
                color: #475569;
            }
            .order_info_card_right h3 {
                margin-top: 0;
                font-size: 14px;
                color: #1e293b;
                display: flex;
                align-items: center;
                gap: 5px;
            }
            .order_info_form_row {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
            }
            .order_info_form_row:last-child {
                margin-bottom: 0;
            }
            .order_info_form_row label {
                width: 130px;
                font-weight: bold;
                font-size: 13px;
                display: flex;
                align-items: center;
            }
            .order_info_form_row input.input_text {
                width: 280px;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 2px;
                font-size: 13px;
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
        </div>

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

            <form name="orderForm" id="orderForm" method="post" action="{{ route('order.store') }}">
                @csrf
                @if(isset($cart_seqs))
                    @foreach($cart_seqs as $seq)
                        <input type="hidden" name="cart_seq[]" value="{{ $seq }}">
                    @endforeach
                @endif
                {{-- 주문서 처리 로직은 다음 Phase에서 구현 --}}

                <h4 class="mt50">주문자 정보</h4>
                <div class="order_info_card_container">
                    <div class="order_info_card_left">
                        <div class="order_info_form_row">
                            <label>이름<span class="badge_required">필수</span></label>
                            <input type="text" name="order_user_name" value="{{ old('order_user_name', $user->user_name ?? '') }}" class="input_text" required>
                        </div>
                        @php
                            $orderPhoneArr = explode('-', old('order_phone', $user->phone ?? ''));
                            $orderCellphoneArr = explode('-', old('order_cellphone', $user->cellphone ?? ''));
                        @endphp
                        <div class="order_info_form_row" style="margin-top: 15px;">
                            <label>전화번호</label>
                            <input type="text" name="order_phone[]" value="{{ $orderPhoneArr[0] ?? '' }}" style="width: 20%; text-align: center;" class="input_text"> -
                            <input type="text" name="order_phone[]" value="{{ $orderPhoneArr[1] ?? '' }}" style="width: 20%; text-align: center;" class="input_text"> -
                            <input type="text" name="order_phone[]" value="{{ $orderPhoneArr[2] ?? '' }}" style="width: 20%; text-align: center;" class="input_text">
                        </div>
                        <div class="order_info_form_row" style="margin-top: 15px;">
                            <label>휴대전화<span class="badge_required">필수</span></label>
                            <input type="text" name="order_cellphone[]" value="{{ $orderCellphoneArr[0] ?? '' }}" style="width: 20%; text-align: center;" class="input_text" required> -
                            <input type="text" name="order_cellphone[]" value="{{ $orderCellphoneArr[1] ?? '' }}" style="width: 20%; text-align: center;" class="input_text" required> -
                            <input type="text" name="order_cellphone[]" value="{{ $orderCellphoneArr[2] ?? '' }}" style="width: 20%; text-align: center;" class="input_text" required>
                        </div>
                        <div class="order_info_form_row" style="margin-top: 15px;">
                            <label>이메일<span class="badge_required">필수</span></label>
                            <input type="email" name="order_email" value="{{ old('order_email', $user->email ?? '') }}" class="input_text" required>
                        </div>
                    </div>
                    
                    <div class="order_info_card_right">
                        <h3><span style="color: #ff3300; font-size: 16px;">!</span> 주의사항</h3>
                        <ul style="list-style: none; margin: 0; padding: 0; line-height: 1.8; color: #666;">
                            <li style="margin-bottom: 5px; text-indent: -10px; padding-left: 10px;">ㆍ비회원의 주문배송조회를 위한 로그인은 주문번호와 이메일 정보로 확인할 수 있습니다.</li>
                            <li style="margin-bottom: 5px; text-indent: -10px; padding-left: 10px;">ㆍ구매 내역은 이메일과 SMS로 발송됩니다.</li>
                            <li style="text-indent: -10px; padding-left: 10px;">ㆍ정확한 이메일과 휴대폰번호를 입력해 주십시오.</li>
                        </ul>
                    </div>
                </div>

                <h4 class="mt30">배송지 정보 <label><input type="checkbox" id="copy_user_info"> 주문자 정보와 동일</label>
                </h4>
                
                {{-- [LEGACY PARITY] Quick Address Selector (chkQuickAddress) --}}
                @if(auth()->check())
                <div class="order_info_table" style="margin-bottom:10px;">
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>배송지 불러오기</th>
                                <td>
                                    <div style="display: flex; gap: 15px; align-items: center;">
                                        <label><input type="radio" name="chkQuickAddress" value="member" id="chkQuick_member"> 회원정보주소</label>
                                        <label><input type="radio" name="chkQuickAddress" value="often" id="chkQuick_often"> 자주쓰는배송지</label>
                                        <label><input type="radio" name="chkQuickAddress" value="lately" id="chkQuick_lately"> 최근배송지</label>
                                        <label><input type="radio" name="chkQuickAddress" value="new" id="chkQuick_new" checked> 새로운 배송지</label>
                                        
                                        <select name="chkQuickAddressLately" id="chkQuickAddressLately" class="input_text" style="display:none; min-width:180px;">
                                            <option value="">최근 배송지를 선택하세요</option>
                                        </select>
                                        
                                        <button type="button" class="btn_base btn_addr_list" onclick="openAddressModal()">주소록 목록</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @endif
                <div class="order_info_table">
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>수령인 <span class="required">*</span></th>
                                <td><input type="text" name="recipient_user_name" id="recipient_user_name" value="{{ old('recipient_user_name') }}"
                                        class="input_text" required></td>
                            </tr>
                            <tr>
                                <th>전화번호</th>
                                <td>
                                    <input type="text" name="recipient_phone[]" id="recipient_phone_0" style="width: 20%; text-align: center;" class="input_text"> -
                                    <input type="text" name="recipient_phone[]" id="recipient_phone_1" style="width: 20%; text-align: center;" class="input_text"> -
                                    <input type="text" name="recipient_phone[]" id="recipient_phone_2" style="width: 20%; text-align: center;" class="input_text">
                                </td>
                            </tr>
                            <tr>
                                <th>휴대전화 <span class="required">*</span></th>
                                <td>
                                    <input type="text" name="recipient_cellphone[]" id="recipient_cellphone_0" style="width: 20%; text-align: center;" class="input_text" required> -
                                    <input type="text" name="recipient_cellphone[]" id="recipient_cellphone_1" style="width: 20%; text-align: center;" class="input_text" required> -
                                    <input type="text" name="recipient_cellphone[]" id="recipient_cellphone_2" style="width: 20%; text-align: center;" class="input_text" required>
                                </td>
                            </tr>
                            <tr>
                                <th>주소 <span class="required">*</span></th>
                                <td>
                                    <input type="text" name="recipient_new_zipcode" id="recipient_new_zipcode" class="input_text" value="{{ old('recipient_new_zipcode') }}"
                                        style="width: 80px;" placeholder="우편번호" readonly>
                                    <button type="button" class="btn_base" onclick="openDaumPostcode()">우편번호 찾기</button>
                                    <br>

                                    {{-- Visible Address Input (Display Only) --}}
                                    <input type="text" id="recipient_address_display" class="input_text" value="{{ old('recipient_address_type') == 'street' ? old('recipient_address_street') : old('recipient_address') }}"
                                        style="width: 300px; margin-top: 5px;" placeholder="기본주소" readonly>

                                    {{-- Actual Data Inputs --}}
                                    <input type="hidden" name="recipient_address" id="recipient_address" value="{{ old('recipient_address') }}"> {{-- Jibun Address --}}
                                    <input type="hidden" name="recipient_address_street" id="recipient_address_street" value="{{ old('recipient_address_street') }}"> {{-- Road Address --}}
                                    <input type="hidden" name="recipient_address_type" id="recipient_address_type" value="{{ old('recipient_address_type') }}"> {{-- Type: street/zibun --}}

                                    <input type="text" name="recipient_address_detail" id="recipient_address_detail" value="{{ old('recipient_address_detail') }}"
                                        class="input_text" style="width: 200px; margin-top: 5px;" placeholder="상세주소">
                                    
                                    @if(auth()->check())
                                    <div style="margin-top: 5px;">
                                        <label><input type="checkbox" name="save_delivery_address" id="save_delivery_address" value="1"> <span style="color: #0088ff; font-size: 13px;">기본 배송지로 저장</span></label>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            
                            {{-- [LEGACY PARITY] Overseas English Address form --}}
                            <tr id="international_address_row" style="display:none;">
                                <th>해외배송 영문 주소</th>
                                <td>
                                    <div style="margin-bottom: 5px;">
                                        <span style="display:inline-block; width:80px;">English Addr:</span>
                                        <input type="text" name="international_address" id="international_address" class="input_text" style="width: 350px;">
                                    </div>
                                    <div style="margin-bottom: 5px;">
                                        <span style="display:inline-block; width:80px;">Town/City:</span>
                                        <input type="text" name="international_town_city" id="international_town_city" class="input_text" style="width: 150px;">
                                        <span style="margin:0 10px;">County/State:</span>
                                        <input type="text" name="international_county" id="international_county" class="input_text" style="width: 150px;">
                                    </div>
                                    <div>
                                        <span style="display:inline-block; width:80px;">Postcode:</span>
                                        <input type="text" name="international_postcode" id="international_postcode" class="input_text" style="width: 150px;">
                                        <span style="margin:0 10px;">Country:</span>
                                        <input type="text" name="international_country" id="international_country" class="input_text" style="width: 150px;">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>배송메시지</th>
                                <td><input type="text" name="memo" value="{{ old('memo') }}" class="input_text full_width"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4 class="mt30">할인 / 혜택 사용</h4>
                <div class="order_info_table">
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr id="coupon_use_row">
                                <th>쿠폰 사용</th>
                                <td>
                                    <select name="download_seq" id="download_seq" class="input_text" style="min-width: 200px;">
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
                                </td>
                            </tr>
                            <tr id="emoney_use_row">
                                <th>적립금</th>
                                <td>
                                    <input type="number" name="use_emoney_view" id="use_emoney_view" class="input_text" value="{{ old('use_emoney', 0) }}" style="text-align:right; width: 120px;"> 원
                                    <input type="hidden" name="use_emoney" id="use_emoney" value="{{ old('use_emoney', 0) }}">
                                    
                                    <span style="color:#888; margin-left:10px;">(보유: <strong>{{ number_format($user->emoney ?? 0) }}</strong>원)</span>
                                    
                                    <span class="emoney_input_button" style="margin-left:5px;"><button type="button" class="btn_base" onclick="useEmoneyBtn()">입력</button></span>
                                    <span class="emoney_all_input_button" style="margin-left:5px;"><button type="button" class="btn_base" onclick="useAllEmoneyBtn()">전액사용</button></span>
                                    <span class="emoney_cancel_button" style="margin-left:5px; display:none;"><button type="button" class="btn_base" onclick="cancelEmoneyBtn()">초기화</button></span>
                                    
                                    @if(isset($errReserve) && $errReserve)
                                        <div style="color: #d00; font-size:11px; margin-top:4px;">※ {{ $errReserve }}</div>
                                    @else
                                        <div style="color: #666; font-size:11px; margin-top:4px;">※ 적립금은 100원 이상 보유 시, 최소 100원부터 사용 가능합니다.</div>
                                    @endif
                                </td>
                            </tr>
                            <tr id="cash_use_row">
                                <th>캐시</th>
                                <td>
                                    <input type="number" name="use_cash_view" id="use_cash_view" class="input_text" value="{{ old('use_cash', 0) }}" style="text-align:right; width: 120px;"> 원
                                    <input type="hidden" name="use_cash" id="use_cash" value="{{ old('use_cash', 0) }}">
                                    
                                    <span style="color:#888; margin-left:10px;">(보유: <strong>{{ number_format($user->cash ?? 0) }}</strong>원)</span>
                                    
                                    <span class="cash_input_button" style="margin-left:5px;"><button type="button" class="btn_base" onclick="useCashBtn()">입력</button></span>
                                    <span class="cash_all_input_button" style="margin-left:5px;"><button type="button" class="btn_base" onclick="useAllCashBtn()">전액사용</button></span>
                                    <span class="cash_cancel_button" style="margin-left:5px; display:none;"><button type="button" class="btn_base" onclick="cancelCashBtn()">초기화</button></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="order_info_table">
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>결제 방법</th>
                                <td>
                                    <label><input type="radio" name="payment" value="bank" {{ old('payment', 'bank') == 'bank' ? 'checked' : '' }}> 무통장 입금</label>
                                    <label style="margin-left: 20px;" id="card_payment_label"><input type="radio" name="payment" value="card" {{ old('payment') == 'card' ? 'checked' : '' }}> 신용카드</label>
                                </td>
                            </tr>
                            <tr id="bank_info_row">
                                <th>입금 계좌</th>
                                <td>
                                    <select name="bank_account" class="input_text">
                                        <option value="국민은행 123-456-7890 도매토피아" {{ old('bank_account') == '국민은행 123-456-7890 도매토피아' ? 'selected' : '' }}>국민은행 123-456-7890 도매토피아</option>
                                        <option value="농협 098-765-4321 도매토피아" {{ old('bank_account') == '농협 098-765-4321 도매토피아' ? 'selected' : '' }}>농협 098-765-4321 도매토피아</option>
                                    </select>
                                    <input type="text" name="depositor" value="{{ old('depositor') }}" class="input_text" placeholder="입금자명">
                                </td>
                            </tr>
                            
                            {{-- [LEGACY PARITY] Bank Refund Account Form (Shown for Bank, virtual, account payments) --}}
                            <tr id="refund_info_row" style="display:none;">
                                <th>환불시 입금 정보 입력<br>(선택사항)</th>
                                <td>
                                    <div style="margin-bottom: 5px;">
                                        <span style="display:inline-block; width:100px;">입금자명(예금주)</span> 
                                        <input type="text" name="refund_name" id="refund_name" value="{{ old('refund_name') }}" class="input_text" style="width:150px;">
                                    </div>
                                    <div style="margin-bottom: 5px;">
                                        <span style="display:inline-block; width:100px;">입금은행</span> 
                                        <input type="text" name="refund_bank" id="refund_bank" value="{{ old('refund_bank') }}" class="input_text" style="width:150px;">
                                    </div>
                                    <div>
                                        <span style="display:inline-block; width:100px;">입금계좌</span> 
                                        <input type="text" name="refund_acount" id="refund_acount" value="{{ old('refund_acount') }}" class="input_text" style="width:250px;" placeholder="'-'없이 입력">
                                        <span style="color:#888; font-size:11px; margin-left:10px;">( 하이픈 [ - ] 없이 입력 )</span>
                                    </div>
                                    <div style="margin-top:5px; color:#555; font-size:11px;">
                                        ※ 환불은 작성하신 계좌로 입금 됩니다. 입금자명 및 계좌를 정확히 입력해주세요.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- [LEGACY PARITY] Tax Invoice & Cash Receipt Form Area --}}
                <div class="order_info_table mt30" id="receipt_request_table">
                    <h4 style="margin-bottom:10px;">증빙 서류 신청</h4>
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr id="typereceipt_selection_row">
                                <th>신청 선택</th>
                                <td>
                                    <label><input type="radio" name="typereceipt" value="0" {{ old('typereceipt', '0') == '0' ? 'checked' : '' }} onclick="toggleReceipt(0)"> 신청안함</label>
                                    <label class="ml10" id="tax_invoice_label"><input type="radio" name="typereceipt" value="1" {{ old('typereceipt') == '1' ? 'checked' : '' }} onclick="toggleReceipt(1)"> 세금계산서</label>
                                    <label class="ml10" id="cash_receipt_label"><input type="radio" name="typereceipt" value="2" {{ old('typereceipt') == '2' ? 'checked' : '' }} onclick="toggleReceipt(2)"> 현금영수증</label>
                                    <span id="tax_exempt_warning" style="color:#d00; font-size:12px; margin-left:10px; display:none;">(비과세 상품 포함시 증빙서류 발급 불가)</span>
                                </td>
                            </tr>
                            <tr id="receipt_form_row" class="hide">
                                <th>정보 입력</th>
                                <td>
                                    {{-- Tax Invoice Form (co_new_zipcode, co_address, co_address_street, co_address_detail, person, phone, email) --}}
                                    <div id="tax_form" class="hide">
                                        <div class="receipt_row"><span class="label">상호명</span> <input type="text" name="co_name" id="co_name" value="{{ old('co_name') }}" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">사업자번호</span> <input type="text" name="busi_no" id="busi_no" value="{{ old('busi_no') }}" class="input_text" placeholder="'-'없이 입력"></div>
                                        <div class="receipt_row"><span class="label">대표자명</span> <input type="text" name="co_ceo" id="co_ceo" value="{{ old('co_ceo') }}" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">업태</span> <input type="text" name="co_status" id="co_status" value="{{ old('co_status') }}" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">종목</span> <input type="text" name="co_type" id="co_type" value="{{ old('co_type') }}" class="input_text"></div>
                                        
                                        <div class="receipt_row">
                                            <span class="label">사업장주소</span>
                                            <button type="button" class="btn_base" onclick="openTaxDaumPostcode()">우편번호 찾기</button>
                                            <input type="text" name="co_new_zipcode" id="co_new_zipcode" value="{{ old('co_new_zipcode') }}" class="input_text" style="width: 100px; margin-left:5px;" readonly>
                                        </div>
                                        <div class="receipt_row">
                                            <span class="label" style="visibility:hidden;">주소</span>
                                            <input type="text" name="co_address" id="co_address" value="{{ old('co_address') }}" class="input_text" style="width:300px;" placeholder="기본주소(지번)" readonly>
                                            <input type="text" name="co_address_street" id="co_address_street" value="{{ old('co_address_street') }}" class="input_text hide" style="width:300px;" placeholder="도로명주소" readonly>
                                            <input type="hidden" name="co_address_type" id="co_address_type" value="{{ old('co_address_type', 'zibun') }}">
                                        </div>
                                        <div class="receipt_row">
                                            <span class="label" style="visibility:hidden;">상세주소</span>
                                            <input type="text" name="co_address_detail" id="co_address_detail" value="{{ old('co_address_detail') }}" class="input_text" style="width:300px;" placeholder="상세주소">
                                        </div>
                                        
                                        <div style="margin-top:10px; border-top:1px dashed #ddd; padding-top:10px;">
                                            <div class="receipt_row"><span class="label">담당자명</span> <input type="text" name="person" id="person" value="{{ old('person') }}" class="input_text"></div>
                                            <div class="receipt_row"><span class="label">연락처</span> <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="input_text"></div>
                                            <div class="receipt_row"><span class="label">이메일</span> <input type="text" name="email" id="email" value="{{ old('email') }}" class="input_text"></div>
                                        </div>
                                    </div>
                                    
                                    {{-- Cash Receipt Form --}}
                                    <div id="cash_form" class="hide">
                                        <div class="receipt_row">
                                            <label><input type="radio" name="cuse" value="0" {{ old('cuse', '0') == '0' ? 'checked' : '' }} onclick="toggleCashReceiptType(0)"> 개인소득공제용</label>
                                            <label class="ml10"><input type="radio" name="cuse" value="1" {{ old('cuse') == '1' ? 'checked' : '' }} onclick="toggleCashReceiptType(1)"> 사업자지출증빙용</label>
                                        </div>
                                        <div class="receipt_row mt5" id="personal_receipt_row">
                                            <span class="label">휴대폰번호</span> 
                                            <input type="text" name="creceipt_number[]" value="{{ old('creceipt_number') ? (old('creceipt_number')[0] ?? '') : '' }}" class="input_text" placeholder="'-'없이 입력">
                                        </div>
                                        <div class="receipt_row mt5" id="business_receipt_row" style="display:none;">
                                            <span class="label">사업자번호</span> 
                                            <input type="text" name="creceipt_number[]" value="{{ old('creceipt_number') ? (old('creceipt_number')[1] ?? '') : '' }}" class="input_text" placeholder="'-'없이 입력">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>



                {{-- Phase 1: Agreements Section --}}
                <div class="agreement_area">
                
                @if(true)
                <div class="agreement_grid_container">
                    {{-- Row 1: Shipping Policy & Cancellation Policy --}}
                    {{-- Item 1: Shipping Policy --}}
                    <div class="agreement_item agreement-full-width">
                         <div style="margin-bottom:10px;">
                            <label><input type="checkbox" name="delivery_chk"> <span style="font-weight:bold; color:#d00;">배송비 정책에 동의합니다</span></label>
                        </div>
                        <div class="shipping-agree-text">
                            <ul style="list-style: none; padding-left: 0; font-size: 13px; color: #555;">
                                <li style="margin-bottom: 5px;">1. 배송비는 15만원 이상 구매하면 무료입니다.</li>
                                <li style="margin-bottom: 5px;">2. 선불/착불 선택 가능하며 선불은 기본 1박스만 선결제 됩니다.</li>
                                <li style="margin-bottom: 5px;">3. 1박스를 선불로 결제하더라도 추가 박스 발생시 착불로 배송됩니다.<br>(전량 선불 결제를 원할 시 고객센터로 연락주세요.)</li>
                                <li>4. 궁금한 점은 고객센터로 연락주시기 바랍니다.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Item 2: Cancellation Policy --}}
                    <div class="agreement_item">
                        <h6 class="fx20">청약철회 관련 방침</h6>
                        <div class="agreement_box" contenteditable="false">
                            1. 반품/교환 사유에 따른 요청 가능 기간<br>
                            반품 시 먼저 판매자와 연락하셔서 반품사유, 택배사, 배송비, 반품지 주소 등을 협의하신 후 반품상품을 발송해 주시기 바랍니다.<br>
                            구매자 단순변심 : 상품 수령 후 7일 이내 (구매자 반품 배송비 부담)<br>
                            표시/광고와 상이, 상품 하자 : 상품 수령 후 3개월 이내 혹은 표시/광고와 다른 사실을 안 날로부터 30일 이내 (판매자 반품 배송비 부담) 둘 중 하나 경과 시 반품/교환 불가<br><br>
                            2. 반품/교환 불가능 사유<br>
                            - 반품 요청 기간이 지난 경우<br>
                            - 구매자의 책임 있는 사유로 상품 등이 멸실 또는 훼손된 경우 (단, 상품의 내용을 확인하기 위하여 포장 등을 훼손한 경우는 제외)<br>
                            - 포장을 개봉하였으나 포장이 훼손되어 상품가치가 현저히 상실된 경우<br>
                            - 구매자의 사용 또는 일부 소비에 의하여 상품의 가치가 현저히 감소한 경우<br>
                            - 시간의 경과에 의하여 재판매가 곤란할 정도로 상품 등의 가치가 현저히 감소한 경우<br>
                            - 고객주문 확인 후 상품제작에 들어가는 주문제작상품 (판매자에게 회복 불가능한 손해가 예상되고, 그러한 예정으로 청약철회권 행사가 불가하다는 사실을 서면 동의 받은 경우)<br>
                            - 복제가 가능한 상품 등의 포장을 훼손한 경우<br>
                        </div>
                        <div class="agreement_check center">
                            <label><input type="radio" name="cancellation" value="Y"> 동의함</label>
                            <label class="ml10"><input type="radio" name="cancellation" value="N" checked> 동의안함</label>
                        </div>
                    </div>
                    
                    {{-- Row 2: Privacy Agreement (Mandatory & Optional) --}}
                    @if(!auth()->check())
                    {{-- Item 3: Non-member Mandatory Privacy Agreement --}}
                    <div class="agreement_item">
                        <h6 class="fx20">비회원 개인정보 동의(필수)</h6>
                        <div class="agreement_box" contenteditable="false">
                            도매토피아는 회원님께 최대한으로 최적화되고 맞춤화된 서비스를 제공하기 위하여 다음과 같은 목적으로 개인정보를 수집하고 있습니다.
                            @if(isset($Overseas) && $Overseas == 'Y')
                            <table class="privacy_table">
                                <tr align="center">
                                    <th>목적</th>
                                    <th>항목</th>
                                    <th>보유기간</th>
                                </tr>
                                <tr align="center">
                                    <td>상품 주문내역 안내, 상품배송</td>
                                    <td>이름, 핸드폰번호, 주문자 이메일, 받는분 성함, 받는분 핸드폰, 받는분 주소</td>
                                    <td>개인정보는 서비스 제공 기간동안 보유 및 이용하며, 탈퇴 시 즉시 파기됩니다.</td>
                                </tr>
                                <tr align="center">
                                    <td>해외 직배송 상품 통관업무처리</td>
                                    <td>개인통관번호</td>
                                    <td>개인정보는 서비스 제공 기간동안 보유 및 이용하며, 탈퇴 시 즉시 파기됩니다.</td>
                                </tr>
                            </table>
                            @else
                            <table class="privacy_table">
                                <tr align="center">
                                    <th>목적</th>
                                    <th>항목</th>
                                    <th>보유기간</th>
                                </tr>
                                <tr align="center">
                                    <td>상품 주문내역 안내, 상품배송</td>
                                    <td>주문자 정보(전화번호) 배송지정보(이름, 핸드폰, 주소)</td>
                                    <td>개인정보는 서비스 제공 기간동안 보유 및 이용하며, 탈퇴 시 즉시 파기됩니다.</td>
                                </tr>
                            </table>
                            @endif
                        </div>
                        <div class="agreement_check center">
                            <label><input type="radio" name="privacy_agree" value="Y"> 개인정보 수집ㆍ이용에 동의</label>
                            <label class="ml10"><input type="radio" name="privacy_agree" value="N" checked> 개인정보 수집ㆍ이용에 동의하지 않음</label>
                        </div>
                    </div>

                    {{-- Item 4: Non-member Optional Privacy Agreement (Removed) --}}
                    @endif
                </div>
                {{-- End Grid Container --}}
                @endif
                </div>
                {{-- End Agreement Area --}}

                <div class="btn_area_center">
                    <button type="submit" class="btn_order_all">결제하기</button>
                    <a href="{{ route('cart.index') }}" class="btn_cancel">취소</a>
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

    document.querySelector('.final_price').innerText = new Intl.NumberFormat().format(finalPrice);
    
    const totalShipping = {{ $shipping + $packagingCost }} + extraShippingCost;
    document.getElementById('total_shipping_display_text').innerText = new Intl.NumberFormat().format(totalShipping);
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
        row.style.display = 'table-row';
        taxForm.style.display = 'block';
        cashForm.style.display = 'none';
    } else if (type == 2) { 
        row.style.display = 'table-row';
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
    const cancelAgree = document.querySelector('input[name="cancellation"]:checked');
    if (!cancelAgree || cancelAgree.value !== 'Y') {
        alert('청약철회 관련 방침에 동의하셔야 합니다.');
        e.preventDefault();
        return false;
    }
    const privacyAgree = document.querySelector('input[name="privacy_agree"]:checked');
    if (privacyAgree && privacyAgree.value !== 'Y') {
        alert('비회원 개인정보 수집 이용에 동의하셔야 합니다.');
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