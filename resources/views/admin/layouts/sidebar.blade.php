<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>대시보드</p>
            </a>
        </li>

        <li class="nav-header">주문관리</li>
        <li class="nav-item">
            <a href="{{ route('admin.order.catalog') }}" class="nav-link {{ request()->routeIs('admin.order.catalog') ? 'active' : '' }}">
                <i class="nav-icon fas fa-shopping-cart"></i>
                <p>전체 주문리스트</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.order.bank_check') }}" class="nav-link {{ request()->routeIs('admin.order.bank_check') ? 'active' : '' }}">
                <i class="nav-icon fas fa-money-check-alt"></i>
                <p>무통장 입금확인</p>
            </a>
        </li>

        <li class="nav-header">회원관리</li>
        <li class="nav-item">
            <a href="{{ route('admin.member.catalog') }}" class="nav-link {{ request()->routeIs('admin.member.catalog') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>회원리스트</p>
            </a>
        </li>

        <li class="nav-header">입점사관리</li>
        <li class="nav-item">
            <a href="{{ route('admin.provider.catalog') }}" class="nav-link {{ request()->routeIs('admin.provider.catalog') ? 'active' : '' }}">
                <i class="nav-icon fas fa-store"></i>
                <p>입점사리스트</p>
            </a>
        </li>

    </ul>
</nav>
