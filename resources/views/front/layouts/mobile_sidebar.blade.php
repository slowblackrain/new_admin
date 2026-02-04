<div id="mobile_sidebar_overlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
<div id="mobile_sidebar" class="sidebar-content">
    <div class="sidebar-header">
        @auth
            <div class="user-info">
                <strong>{{ Auth::user()->name }}</strong>님 환영합니다.
                <a href="/member/logout" class="btn-logout">로그아웃</a>
            </div>
            <div class="user-links">
                <a href="/mypage">마이페이지</a>
                <a href="{{ route('mypage.order.list') }}">주문배송조회</a>
            </div>
        @else
            <div class="login-links">
                <a href="/member/login" class="btn-login">로그인</a>
                <a href="/member/agreement" class="btn-join">회원가입</a>
            </div>
        @endauth
        <button class="btn-close" onclick="toggleSidebar()"><i class="fas fa-times"></i></button>
    </div>
    
    <div class="sidebar-search">
         <form action="/goods/search" style="margin:0;" onSubmit="return Checkform(this)">
            <input type="text" name="search_text" placeholder="검색어를 입력하세요" value="{{ request('search_text') }}">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div class="sidebar-menu">
        <ul class="menu-list">
             <li><a href="/goods/newlist" class="highlight">신상품</a></li>
             <li><a href="/page/index?tpl=etc%2Fevent1809028.html" class="highlight">베스트100</a></li>
             <li><a href="/gift" class="highlight">판촉물</a></li>
             <li><a href="/goods/catalog?sort=single_item&code=0055" class="highlight">땡처리</a></li>
            
            <li class="divider"></li>

            @if(isset($globalCategories))
                @foreach($globalCategories as $cat)
                    <li>
                        <a href="{{ route('goods.catalog', ['code' => $cat->category_code]) }}">
                            {{ $cat->title }}
                        </a>
                    </li>
                @endforeach
            @else
                <li><a href="#">카테고리 로딩 실패</a></li>
            @endif
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <p>고객센터: 02-2026-2754</p>
        <p>운영시간: 평일 09:00 ~ 18:00</p>
    </div>
</div>

<style>
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
    }
    .sidebar-content {
        position: fixed;
        top: 0;
        left: -100%; /* Hidden */
        width: 80%;
        max-width: 320px;
        height: 100%;
        background: #fff;
        z-index: 1001;
        transition: left 0.3s ease;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }
    .sidebar-content.active {
        left: 0;
    }
    
    .sidebar-header {
        background: #333;
        color: #fff;
        padding: 15px;
        position: relative;
    }
    .sidebar-header .login-links a {
        color: #fff;
        margin-right: 10px;
        font-weight: bold;
        font-size: 16px;
        text-decoration: none;
    }
    .sidebar-header .user-info {
        font-size: 14px;
        margin-bottom: 5px;
    }
    .sidebar-header .user-links a {
        color: #ddd;
        font-size: 12px;
        margin-right: 10px;
    }
    .btn-close {
        position: absolute;
        top: 15px;
        right: 15px;
        background: none;
        border: none;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
    }
    
    .sidebar-search {
        padding: 10px;
        background: #f1f1f1;
    }
    .sidebar-search form {
        display: flex;
        border: 1px solid #ddd;
        background: #fff;
    }
    .sidebar-search input {
        flex: 1;
        border: none;
        padding: 8px;
        outline: none;
    }
    .sidebar-search button {
        background: none;
        border: none;
        padding: 0 10px;
        color: #555;
    }

    .sidebar-menu {
        flex: 1;
        padding: 10px 0;
    }
    .sidebar-menu ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .sidebar-menu li a {
        display: block;
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
        color: #333;
        text-decoration: none;
        font-size: 14px;
    }
    .sidebar-menu li a.highlight {
        color: #eb6506;
        font-weight: bold;
    }
    .sidebar-menu li.divider {
        height: 5px;
        background: #f5f5f5;
        border-bottom: 1px solid #eee;
    }
    
    .sidebar-footer {
        background: #f9f9f9;
        padding: 15px;
        font-size: 12px;
        color: #888;
        border-top: 1px solid #eee;
    }
    .sidebar-footer p {
        margin: 2px 0;
    }
</style>

<script>
    function Checkform(f) {
        if (!f.search_text.value.trim()) {
            alert('검색어를 입력하세요.');
            return false;
        }
        return true;
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('mobile_sidebar');
        const overlay = document.getElementById('mobile_sidebar_overlay');
        
        if (sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            overlay.style.display = 'none';
        } else {
            sidebar.classList.add('active');
            overlay.style.display = 'block';
        }
    }
    
    function toggleSearch() {
        // Option to toggle a header search bar or focus sidebar search
        toggleSidebar();
        setTimeout(() => {
            document.querySelector('.sidebar-search input').focus();
        }, 300);
    }
</script>
