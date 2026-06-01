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

                {{-- [LEGACY PARITY] 하위 카테고리 리스트 가로 격자형 7열 그리드 패널 (Immediate visibility) --}}
                @if(isset($childCategories) && $childCategories->count() > 0)
                    <div class="legacy-sub-category-grid" style="border: 1px solid #e9ecef; background: #fff; margin-bottom: 25px; font-family: '맑은고딕', 'Malgun Gothic', sans-serif;">
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; text-align: left;">
                            {{-- 전체보기 --}}
                            <li style="width: 14.28%; border-right: 1px solid #e9ecef; border-bottom: 1px solid #e9ecef; box-sizing: border-box; padding: 12px 15px; font-size: 13px;">
                                <a href="{{ route('goods.catalog', ['code' => substr($categoryCode, 0, 4)]) }}" style="text-decoration: none; color: #555; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    전체보기
                                </a>
                            </li>
                            @foreach($childCategories as $index => $child)
                                <li style="width: 14.28%; border-right: {{ ($index + 2) % 7 == 0 ? 'none' : '1px solid #e9ecef' }}; border-bottom: 1px solid #e9ecef; box-sizing: border-box; padding: 12px 15px; font-size: 13px; background: {{ request('code') == $child->category_code ? '#fafafa' : '#fff' }};">
                                    <a href="{{ route('goods.catalog', ['code' => $child->category_code]) }}" style="text-decoration: none; color: {{ request('code') == $child->category_code ? '#f25e1a' : '#555' }}; font-weight: {{ request('code') == $child->category_code ? 'bold' : 'normal' }}; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $child->title }}
                                    </a>
                                </li>
                            @endforeach
                            {{-- 빈 공간 메우기용 더미 셀 채우기 --}}
                            @php
                                $totalCells = $childCategories->count() + 1;
                                $rem = $totalCells % 7;
                                $dummyCount = $rem > 0 ? (7 - $rem) : 0;
                            @endphp
                            @for($i = 0; $i < $dummyCount; $i++)
                                <li style="width: 14.28%; border-bottom: 1px solid #e9ecef; border-right: {{ (($totalCells + $i + 1) % 7 == 0) ? 'none' : '1px solid #e9ecef' }}; box-sizing: border-box; padding: 12px 15px; background:#fff;"></li>
                            @endfor
                        </ul>
                    </div>
                @endif

                {{-- [LEGACY PARITY] 상세 통합검색 및 엑셀다운로드 패널 --}}
                <div class="legacy-filter-section" style="border: 1px solid #ddd; background: #fff; padding: 18px 20px; margin-bottom: 25px; font-size: 12px; color: #333; font-family: '맑은고딕', 'Malgun Gothic', sans-serif;">
                    <form name="frmListSearch" method="get" action="{{ url()->current() }}">
                        <input type="hidden" name="code" value="{{ request('code') }}">
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">

                        {{-- 결과내 재검색, 가격별, 등록일 --}}
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 20px; margin-bottom: 18px; text-align: left;">
                            
                            {{-- 결과내 재검색 --}}
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <span style="font-weight: bold; color: #f25e1a; font-size:13px; margin-right:5px;">결과내 재검색</span>
                                <label style="display: inline-flex; align-items: center; gap: 3px; cursor: pointer; font-size:12px;">
                                    <input type="radio" name="search_type" value="include" {{ request('search_type') !== 'exclude' ? 'checked' : '' }} style="margin: 0; vertical-align: middle;"> 포함
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 3px; cursor: pointer; font-size:12px; margin-left:5px;">
                                    <input type="radio" name="search_type" value="exclude" {{ request('search_type') === 'exclude' ? 'checked' : '' }} style="margin: 0; vertical-align: middle;"> 제외
                                </label>
                                <input type="text" name="search_text" value="{{ request('search_text') }}" placeholder="검색어 입력" style="border: 1px solid #ccc; height: 26px; width: 150px; padding: 0 5px; margin-left: 8px; outline: none; box-sizing: border-box;">
                            </div>

                            {{-- 가격별 검색 --}}
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <select name="price_type" style="border: 1px solid #ccc; height: 26px; padding: 0 5px; outline: none; background: #fff; font-size:12px;">
                                    <option value="sell">가격별 검색</option>
                                </select>
                                <input type="text" name="price_start" value="{{ request('price_start') }}" style="border: 1px solid #ccc; height: 26px; width: 80px; padding: 0 5px; outline: none; text-align: right; box-sizing: border-box;">
                                <span>~</span>
                                <input type="text" name="price_end" value="{{ request('price_end') }}" style="border: 1px solid #ccc; height: 26px; width: 80px; padding: 0 5px; outline: none; text-align: right; box-sizing: border-box;">
                                <span>원</span>
                            </div>

                            {{-- 등록일 검색 --}}
                            <div style="display: flex; align-items: center; gap: 5px;">
                                <select name="date_type" style="border: 1px solid #ccc; height: 26px; padding: 0 5px; outline: none; background: #fff; font-size:12px;">
                                    <option value="regist">등록일 검색</option>
                                </select>
                                <input type="text" name="date_start" value="{{ request('date_start') }}" placeholder="YYYY-MM-DD" style="border: 1px solid #ccc; height: 26px; width: 90px; padding: 0 5px; outline: none; text-align: center; box-sizing: border-box;">
                                <span>~</span>
                                <input type="text" name="date_end" value="{{ request('date_end') }}" placeholder="YYYY-MM-DD" style="border: 1px solid #ccc; height: 26px; width: 90px; padding: 0 5px; outline: none; text-align: center; box-sizing: border-box;">
                            </div>
                        </div>

                        {{-- 전체선택 / 엑셀다운로드 / 통합검색 --}}
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #eee; padding-top: 15px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: 1px solid #ccc; padding: 5px 12px; background: #fdfdfd; font-size: 11px; font-weight: bold; border-radius: 2px;">
                                    <input type="checkbox" id="check_all_products" onclick="toggleAllProducts(this)" style="margin: 0; vertical-align: middle;"> 전체선택
                                </label>
                                <button type="button" onclick="excelDownload()" style="display: inline-flex; align-items: center; gap: 5px; border: 1px solid #ccc; padding: 5px 12px; background: #fdfdfd; font-size: 11px; cursor: pointer; color: #555; font-weight: bold; border-radius: 2px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #028df4;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    엑셀다운로드
                                </button>
                            </div>
                            
                            <div>
                                <button type="submit" style="background: #f15f23; color: #fff; border: none; font-size: 13px; font-weight: bold; padding: 8px 50px; cursor: pointer; border-radius: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                                    통합검색
                                </button>
                            </div>
                            <div style="width: 160px;" class="hidden-mobile"></div>
                        </div>
                    </form>
                </div>

                {{-- [LEGACY PARITY] 라디오버튼형 정렬 바 및 노출개수 설정 영역 --}}
                <div class="sort_area" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #555; padding-bottom: 10px; margin-bottom: 20px; font-family:'Dotum', sans-serif; font-size:12px;">
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; align-items: center; gap: 15px;">
                        <li style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: {{ $sort == '' || $sort == 'new' ? '#f15f23' : '#bbb' }}; font-size: 10px;">●</span>
                            <a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'new'])) }}" style="text-decoration: none; color: {{ $sort == '' || $sort == 'new' ? '#333' : '#888' }}; font-weight: {{ $sort == '' || $sort == 'new' ? 'bold' : 'normal' }};">신상품순</a>
                        </li>
                        <li style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: {{ $sort == 'G' ? '#f15f23' : '#bbb' }}; font-size: 10px;">✔</span>
                            <a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'G'])) }}" style="text-decoration: none; color: {{ $sort == 'G' ? '#333' : '#888' }}; font-weight: {{ $sort == 'G' ? 'bold' : 'normal' }};">낱개판매순</a>
                        </li>
                        <li style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #bbb; font-size: 10px;">●</span>
                            <a href="javascript:void(0)" style="text-decoration: none; color: #888;">판매량순</a>
                        </li>
                        <li style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: #bbb; font-size: 10px;">●</span>
                            <a href="javascript:void(0)" style="text-decoration: none; color: #888;">클릭순</a>
                        </li>
                        <li style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: {{ $sort == 'price_asc' ? '#f15f23' : '#bbb' }}; font-size: 10px;">●</span>
                            <a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'price_asc'])) }}" style="text-decoration: none; color: {{ $sort == 'price_asc' ? '#333' : '#888' }}; font-weight: {{ $sort == 'price_asc' ? 'bold' : 'normal' }};">낮은가격순</a>
                        </li>
                        <li style="display: inline-flex; align-items: center; gap: 4px;">
                            <span style="color: {{ $sort == 'price_desc' ? '#f15f23' : '#bbb' }}; font-size: 10px;">●</span>
                            <a href="{{ route('goods.catalog', array_merge(request()->all(), ['sort' => 'price_desc'])) }}" style="text-decoration: none; color: {{ $sort == 'price_desc' ? '#333' : '#888' }}; font-weight: {{ $sort == 'price_desc' ? 'bold' : 'normal' }};">높은가격순</a>
                        </li>
                    </ul>

                    <div>
                        <select name="per_page" onchange="changePerPage(this.value)" style="border: 1px solid #ccc; height: 26px; padding: 0 5px; outline: none; background: #fff; font-size: 12px; font-family:'Dotum';">
                            <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20개씩 보기</option>
                            <option value="40" {{ request('per_page') == 40 ? 'selected' : '' }}>40개씩 보기</option>
                            <option value="75" {{ request('per_page') == 75 ? 'selected' : '' }}>75개씩 보기</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100개씩 보기</option>
                        </select>
                    </div>
                </div>

                <div class="goods_list_area">
                    @if($goods->isEmpty())
                        <div class="no_data">등록된 상품이 없습니다.</div>
                    @else
                        {{-- Legacy Grid Wrapper --}}
                        <div class="goods_list_legacy_wrapper">
                            <ul class="goods_list_ul">
                                @foreach($goods as $product)
                                    <li style="position: relative;">
                                        <!-- [LEGACY PARITY] Product Checkbox Overlay -->
                                        <div style="position: absolute; top: 18px; left: 18px; z-index: 5;">
                                            <input type="checkbox" class="product-select-chk" value="{{ $product->goods_seq }}" style="width: 18px; height: 18px; cursor: pointer; border: 1px solid #ccc; background: #fff;">
                                        </div>
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
        function add_to_cart(goodsSeq, type, optionSeq, hasMultipleOptions) {
            // Check if QuickMenu is available
            if(typeof QuickMenu !== 'undefined') {
                if(type === 'direct') {
                    // Buy Now
                    QuickMenu.buy(goodsSeq, optionSeq, hasMultipleOptions); 
                } else {
                    // Add to cart
                    QuickMenu.cart(goodsSeq, optionSeq, hasMultipleOptions);
                }
            } else {
                alert('쇼핑몰 기능 로딩 중입니다. 잠시 후 다시 시도해 주세요.');
            }
        }

        // [LEGACY PARITY] per_page change handler
        function changePerPage(val) {
            var url = new URL(window.location.href);
            url.searchParams.set('per_page', val);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        // [LEGACY PARITY] toggle select checkboxes
        function toggleAllProducts(elem) {
            var chks = document.querySelectorAll('.product-select-chk');
            chks.forEach(function(chk) {
                chk.checked = elem.checked;
            });
        }

        // [LEGACY PARITY] download excel
        function excelDownload() {
            var chks = document.querySelectorAll('.product-select-chk:checked');
            var seqs = [];
            chks.forEach(function(chk) {
                seqs.push(chk.value);
            });
            
            var seqsStr = seqs.join(',');
            
            var url = new URL('{{ route("goods.catalog.excel") }}', window.location.origin);
            if (seqsStr) {
                url.searchParams.set('seqs', seqsStr);
            }
            
            var code = '{{ request("code") }}';
            if (code) {
                url.searchParams.set('code', code);
            }
            
            var searchText = '{{ request("search_text") }}';
            var searchType = '{{ request("search_type") }}';
            var priceStart = '{{ request("price_start") }}';
            var priceEnd = '{{ request("price_end") }}';
            var dateStart = '{{ request("date_start") }}';
            var dateEnd = '{{ request("date_end") }}';
            
            if (searchText) url.searchParams.set('search_text', searchText);
            if (searchType) url.searchParams.set('search_type', searchType);
            if (priceStart) url.searchParams.set('price_start', priceStart);
            if (priceEnd) url.searchParams.set('price_end', priceEnd);
            if (dateStart) url.searchParams.set('date_start', dateStart);
            if (dateEnd) url.searchParams.set('date_end', dateEnd);

            window.location.href = url.toString();
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
        .paging_area ul {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: row !important;
            justify-content: center;
            align-items: center;
        }
        .paging_area ul li {
            margin: 0 2px !important;
            padding: 0 !important;
            width: auto !important;
            border: none !important;
            display: block !important;
        }
        .paging_area ul li::before {
            content: none !important;
            display: none !important;
        }
        /* Style links */
        .paging_area a, .paging_area span, .paging_area a.page-link, .paging_area span.page-link {
            display: inline-block;
            padding: 5px 10px;
            margin: 0;
            border: 1px solid #ddd;
            color: #666;
            text-decoration: none;
            font-size: 12px;
            border-radius: 0; /* Legacy square style */
            background: #fff;
            line-height: 1.5;
        }
        .paging_area .active span, .paging_area .active span.page-link {
            background-color: #444;
            color: #fff;
            border: 1px solid #444;
            font-weight: bold;
        }
        .paging_area a:hover, .paging_area a.page-link:hover {
            border-color: #888;
            color: #333;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .goods_list_ul li {
                width: 50%; /* 2 cols on mobile */
            }
            .sub_tit_area, .location_wrap, .sort_area {
                padding-left: 10px; padding-right: 10px;
            }
            #main-wrap {
                width: 100% !important; /* Override fixed width */
                box-sizing: border-box;
            }
        }
    </style>
@endsection