@extends('layouts.front')

@section('content')
<div id="contents">
    <div class="location_cont">
        <em><a href="/">HOME</a> &gt; 기획전/이벤트</em>
    </div>

    <div class="promotion_list_area">
        <h1>기획전/이벤트</h1>
        
        <div class="promotion_list">
            @forelse($events as $event)
            <div class="promotion_item">
                <a href="{{ route('promotion.view', ['seq' => $event->event_seq]) }}">
                    <div class="thumb">
                        @if($event->event_banner)
                            <img src="/data/event/{{ $event->event_banner }}" alt="{{ $event->title }}" onerror="this.src='/images/no_image.gif'">
                        @else
                            <img src="/images/no_image.gif" alt="No Image">
                        @endif
                    </div>
                    <div class="info">
                        <div class="title">{{ $event->title }}</div>
                        <div class="date">{{ $event->start_date->format('Y.m.d') }} ~ {{ $event->end_date->format('Y.m.d') }}</div>
                        <div class="desc">{{ Str::limit(strip_tags($event->title_contents), 100) }}</div>
                    </div>
                </a>
            </div>
            @empty
            <div class="no_data">
                현재 진행중인 기획전이 없습니다.
            </div>
            @endforelse
        </div>

        <div class="paging_area">
            {{ $events->links() }}
        </div>
    </div>
</div>

<style>
.promotion_list {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 30px;
}
.promotion_item {
    width: 100%; /* Or specific width if grid */
    border: 1px solid #eee;
    padding: 20px;
    display: flex;
    gap: 20px;
}
.promotion_item .thumb img {
    max-width: 300px;
    height: auto;
}
.promotion_item .info .title {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 10px;
}
.promotion_item .info .date {
    color: #888;
    margin-bottom: 10px;
}
.no_data {
    width: 100%;
    text-align: center;
    padding: 50px 0;
    background: #f9f9f9;
}
</style>
@endsection
