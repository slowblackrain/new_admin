@extends('layouts.front')

@section('content')
<div id="contents">
    <div class="location_cont">
        <em><a href="/">HOME</a> &gt; <a href="{{ route('promotion.index') }}">기획전/이벤트</a> &gt; {{ $event->title }}</em>
    </div>

    <div class="promotion_view_area">
        <div class="event_header">
            <h1>{{ $event->title }}</h1>
            <div class="date">{{ $event->start_date->format('Y.m.d') }} ~ {{ $event->end_date->format('Y.m.d') }}</div>
        </div>

        <div class="event_content">
            {!! $event->title_contents !!}
            
            {{-- Main Contents --}}
            @if($event->contents)
                 <div class="mt-4">{!! $event->contents !!}</div>
            @endif
        </div>

        {{-- Product List --}}
        <div class="goods_list_area mt-5">
             <div class="goods_list">
                @forelse($goodsList as $item)
                    @include('front.goods.component.legacy_product_item', ['item' => $item])
                @empty
                    <div class="no_data" style="width:100%; padding: 50px; text-align: center;">
                        관련 상품이 없습니다.
                    </div>
                @endforelse
            </div>
             <div class="paging_area">
                {{ $goodsList->links() }}
            </div>
        </div>
        
        <div class="btn_area text-center mt-4">
            <a href="{{ route('promotion.index') }}" class="btn_gray">목록보기</a>
        </div>
    </div>
</div>

<style>
.event_header {
    border-bottom: 2px solid #333;
    padding-bottom: 20px;
    margin-bottom: 30px;
}
.event_header h1 {
    font-size: 24px;
    margin-bottom: 10px;
}
.event_content img {
    max-width: 100%;
}
/* Reusing goods list styles from catalog */
.goods_list {
    display: flex;
    flex-wrap: wrap;
    margin-left: -10px; /* Counteract padding */
}
</style>
@endsection
