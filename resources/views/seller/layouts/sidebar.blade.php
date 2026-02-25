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

                <li class="nav-item">
                    <a href="{{ route('seller.my.index') }}" class="nav-link {{ request()->routeIs('seller.my.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>내 정보 관리</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="alert('계약서 열람 라우트는 구상 중입니다.')">
                        <i class="nav-icon fas fa-file-contract"></i>
                        <p>계약서 열람</p>
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

                {{-- 상품 관리 메뉴 (일반 셀러용) --}}
                <li class="nav-header">상품 관리</li>
                <li class="nav-item">
                    <a href="{{ route('seller.goods.index') }}" class="nav-link {{ request()->routeIs('seller.goods.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>상품 목록</p>
                    </a>
                </li>

                {{-- 일반 공급사 메뉴 --}}
                <li class="nav-header">매출/정산</li>
                <li class="nav-item">
                    <a href="{{ route('seller.order.catalog') }}" class="nav-link {{ request()->routeIs('seller.order.catalog') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-link"></i>
                        <p>일반/연동주문 (리스트)</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.link.upload') }}" class="nav-link {{ request()->routeIs('seller.link.upload') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-excel"></i>
                        <p>엑셀업로드</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.export.catalog') }}" class="nav-link {{ request()->routeIs('seller.export.catalog') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>출고리스트</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.return.index') }}" class="nav-link {{ request()->routeIs('seller.return.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-undo"></i>
                        <p>반품리스트</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.refund.index') }}" class="nav-link {{ request()->routeIs('seller.refund.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p>환불리스트</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.point.emoney') }}" class="nav-link {{ request()->routeIs('seller.point.emoney') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>포인트내역</p>
                    </a>
                </li>

                {{-- ATS 공급사 전용 (Premium/Investment) 메뉴 --}}
                @if($is_ats_provider)
                <li class="nav-header">전용 정산/통계 (Premium)</li>
                <li class="nav-item">
                    <a href="{{ route('seller.point.cash') }}" class="nav-link {{ request()->routeIs('seller.point.cash') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-money-bill"></i>
                        <p>캐시내역</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.account.summary') }}" class="nav-link {{ request()->routeIs('seller.account.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice-dollar"></i>
                        <p>정산내역</p>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('seller.statistics.*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('seller.statistics.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-pie"></i>
                        <p>
                            통계
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                         <li class="nav-item">
                            <a href="{{ route('seller.statistics.sales_monthly') }}" class="nav-link {{ request()->routeIs('seller.statistics.sales_monthly') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>월별 매출통계</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.statistics.sales_daily') }}" class="nav-link {{ request()->routeIs('seller.statistics.sales_daily') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>일별 매출통계</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('seller.statistics.goods') }}" class="nav-link {{ request()->routeIs('seller.statistics.goods') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>상품별 판매통계</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                
                {{-- ShopOn 제휴 공급사 --}}
                @php
                    $is_shopon = $member->shopon ?? false;
                    $is_shopon_hosting = ($member->shopon_hosting ?? '') == 'Y';
                @endphp
                @if($is_shopon || $is_shopon_hosting)
                <li class="nav-header">ShopOn 제휴</li>
                @if($is_shopon)
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="alert('샵온 로그인 라우트는 구상 중입니다.')">
                        <i class="nav-icon fas fa-store"></i>
                        <p>샵온 로그인</p>
                    </a>
                </li>
                @endif
                @if($is_shopon_hosting)
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="alert('샵온 생성 라우트는 구상 중입니다.')">
                        <i class="nav-icon fas fa-plus-circle"></i>
                        <p>샵온 생성</p>
                    </a>
                </li>
                @endif
                @endif

                <li class="nav-header">게시판</li>
                <li class="nav-item">
                    <a href="{{ route('seller.board.index', 'notice') }}" class="nav-link {{ request()->fullUrlIs(route('seller.board.index', 'notice')) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-bullhorn"></i>
                        <p>공지사항</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('seller.board.index', 'product_notice') }}" class="nav-link {{ request()->fullUrlIs(route('seller.board.index', 'product_notice')) ? 'active' : '' }}">
                        <i class="nav-icon fas fa-exclamation-circle"></i>
                        <p>상품 중요 공지</p>
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
