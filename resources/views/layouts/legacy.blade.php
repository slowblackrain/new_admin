<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? '도메토피아' }}</title>
    <link rel="stylesheet" href="{{ asset('css/legacy/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/legacy/main.css') }}">
    <style>
        /* Header specific legacy styling integration */
        .dometopia_header {
            width: 100%;
            background: #fff;
        }

        .header-top-wrap {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }

        .logo {
            margin-right: 40px;
        }

        .header-search-wrap {
            flex: 1;
        }

        .header-search-box {
            width: 100%;
            max-width: 500px;
            border: 2px solid #eb6506;
            border-radius: 25px;
            padding: 5px 20px;
            display: flex;
        }

        .search_input {
            border: none;
            flex: 1;
            outline: none;
            font-size: 16px;
        }

        .topmenu ul {
            display: flex;
            list-style: none;
        }

        .topmenu li {
            margin-left: 15px;
            font-size: 13px;
        }

        #doto_footer {
            background: #f8f8f8;
            padding: 40px 0;
            margin-top: 50px;
            border-top: 1px solid #eee;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .f_nav ul {
            display: flex;
            list-style: none;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .f_nav li {
            margin-right: 20px;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <header class="dometopia_header" id="dometopia_header">
        <div class="header-top-wrap">
            <h1 class="logo">
                <a href="/"><img src="{{ asset('images/legacy/design/logo.png') }}" alt="dometopia" /></a>
            </h1>
            <div class="header-search-wrap">
                <div class="header-search-box">
                    <form action="/goods/search" style="width: 100%; display: flex;">
                        <input type="text" name="search_text" class="search_input" placeholder="검색어를 입력하세요" required>
                        <button type="submit" style="background:none; border:none; cursor:pointer;">🔍</button>
                    </form>
                </div>
            </div>
            <div class="topmenu">
                <ul>
                    <li><a href="/member/login">로그인</a></li>
                    <li><a href="/member/join">회원가입</a></li>
                    <li><a href="/service/cs">고객센터</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main id="main-container">
        @yield('content')
    </main>

    <footer id="doto_footer">
        <div class="footer-container">
            <div class="f_nav">
                <ul class="menu">
                    <li><a href="/service/privacy"><b>개인정보처리방침</b></a></li>
                    <li><a href="/service/agreement">이용약관</a></li>
                    <li><a href="/service/company">회사소개</a></li>
                </ul>
            </div>
            <div class="footer-contents">
                <p>(주)나무 | 대표이사: 대표자명 | 사업자등록번호: 000-00-00000</p>
                <p>주소: 서울특별시 어딘가 | Tel: 02-000-0000</p>
            </div>
            <div class="copyright">
                <span>Copyright ⓒ Tree Co., Ltd. All rights reserved.</span>
            </div>
        </div>
    </footer>
</body>

</html>