<div id="mypage_sidebar" class="mypage-sidebar-responsive" style="text-align: left; font-family: '맑은고딕', 'Malgun Gothic', sans-serif; width: 100%;">
    <ul style="list-style: none; padding: 0; margin: 0; line-height: 2.3; font-size: 13px;">
        {{-- 최상단 주황색 굵은 메뉴 --}}
        <li style="margin-bottom: 3px;">
            <a href="{{ route('mypage.index') }}" style="font-size: 15px; font-weight: bold; color: #f25e1a; text-decoration: none;">마이페이지</a>
        </li>
        <li style="margin-bottom: 3px;">
            <a href="{{ route('mypage.emoney') }}" style="color: #444; text-decoration: none; font-weight: 500;">적립금내역</a>
        </li>
        <li style="margin-bottom: 3px;">
            <a href="#" style="color: #444; text-decoration: none; font-weight: 500;">개인결제</a>
        </li>
        <li style="margin-bottom: 15px;">
            <a href="{{ route('mypage.delivery_address.index') }}" style="color: #444; text-decoration: none; font-weight: 500;">내 배송지 관리</a>
        </li>
        
        {{-- 주문관련 --}}
        <li style="font-weight: bold; color: #333; font-size: 13px; border-top: 1px solid #eee; padding-top: 12px; margin-top: 12px; margin-bottom: 5px;">
            주문관련
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('mypage.order.list') }}" style="color: #666; text-decoration: none;">· 주문리스트</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('mypage.order.claim_list') }}" style="color: #666; text-decoration: none;">· 반품내역</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('mypage.order.claim_list') }}" style="color: #666; text-decoration: none;">· 환불내역</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('mypage.wishlist') }}" style="color: #666; text-decoration: none;">· 위시리스트</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="#" style="color: #666; text-decoration: none;">· 세금계산서 신청</a>
        </li>

        {{-- 내가 쓴 글 --}}
        <li style="font-weight: bold; color: #333; font-size: 13px; border-top: 1px solid #eee; padding-top: 12px; margin-top: 12px; margin-bottom: 5px;">
            내가 쓴 글
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('board.index', ['id' => 'mbqna']) }}" style="color: #666; text-decoration: none;">· 1:1 문의내역</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="/board?id=qna" style="color: #666; text-decoration: none;">· 상품 문의 내역</a>
        </li>

        {{-- 쿠폰관리 --}}
        <li style="font-weight: bold; color: #333; font-size: 13px; border-top: 1px solid #eee; padding-top: 12px; margin-top: 12px; margin-bottom: 5px;">
            쿠폰관리
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('mypage.coupon') }}" style="color: #666; text-decoration: none;">· 쿠폰내역</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="#" style="color: #666; text-decoration: none;">· 오프라인 쿠폰 인증</a>
        </li>

        {{-- 회원정보 --}}
        <li style="font-weight: bold; color: #333; font-size: 13px; border-top: 1px solid #eee; padding-top: 12px; margin-top: 12px; margin-bottom: 5px;">
            회원정보
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('mypage.member.check_password') }}" style="color: #666; text-decoration: none;">· 회원정보수정</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 3px;">
            <a href="{{ route('mypage.withdraw') }}" style="color: #666; text-decoration: none;">· 회원탈퇴</a>
        </li>
    </ul>
</div>

<style>
    #mypage_sidebar a {
        transition: color 0.1s;
    }
    #mypage_sidebar a:hover {
        color: #f25e1a !important;
        text-decoration: underline;
    }
</style>