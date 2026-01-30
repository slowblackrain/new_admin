@extends('layouts.front')

@section('content')
    <div id="goods_list" class="content_wrap">
        <div class="sub_tit_area">
            <h3>상품검색</h3>
        </div>

        {{-- Legacy Search Form Start --}}
        <form name="goodsSearchForm" action="{{ route('goods.search') }}" method="GET">
            <input type="hidden" name="search_text" value="{{ $keyword }}" />
            <input type="hidden" name="sort" value="{{ $sort }}" />

            <div class="search-form-container" style="border:1px solid #ddd; padding:20px; margin-bottom:20px; background:#fff;">
                <div class="gsf-main-table">
                    {{-- 1. Sort Tabs --}}
                    <div class="search-form-line clearbox" style="margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">
                        <div class="pl_inline_btn">
                            <ul class="sort_list" style="overflow:hidden; list-style:none; margin:0; padding:0;">
                                @php
                                    $sortOptions = [];
                                    if(!empty($keyword)) {
                                        $sortOptions['accuracy'] = '정확도순';
                                    }
                                    $sortOptions += [
                                        'new' => '신상품순',
                                        'price_asc' => '낮은가격순',
                                        'price_desc' => '높은가격순',
                                        'popular' => '인기상품순',
                                        'popular_sales' => '판매순'
                                    ];
                                @endphp
                                @foreach($sortOptions as $key => $label)
                                    <li class="{{ $sort == $key ? 'on' : '' }}" style="float:left; margin-right:5px;">
                                        <a href="javascript:setSort('{{ $key }}')" 
                                           style="display:block; padding:5px 15px; border:1px solid #ddd; text-decoration:none; {{ $sort == $key ? 'background:#333; color:#fff;' : 'background:#f9f9f9; color:#666;' }}">
                                            {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- 2. Result Summary & Re-search --}}
                    <div class="price_wrap2" style="margin-bottom:10px;">
                        <div>
                            @if($keyword)
                                <span style="font-size:14px; margin-right:15px;">
                                    <strong>'{{ $keyword }}'</strong> 검색결과 (총 <strong style="color:#d00;">{{ number_format($goods->total()) }}</strong>개)
                                </span>
                            @else
                                <span style="font-size:14px; margin-right:15px;">총 <strong style="color:#d00;">{{ number_format($goods->total()) }}</strong>개 상품</span>
                            @endif

                            <b style="margin-left:20px;">결과내 재검색</b>
                            <span class="icon_q" title="띄어쓰기로 다중 검색이 가능합니다." style="cursor:help;">?</span>
                            <label><input type="radio" name="sub_search" value="I" {{ request('sub_search', 'I') == 'I' ? 'checked' : '' }}> 포함</label>
                            <label><input type="radio" name="sub_search" value="E" {{ request('sub_search') == 'E' ? 'checked' : '' }}> 제외</label>
                            <input type="text" name="sub_text" value="{{ request('sub_text') }}" class="doto-line2" style="border:1px solid #ddd; padding:3px;">
                            <button type="submit" class="doto-white-btn" style="border:1px solid #ddd; background:#fff; padding:3px 10px; cursor:pointer;">검색</button>
                        </div>
                    </div>

                    {{-- 3. Price Filter --}}
                    <div class="price_wrap2" style="margin-bottom:10px;">
                        <select class="gsfm-prices doto-line mr5" onchange="setPriceRange(this)">
                            <option value="">가격별 검색</option>
                            @foreach($priceList as $idx => $p)
                                <option value="{{ $idx }}" start_price="{{ $p['min'] }}" end_price="{{ $p['max'] }}"
                                    {{ (request('start_price') == $p['min'] && request('end_price') == $p['max']) ? 'selected' : '' }}>
                                    {{ $p['title'] }}
                                </option>
                            @endforeach
                        </select>
                        <span class="price_search">
                           <input type="text" name="start_price" value="{{ request('start_price') }}" class="onlynumber doto-line" size="10" placeholder="0">
                           <span class="dash">~</span>
                           <input type="text" name="end_price" value="{{ request('end_price') }}" class="onlynumber doto-line" size="10" placeholder=""> <span>원</span>
                        </span>
                    </div>

                    {{-- 4. Date Filter --}}
                    <div class="price_wrap2">
                        <select class="gsfm-days doto-line mr5" onchange="setDateRange(this)">
                            <option value="">등록일 검색</option>
                            @foreach($dayList as $idx => $d)
                                <option value="{{ $idx }}" fm_date="{{ $d['from'] }}" to_date="{{ $d['to'] }}"
                                    {{ (request('fm_date') == $d['from'] && request('to_date') == $d['to']) ? 'selected' : '' }}>
                                    {{ $d['title'] }}
                                </option>
                            @endforeach
                        </select>
                        <span class="price_search">
                           <input type="text" name="fm_date" id="fm_date" value="{{ request('fm_date') }}" class="doto-line" size="10" placeholder="YYYY-MM-DD">
                           <span class="dash">~</span>
                           <input type="text" name="to_date" id="to_date" value="{{ request('to_date') }}" class="doto-line" size="10" placeholder="YYYY-MM-DD">
                        </span>
                        <input type="submit" value="적용" style="padding:3px 10px; background:#555; color:#fff; border:0; cursor:pointer; margin-left:5px;">
                    </div>
                </div>
            </div>
            
            @if(isset($aiAnalysis) && (!empty($aiAnalysis['keywords']) || !empty($aiAnalysis['filters']) || !empty($aiAnalysis['sort'])))
                <style>
                    .ai-smart-box {
                        background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
                        border-left: 4px solid #3498db;
                        border-radius: 4px;
                        padding: 15px 20px;
                        margin-bottom: 25px;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
                    }
                    .ai-title {
                        font-weight: bold;
                        color: #2c3e50;
                        font-size: 14px;
                        margin-bottom: 8px;
                        display: flex;
                        align-items: center;
                    }
                    .ai-badge {
                        display: inline-block;
                        background: #fff;
                        border: 1px solid #ddd;
                        border-radius: 12px;
                        padding: 4px 10px;
                        font-size: 12px;
                        color: #555;
                        margin-right: 5px;
                        margin-bottom: 5px;
                    }
                    .ai-badge.highlight {
                        background: #e1f5fe;
                        border-color: #81d4fa;
                        color: #0277bd;
                    }
                </style>

                <div class="ai-smart-box">
                    <div class="ai-title">
                        <span style="margin-right:5px;">✨</span> AI 스마트 추천 결과
                    </div>
                    <div>
                        {{-- Keywords --}}
                        @if(!empty($aiAnalysis['keywords']) && $aiAnalysis['keywords'] != [$keyword])
                            @foreach($aiAnalysis['keywords'] as $k)
                                <span class="ai-badge highlight">#{{ $k }}</span>
                            @endforeach
                        @endif

                        {{-- Price Filters --}}
                        @if(!empty($aiAnalysis['filters']['price_min']))
                            <span class="ai-badge">최소 {{ number_format($aiAnalysis['filters']['price_min']) }}원~</span>
                        @endif
                        @if(!empty($aiAnalysis['filters']['price_max']))
                            <span class="ai-badge">~최대 {{ number_format($aiAnalysis['filters']['price_max']) }}원</span>
                        @endif

                        {{-- Sort --}}
                        @if(!empty($aiAnalysis['sort']))
                            @php
                                $sortLabels = [
                                    'price_asc' => '낮은가격순',
                                    'price_desc' => '높은가격순',
                                    'popular' => '인기순',
                                    'popular_sales' => '판매순'
                                ];
                            @endphp
                            <span class="ai-badge">정렬: {{ $sortLabels[$aiAnalysis['sort']] ?? $aiAnalysis['sort'] }}</span>
                        @endif
                    </div>
                </div>
            @endif

        </form>

        <script>
            function setSort(val) {
                var f = document.goodsSearchForm;
                f.sort.value = val;
                f.submit();
            }
            function setPriceRange(sel) {
                var opt = sel.options[sel.selectedIndex];
                var start = opt.getAttribute('start_price');
                var end = opt.getAttribute('end_price');
                if(start !== null) document.goodsSearchForm.start_price.value = start;
                if(end !== null) document.goodsSearchForm.end_price.value = end;
            }
            function setDateRange(sel) {
                var opt = sel.options[sel.selectedIndex];
                var from = opt.getAttribute('fm_date');
                var to = opt.getAttribute('to_date');
                if(from !== null) document.getElementById('fm_date').value = from;
                if(to !== null) document.getElementById('to_date').value = to;
            }
        </script>
        {{-- Legacy Search Form End --}}

        <div class="goods_list_area">
            @if($goods->isEmpty())
                <div class="no_data" style="padding: 50px 0; text-align: center; color: #666;">
                    검색된 상품이 없습니다. 다른 검색어로 다시 시도해주세요.
                </div>
            @else
                <ul class="goods_list_ul" style="list-style:none; padding:0; margin:0;">
                    @foreach($goods as $item)
                        <li>
                            <div class="goods_box">
                                <a href="{{ route('goods.view', ['no' => $item->goods_seq]) }}" style="text-decoration:none;">
                                    <div class="img_area">
                                        @php
                                            $mainImage = $item->images->where('image_type', 'list1')->first();
                                            $imgSrc = '/images/no_image.gif';
                                            
                                            $imagePath = $mainImage ? $mainImage->image : '';
                                            if ($imagePath) {
                                                if (Str::startsWith($imagePath, 'http')) {
                                                    $imgSrc = $imagePath;
                                                } elseif (strpos($imagePath, 'goods_img') !== false) {
                                                    $suffix = substr($imagePath, strpos($imagePath, 'goods_img') + 9);
                                                    $imgSrc = "https://dmtusr.vipweb.kr/goods_img" . $suffix;
                                                } elseif (strpos($imagePath, '/data/goods/') === 0) {
                                                    $imgSrc = "http://dometopia.com" . $imagePath;
                                                } else {
                                                    $imgSrc = "http://dometopia.com/data/goods/" . $imagePath;
                                                }
                                            }
                                        @endphp
                                        <img src="{{ $imgSrc }}" alt="{{ $item->goods_name }}"
                                            onerror="this.src='/images/no_image.gif'">
                                    </div>
                                    <div class="info_area">
                                        <div class="goods_name">{{ $item->goods_name }}</div>
                                        <div class="price_area">
                                            @php
                                                $price = optional($item->option->first())->price ?? 0;
                                                $consumerPrice = optional($item->option->first())->consumer_price ?? 0;
                                            @endphp
                                            @if($consumerPrice > $price)
                                                <span class="consumer_price">{{ number_format($consumerPrice) }}원</span>
                                            @endif
                                            <span class="price">{{ number_format($price) }}원</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <div class="paging_area">
                    {{ $goods->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Reusing Catalog Styles */
        .goods_list_ul {
            overflow: hidden;
            margin-top: 20px;
        }

        .goods_list_ul li {
            float: left;
            width: 25%;
            padding: 10px;
            box-sizing: border-box;
        }

        @media (max-width: 768px) {
            .goods_list_ul li {
                width: 50%;
            }
        }

        .goods_box {
            border: 1px solid #eee;
            padding: 10px;
            text-align: center;
        }
        .goods_box a {
            text-decoration: none;
            color: inherit;
        }

        .goods_box .img_area img {
            width: 100%;
            height: auto;
        }

        .goods_name {
            margin: 10px 0;
            font-size: 14px;
            color: #333;
            height: 40px;
            overflow: hidden;
        }

        .price_area .price {
            font-weight: bold;
            color: #d00;
            font-size: 16px;
        }

        .price_area .consumer_price {
            text-decoration: line-through;
            color: #999;
            font-size: 12px;
            margin-right: 5px;
        }

        .paging_area {
            text-align: center;
            margin-top: 30px;
        }
    </style>
@endsection