@extends('layouts.front')

@section('content')
    <div class="location_wrap">
        <div class="location_cont">
            <em><a href="/" class="local_home">HOME</a> &gt; 상품검색</em>
        </div>
    </div>

    <div id="goods_list" class="content_wrap">
        <div class="sub_tit_area">
            <h3>상품검색</h3>
        </div>

        <div class="search_result_summary" style="padding: 20px 0; border-bottom: 1px solid #ddd; margin-bottom: 20px;">
            <p style="font-size: 16px;">
                @if($keyword)
                    <strong>'{{ $keyword }}'</strong> 에 대한 검색결과
                    
                    @if(isset($aiAnalysis) && (!empty($aiAnalysis['keywords']) || !empty($aiAnalysis['filters'])))
                        <div class="ai_debug_box" style="margin-top: 10px; background: #f9f9f9; padding: 10px; border: 1px dashed #ccc; font-size: 13px;">
                            <span style="color: #2c3e50; font-weight: bold;">🤖 AI 스마트 검색 분석:</span>
                            <ul style="margin-top: 5px; list-style: circle; padding-left: 20px;">
                                @if(!empty($aiAnalysis['keywords']))
                                    <li>확장 키워드: {{ implode(', ', $aiAnalysis['keywords']) }}</li>
                                @endif
                                @if(isset($aiAnalysis['filters']['price_max']))
                                    <li>가격 필터: {{ number_format($aiAnalysis['filters']['price_max']) }}원 이하</li>
                                @endif
                                @if(isset($aiAnalysis['filters']['color']))
                                    <li>색상 필터: {{ $aiAnalysis['filters']['color'] }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif
                @else
                    검색어를 입력해주세요.
                @endif
                <div style="margin-top: 10px; color: #d00;">(총 {{ number_format($goods->total()) }} 건)</div>
            </p>
        </div>

        <div class="goods_list_area">
            @if($goods->isEmpty())
                <div class="no_data" style="padding: 50px 0; text-align: center; color: #666;">
                    검색된 상품이 없습니다. 다른 검색어로 다시 시도해주세요.
                </div>
            @else
                <ul class="goods_list_ul">
                    @foreach($goods as $item)
                        <li>
                            <div class="goods_box">
                                <a href="{{ route('goods.view', ['no' => $item->goods_seq]) }}">
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