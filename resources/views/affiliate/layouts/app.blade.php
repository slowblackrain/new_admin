<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>제휴처 관리 프로그램 - 도매토피아</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Gray-100 */
        }
    </style>
</head>
<body class="antialiased text-slate-800">

    @auth('admin')
        <!-- Navbar -->
        <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <span class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                                DOMETOPIA Affiliate
                            </span>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('affiliate.dashboard') }}" class="{{ request()->routeIs('affiliate.dashboard') ? 'border-indigo-500 text-slate-900' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                대시보드
                            </a>
                            <a href="{{ route('affiliate.settings.category') }}" class="{{ request()->routeIs('affiliate.settings.category') ? 'border-indigo-500 text-slate-900' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                                카테고리 맵핑
                            </a>
                            <a href="{{ route('affiliate.settings.sync') }}" class="{{ request()->routeIs('affiliate.settings.sync') ? 'border-indigo-500 text-slate-900' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors">
                                상품 동기화(Batch)
                            </a>
                        </div>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <span class="text-sm text-slate-500 mr-4">{{ Auth::guard('admin')->user()->mname ?? '관리자' }} 님</span>
                        <form method="POST" action="{{ route('affiliate.logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium transition-colors">로그아웃</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endauth

    <!-- Main Content -->
    <main class="py-10">
        @yield('content')
    </main>

</body>
</html>
