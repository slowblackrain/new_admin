@extends('layouts.front')

@section('content')
    <link rel="stylesheet" type="text/css" href="/css/legacy/sub_page.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/view.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/buttons.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/left_category.css">

    <div id="goods_view_wrap" style="max-width: 1200px; margin: 0 auto; padding: 20px; font-family: '맑은고딕', 'Malgun Gothic', sans-serif;">
        
        {{-- 1. Blue Header Banner --}}
        <div class="mypage-title-banner" style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); color: #fff; padding: 40px 30px; border-radius: 8px; margin-bottom: 25px; text-align: left; box-shadow: 0 4px 10px rgba(33, 150, 243, 0.2);">
            <h1 style="font-size: 32px; font-weight: bold; margin: 0; font-family: 'Outfit', 'Inter', sans-serif; letter-spacing: -1px;">마이페이지</h1>
        </div>

        {{-- 2. User Info & Summary Summary Box --}}
        <div class="mypage-dashboard-box" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
            {{-- User Grade & Details --}}
            <div class="user-profile-header" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 20px; margin-bottom: 20px;">
                <div style="text-align: left;">
                    <span style="font-size: 20px; font-weight: bold; color: #2d3748;">
                        <strong style="color: #1a0dab;">{{ $user->userid }}</strong> ({{ $user->userid }}) 님
                    </span>
                    <span style="font-size: 13px; color: #718096; margin-left: 15px; background: #ebf8ff; color: #2b6cb0; padding: 3px 10px; border-radius: 20px; font-weight: bold; vertical-align: middle;">
                        회원등급: @if($user->gubun == 'business') 기업회원 @else 일반회원 @endif
                    </span>
                </div>
                <div style="font-size: 13px; color: #4a5568; text-align: left;">
                    <span style="margin-right: 15px;"><strong>총 구매금액:</strong> <span style="color: #e53e3e; font-weight: bold;">{{ number_format($user->total_sales ?? 0) }}원</span></span>
                    <span style="margin-right: 15px;"><strong>이메일:</strong> {{ $user->email ?? '-' }}</span>
                    <span><strong>휴대폰:</strong> {{ $user->cellphone ?? '-' }}</span>
                </div>
            </div>

            {{-- Summary Cards Bar --}}
            <div class="summary-cards-bar" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px; width: 100%;">
                <div class="summary-card" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px 10px; text-align: center; background: #fff; transition: transform 0.2s;">
                    <span style="display: block; font-size: 13px; color: #718096; margin-bottom: 8px;">진행중인 주문</span>
                    <strong style="font-size: 20px; color: #e53e3e;">{{ number_format($orderCount) }} <span style="font-size: 13px; font-weight: normal; color: #718096;">건</span></strong>
                </div>
                <div class="summary-card" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px 10px; text-align: center; background: #fff;">
                    <span style="display: block; font-size: 13px; color: #718096; margin-bottom: 8px;">교환, 반품</span>
                    <strong style="font-size: 20px; color: #dd6b20;">{{ number_format($claimCount) }} <span style="font-size: 13px; font-weight: normal; color: #718096;">건</span></strong>
                </div>
                <div class="summary-card" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px 10px; text-align: center; background: #fff;">
                    <span style="display: block; font-size: 13px; color: #718096; margin-bottom: 8px;">적립금</span>
                    <strong style="font-size: 20px; color: #3182ce;">{{ number_format($user->emoney ?? 0) }} <span style="font-size: 13px; font-weight: normal; color: #718096;">원</span></strong>
                </div>
                <div class="summary-card" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px 10px; text-align: center; background: #fff;">
                    <span style="display: block; font-size: 13px; color: #718096; margin-bottom: 8px;">할인쿠폰</span>
                    <strong style="font-size: 20px; color: #319795;">{{ number_format($couponCount) }} <span style="font-size: 13px; font-weight: normal; color: #718096;">개</span></strong>
                </div>
                <div class="summary-card" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px 10px; text-align: center; background: #fff;">
                    <span style="display: block; font-size: 13px; color: #718096; margin-bottom: 8px;">장바구니</span>
                    <strong style="font-size: 20px; color: #4a5568;">{{ number_format($cartCount) }} <span style="font-size: 13px; font-weight: normal; color: #718096;">개</span></strong>
                </div>
                <div class="summary-card" style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px 10px; text-align: center; background: #fff;">
                    <span style="display: block; font-size: 13px; color: #718096; margin-bottom: 8px;">위시리스트</span>
                    <strong style="font-size: 20px; color: #4a5568;">{{ number_format($wishCount) }} <span style="font-size: 13px; font-weight: normal; color: #718096;">개</span></strong>
                </div>
            </div>
        </div>

        {{-- 3. 2-Column Responsive Body Layout --}}
        <div class="mypage-body-grid" style="display: flex; gap: 25px; flex-wrap: wrap;">
            
            {{-- Left Sidebar Column --}}
            <div style="flex: 1; min-width: 240px; max-width: 260px;">
                @include('front.mypage.sidebar')
            </div>

            {{-- Right Main Dashboard Column --}}
            <div class="mypage-main-content" style="flex: 3; min-width: 320px; text-align: left;">
                
                {{-- Recent Orders Section --}}
                <div class="dashboard-section" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #4a5568; padding-bottom: 10px; margin-bottom: 15px;">
                        <h3 style="font-size: 16px; font-weight: bold; margin: 0; color: #2d3748;">최근 주문내역</h3>
                        <a href="{{ route('mypage.order.list') }}" style="font-size: 12px; color: #718096; text-decoration: none;">모두보기 +</a>
                    </div>
                    
                    <table class="goods_spec_table" width="100%" style="border-collapse: collapse; text-align: center; font-size: 13px;">
                        <thead>
                            <tr style="background: #f7fafc; border-bottom: 1px solid #edf2f7;">
                                <th style="padding: 10px; font-weight: bold; color: #4a5568;">날짜</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568;">주문번호</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568; text-align: left;">상품명</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568;">주문금액</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568;">상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr style="border-bottom: 1px solid #edf2f7; hover: background: #fcfcfc;">
                                    <td style="padding: 12px 10px; color: #718096;">
                                        {{ date('Y-m-d', strtotime($order->regist_date)) }}
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <a href="{{ route('mypage.order.view', $order->order_seq) }}" style="color: #2b6cb0; text-decoration: none; font-weight: bold;">
                                            {{ $order->order_seq }}
                                        </a>
                                    </td>
                                    <td style="padding: 12px 10px; text-align: left; color: #2d3748;">
                                        @if($order->items->count() > 0)
                                            @php $firstItem = $order->items->first(); @endphp
                                            {{ $firstItem->goods_name ?? '상품명 정보 없음' }}
                                            @if($order->items->count() > 1)
                                                외 {{ $order->items->count() - 1 }}건
                                            @endif
                                        @else
                                            주문 상품 정보 없음
                                        @endif
                                    </td>
                                    <td style="padding: 12px 10px; font-weight: bold; color: #2d3748;">
                                        {{ number_format($order->total_price ?? 0) }}원
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;
                                            @if($order->step == 15) background: #edf2f7; color: #4a5568;
                                            @elseif($order->step == 25) background: #feebc8; color: #c05621;
                                            @elseif($order->step == 35 || $order->step == 45) background: #e2f9e9; color: #276749;
                                            @elseif($order->step == 55) background: #ebf8ff; color: #2b6cb0;
                                            @elseif($order->step == 65) background: #e2e8f0; color: #4a5568;
                                            @elseif($order->step == 75) background: #e6fffa; color: #234e52;
                                            @elseif($order->step == 95) background: #fed7d7; color: #9b2c2c;
                                            @else background: #f7fafc; color: #718096; @endif">
                                            @if($order->step == 15) 주문접수
                                            @elseif($order->step == 25) 결제확인
                                            @elseif($order->step == 35) 상품배송중
                                            @elseif($order->step == 45) 배송완료
                                            @elseif($order->step == 55) 거래완료
                                            @elseif($order->step == 65) 주문대기
                                            @elseif($order->step == 75) 구매확정
                                            @elseif($order->step == 95) 주문취소
                                            @else 상태({{ $order->step }}) @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 30px; color: #a0aec0; text-align: center;">
                                        최근 30일 내에 진행중인 주문 내역이 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div style="text-align: center; margin-top: 15px;">
                        <a href="{{ route('mypage.order.list') }}" class="button bgblack" style="padding: 8px 30px; font-size: 13px; font-weight: bold; border-radius: 4px; display: inline-block; color: #fff; text-decoration: none;">
                            주문내역 전체보기
                        </a>
                    </div>
                </div>

                {{-- Recent Board Inquiries Section --}}
                <div class="dashboard-section" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.01);">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #4a5568; padding-bottom: 10px; margin-bottom: 15px;">
                        <h3 style="font-size: 16px; font-weight: bold; margin: 0; color: #2d3748;">내 문의 사항</h3>
                        <a href="{{ route('board.index', ['id' => 'mbqna']) }}" style="font-size: 12px; color: #718096; text-decoration: none;">모두보기 +</a>
                    </div>
                    
                    <table class="goods_spec_table" width="100%" style="border-collapse: collapse; text-align: center; font-size: 13px;">
                        <thead>
                            <tr style="background: #f7fafc; border-bottom: 1px solid #edf2f7;">
                                <th style="padding: 10px; font-weight: bold; color: #4a5568; width: 10%;">번호</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568; width: 15%;">분류</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568; text-align: left;">글제목</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568; width: 15%;">상태</th>
                                <th style="padding: 10px; font-weight: bold; color: #4a5568; width: 15%;">등록일</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentQuestions as $index => $q)
                                <tr style="border-bottom: 1px solid #edf2f7; hover: background: #fcfcfc;">
                                    <td style="padding: 12px 10px; color: #718096;">
                                        {{ $q->seq }}
                                    </td>
                                    <td style="padding: 12px 10px; color: #4a5568;">
                                        {{ $q->category ?? '1:1문의' }}
                                    </td>
                                    <td style="padding: 12px 10px; text-align: left;">
                                        <a href="/board/view?id=mbqna&seq={{ $q->seq }}" style="color: #2d3748; text-decoration: none;">
                                            {{ $q->subject }}
                                        </a>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: bold;
                                            @if($q->re_reply == 'Y') background: #e2f9e9; color: #276749;
                                            @else background: #edf2f7; color: #4a5568; @endif">
                                            @if($q->re_reply == 'Y') 답변완료
                                            @else 답변대기 @endif
                                        </span>
                                    </td>
                                    <td style="padding: 12px 10px; color: #718096;">
                                        {{ date('Y-m-d', strtotime($q->r_date)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 30px; color: #a0aec0; text-align: center;">
                                        최근 등록하신 문의 사항이 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div> {{-- End Right Main Dashboard --}}

        </div> {{-- End 3. 2-Column Responsive Body --}}
    </div> {{-- End goods_view_wrap --}}
@endsection
