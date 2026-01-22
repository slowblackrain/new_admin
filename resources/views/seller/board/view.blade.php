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
                    <h3 class="card-title font-weight-bold">{{ $post->subject }}</h3>
                    <div class="card-tools text-muted">
                        <small>작성자: {{ $post->name }}</small> | 
                        <small>작성일: {{ $post->r_date }}</small>
                        @if($boardId !== 'mbqna')
                             | <small>조회: {{ number_format($post->hit) }}</small>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="post-content" style="min-height: 200px;">
                        {!! nl2br(e($post->contents)) !!}
                    </div>

                    @if($boardId === 'mbqna' && ($post->re_status == 'y' || $post->re_contents))
                        <div class="alert alert-secondary mt-4">
                            <h5><i class="icon fas fa-reply"></i> 답변</h5>
                            <div class="mb-2 text-muted">
                                <small>답변일: {{ $post->re_date }}</small>
                            </div>
                            {!! nl2br(e($post->re_contents)) !!}
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <a href="{{ route('seller.board.index', $boardId) }}" class="btn btn-default">목록</a>
                    
                    {{-- Edit/Delete for mbqna if not replied? (Optional future feature) --}}
                </div>
            </div>

        </div>
    </div>
@endsection
