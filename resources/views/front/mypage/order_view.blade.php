@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 주문상세</em>
        </div>
    </div>

    <div class="content_wrap clearbox" style="padding-bottom: 100px;">
        <!-- Left Sidebar -->
        @include('front.mypage.sidebar')

        <!-- Right Content -->
        <div id="mypage_content" style="float: right; width: 960px;">
            <div class="cart_title_area">
                <h3>주문 상세 내역</h3>
            </div>

            <div style="margin-bottom: 20px;">
                <span style="font-size: 16px; font-weight: bold;">주문번호: <span
                        style="color: #d00;">{{ $order->order_seq }}</span></span>
                <span style="float: right; color: #666;">주문일시: {{ $order->regist_date }}</span>
            </div>

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
                        @foreach($order->items as $item)
                            @php
                                $goods = $item->goods;
                                // Options are hasMany, but usually 1:1 in this flattened structure or we iterate?
                                // Logic: OrderItem -> hasMany OrderItemOption. 
                                // Usually there is 1 OrderItemOption per OrderItem in this legacy schema style if 'split' happened?
                                // Let's iterate options just in case, or take first if logic dictates.
                                // Based on OrderController store:
                                // One OrderItem created per cart item, and one OrderItemOption created per OrderItem.
                                $option = $item->options->first();

                                $price = $option->price ?? 0;
                                $ea = $option->ea ?? 1;
                                $itemPrice = $price * $ea;

                                $imgSrc = '/images/no_image.gif';
                                if ($goods && $goods->images) {
                                    $mainImage = $goods->images->where('image_type', 'list1')->first();
                                    if ($mainImage) {
                                        $imgSrc = '/data/goods/' . $mainImage->image;
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="img_cell">
                                    <img src="{{ $imgSrc }}" alt="{{ $item->goods_name }}" width="60">
                                </td>
                                <td class="info_cell">
                                    <div class="g_name">{{ $item->goods_name }}</div>
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

                <h4 style="margin-top: 40px;">배송지 정보</h4>
                <table class="form_table">
                    <colgroup>
                        <col width="150" />
                        <col width="*" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>수령인</th>
                            <td>{{ $order->recipient_user_name }}</td>
                        </tr>
                        <tr>
                            <th>연락처</th>
                            <td>{{ $order->recipient_cellphone }}</td>
                        </tr>
                        <tr>
                            <th>주소</th>
                            <td>
                                ({{ $order->recipient_zipcode }})<br>
                                @if($order->recipient_address_type == 'street')
                                    [도로명] {{ $order->recipient_address_street }} {{ $order->recipient_address_detail }}<br>
                                    <span style="color:#888; font-size:12px;">[지번] {{ $order->recipient_address }}
                                        {{ $order->recipient_address_detail }}</span>
                                @else
                                    [지번] {{ $order->recipient_address }} {{ $order->recipient_address_detail }}<br>
                                    @if($order->recipient_address_street)
                                        <span style="color:#888; font-size:12px;">[도로명] {{ $order->recipient_address_street }}
                                            {{ $order->recipient_address_detail }}</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>배송메시지</th>
                            <td>{{ $order->memo }}</td>
                        </tr>
                    </tbody>
                </table>

                <h4 style="margin-top: 40px;">결제 정보</h4>
                <table class="form_table">
                    <colgroup>
                        <col width="150" />
                        <col width="*" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>결제방법</th>
                            <td>
                                @if($order->payment == 'bank')
                                    무통장 입금
                                @else
                                    신용카드
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>결제금액</th>
                            <td style="color: #d00; font-weight: bold; font-size: 16px;">
                                {{ number_format($order->settleprice) }}원
                            </td>
                        </tr>
                        @if($order->payment == 'bank')
                            <tr>
                                <th>입금계좌</th>
                                <td>{{ $order->bank_account }} (입금자: {{ $order->depositor }})</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <div class="btn_area_center">
                    <a href="{{ route('mypage.order.list') }}" class="btn_list">목록으로</a>

                    @php
                        $step = $order->step;
                    @endphp

                    {{-- Cancel: Until Delivery Ready (45) --}}
                    @if($step < 45 && $step != 95 && $step != 99)
                        <a href="{{ route('mypage.claim.apply', ['orderSeq' => $order->order_seq, 'type' => 'cancel']) }}" 
                           class="btn_list" style="background:#d00; border:1px solid #d00;">주문취소</a>
                    @endif

                    {{-- Return/Exchange: After Delivery Complete (75) --}}
                    {{-- 55=Shipping, 65=Delivered, 75=Complete. Usually can return after 55 or 65. --}}
                    @if($step >= 55 && $step < 80)
                        <a href="{{ route('mypage.claim.apply', ['orderSeq' => $order->order_seq, 'type' => 'return']) }}" 
                           class="btn_list" style="background:#fff; color:#333; border:1px solid #333;">반품신청</a>
                        <a href="{{ route('mypage.claim.apply', ['orderSeq' => $order->order_seq, 'type' => 'exchange']) }}" 
                           class="btn_list" style="background:#fff; color:#333; border:1px solid #333;">교환신청</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

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

        .btn_area_center {
            margin: 40px 0;
            text-align: center;
        }

        .btn_list {
            padding: 15px 50px;
            background: #333;
            color: #fff;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
    </style>
@endsection