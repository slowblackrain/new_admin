<script>
    function Checkform(f) {
        var scriptTag2 = /[~^&|<>?]/; // 스크립트 태그 정규식
        var thisVal = f.search_text.value;

        if (scriptTag2.test(thisVal) == true) {
            alert("스크립트 태그는 사용할 수 없습니다.");
            f.search_text.focus();
            return false;
        }
    }

    // Category Menu Function
    function fnCategoryMenuInitial(code, depth, el) {
        // Reset active state
        $('.category_navigation span').css({
            'background': '#fff',
            'border': '1px solid #e9ecef',
            'color': '#9eabbb'
        });
        $('.category_navigation span a').css('color', '#9eabbb');

        // Set active state for clicked element
        if (el) {
            $(el).parent().css({
                'background': '#555',
                'border': '1px solid #555',
                'color': '#fff'
            });
            $(el).css('color', '#fff');
        } else {
            // Default to 'All'
            $('.category_navigation span.all').css({
                'background': '#f44336',
                'border': 'none',
                'color': '#fff'
            });
             $('.category_navigation span.all a').css('color', '#fff');
        }

        $.ajax({
            url: '/main/category_search_initial',
            type: 'POST',
            data: {
                code: code,
                depth: depth
            },
            success: function(data) {
                if (data == 'no_category') {
                    $('#result_category_list').html('');
                } else {
                    $('#result_category_list').html(data);
                }
            },
            error: function(e) {
                console.error("Category load failed", e);
            }
        });
    }

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initial load
        fnCategoryMenuInitial('all', '1', null);

        // Hover Effect for All Categories
        $('.allcate').hover(
            function() {
                $('#allCategoryList').show();
            },
            function() {
                $('#allCategoryList').hide();
            }
        );
    });
</script>

<!-- top-->
<script type="text/javascript" src="{{ asset('js/legacy/doto-header-function.js') }}"></script>
<form name="selladminLoginForm" method="post" action="/selleradmin/login_process/login">
    @csrf
    <input type="hidden" name="provider_seq" value="" />
    <input type="hidden" name="superadmin_login" value="1" />
    <input type="hidden" name="out_login" value="1" />
    <input type="hidden" name="main_id" value="" />
    <input type="hidden" name="main_pwd" value="-" />
</form>

<div class="dometopia_header" id="dometopia_header">
    <div class="header-top-wrap">
        <h1 class="logo">
            <a href="/"><img src="{{ asset('images/legacy/design/logo.png') }}" alt="dometopia" /></a>
        </h1>
        <div class="header-search-wrap">
            <div class="header-search-box">
                <div class="inner_search">
                    <form action="/goods/search" style="margin:0;" onSubmit="return Checkform(this)">
                        <input type="text" list="search_list" name="search_text" id="inner_search" title="검색어 입력"
                            class="search_input" value="{{ request('search_text') }}" required />
                        <datalist id="search_list">
                            {{-- @search_list placeholder --}}
                            {{--
                            <option value="{.title}" {? _GET.search_text==.title}selected{/} pay_period="1">{.title}
                            </option>
                            --}}
                        </datalist>
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
        <script>
            $(".header-search-wrap").focusin(function () {
                $(".header-search-box").toggleClass("focus");
            })
        </script>
        <div class="topmenu">
            <ul>
                @auth
                    {{-- Assuming userInfo is available via Auth::user() --}}
                    @if(in_array(Auth::user()->member_seq, ['319377', '319389', '310673', '245644']))
                        <li><a href="/member/sales">도토통계</a></li>
                    @endif
                    @if(Auth::user()->member_seq == '332741')
                        <li><a href="/member/youis_p">위탁통계</a></li>
                    @endif
                    @if(Auth::user()->group_seq == '4')
                        <li><a href="/order/add_deli">추가송장입력</a></li>
                    @endif
                    <li><a href="/mypage">마이페이지</a></li>
                @else
                    <li><a href="/member/login">로그인</a></li>
                    <li><a href="/member/agreement">회원가입</a></li>
                @endauth

                <li><a href="/page/index?tpl=etc/print_info.html">판촉물인쇄</a></li>
                <li><a href="{{ route('mypage.order.list') }}">주문배송조회</a></li>
                <li><a href="/order/cart">장바구니</a></li>
                <li><a href="/service/cs">고객센터</a></li>

                @if(Auth::check() && Auth::user()->member_provider_seq)
                    <li><a href="/selleradmin/main/seller_doto_login" style="color:red;">셀러관리자</a></li>
                @else
                    <li><a href="/member/agreement" onClick="alert('셀러 회원 신청을 해주세요!')" style="color:red;">셀러관리자</a></li>
                @endif
            </ul>
        </div>
    </div>
    <div class="doto_scrollmenu" id="doto_scrollmenu">
        <div class="scrollmenu-inner clearbox">
            <div class="allcate">

                <span id="allCategoryListBtn" class="">
                    <img src="{{ asset('images/legacy/design/all_cate_02.jpg') }}" alt="dometopia" />
                </span>
                <!-- 전체 카테고리 리스트 목록 -->
                <div id="allCategoryList" style="display:none;">
                    <!--전체카테고리 : 시작-->
                    <div class="list_layer">
                        <div class="tit_area">
                            <h2>전체 카테고리</h2>
                            <div class="category_navigation">
                                <span class="all"><a
                                        href="javascript:fnCategoryMenuInitial('all','1',this);">전체</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('ga','1',this);">ㄱ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('na','1',this);">ㄴ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('da','1',this);">ㄷ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('ra','1',this);">ㄹ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('ma','1',this);">ㅁ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('ba','1',this);">ㅂ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('sa','1',this);">ㅅ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('aa','1',this);">ㅇ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('za','1',this);">ㅈ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('cha','1',this);">ㅊ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('ka','1',this);">ㅋ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('ta','1',this);">ㅌ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('pa','1',this);">ㅍ</a></span>
                                <span><a href="javascript:fnCategoryMenuInitial('ha','1',this);">ㅎ</a></span>
                            </div>
                        </div>

                        <div class="category_list clearbox" id="result_category_list"></div>
                    </div>
                    <!--전체카테고리 : 끝-->
                    <button class="cls_category_list" onclick="$('#allCategoryListBtn').click();">창닫기</button>
                    <style>
                        /* Legacy Category Menu Styles (from dometopia.css) */
                        #allCategoryList {
                            position: absolute;
                            width: 1200px;
                            background: #fff;
                            box-sizing: border-box;
                            border: 1px solid #333;
                            z-index: 90;
                            display: none;
                            padding: 35px 20px;
                            left: 0;
                            /* Ensure left alignment relative to container */
                        }

                        #allCategoryList .list_layer {
                            width: 100%;
                        }

                        #allCategoryList .list_layer .tit_area {
                            height: 25px;
                        }

                        #allCategoryList .list_layer .tit_area h2 {
                            font-size: 26px;
                            font-family: 'Noto Sans KR';
                            font-style: normal;
                            line-height: 1;
                            float: left;
                            font-weight: 500;
                            margin: 0;
                        }

                        #allCategoryList .list_layer .tit_area .category_navigation {
                            float: left;
                            font-size: 0;
                            padding-top: 4px;
                            margin-left: 14px;
                        }

                        #allCategoryList .list_layer .tit_area .category_navigation span {
                            border: 1px solid #e9ecef;
                            display: inline-block;
                            width: 21px;
                            font-size: 12px;
                            text-align: center;
                            line-height: 17px;
                            box-sizing: border-box;
                            height: 21px;
                            color: #9eabbb;
                            box-shadow: 0 1px 0 #f7f8f9;
                            float: left;
                            background-color: white;
                            margin-left: -1px;
                        }

                        #allCategoryList .list_layer .tit_area .category_navigation span a {
                            display: block;
                            height: 100%;
                            text-decoration: none;
                            color: inherit;
                        }

                        #allCategoryList .list_layer .tit_area .category_navigation span.all {
                            width: 39px;
                            background: #f44336;
                            border: none;
                            line-height: 19px;
                            color: #FFF;
                            margin-right: 7px;
                        }

                        #allCategoryList .list_layer .category_list {
                            border-top: 2px solid #222;
                            border-bottom: 2px solid #222;
                            padding: 10px 0;
                            margin-top: 10px;
                        }

                        #allCategoryList .list_layer .category_list ul {
                            float: left;
                            width: 100%;
                            /* Ensure ul takes full width */
                            padding: 0;
                            margin: 0;
                        }

                        #allCategoryList .list_layer .category_list ul li {
                            width: 193px;
                            height: 35px;
                            line-height: 35px;
                            cursor: pointer;
                            font-size: 13px;
                            font-weight: normal;
                            font-family: '맑은고딕', 'Malgun Gothic', sans-serif !important;
                            padding-left: 10px;
                            float: left;
                            /* Essential for grid layout */
                            list-style: none;
                            box-sizing: border-box;
                        }

                        #allCategoryList .list_layer .category_list ul li span:hover {
                            text-decoration: underline !important;
                        }

                        #allCategoryList .cls_category_list {
                            background: #f44336;
                            margin-top: 5px;
                            box-sizing: border-box;
                            color: #fff;
                            font-weight: bold;
                            font-size: 13px;
                            width: 61px;
                            height: 26px;
                            line-height: 19px;
                            border: none;
                            cursor: pointer;
                        }
                    </style>
                </div>
            </div>
            <!--스크롤시 검색바 활성화-->
            <div class="hidden_sch hide">
                <form action="/goods/search" onSubmit="return Checkform(this)">
                    <input type="text" name="search_text" class="sch_field" placeholder="검색어 입력"
                        value="{{ request('search_text') }}">
                    <button type="submit" class="sch_submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="menu_box ml30">
                <ul>
                    <li><a href="/page/index?tpl=etc%2Fevent1809028.html">베스트100</a></li>
                    <li><a href="/gift">판촉물</a></li>
                    <li><a href="/goods/catalog?code=0180">유럽브랜드관</a></li>
                    <li><a href="/goods/catalog?code=0147">직수입특가</a></li>
                    <li><a href="/goods/catalog?sort=single_item&code=0055">땡처리</a></li>
                    <li><a href="/goods/newlist">신상품</a></li>
                    <li><a href="/board/?id=bulkorder">대량견적</a></li>
                    <li><a href="/page/index?tpl=etc/school_29.html">아카데미</a></li>
                    <li><a href="https://www.youtube.com/@dometopia-tv" target="_blank">도매토피아TV</a></li>
                </ul>
            </div><!--menu_box-->
            <div class="menu_box_r">
                <div class="icon_login">
                    @auth
                        <a href="/member/logout" style="color:#9eabbb;">로그아웃</a>
                    @else
                        <a href="/member/login" style="color:#2979ff;">로그인</a>
                    @endauth
                    <dl class="submenu">
                        @auth
                            <dd><a href="/mypage">마이페이지</a></dd>
                        @else
                            <dd><a href="/member/agreement">회원가입</a></dd>
                        @endauth
                        <dd><a href="{{ route('mypage.order.list') }}">주문조회</a></dd>
                        <dd><a href="/board/?id=order_prt">인쇄시안</a></dd>
                        <dd><a href="/service/cs">고객센터</a></dd>
                    </dl>
                </div>
            </div>
            <style>
                /* Sticky Menu Flexbox Fix */
                #doto_scrollmenu .scrollmenu-inner {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 30px;
                    
                    /* Alignment Fix for #allCategoryList (absolute 1200px) */
                    position: relative;
                    width: 1200px;
                    margin: 0 auto;
                }

                #doto_scrollmenu .allcate {
                    flex: 0 0 180px;
                    /* Fixed width for category button */
                    float: none !important;
                    margin-top: 0 !important;
                }

                #doto_scrollmenu .hidden_sch {
                    flex: 0 0 140px;
                    /* Fixed width for search */
                    float: none !important;
                    margin: 0 !important;
                    /* display: block !important; Removed to allow JS toggle */
                    padding: 0 !important;
                    height: auto !important;
                }

                #doto_scrollmenu .hidden_sch.show,
                #doto_scrollmenu .hidden_sch[style*="block"] {
                    display: block !important;
                }

                #doto_scrollmenu .hidden_sch form {
                    position: relative;
                    display: flex;
                    align-items: center;
                }

                #doto_scrollmenu .sch_field {
                    width: 100% !important;
                    float: none !important;
                    margin: 0 !important;
                }

                #doto_scrollmenu .sch_submit {
                    position: absolute !important;
                    top: 0 !important;
                    bottom: 0 !important;
                    height: 100% !important;
                    right: 0 !important;
                    margin: auto !important;
                    float: none !important;
                    background: transparent;
                    border: none;
                    display: flex;
                    /* Use flex to center the icon */
                    align-items: center;
                    justify-content: center;
                }

                #doto_scrollmenu .sch_submit i {
                    top: 0 !important;
                    line-height: normal;
                }

                #doto_scrollmenu .menu_box {
                    flex: 1;
                    /* Take remaining space */
                    float: none !important;
                    margin-left: 0 !important;
                    /* Reset margin, use gap */
                    white-space: nowrap;
                    overflow: hidden;
                }

                #doto_scrollmenu .menu_box ul {
                    height: auto !important;
                    display: flex;
                    align-items: center;
                }

                #doto_scrollmenu .menu_box li {
                    float: none !important;
                    display: inline-block;
                    margin-right: 20px !important;
                }

                #doto_scrollmenu .menu_box_r {
                    flex: 0 0 100px;
                    /* Right menu width */
                    float: none !important;
                    display: block !important;
                    /* Force display */
                    opacity: 1 !important;
                    visibility: visible !important;
                }

                /* Reset sub-elements if they were hidden */
                #doto_scrollmenu .menu_box_r .icon_login {
                    display: block !important;
                    float: right !important;
                    /* Keep internal float if needed, or flex */
                    width: 82px !important;
                    height: 50px !important;
                }

                /* New Admin Category Dropdown Styles (Ported from common_bk.css/.dometopia.css) */
                #allCategoryList {
                    /* position: absolute; ... is already in the main style block above, but we override here for context if needed, 
                       or specifically target .list_layer items which were missing */
                     left: 0; /* Ensure left alignment with parent relative container */
                }
                /* Re-affirming styles for visibility and layout in context of sticky menu */
                #allCategoryList .list_layer .category_list ul li {
                    float: left; /* Critical for grid layout */
                }
            </style>
        </div>
    </div>
</div>
<!-- top end-->
<div id="doto_header_modal" onclick="$('#allCategoryListBtn').click();"></div>

<style>
    /* Header Mobile Responsive */
    @media (max-width: 768px) {
        .doto_scrollmenu {
            display: none !important;
            /* Hide fixed menu on mobile */
        }

        .dometopia_header {
            height: auto !important;
            /* Allow height to grow */
        }

        .header-top-wrap {
            width: 100% !important;
            padding: 10px !important;
            height: auto !important;
            background: #fff;
            /* Ensure bg */
        }

        .header-top-wrap h1.logo {
            float: none !important;
            width: 100% !important;
            text-align: center;
            margin-bottom: 10px;
        }

        .header-top-wrap h1.logo img {
            height: 40px;
            /* Resize logo */
            width: auto;
        }

        .header-search-wrap {
            float: none !important;
            width: 100% !important;
            margin: 0 0 10px 0 !important;
            position: static !important;
        }

        .header-search-box {
            width: 100% !important;
            background: #f1f1f1;
        }

        .header-search-box .inner_search input {
            width: calc(100% - 40px) !important;
        }

        /* Top Menu (Login, Cart etc) */
        .topmenu {
            position: static !important;
            margin-top: 0 !important;
            text-align: center;
            width: 100% !important;
        }

        .topmenu ul {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .topmenu ul li {
            width: auto !important;
            /* Allow variable width */
            font-size: 13px !important;
        }

        .topmenu ul li:before {
            display: none;
            /* Remove separators if needed */
        }
    }
</style>