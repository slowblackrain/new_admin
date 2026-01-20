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

        <div class="board_view_area">
            <div class="view_header">
                <h4 class="subject">{{ $post->subject }}</h4>
                <div class="meta_info">
                    <span class="writer">작성자: {{ $post->name }}</span>
                    <span class="date">등록일: {{ substr($post->r_date, 0, 16) }}</span>
                    <span class="hit">조회수: {{ number_format($post->hit) }}</span>
                </div>
            </div>

            {{-- Files --}}
            @if($post->upload)
                {{-- File logic: often serialized or piped string in legacy --}}
                {{-- For now displayed if logic needed --}}
            @endif

            <div class="view_content">
                {{-- Content is raw HTML from editor --}}
                {!! $post->contents !!}

                {{-- Image attachments logic (fm_boarddata often has inserted images) --}}
                @if(!empty($post->file_key_w))
                    {{-- Example: show images if not in content --}}
                @endif
            </div>

            <div class="btn_area_view">
                <a href="{{ route('board.index', ['id' => $boardConfig->id]) }}" class="btn_list">목록보기</a>
            </div>
        </div>
    </div>

    <style>
        .board_view_area {
            border-top: 2px solid #333;
            margin-top: 20px;
        }

        .view_header {
            padding: 20px 15px;
            border-bottom: 1px solid #ddd;
            background: #f9f9f9;
        }

        .view_header .subject {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }

        .view_header .meta_info {
            color: #888;
            font-size: 13px;
        }

        .view_header .meta_info span {
            margin-right: 15px;
        }

        .view_content {
            padding: 30px 15px;
            min-height: 200px;
            border-bottom: 1px solid #ddd;
            line-height: 1.6;
            color: #444;
            overflow-x: auto;
        }

        .view_content img {
            max-width: 100%;
            height: auto;
        }

        .btn_area_view {
            margin-top: 20px;
            text-align: center;
        }

        .btn_list {
            display: inline-block;
            padding: 10px 30px;
            background: #666;
            color: #fff;
            text-decoration: none;
            font-size: 14px;
        }

        .btn_list:hover {
            background: #555;
        }
    </style>
@endsection