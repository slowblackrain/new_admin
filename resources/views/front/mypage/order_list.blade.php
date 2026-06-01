@extends('layouts.front')

@section('content')
    <link rel="stylesheet" type="text/css" href="/css/legacy/sub_page.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/view.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/buttons.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/left_category.css">
    <style>
        .pagination li { list-style: none !important; display: inline-block !important; }
        .pagination { display: flex; justify-content: center; padding: 0; }
    </style>

    <div id="goods_view_wrap" style="max-width: 1200px; margin: 0 auto; padding: 20px 0; font-family: '맑은고딕', 'Malgun Gothic', sans-serif;">
        
        {{-- 1. Blue Header Banner (Legacy Style) --}}
        <div class="mypage-title-banner" style="background: #117fc6; color: #fff; padding: 20px 30px; border-radius: 0; margin-bottom: 25px; text-align: left;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0; font-family: '맑은고딕', 'Malgun Gothic', sans-serif; letter-spacing: -1px;">주문/배송 조회</h1>
        </div>

        {{-- 2. 2-Column Body Layout --}}
        <div class="mypage-body-grid" style="display: flex; gap: 30px; flex-wrap: wrap;">
            
            {{-- Left Sidebar Column --}}
            <div style="flex: 1; min-width: 200px; max-width: 220px;" class="hidden-mobile">
                @include('front.mypage.sidebar')
            </div>

            {{-- Right Main Content Column --}}
            <div class="mypage-main-content" style="flex: 4; min-width: 320px; text-align: left;">
                <div class="cart_title_area" style="border-bottom: 1px solid #e5e5e5; padding-bottom: 10px; margin-bottom: 15px;">
                    <h3 style="font-size: 15px; font-weight: bold; margin: 0; color: #333;">주문내역 / 배송조회</h3>
                </div>

                <!-- Mobile Status Tabs (Visible on Mobile) -->
                <div class="sub_title_button_bar hidden-desktop" style="margin-bottom: 15px;">
                    <table class="sub_title_button_tb" border="0" cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; text-align: center;">
                        <tr>
                            <td onclick="location.href='{{ route('mypage.order.list') }}'"
                                class="{{ !request('step') ? 'sel' : '' }}" style="padding: 10px; border: 1px solid #eee; cursor: pointer;">
                                전체<br />
                                <span style="font-weight: bold; color: #f25e1a;">{{ $allCount }}</span>
                            </td>
                            <td onclick="location.href='{{ route('mypage.order.list', ['step' => 'order']) }}'"
                                class="{{ request('step') == 'order' ? 'sel' : '' }}" style="padding: 10px; border: 1px solid #eee; cursor: pointer;">
                                주문접수<br />
                                <span style="font-weight: bold; color: #f25e1a;">{{ $orderCount }}</span>
                            </td>
                            <td onclick="location.href='{{ route('mypage.order.list', ['step' => 'delivery']) }}'"
                                class="{{ request('step') == 'delivery' ? 'sel' : '' }}" style="padding: 10px; border: 1px solid #eee; cursor: pointer;">
                                배송중<br />
                                <span style="font-weight: bold; color: #f25e1a;">{{ $deliveryCount }}</span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="cart_list_area">
                    <!-- Desktop Table View -->
                    <table class="goods_spec_table hidden-mobile" width="100%" style="border-collapse: collapse; text-align: center; font-size: 12px; border: 1px solid #eee;">
                        <thead>
                            <tr style="background: #f7f7f7; border-bottom: 1px solid #e5e5e5;">
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; border-right: 1px solid #eee;">주문일자</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; border-right: 1px solid #eee;">주문번호</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; text-align: left; border-right: 1px solid #eee; padding-left: 15px;">상품정보</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; border-right: 1px solid #eee;">결제금액</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; border-right: 1px solid #eee;">진행상태</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444;">상세</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 10px 8px; color: #666; border-right: 1px solid #eee;">{{ substr($order->regist_date, 0, 10) }}</td>
                                    <td style="padding: 10px 8px; border-right: 1px solid #eee;">
                                        <a href="{{ route('mypage.order.view', $order->order_seq) }}" style="color:#008df4; text-decoration: none; font-weight: bold;">
                                            {{ $order->order_seq }}
                                        </a>
                                    </td>
                                    <td style="padding: 10px 8px; text-align: left; color: #333; border-right: 1px solid #eee; padding-left: 15px;">
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
                                    <td style="padding: 10px 8px; font-weight: bold; color: #333; border-right: 1px solid #eee;">
                                        {{ number_format($order->settleprice) }}원
                                    </td>
                                    <td style="padding: 10px 8px; border-right: 1px solid #eee;">
                                        <span style="font-weight: bold;
                                            @if($order->step == 15) color: #666;
                                            @elseif($order->step == 25) color: #f25e1a;
                                            @elseif($order->step == 35 || $order->step == 45) color: #2e83e7;
                                            @elseif($order->step == 55) color: #2e83e7;
                                            @elseif($order->step == 75) color: #000;
                                            @elseif($order->step == 95) color: #d00;
                                            @else color: #666; @endif">
                                            @php
                                                $stepName = \App\Models\Order::getStepName($order->step);
                                                if ($order->step == 15 && $order->payment == 'bank') {
                                                    $stepName .= '(입금대기)';
                                                }
                                            @endphp
                                            {{ $stepName }}
                                        </span>
                                    </td>
                                    <td style="padding: 10px 8px;">
                                        <a href="{{ route('mypage.order.view', $order->order_seq) }}" style="padding: 4px 10px; font-size: 11px; color: #444; border: 1px solid #ccc; background: #fff; border-radius: 4px; text-decoration: none; font-weight: bold;">조회</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 50px 0; text-align: center; color: #999;">주문 내역이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Mobile List View -->
                    <div class="mobile_order_list hidden-desktop" style="margin-top: 10px;">
                        @forelse($orders as $order)
                            <div class="m_order_item" style="border: 1px solid #eee; padding: 15px; margin-bottom: 15px; background: #fff; border-radius: 4px;">
                                <div class="m_order_header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f9f9f9; padding-bottom: 8px; margin-bottom: 10px;">
                                    <div>
                                        <span class="date" style="color: #666; font-size: 12px; font-weight: bold;">{{ substr($order->regist_date, 0, 10) }}</span>
                                        <span class="orderno" style="color: #999; font-size: 11px;">({{ $order->order_seq }})</span>
                                    </div>
                                    <a href="{{ route('mypage.order.view', $order->order_seq) }}" class="btn_detail_arrow" style="color: #008df4; font-size: 11px; text-decoration: none; font-weight: bold;">상세 ></a>
                                </div>
                                <div class="m_order_status" style="margin-bottom: 10px; text-align: left;">
                                    @php
                                        $stepName = \App\Models\Order::getStepName($order->step);
                                    @endphp
                                    <span class="status_badge" style="color: {{ \App\Models\Order::getStepColor($order->step) }}; font-weight: bold; font-size: 12px;">
                                        {{ $stepName }}
                                    </span>
                                </div>
                                <div class="m_order_goods" onclick="location.href='{{ route('mypage.order.view', $order->order_seq) }}'" style="cursor: pointer;">
                                    @foreach($order->items as $item)
                                        <div class="goods_row" style="display: flex; gap: 15px; margin-top: 10px; text-align: left;">
                                            <div class="img_box" style="width: 60px; height: 60px; border: 1px solid #eee; overflow: hidden; border-radius: 4px; flex-shrink: 0;">
                                                @php
                                                    $imgSrc = '/images/no_image.gif';
                                                    if ($item->goods && $item->goods->images) {
                                                        $mainImage = $item->goods->images->where('image_type', 'list1')->first();
                                                        if ($mainImage)
                                                            $imgSrc = '/data/goods/' . $mainImage->image;
                                                    }
                                                @endphp
                                                <img src="{{ $imgSrc }}" alt="Products" style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                            <div class="info_box" style="font-size: 12px; color: #333;">
                                                <div class="g_name" style="font-weight: bold; margin-bottom: 4px;">{{ $item->goods_name }}</div>
                                                <div class="g_opt" style="color: #666; font-size: 11px; margin-bottom: 4px;">옵션: {{ $item->options->first()->option1 ?? '기본' }}</div>
                                                <div class="g_price" style="font-weight: bold; color: #f25e1a;">
                                                    {{ number_format($item->options->first()->price ?? 0) }}원 / {{ $item->options->first()->ea ?? 1 }}개
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div style="padding: 50px 0; text-align: center; background: #fafafa; border: 1px solid #eee; color: #999; border-radius: 4px;">
                                주문내역이 없습니다.
                            </div>
                        @endforelse
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div> {{-- End Right Content --}}
        </div> {{-- End 2-Column Body Layout --}}
    </div> {{-- End goods_view_wrap --}}
@endsection