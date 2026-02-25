<div id="mobile_header" class="mobile-header">
    {{-- Kakao Banner (Legacy Parity) --}}
    <a href="https://pf.kakao.com/_AUxbuT/chat" target="_blank" class="kakao-banner" style="background-color: #fce734; width: 100%; display: block; text-align: center;">    
        <img src="{{ asset('images/legacy/design/kko_talk.jpg') }}" width="100%" alt="KakaoTalk Consultation">
    </a>

    <div class="mobile-top-bar">
        <div class="header-left">
            <button type="button" class="btn_category" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        </div>
        <div class="header-center">
            <h1 class="logo">
                <a href="/"><img src="{{ asset('images/legacy/design/logo.png') }}" alt="dometopia" /></a>
            </h1>
        </div>
        <div class="header-right">
            <button type="button" class="btn_search" onclick="toggleHeaderSearch()"><i class="fas fa-search"></i></button>
            <a href="/cart" class="btn_cart" style="position:relative;">
                <i class="fas fa-shopping-cart"></i>
                @if(isset($cartCount) && $cartCount > 0)
                    <span style="position:absolute; top:-5px; right:-5px; background:#eb6506; color:#fff; font-size:10px; border-radius:50%; width:15px; height:15px; display:flex; justify-content:center; align-items:center;">{{ $cartCount }}</span>
                @endif
            </a>
        </div>
    </div>
    
    {{-- Search Form (Hidden by default) --}}
    <div id="mobile_search_form" class="mobile-search-form" style="display:none; padding:10px; background:#f9f9f9; border-top:1px solid #eee;">
        <form action="/goods/search" method="GET" style="margin:0; display:flex;">
            <input type="text" name="search_text" placeholder="검색어를 입력하세요" style="flex:1; padding:8px; border:1px solid #ccc; margin-right:5px; border-radius:3px;">
            <button type="submit" style="padding:8px 15px; background:#333; color:#fff; border:none; border-radius:3px;">검색</button>
        </form>
    </div>
    
    <div class="mobile-nav-scroller">
        <a href="/goods/catalog?sort=popular" class="{{ Request::fullUrlIs('*sort=popular*') ? 'active' : '' }}">베스트100</a>
        <a href="/gift" class="{{ Request::is('gift') ? 'active' : '' }}">판촉물</a>
        <a href="/goods/catalog?code=0180" class="{{ Request::fullUrlIs('*code=0180*') ? 'active' : '' }}">유럽브랜드관</a>
        <a href="/goods/catalog?code=0147" class="{{ Request::fullUrlIs('*code=0147*') ? 'active' : '' }}">직수입특가</a>
        <a href="/goods/catalog?sort=single_item&code=0055" class="{{ Request::fullUrlIs('*code=0055*') ? 'active' : '' }}">땡처리</a>
        <a href="/goods/new" class="{{ Request::is('goods/new*') ? 'active' : '' }}">신상품</a>
        <a href="/board?id=bulkorder" class="{{ Request::fullUrlIs('*id=bulkorder*') ? 'active' : '' }}">대량견적</a>
        <a href="/page/ats" class="{{ Request::is('page/ats') ? 'active' : '' }}">판매대행</a>
        <a href="/page/academy" class="{{ Request::is('page/academy') ? 'active' : '' }}">아카데미</a>
        <a href="https://www.youtube.com/@dometopia-tv" target="_blank" style="color:#f00;">도매토피아TV</a>
    </div>
</div>

<style>
    /* Mobile Header Styles */
    .mobile-header {
        display: none; /* Hidden on desktop by default */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: auto; /* Changed from fixed height */
        background: #fff;
        z-index: 999;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        flex-direction: column; /* Enable stacking */
    }

    .mobile-top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 60px;
        padding: 0 10px;
        box-sizing: border-box;
    }

    .mobile-header .header-center .logo {
        margin: 0;
        padding: 0;
        line-height: 1;
    }
    .mobile-header .header-center .logo img {
        height: 40px;
        width: auto;
    }

    .mobile-header button, .mobile-header a.btn_cart {
        background: none;
        border: none;
        font-size: 20px;
        color: #333;
        padding: 5px;
        cursor: pointer;
        text-decoration: none;
    }

    /* Horizontal Nav Scroller */
    .mobile-nav-scroller {
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        border-top: 1px solid #f2f2f2;
        background: #fff;
        padding: 0 10px;
        box-sizing: border-box;
    }
    .mobile-nav-scroller::-webkit-scrollbar {
        display: none; /* Hide scrollbar */
    }
    .mobile-nav-scroller a {
        display: inline-block;
        padding: 12px 10px;
        font-size: 14px;
        color: #444;
        text-decoration: none;
        font-weight: 500;
        border-bottom: 2px solid transparent; /* For active state */
    }
    .mobile-nav-scroller a:hover, .mobile-nav-scroller a.active {
        color: #eb6506;
        border-bottom-color: #eb6506;
        font-weight: 700;
    }

    /* Show only on mobile and tablet */
    @media (max-width: 1024px) {
        .mobile-header {
            display: flex;
        }
        /* Hide desktop header wrapper */
        .dometopia_header {
            display: none !important;
        }
        /* Adjust body padding for fixed header */
        body {
            padding-top: 155px; /* 50px Kakao + 60px top + 45px nav */
        }
    }
</style>

<script>
    function toggleHeaderSearch() {
        var form = document.getElementById('mobile_search_form');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            form.querySelector('input').focus();
        } else {
            form.style.display = 'none';
        }
    }
</script>
