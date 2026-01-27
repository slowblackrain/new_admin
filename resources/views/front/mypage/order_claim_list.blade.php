@extends('layouts.front')

@section('content')
<div class="location_wrap hidden-mobile">
    <div class="location_cont">
        <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 취소/반품/교환 내역</em>
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
            <h3>취소/반품/교환 내역</h3>
        </div>

        <div class="sub_title_button_bar hidden-desktop">
            <table class="sub_title_button_tb" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="sel">
                        취소/반품/교환<br />
                        <span>{{ $cancelCount + $returnCount + $exchangeCount }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="cart_list_area">
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
                            <td><a href="{{ route('mypage.order.view', $order->order_seq) }}" style="color:#007bff; text-decoration: underline;">{{ $order->order_seq }}</a></td>
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
                                {{ \App\Models\Order::getStepName($order->step) }}
                            </td>
                            <td>
                                <a href="{{ route('mypage.order.view', $order->order_seq) }}" class="btn_base">조회</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 50px 0; text-align: center;">취소/반품/교환 내역이 없습니다.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Mobile List View --}}
            <div class="hidden-desktop">
                 @forelse($orders as $order)
                    <div style="border:1px solid #ddd; margin-bottom:10px; padding:10px; background:#fff;">
                         <div style="border-bottom:1px solid #eee; padding-bottom:5px; margin-bottom:5px; font-size:12px; color:#888;">
                             {{ substr($order->regist_date, 0, 10) }} | <span style="color:#333; font-weight:bold;">{{ $order->order_seq }}</span>
                         </div>
                         <div style="margin-bottom:10px;">
                            @php
                                $firstItem = $order->items->first();
                                $itemCount = $order->items->count();
                                $title = $firstItem ? $firstItem->goods_name : '상품 정보 없음';
                                if ($itemCount > 1) {
                                    $title .= ' 외 ' . ($itemCount - 1) . '건';
                                }
                            @endphp
                            <a href="{{ route('mypage.order.view', $order->order_seq) }}" style="color:#333; font-weight:bold; text-decoration:none;">{{ $title }}</a>
                         </div>
                         <div class="clearbox">
                             <span style="float:left; font-weight:bold; color:#000;">{{ number_format($order->settleprice) }}원</span>
                             <span style="float:right; color:#d00;">{{ \App\Models\Order::getStepName($order->step) }}</span>
                         </div>
                         <div style="margin-top:10px; text-align:center;">
                             <a href="{{ route('mypage.order.view', $order->order_seq) }}" style="display:block; padding:8px; border:1px solid #ddd; background:#f9f9f9; color:#666; text-decoration:none; font-size:12px;">상세보기</a>
                         </div>
                    </div>
                @empty
                    <div style="padding: 50px 0; text-align: center; border:1px solid #ddd; background:#fff;">내역이 없습니다.</div>
                @endforelse
            </div>

            <div class="paging_area">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
