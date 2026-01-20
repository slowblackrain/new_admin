@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 주문서 작성</em>
        </div>
    </div>

    <div class="content_wrap">
        <div class="cart_title_area">
            <h3>주문서 작성</h3>
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
            <h4>주문 상품 정보</h4>
            <table class="cart_table">
                <colgroup>
                    <col width="100" />
                    <col width="*" />
                    <col width="100" />
                    <col width="100" />
                    <col width="100" />
                    <col width="100" />
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">이미지</th>
                        <th scope="col">상품정보</th>
                        <th scope="col">수량</th>
                        <th scope="col">상품금액</th>
                        <th scope="col">배송비</th>
                        <th scope="col">합계</th>
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
                            $imgSrc = $mainImage ? '/data/goods/' . $mainImage->image : '/images/no_image.gif';
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
                            <td>기본배송</td>
                            <td class="price_bold">{{ number_format($itemPrice) }}원</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form name="orderForm" id="orderForm" method="post" action="{{ route('order.store') }}">
                @csrf
                {{-- 주문서 처리 로직은 다음 Phase에서 구현 --}}

                <h4 style="margin-top: 50px;">주문자 정보</h4>
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

                <h4 style="margin-top: 30px;">배송지 정보 <label><input type="checkbox" id="copy_user_info"> 주문자 정보와 동일</label>
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

                <h4 style="margin-top: 30px;">결제 정보</h4>
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

                <div class="cart_total_area" style="margin-top: 50px;">
                    <div class="total_box">
                        <span>상품금액 <strong>{{ number_format($totalPrice) }}원</strong></span>
                        <span class="plus" style="margin:0 10px;">+</span>
                        <span>부가세 <strong>{{ number_format($tax) }}원</strong></span>
                        <span class="equal" style="margin:0 10px;">=</span>
                        <span>총 결제금액 <strong class="final_price"
                                style="color:#d00; font-size:24px;">{{ number_format($finalPrice) }}원</strong></span>
                    </div>
                </div>

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

    <style>
        .cart_table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #333;
            margin-bottom: 20px;
        }

        .cart_table th {
            background: #f9f9f9;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .cart_table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        .cart_table .info_cell {
            text-align: left;
        }

        .form_table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #ddd;
        }

        .form_table th {
            background: #f9f9f9;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-weight: normal;
        }

        .form_table td {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
        }

        .input_text {
            height: 30px;
            border: 1px solid #ccc;
            padding: 0 5px;
        }

        .full_width {
            width: 98%;
        }

        .required {
            color: #d00;
        }

        .btn_area_center {
            margin: 40px 0;
            text-align: center;
        }

        .btn_order_all {
            padding: 15px 50px;
            background: #d00;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        .btn_cancel {
            padding: 15px 50px;
            background: #666;
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            margin-left: 10px;
        }

        .btn_base {
            padding: 5px 10px;
            background: #333;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .btn_addr_list {
            background: #007bff;
            margin-left: 5px;
        }

        /* Modal Styles */
        .modal_overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        }
        .modal_content {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .modal_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .btn_close_modal {
            background: none; border: none; font-size: 20px; cursor: pointer;
        }
        .address_list {
            list-style: none; padding: 0; margin: 0;
            max-height: 300px; overflow-y: auto;
        }
        .address_list li {
            border-bottom: 1px solid #eee;
            padding: 10px;
            cursor: pointer;
        }
        .address_list li:hover {
            background: #f9f9f9;
        }
        .addr_item strong { font-size: 14px; color: #333; }
        .addr_item span { font-size: 12px; color: #888; margin-left: 5px; }
        .addr_item p { margin: 5px 0 0; font-size: 13px; color: #666; }
    </style>
@endsection