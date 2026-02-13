@extends('layouts.front')

@section('content')
<div class="content-container">
    <h2 class="board-title">{{ $boardConfig->name ?? '대량견적' }}</h2>

    {{-- Search Form --}}
    <div class="search-box">
        <form action="{{ route('board.index') }}" method="GET">
            <input type="hidden" name="id" value="{{ $boardId }}">
            <input type="text" name="search_text" value="{{ request('search_text') }}" placeholder="검색어를 입력하세요">
            <button type="submit">검색</button>
        </form>
    </div>

    {{-- Post List --}}
    <table class="board-table">
        <thead>
            <tr>
                <th>번호</th>
                <th>제목</th>
                <th>작성자</th>
                <th>날짜</th>
                <th>조회수</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posts as $post)
            <tr>
                <td>{{ $post->seq }}</td>
                <td class="subject">
                    <a href="{{ route('board.view', ['id' => $boardId, 'seq' => $post->seq]) }}">
                        {{ $post->subject }}
                        @if($post->comment > 0)
                            <span class="comment-count">({{ $post->comment }})</span>
                        @endif
                        @if($post->hidden == 1)
                            <i class="fas fa-lock"></i>
                        @endif
                    </a>
                </td>
                <td>{{ $post->name }}</td>
                <td>{{ substr($post->r_date, 0, 10) }}</td>
                <td>{{ $post->hit }}</td>
            </tr>
            @endforeach

            @if($posts->isEmpty())
            <tr>
                <td colspan="5" class="empty-message">게시글이 없습니다.</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="pagination">
        {{ $posts->links() }}
    </div>

    {{-- Write Button --}}
    <div class="btn-area">
        <a href="{{ route('board.create', ['id' => $boardId]) }}" class="btn-write">글쓰기</a>
    </div>
</div>

<style>
    /* Basic Styles for Board */
    .content-container { padding: 15px; }
    .board-title { font-size: 20px; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .board-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
    .board-table th { border-bottom: 1px solid #ccc; padding: 10px; background: #f9f9f9; font-weight: bold; }
    .board-table td { border-bottom: 1px solid #eee; padding: 10px; text-align: center; }
    .board-table td.subject { text-align: left; }
    .board-table td.subject a { text-decoration: none; color: #333; display: block; }
    .empty-message { padding: 50px; color: #999; }
    .pagination { text-align: center; margin: 20px 0; }
    .btn-area { text-align: right; }
    .btn-write { display: inline-block; padding: 10px 20px; background: #333; color: #fff; text-decoration: none; font-weight: bold; border-radius: 3px; }
</style>
@endsection
