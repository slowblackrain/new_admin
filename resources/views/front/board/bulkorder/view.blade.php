@extends('layouts.front')

@section('content')
<div class="content-container">
    <h2 class="board-title">{{ $boardConfig->name ?? '대량견적' }}</h2>

    <div class="post-view">
        <div class="post-header">
            <h3 class="post-subject">{{ $post->subject }}</h3>
            <div class="post-meta">
                <span>작성자: {{ $post->name }}</span>
                <span>날짜: {{ $post->r_date }}</span>
                <span>조회: {{ $post->hit }}</span>
            </div>
        </div>
        
        <div class="post-content">
            {!! nl2br(e($post->contents)) !!}
        </div>

        {{-- Reply/Answer Section if exists --}}
        @if($post->re_contents)
        <div class="post-reply">
            <h4><i class="fas fa-reply"></i> 답변</h4>
            <div class="reply-content">
                {!! nl2br(e($post->re_contents)) !!}
            </div>
            <div class="reply-meta">
                답변일: {{ $post->re_date }}
            </div>
        </div>
        @endif

        <div class="btn-area">
            <a href="{{ route('board.index', ['id' => $boardId]) }}" class="btn-list">목록</a>
            {{-- Modify/Delete buttons check permission --}}
        </div>
    </div>
</div>

<style>
    .content-container { padding: 15px; }
    .board-title { font-size: 20px; font-weight: bold; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
    .post-view { border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
    .post-header { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
    .post-subject { font-size: 18px; margin: 0 0 10px; }
    .post-meta { font-size: 13px; color: #888; }
    .post-meta span { margin-right: 15px; }
    .post-content { min-height: 200px; line-height: 1.6; }
    .post-reply { margin-top: 30px; background: #f9f9f9; padding: 15px; border-left: 5px solid #007bff; border-radius: 3px; }
    .post-reply h4 { margin-top: 0; color: #007bff; }
    .btn-area { margin-top: 20px; text-align: center; }
    .btn-list { padding: 10px 30px; border: 1px solid #ccc; background: #fff; color: #333; text-decoration: none; border-radius: 3px; }
</style>
@endsection
