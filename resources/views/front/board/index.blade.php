@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; {{ $boardConfig->name ?? '게시판' }}</em>
        </div>
    </div>

    <div class="content_wrap">
        <div class="sub_tit_area">
            <h3>{{ $boardConfig->name ?? '게시판' }}</h3>
        </div>

        <div class="board_list_area">
            <table class="board_table">
                <colgroup>
                    <col width="8%">
                    <col width="*">
                    <col width="12%">
                    <col width="12%">
                    <col width="8%">
                </colgroup>
                <thead>
                    <tr>
                        <th>번호</th>
                        <th>제목</th>
                        <th>작성자</th>
                        <th>등록일</th>
                        <th>조회수</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                        <tr class="{{ $post->notice ? 'notice_tr' : '' }}">
                            <td class="text-center">
                                @if($post->notice)
                                    <span class="badge_notice">공지</span>
                                @else
                                    {{ $post->seq }}
                                    {{-- Use virtual number logic if needed: $posts->total() - ($posts->currentPage() - 1) *
                                    $posts->perPage() - $loop->index --}}
                                @endif
                            </td>
                            <td class="text-left subject">
                                <a href="{{ route('board.view', ['id' => $boardId, 'seq' => $post->seq]) }}">
                                    {{ $post->subject }}
                                    @if($post->secret_use == 'Y' || $post->secret_use == 'A')
                                        <i class="fas fa-lock"></i>
                                    @endif
                                    @if($post->comment > 0)
                                        <span class="comment_cnt">({{ $post->comment }})</span>
                                    @endif
                                </a>
                            </td>
                            <td class="text-center">{{ $post->name }}</td>
                            <td class="text-center">{{ substr($post->r_date, 0, 10) }}</td>
                            <td class="text-center">{{ number_format($post->hit) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="no_data">등록된 게시물이 없습니다.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="paging_area">
                {{ $posts->withQueryString()->links() }}
            </div>

            <div class="btn_area text-right" style="margin-top: 20px;">
                {{-- Check Write Permission logic ideally --}}
                @if($boardConfig->auth_write_use == 'Y' || ($boardConfig->auth_write_use == 'N' && Auth::check() /* Only members? needs strict check */))
                    <a href="#" class="btn_blue">글쓰기</a>
                @endif
            </div>
        </div>
    </div>

    <style>
        .board_table {
            width: 100%;
            border-top: 2px solid #333;
            border-bottom: 1px solid #ccc;
        }

        .board_table th {
            background: #f9f9f9;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            color: #333;
        }

        .board_table td {
            padding: 12px 10px;
            border-bottom: 1px solid #eee;
            color: #666;
            font-size: 13px;
        }

        .board_table td.subject a {
            color: #333;
            text-decoration: none;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .board_table td.subject a:hover {
            text-decoration: underline;
            color: #000;
        }

        .notice_tr td {
            background-color: #fcfcfc;
            font-weight: bold;
        }

        .badge_notice {
            background: #666;
            color: #fff;
            padding: 2px 6px;
            font-size: 11px;
            border-radius: 3px;
        }

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .paging_area {
            margin-top: 30px;
            text-align: center;
        }

        .comment_cnt {
            color: #f60;
            font-size: 11px;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .board_table colgroup {
                display: none;
            }

            .board_table thead {
                display: none;
            }

            .board_table tr {
                display: block;
                border-bottom: 1px solid #ddd;
                padding: 10px;
                position: relative;
            }

            .board_table td {
                display: block;
                border: none;
                padding: 2px 0;
                text-align: left;
            }

            .board_table td:nth-child(1) {
                display: none;
                /* Hide No */
            }

            .board_table td:nth-child(2) {
                font-size: 15px;
                font-weight: bold;
                padding-bottom: 5px;
            }

            .board_table td:nth-child(3) {
                display: inline-block;
                font-size: 12px;
                color: #999;
                margin-right: 10px;
            }

            .board_table td:nth-child(3):after {
                content: '|';
                margin-left: 10px;
                color: #ddd;
            }

            .board_table td:nth-child(4) {
                display: inline-block;
                font-size: 12px;
                color: #999;
            }

            .board_table td:nth-child(5) {
                position: absolute;
                right: 10px;
                top: 15px;
                font-size: 12px;
            }
        }
    </style>
@endsection