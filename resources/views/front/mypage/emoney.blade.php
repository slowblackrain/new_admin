@extends('layouts.front')

@section('content')
    <div class="location_wrap hidden-mobile">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 마이페이지 &gt; 적립금 내역</em>
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
                <h3>적립금 내역</h3>
            </div>

            <div class="benefit_summary_box" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd;">
                <span style="font-size: 16px; font-weight: bold;">현재 보유 적립금: </span>
                <span style="font-size: 20px; color: #ff5500;">{{ number_format($currentEmoney) }}</span> 원
            </div>

            <div class="table_basic_list">
                <table class="list_table" style="width: 100%; border-collapse: collapse;">
                    <colgroup>
                        <col width="15%">
                        <col width="*">
                        <col width="15%">
                        <col width="15%">
                    </colgroup>
                    <thead>
                        <tr style="background: #f5f5f5; border-top: 2px solid #333; border-bottom: 1px solid #ddd;">
                            <th style="padding: 10px;">일자</th>
                            <th>내용</th>
                            <th>지급/사용</th>
                            <th>잔액</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($emoneyList as $log)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="text-align: center; padding: 10px;">
                                    {{ substr($log->regist_date, 0, 10) }}
                                </td>
                                <td style="padding: 10px;">
                                    {{ $log->memo }}
                                </td>
                                <td style="text-align: right; padding-right: 20px;">
                                    @if($log->emoney > 0)
                                        <span style="color: blue;">+{{ number_format($log->emoney) }}</span>
                                    @else
                                        <span style="color: red;">{{ number_format($log->emoney) }}</span>
                                    @endif
                                </td>
                                <td style="text-align: right; padding-right: 20px;">
                                    {{ number_format($log->remain) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 50px; color: #666;">
                                    적립금 내역이 없습니다.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination_area" style="margin-top: 20px; text-align: center;">
                {{ $emoneyList->links() }}
            </div>
        </div>
    </div>
@endsection
