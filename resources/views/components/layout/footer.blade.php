<!-- footer-->
<div id="doto_footer">
    <div class="f_nav mobile-hidden">
        <div class="container">
            <ul class="menu">
                <li><a href="/service/privacy"><b>개인정보처리방침</b></a></li>
                <li><a href="/service/agreement">회원약관</a></li>
                <li><a href="/service/guide">이용안내</a></li>
                <li><a href="/service/cs">고객센터</a></li>
                <li><a href="/page/index?tpl=etc/school_29.html">창업문의</a></li>
                <li><a href="/service/partnership">제휴문의</a></li>
                <li><a href="/service/company">회사소개</a></li>
            </ul>
            <div class="social">
                <h3><a href="https://www.youtube.com/channel/UCR_d50UMwZbMV3Gj7sKjUfw" target="_blank">공식SNS</h3></a>
                </h3>
            </div>
        </div>
    </div><!--f_nav-->
    <div class="footer-contents clearbox mobile-hidden">
        <div class="container">
            <div class="foot_left">
                <div class="foot-top">
                    <div class="foot-content cs">
                        <h6>주문상담</h6>
                        <p class="foot-tell">02-2026-2754</p>
                        <p class="foot-list">- <strong>평일</strong>09:00~18:00</p>
                        <p class="foot-list">- <strong>점심</strong>12:30~13:30</p>
                        <p class="foot-list">- 토, 일, 공휴일 휴무</p>
                    </div>
                    <div class="foot-content cs">
                        <h6>배송 · 교환 · 반품문의</h6>
                        <p class="foot-tell">{{ config('app.company_phone', '02-000-0000') }}</p>
                        <p class="foot-list">- <strong>평일</strong>10:00~16:00</p>
                        <p class="foot-list">- <strong>Fax</strong>041-353-9060</p>
                        <p class="foot-email">{{ config('app.company_email', 'help@dometopia.com') }}</p>
                    </div>
                    <div class="foot-content" style="margin-bottom: 15px;">
                        <h6 style="margin-bottom: 8px;">당진 물류 센터</h6>
                        <p class="">- 충청남도 당진시 송악읍 틀모시로 355-22 (우) 31738 </p>
                    </div>
                    <div class="foot-content">
                        <h6 style="margin-bottom: 8px;">반품주소</h6>
                        <p class="">- 충남 아산시 고불로 755-10 (씨제이대한통운택배 아산서브 정안물류) (우) 31581 </p>

                    </div>
                </div>
                <div class="foot-middle clearbox">
                    <div class="print_left">
                        <h4>인쇄시안 문의하기</h4><i class="fas fa-chevron-right"></i><span>print@dometopia.com</span>
                    </div>
                    <div class="print_right">
                        <h4 class="tit_bg">대량견적 전용계좌</h4>
                        <span>농협</span>
                        <span>351-0356-7285-33 </span>
                        <span>예금주: (주)트리</span>
                    </div>
                </div>
                <div class="foot-bottom clearbox">
                    <div class="company_info">
                        <div class="fleft">
                            <p>상호: {{ config('app.company_name', '(주)나무') }} <span>대표:
                                    {{ config('app.seo', '대표자명') }}</span> <span>사업자 등록번호:
                                    {{ config('app.business_license', '000-00-00000') }}</span> <span><b>통신판매 신고번호:
                                        {{ config('app.mail_selling_license', '2020-인천-0000') }}</b></span> <span>개인정보보호
                                    책임자 : 서현우이사</span></p>
                            <p class="foot-addr">인천 본사: {{ config('app.head_address', '인천시 ...') }} (우)
                                {{ config('app.head_zip', '00000') }} <span> Tel.
                                    {{ config('app.company_phone', '02-000-0000') }}</span></p>
                            <p>당진 물류 센터: {{ config('app.logistics_address', '충남 당진시 ...') }} (우)
                                {{ config('app.logistics_zip', '00000') }} <span> Tel.
                                    {{ config('app.company_phone', '02-000-0000') }}</span> <span> Fax.
                                    {{ config('app.company_fax', '000-000-0000') }}</span></p>
                            <p>중국지사 : 中国 浙江省 金华市 佛堂大道80号 (우) 322000 <span> Tel. 0579)8513-9393</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Simplified Footer (Updated to match Legacy) -->
    <div class="mobile-footer">
        <div class="mf-links">
            @guest
                <a href="/member/login">로그인</a>
            @else
                <a href="/member/logout">로그아웃</a>
            @endguest
            <a href="/service/cs">고객센터</a>
            <a href="/service/privacy">개인정보처리방침</a>
            <a href="/service/agreement">이용약관</a>
        </div>
        <div class="mf-info">
            <p><strong>(주)트리</strong> | 대표: 부영운</p>
            <p>사업자등록번호: 137-86-10726 | 통신판매업신고: 제2017-충남당진-0028호</p>
            <p>고객센터: <a href="tel:1566-6779">1566-6779</a> | 팩스: 041-353-9060</p>
            <p>메일: dometopia@dometopia.com</p>
            <p>인천 본사: 인천광역시 서구 봉오재3로 120 가정봄2프라자 2층</p>
            <p>당진 물류/반품: 충청남도 당진시 송악읍 틀모시로 355-22</p>
            <p class="copyright-text">Copyright ⓒ Tree Co., Ltd. All rights reserved.</p>
        </div>
    </div>

    <div class="copyright mobile-hidden">
        <div class="container">
            <span class="fleft">Copyright ⓒ Tree Co., Ltd. All rights reserved.</span>
        </div>
    </div>
</div>

<style>
    .mobile-footer {
        display: none;
        background: #f9f9f9;
        padding: 20px 15px;
        text-align: center;
        border-top: 1px solid #eee;
        margin-bottom: 60px; /* Space for Bottom Nav */
        font-size: 11px;
        color: #666;
        line-height: 1.6;
    }
    .mobile-footer .mf-links {
        margin-bottom: 15px;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 15px;
    }
    .mobile-footer .mf-links a {
        display: inline-block;
        margin: 0 8px;
        font-size: 12px;
        color: #333;
        text-decoration: none;
        font-weight: bold;
    }
    .mobile-footer .mf-info p {
        margin: 4px 0;
        word-break: keep-all;
    }
    .mobile-footer .mf-info strong {
        color: #333;
    }
    .mobile-footer .copyright-text {
        margin-top: 10px;
        color: #999;
    }

    @media (max-width: 768px) {
        .mobile-hidden {
            display: none !important;
        }
        .mobile-footer {
            display: block;
        }
        #doto_footer {
            border-top: none;
        }
    }
</style>

<!-- 공통 적용 스크립트 -->
<script type="text/javascript" src="//wcs.naver.net/wcslog.js"> </script>
<script type="text/javascript">
    if (!window.wcs_add) window.wcs_add = {};
    window.wcs_add["wa"] = "s_ead6d4ef0f3";
    if (!window._nasa) window._nasa = {};
    if (window.wcs) {
        wcs.inflow();
        wcs_do(_nasa);
    }
</script>