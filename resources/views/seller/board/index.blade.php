@extends('seller.layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $boardName }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">목록</h3>
                    <div class="card-tools">
                        <form method="GET" class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="keyword" class="form-control float-right" placeholder="제목 검색" value="{{ request('keyword') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>번호</th>
                                <th>제목</th>
                                <th>작성자</th>
                                <th>작성일</th>
                                @if($boardId === 'mbqna')
                                    <th>상태</th>
                                @else
                                    <th>조회수</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                                <tr class="{{ $post->notice == '1' ? 'bg-light font-weight-bold' : '' }}">
                                    <td>
                                        @if($post->notice == '1')
                                            <span class="badge badge-danger">공지</span>
                                        @else
                                            {{ $post->seq }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('seller.board.show', ['id' => $boardId, 'seq' => $post->seq]) }}" class="text-dark">
                                            {{ $post->subject }}
                                            @if($post->comment > 0)
                                                <small class="text-primary">({{ $post->comment }})</small>
                                            @endif
                                        </a>
                                    </td>
                                    <td>{{ $post->name }}</td>
                                    <td>{{ substr($post->r_date, 0, 10) }}</td>
                                    @if($boardId === 'mbqna')
                                        <td>
                                            @if($post->re_status == 'y' || $post->re_contents)
                                                <span class="badge badge-success">답변완료</span>
                                            @else
                                                <span class="badge badge-warning">답변대기</span>
                                            @endif
                                        </td>
                                    @else
                                        <td>{{ number_format($post->hit) }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $boardId === 'mbqna' ? 5 : 5 }}" class="text-center py-5 text-muted">게시물이 없습니다.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $posts->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
                @if($boardId === 'mbqna')
                <div class="card-footer">
                    <a href="{{ route('seller.board.create', $boardId) }}" class="btn btn-primary float-right">문의하기</a>
                </div>
                @endif
            </div>

        </div>
    </div>
@endsection
