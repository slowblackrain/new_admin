<div id="mobile_header" class="mobile-header">
    <div class="header-left">
        <button type="button" class="btn_category" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    </div>
    <div class="header-center">
        <h1 class="logo">
            <a href="/"><img src="{{ asset('images/legacy/design/logo.png') }}" alt="dometopia" /></a>
        </h1>
    </div>
    <div class="header-right">
        <button type="button" class="btn_search" onclick="toggleSearch()"><i class="fas fa-search"></i></button>
        <a href="/order/cart" class="btn_cart"><i class="fas fa-shopping-cart"></i></a>
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
        height: 60px; /* Increased height */
        background: #fff;
        z-index: 999;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        align-items: center;
        justify-content: space-between;
        padding: 0 10px;
        box-sizing: border-box;
    }

    .mobile-header .header-center .logo {
        margin: 0;
        padding: 0;
        line-height: 1;
    }
    .mobile-header .header-center .logo img {
        height: 40px; /* Increased logo size */
        width: auto;
    }

    .mobile-header button, .mobile-header a {
        background: none;
        border: none;
        font-size: 20px;
        color: #333;
        padding: 5px;
        cursor: pointer;
        text-decoration: none;
    }

    /* Show only on mobile */
    @media (max-width: 768px) {
        .mobile-header {
            display: flex;
        }
        /* Hide desktop header wrapper */
        .dometopia_header {
            display: none !important;
        }
    }
</style>
