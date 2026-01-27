<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>도매토피아 슈퍼관리자</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        /* Custom Top Menu Styling for Readability */
        .main-header.navbar {
            border-bottom: 2px solid #dee2e6;
        }
        .navbar-nav .nav-link {
            font-size: 1.05rem;
            font-weight: 600;
            color: #343a40 !important;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
            color: #007bff !important;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .dropdown-menu {
            margin-top: 0;
            border-top: 3px solid #007bff;
        }
        .dropdown-item {
            padding: 8px 20px;
            font-size: 0.95rem;
        }
        .dropdown-item:hover {
            background-color: #e9ecef;
            color: #007bff;
        }
        /* Make content width fluid but padded */
        .content-wrapper > .content {
            padding: 20px;
        }
    </style>
</head>
<body class="hold-transition layout-top-nav layout-navbar-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
        <div class="container-fluid">
            <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
                <img src="{{ asset('images/logo.jpg') }}" alt="Dometopia" class="brand-image" style="height: 40px; width: auto;">
            </a>

            <button class="navbar-toggler order-1" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-3" id="navbarCollapse">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    
                    @foreach(config('admin_menu') as $key => $section)
                    <li class="nav-item dropdown">
                        <a id="dropdownSubMenu{{ $key }}" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle {{ request()->is('admin/'. $key .'*') ? 'active' : '' }}">
                            {{ $section['name'] }}
                        </a>
                        <ul aria-labelledby="dropdownSubMenu{{ $key }}" class="dropdown-menu border-0 shadow">
                            @foreach($section['items'] as $item)
                            <li><a href="{{ url($item['url']) }}" class="dropdown-item">{{ $item['name'] }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                    @endforeach

                </ul>
            </div>

            <!-- Right navbar links -->
            <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
                <li class="nav-item">
                    @if(Auth::guard('admin')->check())
                        <span class="nav-link">{{ Auth::guard('admin')->user()->mname }} ({{ Auth::guard('admin')->user()->manager_id }})</span>
                    @else
                        <a href="{{ route('admin.login') }}" class="nav-link">로그인</a>
                    @endif
                </li>
                <li class="nav-item">
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">로그아웃</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>
    <!-- /.navbar -->
    
    <!-- Main Sidebar Container (Hidden/Removed per request) -->
    <!--
    <aside class="main-sidebar sidebar-dark-primary elevation-4" style="display:none;">
    </aside>
    -->

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid"></div>
        </div>
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>
    <!-- /.content-wrapper -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; 2024 <a href="#">Dometopia</a>.</strong> All rights reserved.
    </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@yield('custom_js')
</body>
</html>
