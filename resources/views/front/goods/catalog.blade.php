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
                                            $imgSrc = $mainImage ? 'http://dometopia.com' . $mainImage->image : '/images/no_image.gif';
                                        @endphp
                                        <img src="{{ $imgSrc }}" alt="{{ $item->goods_name }}"
                                            onerror="this.src='/images/no_image.gif'">
                                    </div>
                                    <div class="info_area">
                                        <div class="goods_name">{{ $item->goods_name }}</div>
                                        <div class="price_area">
                                            {{-- Calculate price based on options if needed, here basic price --}}
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

        @media (max-width: 768px) {
            .goods_list_ul li {
                width: 50% !important;
            }
            .goodsDisplayThumbList {
                display: none !important;
            }
        }
    </style>
@endsection