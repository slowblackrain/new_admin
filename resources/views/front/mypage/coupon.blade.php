@extends('layouts.front')

@section('content')
    <div class="location_wrap hidden-mobile">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 쿠폰 내역</em>
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
                <h3>쿠폰 내역</h3>
            </div>

            <div class="benefit_summary_box" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
                <span style="font-size: 16px; font-weight: bold;">사용 가능한 쿠폰: </span>
                <span style="font-size: 20px; color: #ff5500;">{{ number_format($usableCount) }}</span> 장
            </div>

            <div class="table_basic_list">
                <table class="list_table" style="width: 100%; border-collapse: collapse;">
                    <colgroup>
                        <col width="15%">
                        <col width="*">
                        <col width="15%">
                        <col width="15%">
                        <col width="15%">
                    </colgroup>
                    <thead>
                        <tr style="background: #f5f5f5; border-top: 2px solid #333; border-bottom: 1px solid #ddd;">
                            <th style="padding: 10px;">상태</th>
                            <th>쿠폰명</th>
                            <th>혜택</th>
                            <th>유효기간</th>
                            <th>사용일자</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $download)
                            @php
                                $coupon = $download->coupon;
                                $bgClass = '';
                                if($download->use_status == 'used') $bgClass = 'background:#f0f0f0; color:#999;';
                            @endphp
                            <tr style="border-bottom: 1px solid #eee; {{ $bgClass }}">
                                <td style="text-align: center; padding: 10px;">
                                    @if($download->use_status == 'used')
                                        <span style="color: #999;">사용완료</span>
                                    @elseif($download->use_status == 'unused')
                                        <span style="color: #007bff; font-weight: bold;">사용가능</span>
                                    @else
                                        {{ $download->use_status }}
                                    @endif
                                </td>
                                <td style="padding: 10px;">
                                    {{ $download->coupon_name ?? ($coupon->coupon_name ?? '쿠폰 정보 없음') }}
                                </td>
                                <td style="text-align: center;">
                                    @if($coupon)
                                        @if($coupon->sale_type == 'percent')
                                            {{ $coupon->percent_goods_sale }}% 할인
                                        @elseif($coupon->sale_type == 'won')
                                            {{ number_format($coupon->won_goods_sale) }}원 할인
                                        @else
                                            -
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    {{ substr($download->issue_startdate, 0, 10) }} <br> ~ {{ substr($download->issue_enddate, 0, 10) }}
                                </td>
                                <td style="text-align: center;">
                                    @if($download->use_date && $download->use_date != '0000-00-00')
                                        {{ substr($download->use_date, 0, 10) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 50px; color: #666;">
                                    보유한 쿠폰이 없습니다.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination_area" style="margin-top: 20px; text-align: center;">
                {{ $coupons->links() }}
            </div>
        </div>
    </div>
@endsection
