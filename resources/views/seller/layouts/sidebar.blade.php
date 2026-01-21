<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('seller.dashboard') }}" class="brand-link">
        <span class="brand-text font-weight-light">Seller Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block">{{ Auth::guard('seller')->user()->provider_name }}</a>
            </div>
        </div>

        @php
            $seller = Auth::guard('seller')->user();
            // Fetch member data to check provider_YN (ATS permission)
            $member = \Illuminate\Support\Facades\DB::table('fm_member')
                        ->where('userid', $seller->provider_id)
                        ->first();
            $is_ats_provider = $member && $member->provider_YN == 'Y';
            // Check for admin permission (provider_seq 3151)
            $is_admin = $seller->provider_seq == 3151;
        @endphp

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="{{ route('seller.dashboard') }}" class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- 상품투자 (ATS) - 조건부 --}}
                @if($is_ats_provider)
                <li class="nav-header">상품투자 (ATS)</li>
                <li class="nav-item {{ request()->is('seller/ats*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('seller/ats*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>
                            상품투자
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('seller.ats.catalog') }}" class="nav-link {{ request()->fullUrlIs(route('seller.ats.catalog')) ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>전체상품</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_agency']) }}" class="nav-link {{ request()->fullUrlIs(route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_agency'])) ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>대행상품</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_only']) }}" class="nav-link {{ request()->fullUrlIs(route('seller.ats.catalog', ['ATS_status_plus' => 'ATS_only'])) ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>단독상품</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.ats.social_catalog') }}" class="nav-link {{ request()->routeIs('seller.ats.social_catalog') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>티켓/쿠폰</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.ats.settlement') }}" class="nav-link {{ request()->routeIs('seller.ats.settlement') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>정산확인</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                {{-- 관리자 전용 메뉴 --}}
                @if($is_admin)
                <li class="nav-header">관리자 메뉴</li>
                <li class="nav-item">
                    <a href="{{ route('seller.goods.create') }}" class="nav-link {{ request()->routeIs('seller.goods.create') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-plus-square"></i>
                        <p>상품등록</p>
                    </a>
                </li>
                @endif

                {{-- 일반 공급사 메뉴 --}}
                <li class="nav-header">매출/정산</li>
                <li class="nav-item">
                    <a href="{{ route('seller.order.catalog') }}" class="nav-link {{ request()->routeIs('seller.order.catalog') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-link"></i>
                        <p>연동주문</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.point.emoney') }}" class="nav-link {{ request()->routeIs('seller.point.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>포인트내역</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.statistics.goods') }}" class="nav-link {{ request()->routeIs('seller.statistics.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>통계</p>
                    </a>
                </li>

                <li class="nav-header">게시판</li>
                <li class="nav-item">
                    <a href="{{ route('seller.board.index', 'notice') }}" class="nav-link {{ request()->fullUrlIs(route('seller.board.index', 'notice')) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bullhorn"></i>
                        <p>공지사항</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.board.index', 'gs_seller_notice') }}" class="nav-link {{ request()->fullUrlIs(route('seller.board.index', 'gs_seller_notice')) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bell"></i>
                        <p>셀러 공지사항</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.board.index', 'mbqna') }}" class="nav-link {{ request()->fullUrlIs(route('seller.board.index', 'mbqna')) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-comments"></i>
                        <p>1:1 문의</p>
                    </a>
                </li>

            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
