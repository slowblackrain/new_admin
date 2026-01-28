<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? '도매토피아' }}</title>

    <!-- Legacy CSS -->
    <link rel="stylesheet" href="{{ asset('css/legacy/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/legacy/dometopia.css') }}">
    <link rel="stylesheet" href="{{ asset('css/legacy/main.css') }}">
    {{-- Font Awesome (Assuming it was linked or usually available) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/legacy/responsive.css') }}">
</head>

<body>

    @include('front.layouts.mobile_header')
    @include('front.layouts.mobile_sidebar')
    @include('front.layouts.mobile_bottom_nav')

    <x-layout.header />

    <main id="main-container" style="min-height: 600px;">
        @yield('content')
    </main>

    <x-layout.footer />

    @stack('scripts')
    <script src="{{ asset('js/legacy/goods-display-doto.js') }}"></script>
    <script src="{{ asset('js/quick_menu.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            QuickMenu.init('{{ csrf_token() }}');
        });
    </script>
</body>
</html>