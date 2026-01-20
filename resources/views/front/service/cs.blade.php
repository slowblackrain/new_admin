@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 고객센터</em>
        </div>
    </div>

    <div class="content_wrap">
        <div class="sub_tit_area">
            <h3>고객센터</h3>
        </div>

        <div class="cs_center_wrap">
            {{-- CS Info Box --}}
            <div class="cs_info_box">
                <div class="call_area">
                    <h4><i class="fas fa-headset"></i> 고객센터 전화번호</h4>
                    <p class="tel">02-1234-5678</p>
                    <p class="desc">평일 09:00 ~ 18:00 (점심시간 12:00 ~ 13:00)<br>주말 및 공휴일 휴무</p>
                </div>
                <div class="bank_area">
                    <h4><i class="fas fa-university"></i> 입금계좌 안내</h4>
                    <p class="bank">국민은행 123-456-78-901234</p>
                    <p class="owner">예금주: 도매토피아(주)</p>
                </div>
            </div>

            {{-- Quick Menu --}}
            <div class="cs_quick_menu">
                <ul>
                    <li><a href="{{ route('board.index', ['id' => 'notice']) }}">
                            <i class="fas fa-bullhorn"></i><br>공지사항
                        </a></li>
                    <li><a href="{{ route('board.index', ['id' => 'faq']) }}">
                            <i class="fas fa-question-circle"></i><br>자주묻는질문
                        </a></li>
                    <li><a href="{{ route('board.index', ['id' => 'qna']) }}">
                            <i class="fas fa-comments"></i><br>1:1 문의
                        </a></li>
                    <li><a href="/mypage/order/list">
                            <i class="fas fa-truck"></i><br>배송조회
                        </a></li>
                </ul>
            </div>

            {{-- Latest Notices --}}
            <div class="latest_notice_area">
                <div class="tit_group">
                    <h4>공지사항</h4>
                    <a href="{{ route('board.index', ['id' => 'notice']) }}" class="more">+ 더보기</a>
                </div>
                <ul class="notice_list">
                    @forelse($notices as $notice)
                        <li>
                            <a href="{{ route('board.view', ['id' => 'notice', 'seq' => $notice->seq]) }}">
                                <span class="subject">{{ $notice->subject }}</span>
                                <span class="date">{{ substr($notice->r_date, 0, 10) }}</span>
                            </a>
                        </li>
                    @empty
                        <li class="no_data">등록된 공지사항이 없습니다.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <style>
        .cs_center_wrap {
            padding: 20px 0;
        }

        .cs_info_box {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .cs_info_box>div {
            flex: 1;
            border: 1px solid #ddd;
            padding: 30px;
            background: #fff;
            border-radius: 5px;
        }

        .cs_info_box h4 {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .cs_info_box .tel {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }

        .cs_info_box .desc {
            color: #666;
            line-height: 1.5;
            font-size: 14px;
        }

        .cs_info_box .bank {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .cs_quick_menu {
            margin-bottom: 40px;
        }

        .cs_quick_menu ul {
            display: flex;
            gap: 10px;
        }

        .cs_quick_menu li {
            flex: 1;
            text-align: center;
        }

        .cs_quick_menu li a {
            display: block;
            border: 1px solid #ddd;
            padding: 20px 0;
            font-size: 16px;
            color: #555;
            background: #f9f9f9;
            text-decoration: none;
            transition: 0.3s;
        }

        .cs_quick_menu li a:hover {
            background: #333;
            color: #fff;
            border-color: #333;
        }

        .cs_quick_menu li a i {
            font-size: 30px;
            margin-bottom: 10px;
            color: #888;
        }

        .cs_quick_menu li a:hover i {
            color: #fff;
        }

        .latest_notice_area {
            border: 1px solid #ddd;
            padding: 20px 30px;
            background: #fff;
        }

        .latest_notice_area .tit_group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .latest_notice_area h4 {
            font-size: 18px;
            font-weight: bold;
        }

        .latest_notice_area .more {
            font-size: 12px;
            color: #888;
            text-decoration: none;
        }

        .notice_list li {
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }

        .notice_list li:last-child {
            border-bottom: none;
        }

        .notice_list li a {
            display: flex;
            justify-content: space-between;
            color: #555;
            text-decoration: none;
        }

        .notice_list li a:hover {
            color: #333;
            font-weight: bold;
        }

        .notice_list .date {
            font-size: 13px;
            color: #999;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .cs_info_box {
                flex-direction: column;
            }

            .cs_quick_menu ul {
                flex-wrap: wrap;
            }

            .cs_quick_menu li {
                width: 50%;
                flex: auto;
            }
        }
    </style>
@endsection