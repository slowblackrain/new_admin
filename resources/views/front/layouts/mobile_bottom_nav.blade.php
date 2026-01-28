<div id="mobile_bottom_nav" class="mobile-bottom-nav">
    <a href="/" class="nav-item {{ Request::is('/') ? 'active' : '' }}">
        <i class="fas fa-home"></i>
        <span>홈</span>
    </a>
    <a href="javascript:void(0);" onclick="toggleSidebar()" class="nav-item">
        <i class="fas fa-bars"></i>
        <span>카테고리</span>
    </a>
    <a href="javascript:void(0);" onclick="toggleSearch()" class="nav-item">
        <i class="fas fa-search"></i>
        <span>검색</span>
    </a>
    <a href="/mypage" class="nav-item {{ Request::is('mypage*') ? 'active' : '' }}">
        <i class="fas fa-user"></i>
        <span>마이페이지</span>
    </a>
    {{-- Recent Items or Cart --}}
    <a href="/order/cart" class="nav-item {{ Request::is('order/cart*') ? 'active' : '' }}">
        <div style="position: relative; display: inline-block;">
            <i class="fas fa-shopping-cart"></i>
            {{-- <span class="badge">0</span> --}}
        </div>
        <span>장바구니</span>
    </a>
</div>

<style>
    .mobile-bottom-nav {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 60px;
        background: #fff;
        border-top: 1px solid #eee;
        box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
        z-index: 1000;
        justify-content: space-around;
        align-items: center;
        padding-bottom: env(safe-area-inset-bottom);
    }

    .mobile-bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #888;
        font-size: 10px;
        width: 20%;
        height: 100%;
    }

    .mobile-bottom-nav .nav-item i {
        font-size: 20px;
        margin-bottom: 4px;
    }

    .mobile-bottom-nav .nav-item.active {
        color: #eb6506; /* Dometopia Orange */
    }

    .mobile-bottom-nav .nav-item .badge {
        position: absolute;
        top: -5px;
        right: -8px;
        background: #eb6506;
        color: #fff;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .mobile-bottom-nav {
            display: flex;
        }
        /* Add padding to body so content isn't covered */
        body {
            padding-bottom: 60px; 
        }
    }
</style>
