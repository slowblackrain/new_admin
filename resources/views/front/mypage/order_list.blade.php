@extends('layouts.front')

@section('content')
    <div class="location_wrap hidden-mobile">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 주문내역</em>
        </div>
    </div>

    <div class="content_wrap clearbox" style="padding-bottom: 100px;">
        <!-- Left Sidebar (Desktop Only) -->
        <div class="hidden-mobile">
            @include('front.mypage.sidebar')
        </div>

        <!-- Right Content -->
        <div id="mypage_content" class="mypage-content-responsive">
            <div class="cart_title_area">
                <h3>주문내역 / 배송조회</h3>
            </div>

            <!-- Mobile Status Tabs (Visible on Mobile) -->
            <div class="sub_title_button_bar hidden-desktop">
                <table class="sub_title_button_tb" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td onclick="location.href='{{ route('mypage.order.list') }}'"
                            class="{{ !request('step') ? 'sel' : '' }}">
                            전체<br />
                            <span>{{ $allCount }}</span>
                        </td>
                        <td onclick="location.href='{{ route('mypage.order.list', ['step' => 'order']) }}'"
                            class="{{ request('step') == 'order' ? 'sel' : '' }}">
                            주문접수<br />
                            <span>{{ $orderCount }}</span>
                        </td>
                        <td onclick="location.href='{{ route('mypage.order.list', ['step' => 'delivery']) }}'"
                            class="{{ request('step') == 'delivery' ? 'sel' : '' }}">
                            배송중<br />
                            <span>{{ $deliveryCount }}</span>
                        </td>
                        <!-- More tabs can be added -->
                    </tr>
                </table>
            </div>

            <div class="cart_list_area">
                <!-- Desktop Table View -->
                <table class="cart_table hidden-mobile">
                    <colgroup>
                        <col width="150" />
                        <col width="150" />
                        <col width="*" />
                        <col width="120" />
                        <col width="120" />
                        <col width="100" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">주문일자</th>
                            <th scope="col">주문번호</th>
                            <th scope="col">상품정보</th>
                            <th scope="col">결제금액</th>
                            <th scope="col">진행상태</th>
                            <th scope="col">상세</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td>{{ substr($order->regist_date, 0, 10) }}</td>
                                <td><a href="{{ route('mypage.order.view', $order->order_seq) }}"
                                        style="color:#007bff; text-decoration: underline;">{{ $order->order_seq }}</a></td>
                                <td class="info_cell">
                                    @php
                                        $firstItem = $order->items->first();
                                        $itemCount = $order->items->count();
                                        $title = $firstItem ? $firstItem->goods_name : '상품 정보 없음';
                                        if ($itemCount > 1) {
                                            $title .= ' 외 ' . ($itemCount - 1) . '건';
                                        }
                                    @endphp
                                    {{ $title }}
                                </td>
                                <td class="price_bold">{{ number_format($order->settleprice) }}원</td>
                                <td>
                                    @php
                                        $stepName = '접수대기';
                                        switch ($order->step) {
                                            case 15:
                                                $stepName = '주문접수';
                                                break;
                                            case 25:
                                                $stepName = '결제확인';
                                                break;
                                            case 35:
                                                $stepName = '송장출력';
                                                break;
                                            case 45:
                                                $stepName = '상품준비';
                                                break;
                                            case 55:
                                                $stepName = '출고완료';
                                                break;
                                            case 65:
                                                $stepName = '배송중';
                                                break;
                                            case 75:
                                                $stepName = '배송완료';
                                                break;
                                        }
                                        if ($order->step == 15 && $order->payment == 'bank') {
                                            $stepName .= '(입금대기)';
                                        }
                                    @endphp
                                    {{ $stepName }}
                                </td>
                                <td>
                                    <a href="{{ route('mypage.order.view', $order->order_seq) }}" class="btn_base">조회</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 50px 0; text-align: center;">주문 내역이 없습니다.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Mobile List View -->
                <div class="mobile_order_list hidden-desktop">
                    @forelse($orders as $order)
                        <div class="m_order_item">
                            <div class="m_order_header">
                                <span class="date">{{ substr($order->regist_date, 0, 10) }}</span>
                                <span class="orderno">({{ $order->order_seq }})</span>
                                <a href="{{ route('mypage.order.view', $order->order_seq) }}" class="btn_detail_arrow">상세 ></a>
                            </div>
                            <div class="m_order_status">
                                <span class="status_badge">
                                    @php
                                        $stepName = '접수대기';
                                        switch ($order->step) {
                                            case 15:
                                                $stepName = '주문접수';
                                                break;
                                            case 25:
                                                $stepName = '결제확인';
                                                break;
                                            case 35:
                                                $stepName = '송장출력';
                                                break;
                                            case 45:
                                                $stepName = '상품준비';
                                                break;
                                            case 55:
                                                $stepName = '출고완료';
                                                break;
                                            case 65:
                                                $stepName = '배송중';
                                                break;
                                            case 75:
                                                $stepName = '배송완료';
                                                break;
                                        }
                                    @endphp
                                    {{ $stepName }}
                                </span>
                            </div>
                            <div class="m_order_goods"
                                onclick="location.href='{{ route('mypage.order.view', $order->order_seq) }}'">
                                @foreach($order->items as $item)
                                    <div class="goods_row">
                                        {{-- Image placeholder --}}
                                        <div class="img_box">
                                            @php
                                                $imgSrc = '/images/no_image.gif';
                                                if ($item->goods && $item->goods->images) {
                                                    $mainImage = $item->goods->images->where('image_type', 'list1')->first();
                                                    if ($mainImage)
                                                        $imgSrc = '/data/goods/' . $mainImage->image;
                                                }
                                            @endphp
                                            <img src="{{ $imgSrc }}" alt="Products">
                                        </div>
                                        <div class="info_box">
                                            <div class="g_name">{{ $item->goods_name }}</div>
                                            <div class="g_opt">옵션: {{ $item->options->first()->option1 ?? '기본' }}</div>
                                            <div class="g_price">{{ number_format($item->options->first()->price ?? 0) }}원 /
                                                {{ $item->options->first()->ea ?? 1 }}개
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div style="padding: 50px 0; text-align: center; background: #f9f9f9;">
                            <img src="/images/common/nodata.png" width="50" style="opacity: 0.5;"><br>
                            주문내역이 없습니다.
                        </div>
                    @endforelse
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    {{ $orders->links() }}
                </div>
            </div>
            <!-- End Right Content -->
        </div>
    </div>

    <style>
        /* Desktop styles */
        .mypage-content-responsive {
            float: right;
            width: 960px;
        }

        .hidden-mobile {
            display: block;
        }

        .hidden-desktop {
            display: none;
        }

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
            font-size: 14px;
        }

        .cart_table .info_cell {
            text-align: left;
            padding-left: 20px;
        }

        .price_bold {
            font-weight: bold;
            color: #333;
        }

        .btn_base {
            padding: 5px 10px;
            background: #666;
            color: #fff;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
            border-radius: 2px;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 1024px) {
            .location_wrap {
                display: none;
            }

            .content_wrap {
                width: 100%;
                padding: 0;
            }

            .hidden-mobile {
                display: none !important;
            }

            .hidden-desktop {
                display: block !important;
            }

            .mypage-content-responsive {
                float: none;
                width: 100%;
            }

            .cart_title_area {
                padding: 15px;
                border-bottom: 1px solid #eee;
            }

            .cart_title_area h3 {
                font-size: 18px;
                margin: 0;
            }

            /* Mobile Tabs */
            .sub_title_button_tb {
                width: 100%;
                border-bottom: 1px solid #ccc;
                background: #f5f5f5;
            }

            .sub_title_button_tb td {
                width: 20%;
                text-align: center;
                padding: 10px 0;
                font-size: 12px;
                cursor: pointer;
                border-right: 1px solid #eee;
            }

            .sub_title_button_tb td.sel {
                background: #fff;
                color: #d00;
                font-weight: bold;
                border-bottom: 2px solid #d00;
            }

            .sub_title_button_tb td span {
                display: block;
                margin-top: 5px;
                font-weight: bold;
            }

            /* Mobile Order Item */
            .m_order_item {
                border-bottom: 10px solid #f0f0f0;
                background: #fff;
            }

            .m_order_header {
                padding: 10px 15px;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .m_order_header .date {
                font-weight: bold;
                color: #333;
            }

            .m_order_header .orderno {
                color: #888;
                font-size: 12px;
                margin-left: 5px;
            }

            .btn_detail_arrow {
                color: #666;
                font-size: 12px;
                text-decoration: none;
            }

            .m_order_status {
                padding: 10px 15px;
                background: #fafafa;
            }

            .status_badge {
                color: #d00;
                font-weight: bold;
            }

            .m_order_goods {
                padding: 15px;
            }

            .goods_row {
                display: flex;
                margin-bottom: 10px;
            }

            .goods_row:last-child {
                margin-bottom: 0;
            }

            .img_box {
                width: 70px;
                height: 70px;
                margin-right: 15px;
                border: 1px solid #eee;
            }

            .img_box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .info_box {
                flex: 1;
            }

            .g_name {
                font-size: 14px;
                color: #333;
                margin-bottom: 5px;
                height: 38px;
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }

            .g_opt {
                font-size: 12px;
                color: #888;
                margin-bottom: 3px;
            }

            .g_price {
                font-size: 13px;
                font-weight: bold;
                color: #333;
            }
        }
    </style>
@endsection