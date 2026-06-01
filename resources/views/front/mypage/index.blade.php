@extends('layouts.front')

@section('content')
    <link rel="stylesheet" type="text/css" href="/css/legacy/sub_page.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/view.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/buttons.css">
    <link rel="stylesheet" type="text/css" href="/css/legacy/left_category.css">

    <div id="goods_view_wrap" style="max-width: 1200px; margin: 0 auto; padding: 20px 0; font-family: '맑은고딕', 'Malgun Gothic', sans-serif;">
        
        {{-- 1. Blue Header Banner (Legacy Style) --}}
        <div class="mypage-title-banner" style="background: #117fc6; color: #fff; padding: 20px 30px; border-radius: 0; margin-bottom: 25px; text-align: left;">
            <h1 style="font-size: 24px; font-weight: bold; margin: 0; font-family: '맑은고딕', 'Malgun Gothic', sans-serif; letter-spacing: -1px;">마이페이지</h1>
        </div>

        {{-- 2. User Info & Summary Box (Legacy Horiz Style) --}}
        <div class="mypage-dashboard-box" style="background: #fff; border: 1px solid #e5e5e5; padding: 20px 25px; margin-bottom: 25px; font-size: 12px; color: #666;">
            {{-- 도토관리자(dototest5) 님 --}}
            <div style="font-size: 18px; font-weight: bold; color: #333; margin-bottom: 12px; text-align: left;">
                <span style="color: #333; font-weight: bold;">{{ $user->user_name ?? '도토관리자' }}</span><span style="font-weight: normal; color: #666;">({{ $user->userid }})</span> <span style="font-weight: normal; font-size: 15px; color: #666;">님</span>
            </div>

            {{-- 회원상세 한 줄 정보 바 --}}
            <div class="user-info-text-line" style="display: flex; flex-wrap: wrap; gap: 5px; align-items: center; font-size: 12px; color: #666; margin-bottom: 20px; border-bottom: 1px solid #f2f2f2; padding-bottom: 15px; text-align: left;">
                <span><strong>회원등급</strong> : @if($user->mtype == 'business') 기업 @else 일반 @endif</span>
                <span style="color: #ccc; margin: 0 8px;">|</span>
                <span><strong>총 구매금액</strong> : <span style="color: #f25e1a; font-weight: bold;">{{ number_format($user->member_order_price ?? 0) }}원</span></span>
                <span style="color: #ccc; margin: 0 8px;">|</span>
                <span><strong>이메일</strong> : {{ $user->email ?? '-' }}</span>
                <span style="color: #ccc; margin: 0 8px;">|</span>
                <span><strong>휴대폰</strong> : {{ $user->cellphone ?? '-' }}</span>
                <span style="color: #ccc; margin: 0 8px;">|</span>
                <span><strong>주소</strong> : {{ $user->address ?? '' }} {{ $user->address_detail ?? '' }}</span>
            </div>

            {{-- 진행중인 주문 ~ 위시리스트 가로 슬림 바 --}}
            <div class="summary-horiz-bar" style="background: #fafafa; border: 1px solid #e5e5e5; border-radius: 4px; padding: 15px 10px; display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; text-align: center;">
                <div style="flex: 1; min-width: 120px; border-right: 1px solid #e5e5e5; padding: 5px 0;">
                    <span style="color: #666; font-size: 12px; margin-right: 5px;">진행중인 주문 :</span>
                    <a href="{{ route('mypage.order.list') }}" style="color: #f25e1a; font-weight: bold; text-decoration: none; font-size: 13px;">{{ number_format($orderCount) }}<span style="color: #333; font-weight: normal; font-size: 12px;">건</span></a>
                </div>
                <div style="flex: 1; min-width: 120px; border-right: 1px solid #e5e5e5; padding: 5px 0;">
                    <span style="color: #666; font-size: 12px; margin-right: 5px;">교환, 반품 :</span>
                    <a href="{{ route('mypage.order.claim_list') }}" style="color: #f25e1a; font-weight: bold; text-decoration: none; font-size: 13px;">{{ number_format($claimCount) }}<span style="color: #333; font-weight: normal; font-size: 12px;">건</span></a>
                </div>
                <div style="flex: 1; min-width: 120px; border-right: 1px solid #e5e5e5; padding: 5px 0;">
                    <span style="color: #666; font-size: 12px; margin-right: 5px;">적립금 :</span>
                    <a href="{{ route('mypage.emoney') }}" style="color: #f25e1a; font-weight: bold; text-decoration: none; font-size: 13px;">{{ number_format($user->emoney ?? 0) }}<span style="color: #333; font-weight: normal; font-size: 12px;">원</span></a>
                </div>
                <div style="flex: 1; min-width: 120px; border-right: 1px solid #e5e5e5; padding: 5px 0;">
                    <span style="color: #666; font-size: 12px; margin-right: 5px;">할인쿠폰 :</span>
                    <a href="{{ route('mypage.coupon') }}" style="color: #f25e1a; font-weight: bold; text-decoration: none; font-size: 13px;">{{ number_format($couponCount) }}<span style="color: #333; font-weight: normal; font-size: 12px;">개</span></a>
                </div>
                <div style="flex: 1; min-width: 120px; border-right: 1px solid #e5e5e5; padding: 5px 0;">
                    <span style="color: #666; font-size: 12px; margin-right: 5px;">장바구니 :</span>
                    <a href="{{ route('cart.index') }}" style="color: #f25e1a; font-weight: bold; text-decoration: none; font-size: 13px;">{{ number_format($cartCount) }}<span style="color: #333; font-weight: normal; font-size: 12px;">개</span></a>
                </div>
                <div style="flex: 1; min-width: 120px; padding: 5px 0;">
                    <span style="color: #666; font-size: 12px; margin-right: 5px;">위시리스트 :</span>
                    <a href="{{ route('mypage.wishlist') }}" style="color: #f25e1a; font-weight: bold; text-decoration: none; font-size: 13px;">{{ number_format($wishCount) }}<span style="color: #333; font-weight: normal; font-size: 12px;">개</span></a>
                </div>
            </div>
        </div>

        {{-- 3. 2-Column Body Layout --}}
        <div class="mypage-body-grid" style="display: flex; gap: 30px; flex-wrap: wrap;">
            
            {{-- Left Sidebar Column --}}
            <div style="flex: 1; min-width: 200px; max-width: 220px;">
                @include('front.mypage.sidebar')
            </div>

            {{-- Right Main Dashboard Column --}}
            <div class="mypage-main-content" style="flex: 4; min-width: 320px; text-align: left;">
                
                {{-- Recent Orders Section --}}
                <div class="dashboard-section" style="background: #fff; border: 1px solid #e5e5e5; padding: 20px; margin-bottom: 25px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px; margin-bottom: 15px;">
                        <h3 style="font-size: 15px; font-weight: bold; margin: 0; color: #333;">주문내역</h3>
                        <a href="{{ route('mypage.order.list') }}" style="font-size: 11px; color: #999; text-decoration: none;">모두보기 +</a>
                    </div>
                    
                    <table class="goods_spec_table" width="100%" style="border-collapse: collapse; text-align: center; font-size: 12px; border: 1px solid #eee;">
                        <thead>
                            <tr style="background: #f7f7f7; border-bottom: 1px solid #e5e5e5;">
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; border-right: 1px solid #eee;">날짜</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; border-right: 1px solid #eee;">주문번호</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; text-align: left; border-right: 1px solid #eee; padding-left: 15px;">상품명</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; border-right: 1px solid #eee;">주문금액</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444;">상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 10px 8px; color: #666; border-right: 1px solid #eee;">
                                        {{ date('Y-m-d', strtotime($order->regist_date)) }}
                                    </td>
                                    <td style="padding: 10px 8px; border-right: 1px solid #eee;">
                                        <a href="{{ route('mypage.order.view', $order->order_seq) }}" style="color: #008df4; text-decoration: none; font-weight: bold;">
                                            {{ $order->order_seq }}
                                        </a>
                                    </td>
                                    <td style="padding: 10px 8px; text-align: left; color: #333; border-right: 1px solid #eee; padding-left: 15px;">
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
                                    <td style="padding: 10px 8px; font-weight: bold; color: #333; border-right: 1px solid #eee;">
                                        {{ number_format($order->settleprice ?? 0) }}원
                                    </td>
                                    <td style="padding: 10px 8px;">
                                        <span style="font-weight: bold;
                                            @if($order->step == 15) color: #666;
                                            @elseif($order->step == 25) color: #f25e1a;
                                            @elseif($order->step == 35 || $order->step == 45) color: #2e83e7;
                                            @elseif($order->step == 55) color: #2e83e7;
                                            @elseif($order->step == 75) color: #000;
                                            @elseif($order->step == 95) color: #d00;
                                            @else color: #666; @endif">
                                            @if($order->step == 15) 주문접수
                                            @elseif($order->step == 25) 결제확인
                                            @elseif($order->step == 35) 상품배송중
                                            @elseif($order->step == 45) 배송완료
                                            @elseif($order->step == 55) 거래완료
                                            @elseif($order->step == 65) 주문대기
                                            @elseif($order->step == 75) 구매확정
                                            @elseif($order->step == 95) 주문무효
                                            @else 상태({{ $order->step }}) @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 30px; color: #999; text-align: center;">
                                        최근 30일 내에 진행중인 주문 내역이 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- 주문내역 전체보기 슬림 버튼 --}}
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="{{ route('mypage.order.list') }}" style="padding: 8px 25px; font-size: 12px; color: #444; border: 1px solid #ccc; background: #fff; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; font-weight: bold; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #666;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                            주문내역 전체보기
                        </a>
                    </div>
                </div>

                {{-- Recent Board Inquiries Section --}}
                <div class="dashboard-section" style="background: #fff; border: 1px solid #e5e5e5; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px; margin-bottom: 15px;">
                        <h3 style="font-size: 15px; font-weight: bold; margin: 0; color: #333;">내 문의 사항</h3>
                        <a href="{{ route('board.index', ['id' => 'mbqna']) }}" style="font-size: 11px; color: #999; text-decoration: none;">모두보기 +</a>
                    </div>
                    
                    <table class="goods_spec_table" width="100%" style="border-collapse: collapse; text-align: center; font-size: 12px; border: 1px solid #eee;">
                        <thead>
                            <tr style="background: #f7f7f7; border-bottom: 1px solid #e5e5e5;">
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; width: 10%; border-right: 1px solid #eee;">번호</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; width: 20%; border-right: 1px solid #eee;">분류</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; text-align: left; border-right: 1px solid #eee; padding-left: 15px;">글제목</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; width: 15%; border-right: 1px solid #eee;">상태</th>
                                <th style="padding: 10px 8px; font-weight: bold; color: #444; width: 15%;">등록일</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentQuestions as $index => $q)
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 10px 8px; color: #666; border-right: 1px solid #eee;">
                                        {{ $q->seq }}
                                    </td>
                                    <td style="padding: 10px 8px; color: #666; border-right: 1px solid #eee;">
                                        [{{ $q->category ?? '상품정보 문의' }}]
                                    </td>
                                    <td style="padding: 10px 8px; text-align: left; border-right: 1px solid #eee; padding-left: 15px;">
                                        <a href="/board/view?id=mbqna&seq={{ $q->seq }}" style="color: #333; text-decoration: none;">
                                            {{ $q->subject }}
                                        </a>
                                    </td>
                                    <td style="padding: 10px 8px; border-right: 1px solid #eee;">
                                        <span style="font-weight: bold; @if($q->re_reply == 'Y') color: #2e83e7; @else color: #999; @endif">
                                            @if($q->re_reply == 'Y') 답변완료 @else 답변대기 @endif
                                        </span>
                                    </td>
                                    <td style="padding: 10px 8px; color: #666;">
                                        {{ date('Y-m-d H:i', strtotime($q->r_date)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 30px; color: #999; text-align: center;">
                                        최근 등록하신 문의 사항이 없습니다.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div> {{-- End Right Main Dashboard --}}

        </div> {{-- End 3. 2-Column Body --}}
    </div> {{-- End goods_view_wrap --}}
@endsection
