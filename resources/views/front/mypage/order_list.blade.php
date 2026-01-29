@extends('layouts.front')

@push('styles')
    <link rel="stylesheet" href="/css/mypage.css?v={{ time() }}">
@endpush

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
                                        $stepName = \App\Models\Order::getStepName($order->step);
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
                                    @php
                                        $stepName = \App\Models\Order::getStepName($order->step);
                                    @endphp
                                    <span class="status_badge" style="color: {{ \App\Models\Order::getStepColor($order->step) }};">
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

@endsection