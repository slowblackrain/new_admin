@extends('layouts.front')

@section('content')
<link href="{{ asset('css/gift.css') }}" rel="stylesheet" type="text/css">
<style>
    /* Inline styles for parity if CSS file is missing or needs tweaks */
    .gift-container { width: 100%; overflow-x: hidden; }
    .main-top_tit { text-align: center; }
    .main-top_tit img { max-width: 100%; }
    
    .scroll_wrap {
        overflow-x: auto;
        white-space: nowrap;
        padding: 10px;
        background: #fff;
        -webkit-overflow-scrolling: touch;
    }
    .scroll-element {
        display: inline-block;
        width: 80px;
        text-align: center;
        margin-right: 10px;
        vertical-align: top;
    }
    .scroll-element img { width: 60px; height: 60px; border-radius: 50%; }
    .scroll-element p { font-size: 11px; margin-top: 5px; color: #333; }

    .p_bakcolor { background: #f4f4f4; padding: 20px 0; }
    .best-icon { text-align: center; margin-bottom: 10px; }
    .best-icon.tit { font-size: 18px; font-weight: bold; color: #333; }
    
    .gift-table { width: 100%; border-collapse: collapse; }
    .gift-table td { padding: 5px; text-align: center; vertical-align: top; }
    .gift-table img { max-width: 100%; border-radius: 5px; }

    .p_bakcolor2 { background: #fff; padding: 20px 0; }
    .tabs { list-style: none; padding: 0; margin: 0; display: flex; border-bottom: 2px solid #555; }
    .tabs li { 
        flex: 1; text-align: center; padding: 10px; cursor: pointer; 
        background: #f9f9f9; color: #555; font-weight: bold;
    }
    .tabs li.current { background: #555; color: #fff; }
    .tab-content { display: none; padding: 15px 5px; }
    .tab-content.current { display: block; }

    /* Product Grid Reuse */
    .gift_goods_list ul { display: flex; flex-wrap: wrap; padding: 0; margin: 0; list-style: none; }
    .gift_goods_list li { width: 50%; padding: 5px; box-sizing: border-box; }
</style>

<div class="gift-container">
    {{-- Main Banner (Placeholder for showDesignBanner(6)) --}}
    <div class="main-top_tit">
        <img src="{{ asset('images/legacy/gift/top_banner001.png') }}" style="width:100%;" alt="Gift Main Banner"> 
    </div>

    {{-- Horizontal Scroll Nav --}}
    <div class="scroll_wrap">
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'01460008']) }}"><img src="{{ asset('images/legacy/gift/top_banner001.png') }}"/></a><p>판촉물 BEST</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'01460009']) }}"><img src="{{ asset('images/legacy/gift/top_banner002.png') }}"/></a><p>인쇄 판촉물</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'0021']) }}"><img src="{{ asset('images/legacy/gift/top_banner003.png') }}"/></a><p>가정생활용품</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'0017']) }}"><img src="{{ asset('images/legacy/gift/top_banner004.png') }}"/></a><p>문구</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'0047']) }}"><img src="{{ asset('images/legacy/gift/top_banner005.png') }}"/></a><p>미용</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'0020']) }}"><img src="{{ asset('images/legacy/gift/top_banner006.png') }}"/></a><p>가전</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'0097']) }}"><img src="{{ asset('images/legacy/gift/top_banner007.png') }}"/></a><p>스포츠</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'0042']) }}"><img src="{{ asset('images/legacy/gift/top_banner008.png') }}"/></a><p>주방용품</p></div>
        <div class="scroll-element"><a href="{{ route('goods.catalog', ['code'=>'0098']) }}"><img src="{{ asset('images/legacy/gift/top_banner009.png') }}"/></a><p>캠핑</p></div>
    </div>

    {{-- Best Categories --}}
    <div class="p_bakcolor">
        <div class="best-icon"><img src="{{ asset('images/legacy/gift/promotion_icon.png') }}"></div>
        <div class="best-icon tit">베스트 카테고리</div>
        <table class="gift-table">
            <tr>
                <td colspan="2"><a href="{{ route('goods.catalog', ['code'=>'004200130010']) }}"><img src="{{ asset('images/legacy/gift/p_list_banner01.jpg') }}" alt="보온병"></a></td>
            </tr>
            <tr>
                <td colspan="2"><a href="{{ route('goods.catalog', ['code'=>'0017']) }}"><img src="{{ asset('images/legacy/gift/p_list_banner02.jpg') }}" alt="문구"></a></td>
            </tr>
            <tr>
                <td><a href="{{ route('goods.catalog', ['code'=>'001700270002']) }}"><img src="{{ asset('images/legacy/gift/p_list_banner03.jpg') }}" alt="다이어리"></a></td>
                <td><a href="{{ route('goods.catalog', ['code'=>'00420020']) }}"><img src="{{ asset('images/legacy/gift/p_list_banner04.jpg') }}" alt="수저세트"></a></td>
            </tr>
        </table>
    </div>

    {{-- Theme Exhibition (Tabs) --}}
    <div class="p_bakcolor2">
        <div class="best-icon"><img src="{{ asset('images/legacy/gift/promotion_icon2.png') }}"></div>
        <div class="best-icon tit">테마 기획전</div>
        
        <ul class="tabs">
            <li class="tab-link current" data-tab="tab-1">필기류</li>
            <li class="tab-link" data-tab="tab-2">보온병/물병</li>
            <li class="tab-link" data-tab="tab-3">문구류</li>
        </ul>

        <div id="tab-1" class="tab-content current">
            <div class="gift_goods_list">
                <ul>
                    @foreach($theme1 as $product)
                        <li>@include('front.goods.component.legacy_product_item', ['product' => $product])</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div id="tab-2" class="tab-content">
            <div class="gift_goods_list">
                <ul>
                    @foreach($theme2 as $product)
                        <li>@include('front.goods.component.legacy_product_item', ['product' => $product])</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div id="tab-3" class="tab-content">
             <div class="gift_goods_list">
                <ul>
                    @foreach($theme3 as $product)
                        <li>@include('front.goods.component.legacy_product_item', ['product' => $product])</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Promotional BEST 100 --}}
    <div>
        <div class="best-icon tit" style="margin-top:20px;">판촉 BEST 100</div>	
        <div class="gift_goods_list">
            <ul>
                @foreach($bestProducts as $product)
                    <li>@include('front.goods.component.legacy_product_item', ['product' => $product])</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tabs = document.querySelectorAll('.tab-link');
        var contents = document.querySelectorAll('.tab-content');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                var tabId = this.getAttribute('data-tab');

                tabs.forEach(t => t.classList.remove('current'));
                contents.forEach(c => c.classList.remove('current'));

                this.classList.add('current');
                document.getElementById(tabId).classList.add('current');
            });
        });
    });
</script>
@endsection
