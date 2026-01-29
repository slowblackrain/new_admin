@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; {{ $categoryCode }}</em>
        </div>
    </div>

    <div id="goods_list" class="content_wrap">
        <div class="sub_tit_area">
            <h3>{{ $categoryCode }}</h3>
        </div>

        {{-- Category Navigation (Optional: Subcategories) --}}
        {{-- TODO: Add subcategory list here --}}

        <div class="goods_list_area">
            @if($goods->isEmpty())
                <div class="no_data">등록된 상품이 없습니다.</div>
            @else
                <ul class="goods_list_ul">
                    @foreach($goods as $item)
                        <li>
                            <div class="goods_box">
                                <a href="{{ route('goods.view', ['no' => $item->goods_seq]) }}">
                                    <div class="img_area">
                                        {{-- Display Image: Using list1 image driven by logic --}}
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
                                            
                                            // Pre-calc option data for Quick Menu
                                            $firstOption = $item->option->first();
                                            $optionSeq = $firstOption ? $firstOption->option_seq : 0;
                                            $hasMultiMatches = $item->option->count() > 1;
                                            // If no options loaded, safer to assume multi or redirect
                                            if ($item->option->isEmpty()) $hasMultiMatches = true; 
                                        @endphp
                                        <img src="{{ $imgSrc }}" alt="{{ $item->goods_name }}"
                                            onerror="this.src='/images/no_image.gif'">
                                    </div>
                                    <div class="info_area">
                                        <div class="goods_name">{{ $item->goods_name }}</div>
                                        <div class="price_area">
                                            {{-- Calculate price based on options if needed, here basic price --}}
                                            @php
                                                $price = optional($firstOption)->price ?? 0;
                                                $consumerPrice = optional($firstOption)->consumer_price ?? 0;
                                            @endphp
                                            @if($consumerPrice > $price)
                                                <span class="consumer_price">{{ number_format($consumerPrice) }}원</span>
                                            @endif
                                            <span class="price">{{ number_format($price) }}원</span>
                                        </div>
                                    </div>
                                </a>

                                {{-- Quick Menu Actions --}}
                                <div class="goodsDisplayQuickMenu">
                                    <div class="goodsDisplayQuickIcon">
                                        <span class="goodsDisplayNew" onclick="alert('신상품 정렬 기능 준비중입니다.'); return false;"></span>
                                        <div class="QuickIconComment">신상품</div>
                                    </div>
                                    <div class="goodsDisplayQuickIcon">
                                        <span class="goodsDisplayCart" onclick="QuickMenu.cart({{ $item->goods_seq }}, {{ $optionSeq }}, {{ $hasMultiMatches ? 'true' : 'false' }}); return false;"></span>
                                        <div class="QuickIconComment">장바구니</div>
                                    </div>
                                    <div class="goodsDisplayQuickIcon">
                                        <span class="goodsDisplayCard" onclick="QuickMenu.buy({{ $item->goods_seq }}, {{ $optionSeq }}, {{ $hasMultiMatches ? 'true' : 'false' }}); return false;"></span>
                                        <div class="QuickIconComment">바로구매</div>
                                    </div>
                                </div>
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
        /* Basic styling based on legacy */
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

        .goods_box {
            border: 1px solid #eee;
            padding: 10px;
            text-align: center;
            position: relative; /* Context for absolute QuickMenu */
            transition: border-color 0.3s;
        }
        
        .goods_box:hover {
            border: 2px solid #ff5400; /* Active Border */
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

        /* Quick Menu Styles (Ported) */
        .goodsDisplayQuickMenu {
            width: 100%;
            height: 40px; /* Slight increase for clickable area */
            background: rgba(255, 255, 255, 0.95);
            border-top: 1px solid #f2f3f4;
            position: absolute;
            bottom: -40px; /* Hidden by default */
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: all 0.3s;
            z-index: 10;
        }

        /* Show on hover */
        .goods_box:hover .goodsDisplayQuickMenu {
            bottom: 0;
            opacity: 1;
        }

        .goodsDisplayQuickIcon {
            position: relative;
            width: 50px;
            text-align: center;
        }

        .goodsDisplayQuickIcon:after {
            content: "";
            width: 1px;
            height: 14px;
            background: #f2f3f4;
            display: inline-block;
            vertical-align: middle;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
        }
        .goodsDisplayQuickIcon:last-child:after {
            display: none;
        }
        
        .goodsDisplayQuickIcon > span {
            display: inline-block;
            width: 47px;
            height: 24px;
            opacity: 0.6;
            cursor: pointer;
            margin-top: 8px; /* Vertical center align */
        }
        .goodsDisplayQuickIcon:hover > span {
            opacity: 1;
        }

        /* Icons using asset() helper */
        .goodsDisplayNew { background: url('{{ asset("images/legacy/icon/goodsDisplayNew.png") }}') no-repeat center; }
        .goodsDisplayCart { background: url('{{ asset("images/legacy/icon/goodsDisplayCart.png") }}') no-repeat center; }
        .goodsDisplayCard { background: url('{{ asset("images/legacy/icon/goodsDisplayCard.png") }}') no-repeat center; }

        /* Tooltip */
        .QuickIconComment {
            position: absolute;
            background: #FFF;
            border: 1px solid #cfd5da;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 11px;
            color: #9eabbb;
            display: none;
            padding: 2px 5px;
            white-space: nowrap;
            z-index: 20;
        }
        .goodsDisplayQuickIcon:hover .QuickIconComment {
            display: block;
        }

        @media (max-width: 768px) {
            .goods_list_ul li {
                width: 50% !important;
            }
            /* Hide QuickMenu on mobile as per design */
            .goodsDisplayQuickMenu {
                display: none !important;
            }
        }
    </style>
@endsection