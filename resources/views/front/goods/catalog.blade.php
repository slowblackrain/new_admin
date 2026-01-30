@extends('layouts.front')

@section('content')
    <div id="main-wrap" class="clearbox mb70" style="padding-top: 20px; width: 1200px; margin: 0 auto;">
        
        <div class="goodsroll" style="width: 100%;">
            <div class="location_wrap">
                <div class="location_cont" style="text-align: right; padding: 10px 0; border-bottom: 1px solid #eee;">
                    <em style="font-style: normal; font-size: 11px; color: #888; font-family: 'Dotum', sans-serif;">
                        <a href="/" class="local_home" style="color: #888; text-decoration: none;">HOME</a> 
                        &gt; 
                        <span style="color: #333; font-weight: bold;">{{ $categoryCode }}</span>
                    </em>
                </div>
            </div>

            <div id="goods_list" class="content_wrap">
                <div class="sub_tit_area" style="border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px;">
                    <h3 style="font-size: 24px; font-weight: bold; color: #333;">{{ $categoryCode }}</h3>
                </div>

                @if(isset($currentCategory) && $currentCategory->top_html)
                     <div class="category_top_html">
                         {!! $currentCategory->top_html !!}
                     </div>
                @endif

                {{-- Sub Category Nav --}}
                @if(isset($childCategories) && $childCategories->count() > 0)
                    <div class="sub_category_nav">
                        <ul>
                            <li class="{{ request('code') == substr(request('code'),0,4) ? '' : '' }}">
                                 {{-- Parent or 'All' link could go here if needed --}}
                            </li>
                            @foreach($childCategories as $child)
                                <li class="{{ request('code') == $child->category_code ? 'on' : '' }}">
                                    <a href="{{ route('goods.catalog', ['code' => $child->category_code]) }}">{{ $child->title }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Sort Bar --}}
                 <div class="sort_area">
                    <ul>
                        <li class="{{ $sort == '' || $sort == 'new' ? 'on' : '' }}"><a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'new'])) }}">신상품순</a></li>
                        <li class="{{ $sort == 'price_asc' ? 'on' : '' }}"><a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'price_asc'])) }}">낮은가격순</a></li>
                        <li class="{{ $sort == 'price_desc' ? 'on' : '' }}"><a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'price_desc'])) }}">높은가격순</a></li>
                        <li class="{{ $sort == 'A' ? 'on' : '' }}"><a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'A'])) }}">박스상품</a></li>
                        <li class="{{ $sort == 'G' ? 'on' : '' }}"><a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'G'])) }}">낱개상품</a></li>
                    </ul>
                </div>

                <div class="goods_list_area">
                    @if($goods->isEmpty())
                        <div class="no_data">등록된 상품이 없습니다.</div>
                    @else
                        {{-- Legacy Grid Wrapper --}}
                        <div class="goods_list_legacy_wrapper">
                            <ul class="goods_list_ul">
                                @foreach($goods as $product)
                                    <li>
                                        @include('front.goods.component.legacy_product_item', ['product' => $product])
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="paging_area">
                            {{ $goods->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/quick_menu.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof QuickMenu !== 'undefined') {
                QuickMenu.init('{{ csrf_token() }}');
            }
        });

        // Wrapper to bridge Legacy-style calls to Local QuickMenu
        function add_to_cart(goodsSeq, type) {
            // Check if QuickMenu is available
            if(typeof QuickMenu !== 'undefined') {
                if(type === 'direct') {
                    // Try to buy immediately (redirects to view if options needed)
                    QuickMenu.buy(goodsSeq, null, true); 
                } else {
                    // Add to cart
                    QuickMenu.cart(goodsSeq, null, true);
                }
            } else {
                alert('쇼핑몰 기능 로딩 중입니다. 잠시 후 다시 시도해 주세요.');
            }
        }
    </script>

    <style>
        /* Legacy CSS extraction from goods_display_doto_goods_list.html */
        .goods_list_legacy_wrapper .goodsDisplayItemWrap {
            border: 2px solid #fff;
            margin-bottom: 20px;
            overflow: hidden !important;
            padding-bottom: 20px;
            width: 100%; /* Fill the li */
            background: #fff;
            position: relative;
            text-align: center;
            box-sizing: border-box;
        }
        .goods_list_legacy_wrapper .goodsDisplayItemWrap:hover {
            border: 2px solid #fc824c;
            box-shadow: 0px 0px 25px 3px rgba(0,0,0,0.15);
            z-index: 10;
        }
        .goods_list_legacy_wrapper .goodsDisplayItemWrap dd { margin: 0; padding: 0; }
        .goods_list_legacy_wrapper .goodsDisplayImageWrap { display: inline-block; position: relative; }
        .goods_list_legacy_wrapper .goodsDisplayImageWrap > a > img { 
            transform: scale(1); overflow: hidden !important; transition: all 0.6s; 
            max-width: 100%; height: auto;
        }
        .goods_list_legacy_wrapper .goodsDisplayImageWrap:hover > a > img { transform: scale(1.1); }
        
        /* Quick Menu */
        .goods_list_legacy_wrapper .goodsDisplayQuickMenu {
            width: 216px; height: 25px; 
            font-size: 15px; color: #444; 
            border-bottom: 1px solid #f2f3f4; 
            margin-bottom: 8px; text-align: center; 
            position: absolute; bottom: -20px; 
            background: rgba(255,255,255,0.95);
            transition: all .3s; left: 0; right: 0; margin: auto; 
            opacity: 0;
        }
        .goods_list_legacy_wrapper .goodsDisplayImageWrap:hover .goodsDisplayQuickMenu { bottom: 0px; opacity: 1; z-index: 2; }
        
        .goods_list_legacy_wrapper .goodsDisplayQuickIcon { position: relative; width: 50px; display: inline-block; vertical-align: middle; }
        .goods_list_legacy_wrapper .goodsDisplayQuickIcon:after { 
            content: ""; width: 1px; height: 14px; background: #f2f3f4; 
            display: inline-block; vertical-align: middle; 
            position: absolute; right: 0; top: 5px; 
        }
        .goods_list_legacy_wrapper .goodsDisplayQuickIcon:last-child:after { display: none; }
        
        /* Icons */
        .goods_list_legacy_wrapper .goodsDisplayNew { 
            display: inline-block; width: 47px; height: 24px; opacity: 0.6; cursor: pointer;
            background: url(/images/legacy/icon/goodsDisplayNew.png) no-repeat center; /* Adjust path */
        }
        .goods_list_legacy_wrapper .goodsDisplayCart { 
            display: inline-block; width: 47px; height: 24px; opacity: 0.6; cursor: pointer;
            background: url(/images/legacy/icon/goodsDisplayCart.png) no-repeat center; /* Adjust path */
        }
        .goods_list_legacy_wrapper .goodsDisplayCard { 
            display: inline-block; width: 47px; height: 24px; opacity: 0.6; cursor: pointer;
            background: url(/images/legacy/icon/goodsDisplayCard.png) no-repeat center; /* Adjust path */
        }
        .goods_list_legacy_wrapper .goodsDisplayQuickIcon:hover > span { opacity: 1; }
        
        .goods_list_legacy_wrapper .QuickIconComment {
            position: absolute; background: #FFF; border: 1px solid #cfd5da; 
            top: -25px; left: 0px; font-size: 11px; color: #9eabbb; 
            display: none; height: 18px; width: 50px; line-height: 18px;
        }
        .goods_list_legacy_wrapper .goodsDisplayQuickIcon:hover .QuickIconComment { display: block; }
        
        /* Info Area */
        .goods_list_legacy_wrapper .goodsDisplayCode { 
            padding: 9px 8px 0px; font-size: 12px; line-height: 12px; font-weight: bold; 
            color: #3ba0ff; display: block; position: relative; text-align: left;
        }
        .goods_list_legacy_wrapper .goodsDisplayTitle { padding: 0 10px; margin-bottom: 10px; text-align: left;}
        .goods_list_legacy_wrapper .goodsDisplayTitle h6 {
            font-size: 13px !important; line-height: 15px; font-weight: normal; 
            color: #333; margin: 0; padding-top: 5px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        
        /* Price Table */
        .goods_list_legacy_wrapper .goodsDisplaySalePrice { padding: 0 10px; }
        .goods_list_legacy_wrapper .goodsDisplaySalePrice table td { height: 22px; font-size: 12px; }
        .goods_list_legacy_wrapper .price_txt { padding-left: 2px; color: #666; font-size: 12px; text-align: left; }
        .goods_list_legacy_wrapper .price_num { font-size: 12px; text-align: right; padding-right: 2px; }
        .goods_list_legacy_wrapper .price_txt_HL2 { background-color: #FFDFC0; font-size: 14px; font-weight: bold; color: #2e4aef; padding-bottom: 2px; text-align: left;}
        .goods_list_legacy_wrapper .price_num_HL2 { background-color: #FFDFC0; font-size: 14px; font-weight: bold; text-align: right; color: #2e4aef; padding-right: 2px; padding-bottom: 2px; }

        .goods_list_legacy_wrapper .goodsDisplayIcon { 
            margin-top: 5px; padding: 5px 10px 0; font-size: 15px; 
            color: #0033ff; border-top: solid 1px #f7f8f9; text-align: left;
        }
        
        /* Grid Layout */
        .goods_list_ul {
            overflow: hidden; margin-top: 20px; display: flex; flex-wrap: wrap; 
            list-style: none; padding: 0; margin: 0;
        }
        .goods_list_ul li {
            width: 20%; /* Desktop: 5 cols (Legacy Parity) */
            padding: 10px;
            box-sizing: border-box;
        }
        
        /* Previous Styles for SubCategory and Sort (Keep them) */
        .sub_category_nav { margin-bottom: 20px; border: 1px solid #ddd; padding: 15px; background: #f9f9f9; }
        .sub_category_nav ul { overflow: hidden; list-style: none; padding: 0; margin: 0; }
        .sub_category_nav li { float: left; margin-right: 15px; margin-bottom: 5px; }
        .sub_category_nav li a { color: #555; font-size: 14px; text-decoration: none; }
        .sub_category_nav li.on a { font-weight: bold; color: #d00; }

        .sort_area { text-align: right; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .sort_area ul { display: inline-block; list-style: none; padding: 0; margin: 0; }
        .sort_area li { float: left; margin-left: 10px; padding-left: 10px; border-left: 1px solid #ddd; }
        .sort_area li:first-child { border-left: none; }
        .sort_area li.on a { font-weight: bold; color: #333; }
        .sort_area li a { color: #888; font-size: 12px; text-decoration: none; }
        /* Pagination (Legacy Style Match) */
        .paging_area {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 50px;
        }
        .paging_area nav {
            display: inline-block;
        }
        .paging_area .relative { 
            display: inline-flex; 
        }
        /* Hide the huge SVG/Tailwind default arrows if they appear unstyled */
        .paging_area svg {
            width: 15px; height: 15px; display: inline-block; vertical-align: middle;
            color: #555;
        }
        /* Style links */
        .paging_area a, .paging_area span {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 2px;
            border: 1px solid #ddd;
            color: #666;
            text-decoration: none;
            font-size: 12px;
            border-radius: 0; /* Legacy square style */
        }
        .paging_area span[aria-current="page"], .paging_area .active {
            background-color: #444;
            color: #fff;
            border: 1px solid #444;
            font-weight: bold;
        }
        .paging_area a:hover {
            border-color: #888;
            color: #333;
        }
    </style>
@endsection