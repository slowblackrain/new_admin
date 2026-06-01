<div id="mypage_sidebar" class="mypage-sidebar-responsive" style="text-align: left; font-family: '맑은고딕', 'Malgun Gothic', sans-serif;">
    <ul style="list-style: none; padding: 0; margin: 0; line-height: 2.2; font-size: 13px;">
        <li style="margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
            <a href="{{ route('mypage.index') }}" style="font-size: 15px; font-weight: bold; color: #f25e1a; text-decoration: none;">마이페이지</a>
        </li>
        <li style="margin-bottom: 3px;">
            <a href="{{ route('mypage.emoney') }}" style="color: #444; text-decoration: none;">적립금내역</a>
        </li>
        <li style="margin-bottom: 3px;">
            <a href="#" style="color: #444; text-decoration: none;">개인결제</a>
        </li>
        <li style="margin-bottom: 12px;">
            <a href="{{ route('mypage.delivery_address.index') }}" style="color: #444; text-decoration: none;">내 배송지 관리</a>
        </li>
        
        <li style="font-weight: bold; color: #666; font-size: 13px; border-top: 1px solid #eee; padding-top: 10px; margin-bottom: 5px;">
            주문관련
        </li>
        <li style="margin-bottom: 3px; padding-left: 5px;">
            <a href="{{ route('mypage.order.list') }}" style="color: #666; text-decoration: none;">· 주문리스트</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 5px;">
            <a href="{{ route('mypage.order.claim_list') }}" style="color: #666; text-decoration: none;">· 반품내역</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 5px;">
            <a href="{{ route('mypage.order.claim_list') }}" style="color: #666; text-decoration: none;">· 환불내역</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 5px;">
            <a href="{{ route('mypage.wishlist') }}" style="color: #666; text-decoration: none;">· 위시리스트</a>
        </li>
        <li style="margin-bottom: 3px; padding-left: 5px;">
            <a href="#" style="color: #666; text-decoration: none;">· 세금계산서 신청</a>
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