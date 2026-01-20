<div id="mypage_sidebar" style="width: 200px; float: left; margin-right: 40px;">
    <div class="mypage_menu" style="border: 1px solid #ddd; background: #fff;">
        <h2
            style="background: #333; color: #fff; padding: 15px; font-size: 16px; font-weight: bold; text-align: center; margin: 0;">
            MY PAGE</h2>
        <ul style="list-style: none; padding: 0; margin: 0;">
            <li class="menu_group">
                <div style="padding: 12px 15px; font-weight: bold; background: #f9f9f9; border-bottom: 1px solid #eee;">
                    쇼핑정보</div>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li><a href="{{ route('mypage.order.list') }}"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">주문/배송
                            조회</a></li>
                    <li><a href="#"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">취소/반품/교환
                            내역</a></li>
                    <li><a href="{{ route('cart.index') }}"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">장바구니</a>
                    </li>
                    <li><a href="#"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">관심상품</a>
                    </li>
                </ul>
            </li>
            <li class="menu_group">
                <div style="padding: 12px 15px; font-weight: bold; background: #f9f9f9; border-bottom: 1px solid #eee;">
                    혜택관리</div>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li><a href="#"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">쿠폰
                            내역</a></li>
                    <li><a href="#"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">적립금
                            내역</a></li>
                </ul>
            </li>
            <li class="menu_group">
                <div style="padding: 12px 15px; font-weight: bold; background: #f9f9f9; border-bottom: 1px solid #eee;">
                    회원정보</div>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li><a href="#"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">회원정보
                            수정</a></li>
                    <li><a href="#"
                            style="display: block; padding: 10px 15px; color: #666; text-decoration: none; border-bottom: 1px solid #eee;">회원탈퇴</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <!-- Customer Service Banner -->
    <div style="margin-top: 20px; border: 1px solid #ddd; padding: 15px; text-align: center;">
        <strong style="display: block; font-size: 14px; margin-bottom: 10px;">고객센터</strong>
        <span
            style="display: block; font-size: 20px; font-weight: bold; color: #d00; margin-bottom: 5px;">02-1234-5678</span>
        <span style="display: block; font-size: 12px; color: #888;">평일 09:00 ~ 18:00</span>
    </div>
</div>

<style>
    .mypage_menu a:hover {
        text-decoration: underline;
        color: #333;
    }
</style>