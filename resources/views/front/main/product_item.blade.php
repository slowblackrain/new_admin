<dl class="goodsDisplayItemWrap">
    <dt class="goods-thumb" style="position: relative;">
        <span class="goodsDisplayImageWrap">
            <a href="{{ route('goods.view', ['no' => $product->goods_seq]) }}" target="_blank">
                @php
                    $imgSrc = '/images/no_image.gif';
                    
                    // Helper function for legacy image logic
                    $resolveImage = function($img) {
                        $img = trim($img);
                        if (empty($img)) return null;
                        if (str_starts_with($img, 'http')) return $img;
                        if (strpos($img, 'goods_img') !== false) {
                            $suffix = substr($img, strpos($img, 'goods_img') + 9);
                            return "https://dmtusr.vipweb.kr/goods_img" . $suffix;
                        }
                        return 'http://dometopia.com/data/goods/' . $img;
                    };

                    // Priority 1: Check relation 'images' locally
                    if ($product->images && $product->images->count() > 0) {
                        $targetImg = $product->images->where('image_type', 'list1')->first();
                        if (!$targetImg) $targetImg = $product->images->first();
                        if ($targetImg) {
                            $res = $resolveImage($targetImg->image);
                            if ($res) $imgSrc = $res;
                        }
                    } 
                    // Priority 2: Check legacy column 'img_s'
                    elseif (!empty($product->img_s)) {
                        $res = $resolveImage($product->img_s);
                        if ($res) $imgSrc = $res;
                    }

                    $displayPrice = $product->price;
                    if ($displayPrice == 0 && $product->option->isNotEmpty()) {
                        $displayPrice = $product->option->first()->price;
                    }

                    $priceLabel = '도매가';
                    if (str_starts_with($product->goods_scode, 'GUS')) {
                         $priceLabel = '소매가';
                    } elseif (str_starts_with($product->goods_scode, 'GKQ')) {
                         $priceLabel = '특가';
                    }
                @endphp
                <img src="{{ $imgSrc }}" width="172" height="172" onerror="this.src='/images/no_image.gif'" style="object-fit: cover;">
            </a>
            <div class="goodsDisplayQuickMenu">
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayNew" onclick="window.open('{{ route('goods.view', ['no' => $product->goods_seq]) }}');"></span>
                    <span class="QuickIconComment">새창보기</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCart" onclick="alert('장바구니 담기 기능을 구현 중입니다.');"></span>
                    <span class="QuickIconComment">장바구니</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCard" onclick="alert('바로구매 기능을 구현 중입니다.');"></span>
                    <span class="QuickIconComment">바로구매</span>
                </span>
            </div>
        </span>
        @if(isset($rank))
            <div style="position: absolute; top: 0; left: 0; background-color: #f47425; color: white; width: 20px; height: 20px; text-align: center; line-height: 20px; font-weight: bold; font-size: 12px; z-index: 10;">
                {{ $rank }}
            </div>
        @endif
    </dt>

    {{-- Thumbnail List (Added) --}}
    <dd class="goodsDisplayThumbList">
        @if($product->images && $product->images->count() > 0)
            @foreach($product->images->take(4) as $img)
                @php
                    $thumbSrc = $resolveImage($img->image);
                @endphp
                @if($thumbSrc)
                    <span><img src="{{ $thumbSrc }}" width="30" height="30" onmouseover="$(this).closest('.goodsDisplayItemWrap').find('.goodsDisplayImageWrap img').attr('src', '{{ $thumbSrc }}')"></span>
                @endif
            @endforeach
        @endif
    </dd>

    <dd class="goodsDisplayCode" style="text-align: center; margin-bottom: 5px;">
        <span style="font-weight: bold; color: #444; font-size: 12px;">{{ $product->goods_scode }}</span>
    </dd>
    
    {{-- Product Icons --}}
    <dd class="goodsDisplayIcon" style="text-align: right; display: flex; justify-content: flex-end; padding: 0 10px; margin-bottom: 5px; height: auto; border: none;">
@php
            $iconBaseUrl = 'http://dometopia.com';
        @endphp

        {{-- 1. Icons from fm_goods_icon (Best, New, etc.) --}}
        @if($product->activeIcons && $product->activeIcons->count() > 0)
            @foreach($product->activeIcons as $icon)
                <img src="{{ $iconBaseUrl }}/data/icon/goods/{{ $icon->codecd }}.gif" style="margin-left: 2px; vertical-align: middle;" alt="icon">
            @endforeach
        @endif

        {{-- 2. Video Icon --}}
        @if(isset($product->video_use) && $product->video_use == 'Y')
            <img src="{{ $iconBaseUrl }}/data/icon/goods_status/icon_list_video.gif" style="margin-left: 2px; vertical-align: middle;" alt="Video">
        @endif

        {{-- 3. Tax Free --}}
        @if(isset($product->tax) && $product->tax == 'exempt')
            <img src="{{ $iconBaseUrl }}/data/icon/goods_status/taxfree.gif" style="margin-left: 2px; vertical-align: middle;" alt="Tax Free">
        @endif

        {{-- 4. Sold Out --}}
        @if(isset($product->goods_status) && $product->goods_status == 'runout')
            <img src="{{ $iconBaseUrl }}/data/icon/goods_status/icon_list_soldout.gif" style="margin-left: 2px; vertical-align: middle;" alt="Sold Out">
        @endif

        {{-- 5. Warehousing (Purchasing) --}}
        @if(isset($product->goods_status) && $product->goods_status == 'purchasing')
            <img src="{{ $iconBaseUrl }}/data/icon/goods_status/icon_list_warehousing.gif" style="margin-left: 2px; vertical-align: middle;" alt="Warehousing">
        @endif

        {{-- 6. Unsold (Stop) --}}
        @if(isset($product->goods_status) && $product->goods_status == 'unsold')
            <img src="{{ $iconBaseUrl }}/data/icon/goods_status/icon_list_stop.gif" style="margin-left: 2px; vertical-align: middle;" alt="Stop">
        @endif
    </dd>

    <dd class="goodsDisplayTitle" style="margin-left: 0 !important; width: 100% !important; text-align: center;">
        <div class="list_price">
            <a href="{{ route('goods.view', ['no' => $product->goods_seq]) }}" target="_blank">
                <h6 style="text-align: center;">{{ $product->goods_name }}</h6>
            </a>
        </div>
    </dd>

    {{-- Price Display (Legacy Structure) --}}
    <dd class="goodsDisplaySalePrice" style="margin-left: 0 !important; width: 100% !important;">
        <div class="list_price">
            @if(isset($product->consumer_price) && $product->consumer_price > $displayPrice)
                <div class="retail">
                    <span style="text-decoration: line-through; color: #9eabbb;">{{ number_format($product->consumer_price) }}원</span>
                </div>
            @endif
            <div class="wholesale">
                <span class="price_txt">{{ $priceLabel }}</span>
                <span class="price_num">{{ number_format($displayPrice) }}원</span>
            </div>
        </div>
    </dd>
</dl>