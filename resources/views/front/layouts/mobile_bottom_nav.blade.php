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
    <a href="/cart" class="nav-item {{ Request::is('cart*') ? 'active' : '' }}">
        <div style="position: relative; display: inline-block;">
            <i class="fas fa-shopping-cart"></i>
            @if(isset($cartCount) && $cartCount > 0)
                <span class="badge">{{ $cartCount }}</span>
            @endif
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
        height: 65px; /* Increased height for better touch area */
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 -2px 10px rgba(0,0,0,0.08); /* Softer shadow */
        z-index: 1000;
        justify-content: space-around;
        align-items: center;
        /* iOS Safe Area Padding */
        padding-bottom: env(safe-area-inset-bottom);
        backdrop-filter: blur(10px); /* Modern blur effect */
    }

    .mobile-bottom-nav .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #999;
        font-size: 11px; /* Slightly larger text */
        font-weight: 500;
        flex: 1; /* Distribute evenly */
        height: 100%;
        transition: all 0.2s ease-in-out; /* Smooth hover/active transition */
    }

    .mobile-bottom-nav .nav-item i {
        font-size: 22px; /* Emphasize icon */
        margin-bottom: 5px;
        transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); /* Bouncy scale */
    }

    .mobile-bottom-nav .nav-item:active i {
        transform: scale(0.85); /* Touch feedback */
    }

    .mobile-bottom-nav .nav-item.active {
        color: #ff5722; /* Dometopia Primary Brand Color */
    }

    .mobile-bottom-nav .nav-item.active i {
        transform: translateY(-2px); /* Lift up effect when active */
    }

    .mobile-bottom-nav .nav-item .badge {
        position: absolute;
        top: -4px;
        right: -10px;
        background: #ff5722;
        color: #fff;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #fff;
        box-sizing: content-box;
    }

    @media (max-width: 1024px) {
        .mobile-bottom-nav {
            display: flex;
        }
        body {
            /* Account for nav bar height + safe area */
            padding-bottom: calc(65px + env(safe-area-inset-bottom)); 
        }
    }
</style>
