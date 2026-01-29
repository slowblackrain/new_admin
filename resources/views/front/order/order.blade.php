@extends('layouts.front')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="/css/order.css?v={{ time() }}">
    @endpush
    <div class="order_header_v2">
        <div class="order_header_inner clearbox">
            <!-- Left: Title with Icon -->
            <div class="title_area">
                <h2>주문/결제<i><img src="/images/icon/order_card.png" alt="Card Icon"></i></h2>
            </div>
            
            <!-- Right: Step Indicator -->
            <div class="step_area">
                <ul>
                    <li><span class="num">1</span> <span class="txt">장바구니</span></li>
                    <li class="on"><span class="num">2</span> <span class="txt">주문/결제</span></li>
                    <li><span class="num">3</span> <span class="txt">주문완료</span></li>
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

        <div class="cart_list_area">
            <h4>주문 리스트 확인</h4>
            <table class="cart_table">
                <colgroup>
                    <col width="100" />
                    <col width="*" />
                    <col width="80" />
                    <col width="100" />
                    <col width="100" />
                    <col width="100" />
                    <col width="100" />
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">이미지</th> {{-- No Text in Screenshot --}}
                        <th scope="col">주문상품</th>
                        <th scope="col"><span class="icon_sq_n">N</span> 수량</th>
                        <th scope="col"><span class="icon_sq_w">W</span> 단가</th>
                        <th scope="col"><span class="icon_sq_p">%</span> 할인</th>
                        <th scope="col"><span class="icon_sq_w">W</span> 주문금액</th>
                        <th scope="col"><span class="icon_sq_s">S</span> 배송비</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- $finalPrice, $totalCheckPrice come from controller --}}
                    @foreach($cartItems as $item)
                        @php
                            $goods = $item->goods;
                            $option = $item->options->first();
                            $price = $goods->option->first()->price ?? 0;
                            $ea = $option->ea ?? 1;
                            $itemPrice = $price * $ea;

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
                            <td class="img_cell">
                                <img src="{{ $imgSrc }}" alt="{{ $goods->goods_name }}" width="60">
                            </td>
                            <td class="info_cell">
                                <div class="g_name">{{ $goods->goods_name }}</div>
                                <div class="g_opt">옵션: {{ $option->option1 ?? '기본' }}</div>
                            </td>
                            <td>{{ $ea }}</td>
                            <td>{{ number_format($price) }}원</td>
                            <td>-</td> {{-- Discount Placeholder --}}
                            <td class="price_bold">{{ number_format($itemPrice) }}원</td>
                            <td>기본배송</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="cart_total_area_legacy">
                <div class="total_left">
                    <div class="total_row">
                        <span class="th">총 상품</span>
                        <span class="td"><strong>{{ count($cartItems) }}</strong></span>
                    </div>
                    <div class="total_row">
                        <span class="th">총 수량</span>
                        <span class="td"><strong>{{ $cartItems->sum('ea') }}</strong></span>
                    </div>
                </div>
                <div class="total_right">
                    <div class="calc_box">
                        <span class="item">총 상품 금액: <strong>{{ number_format($totalPrice) }}</strong></span>
                        <span class="op plus">+</span>
                        <span class="item">배송비: <strong>{{ number_format($shipping) }}</strong></span>
                        <span class="op minus">-</span>
                        <span class="item">총 할인: <strong>0</strong></span>
                        <span class="op plus">+</span>
                        <span class="item">총 부가세: <strong>{{ number_format($tax) }}</strong> (면세)</span>
                        <span class="item ml10">예상포인트: 0</span>
                        <span class="op equal">=</span>
                        <span class="item total">총 결제 금액: <strong>{{ number_format($finalPrice) }}</strong></span>
                    </div>
                </div>
            </div>

            <form name="orderForm" id="orderForm" method="post" action="{{ route('order.store') }}">
                @csrf
                {{-- 주문서 처리 로직은 다음 Phase에서 구현 --}}

                <h4 class="mt50">주문자 정보</h4>
                <div class="order_info_table">
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>주문자명 <span class="required">*</span></th>
                                <td><input type="text" name="order_user_name" value="{{ $user->user_name ?? '' }}"
                                        class="input_text" required></td>
                            </tr>
                            <tr>
                                <th>전화번호</th>
                                <td><input type="text" name="order_phone" value="{{ $user->phone ?? '' }}" class="input_text"></td>
                            </tr>
                            <tr>
                                <th>휴대전화 <span class="required">*</span></th>
                                <td><input type="text" name="order_cellphone" value="{{ $user->cellphone ?? '' }}"
                                        class="input_text" required></td>
                            </tr>
                            <tr>
                                <th>이메일 <span class="required">*</span></th>
                                <td><input type="email" name="order_email" value="{{ $user->email ?? '' }}"
                                        class="input_text" required></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if(!auth()->check())
                <div class="doto-order-info-alert">
                    <h3>주의사항</h3>
                    <ul>
                        <li>ㆍ비회원의 주문배송조회를 위한 로그인은 주문번호와 이메일 정보로 확인할 수 있습니다.</li>
                        <li>ㆍ구매 내역은 이메일과 SMS로 발송됩니다.</li>
                        <li>ㆍ정확한 이메일과 휴대폰번호를 입력해 주십시오.</li>
                    </ul>
                </div>
                @endif

                <h4 class="mt30">배송지 정보 <label><input type="checkbox" id="copy_user_info"> 주문자 정보와 동일</label>
                </h4>
                <div class="order_info_table">
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>수령인 <span class="required">*</span></th>
                                <td><input type="text" name="recipient_user_name" id="recipient_user_name"
                                        class="input_text" required></td>
                            </tr>
                            <tr>
                                <th>전화번호</th>
                                <td><input type="text" name="recipient_phone" id="recipient_phone" class="input_text"></td>
                            </tr>
                            <tr>
                                <th>휴대전화 <span class="required">*</span></th>
                                <td><input type="text" name="recipient_cellphone" id="recipient_cellphone"
                                        class="input_text" required></td>
                            </tr>
                            <tr>
                                <th>주소 <span class="required">*</span></th>
                                <td>
                                    <input type="text" name="recipient_zipcode" id="recipient_zipcode" class="input_text"
                                        style="width: 80px;" placeholder="우편번호" readonly>
                                    <button type="button" class="btn_base" onclick="openDaumPostcode()">우편번호 찾기</button>
                                    <button type="button" class="btn_base btn_addr_list" onclick="openAddressModal()">배송지 목록</button><br>

                                    {{-- Visible Address Input (Display Only) --}}
                                    <input type="text" id="recipient_address_display" class="input_text"
                                        style="width: 300px; margin-top: 5px;" placeholder="기본주소" readonly>

                                    {{-- Actual Data Inputs --}}
                                    <input type="hidden" name="recipient_address" id="recipient_address"> {{-- Jibun Address
                                    --}}
                                    <input type="hidden" name="recipient_address_street" id="recipient_address_street"> {{--
                                    Road Address --}}
                                    <input type="hidden" name="recipient_address_type" id="recipient_address_type"> {{--
                                    Type: street/zibun --}}

                                    <input type="text" name="recipient_address_detail" id="recipient_address_detail"
                                        class="input_text" style="width: 200px; margin-top: 5px;" placeholder="상세주소">
                                </td>
                            </tr>
                            <tr>
                                <th>배송메시지</th>
                                <td><input type="text" name="memo" class="input_text full_width"></td>
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
                            <tr>
                                <th>쿠폰 사용</th>
                                <td>
                                    <select name="download_seq" id="download_seq" class="input_text" style="min-width: 200px;">
                                        <option value="">쿠폰을 선택하세요</option>
                                        @foreach($coupons as $coupon)
                                            <option value="{{ $coupon->download_seq }}" 
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
                            <tr>
                                <th>예치금</th>
                                <td>
                                    <input type="number" name="use_emoney" id="use_emoney" class="input_text" value="0" style="text-align:right;"> 원
                                    <span style="color:#888; margin-left:10px;">(보유: <strong>{{ number_format($user->emoney ?? 0) }}</strong>원)</span>
                                    <button type="button" class="btn_base" onclick="useAll('emoney', {{ $user->emoney ?? 0 }})">전액사용</button>
                                </td>
                            </tr>
                            <tr>
                                <th>포인트</th>
                                <td>
                                    <input type="number" name="use_point" id="use_point" class="input_text" value="0" style="text-align:right;"> P
                                    <span style="color:#888; margin-left:10px;">(보유: <strong>{{ number_format($user->point ?? 0) }}</strong>P)</span>
                                    <button type="button" class="btn_base" onclick="useAll('point', {{ $user->point ?? 0 }})">전액사용</button>
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
                                    <label><input type="radio" name="payment" value="bank" checked> 무통장 입금</label>
                                    <label style="margin-left: 20px;"><input type="radio" name="payment" value="card">
                                        신용카드</label>
                                </td>
                            </tr>
                            <tr id="bank_info_row">
                                <th>입금 계좌</th>
                                <td>
                                    <select name="bank_account" class="input_text">
                                        <option value="국민은행 123-456-7890 도매토피아">국민은행 123-456-7890 도매토피아</option>
                                        <option value="농협 098-765-4321 도매토피아">농협 098-765-4321 도매토피아</option>
                                    </select>
                                    <input type="text" name="depositor" class="input_text" placeholder="입금자명">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Phase 1: Receipt Request Section --}}
                <div class="order_info_table mt30">
                    <h4 style="margin-bottom:10px;">증빙 서류 신청</h4>
                    <table class="form_table">
                        <colgroup>
                            <col width="150" />
                            <col width="*" />
                        </colgroup>
                        <tbody>
                            <tr>
                                <th>신청 선택</th>
                                <td>
                                    <label><input type="radio" name="typereceipt" value="0" checked onclick="toggleReceipt(0)"> 신청안함</label>
                                    <label class="ml10"><input type="radio" name="typereceipt" value="1" onclick="toggleReceipt(1)"> 세금계산서</label>
                                    <label class="ml10"><input type="radio" name="typereceipt" value="2" onclick="toggleReceipt(2)"> 현금영수증</label>
                                </td>
                            </tr>
                            <tr id="receipt_form_row" class="hide">
                                <th>정보 입력</th>
                                <td>
                                    {{-- Tax Invoice Form --}}
                                    <div id="tax_form" class="hide">
                                        <div class="receipt_row"><span class="label">상호명</span> <input type="text" name="co_name" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">사업자번호</span> <input type="text" name="busi_no" class="input_text" placeholder="'-'없이 입력"></div>
                                        <div class="receipt_row"><span class="label">대표자명</span> <input type="text" name="co_ceo" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">업태</span> <input type="text" name="co_status" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">종목</span> <input type="text" name="co_type" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">담당자명</span> <input type="text" name="tax_person" class="input_text"></div>
                                        <div class="receipt_row"><span class="label">이메일</span> <input type="text" name="tax_email" class="input_text"></div>
                                    </div>
                                    {{-- Cash Receipt Form --}}
                                    <div id="cash_form" class="hide">
                                        <div class="receipt_row">
                                            <label><input type="radio" name="cuse" value="0" checked> 개인소득공제용</label>
                                            <label class="ml10"><input type="radio" name="cuse" value="1"> 사업자지출증빙용</label>
                                        </div>
                                        <div class="receipt_row mt5">
                                            <span class="label">휴대폰/번호</span> <input type="text" name="cash_receipt_number" class="input_text" placeholder="'-'없이 입력">
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
            <div class="modal_body">
                <ul id="addressList" class="address_list">
                    <!-- Loaded via AJAX -->
                </ul>
            </div>
        </div>
    </div>

    {{-- Daum Address API --}}
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
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
                } else {
                    bankInfoRow.style.display = 'none';
                }
            });
        });

        // Address Modal Functions
        function openAddressModal() {
            const listEl = document.getElementById('addressList');
            listEl.innerHTML = '<li>로딩중...</li>';
            document.getElementById('addressModal').style.display = 'block';

            fetch("{{ route('mypage.delivery_address.index') }}")
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
                            li.innerHTML = `
                                <div class="addr_item" onclick='selectAddress(${JSON.stringify(addr)})'>
                                    <strong>${addr.recipient_user_name}</strong>
                                    <span>[${addr.address_group || '기본'}]</span>
                                    <p>${addr.recipient_address} ${addr.recipient_address_detail || ''}</p>
                                    <p>${addr.recipient_cellphone}</p>
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
            document.getElementById('recipient_cellphone').value = addr.recipient_cellphone;
            document.getElementById('recipient_zipcode').value = addr.recipient_zipcode;
            document.getElementById('recipient_address').value = addr.recipient_address;
            document.getElementById('recipient_address_display').value = addr.recipient_address;
            document.getElementById('recipient_address_detail').value = addr.recipient_address_detail;
            document.getElementById('recipient_address_street').value = addr.recipient_address_street || '';
            document.getElementById('recipient_address_type').value = addr.recipient_address_type || 'zibun';
            
            closeAddressModal();
        }

        // Initial PHP values passing to JS
        const initialFinalPrice = {{ $totalPrice + $shipping + $packagingCost + $tax }};
        const initialGoodsPrice = {{ $totalPrice }};
        const maxEmoney = {{ $user->emoney ?? 0 }};
        const maxPoint = {{ $user->point ?? 0 }};

        function useAll(type, amount) {
            const input = document.getElementById('use_' + type);
            const currentTotal = calculateCurrentTotal(type);
            let useAmount = amount;
            if (amount > currentTotal) useAmount = currentTotal;
            
            input.value = useAmount;
            updateFinalPrice();
        }

        function calculateCurrentTotal(excludeType) {
            // Recalculate everything to be safe
            // Base - Coupon - (Other Points)
            let total = initialFinalPrice - calculateCouponDiscount();
            
            if (excludeType !== 'emoney') total -= parseInt(document.getElementById('use_emoney').value || 0);
            if (excludeType !== 'point') total -= parseInt(document.getElementById('use_point').value || 0);
            return total;
        }

        function calculateCouponDiscount() {
            const select = document.getElementById('download_seq');
            const option = select.options[select.selectedIndex];
            if (!option.value) return 0;

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
            
            // Cannot exceed total price (or goods price? usually goods price but settlement price cap in controller)
            // Let's cap at initialFinalPrice for simplicity in UI
            if (discount > initialFinalPrice) discount = initialFinalPrice;
            
            return discount;
        }

        function updateFinalPrice() {
            let useEmoney = parseInt(document.getElementById('use_emoney').value || 0);
            let usePoint = parseInt(document.getElementById('use_point').value || 0);
            let couponDiscount = calculateCouponDiscount();

            // Display Coupon Discount
            if (couponDiscount > 0) {
                 document.getElementById('coupon_discount_display').innerText = '-' + new Intl.NumberFormat().format(couponDiscount) + '원';
            } else {
                 document.getElementById('coupon_discount_display').innerText = '';
            }

            // Available total for points is (Final - Coupon)
            let availableForPoints = initialFinalPrice - couponDiscount;

            // Re-validate points against new available total
            if (useEmoney > availableForPoints) {
                 useEmoney = availableForPoints;
                 document.getElementById('use_emoney').value = useEmoney;
            }
            availableForPoints -= useEmoney;

            if (usePoint > availableForPoints) {
                 usePoint = availableForPoints;
                 document.getElementById('use_point').value = usePoint;
            }

            // Validation Max Holding
            if (useEmoney > maxEmoney) {
                alert('보유 예치금을 초과할 수 없습니다.');
                useEmoney = maxEmoney;
                document.getElementById('use_emoney').value = useEmoney;
            }
            if (usePoint > maxPoint) {
                alert('보유 포인트를 초과할 수 없습니다.');
                usePoint = maxPoint;
                document.getElementById('use_point').value = usePoint;
            }

            let finalPrice = initialFinalPrice - couponDiscount - useEmoney - usePoint;

            if (finalPrice < 0) finalPrice = 0;

            document.querySelector('.final_price').innerText = new Intl.NumberFormat().format(finalPrice) + '원';
        }

        document.getElementById('use_emoney').addEventListener('change', updateFinalPrice);
        document.getElementById('use_point').addEventListener('change', updateFinalPrice);
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
            } else if (type == 1) { // Tax Invoice
                row.style.display = 'table-row';
                taxForm.style.display = 'block';
                cashForm.style.display = 'none';
            } else if (type == 2) { // Cash Receipt
                row.style.display = 'table-row';
                taxForm.style.display = 'none';
                cashForm.style.display = 'block';
            }
        }

        // Validate Agreements on Submit
        document.getElementById('orderForm').addEventListener('submit', function(e) {
            // Shipping Policy
            if (!document.querySelector('input[name="delivery_chk"]:checked')) {
                alert('배송비 정책에 동의하셔야 합니다.');
                e.preventDefault();
                return false;
            }
            // Cancellation Policy
            const cancelAgree = document.querySelector('input[name="cancellation"]:checked');
            if (!cancelAgree || cancelAgree.value !== 'Y') {
                alert('청약철회 관련 방침에 동의하셔야 합니다.');
                e.preventDefault();
                return false;
            }
            // Privacy Policy (Non-member)
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
                    // 팝업에서 검색결과 항목을 클릭했을때 실행할 코드를 작성하는 부분.

                    // 각 주소의 노출 규칙에 따라 주소를 조합한다.
                    // 내려오는 변수가 값이 없는 경우엔 공백('')값을 가지므로, 이를 참고하여 분기 한다.
                    var addr = ''; // 주소 변수
                    var extraAddr = ''; // 참고항목 변수

                    //사용자가 선택한 주소 타입에 따라 해당 주소 값을 가져온다.
                    if (data.userSelectedType === 'R') { // 사용자가 도로명 주소를 선택했을 경우
                        addr = data.roadAddress;
                        document.getElementById('recipient_address_type').value = 'street';
                    } else { // 사용자가 지번 주소를 선택했을 경우(J)
                        addr = data.jibunAddress;
                        document.getElementById('recipient_address_type').value = 'zibun';
                    }

                    // 우편번호와 주소 정보를 해당 필드에 넣는다.
                    document.getElementById('recipient_zipcode').value = data.zonecode;
                    document.getElementById("recipient_address_display").value = addr; // Show selected address

                    // Save detailed address data
                    document.getElementById("recipient_address").value = data.jibunAddress || data.autoJibunAddress || addr; // Always try to save Jibun
                    document.getElementById("recipient_address_street").value = data.roadAddress || data.autoRoadAddress || ''; // Always try to save Road

                    // 커서를 상세주소 필드로 이동한다.
                    document.getElementById("recipient_address_detail").focus();
                }
            }).open();
        }
    </script>

@endsection