{{-- 
    Legacy Parity Component based on: goods_display_doto_goods_list.html 
    Usage: @include('front.goods.component.legacy_product_item', ['product' => $product])
--}}
{{-- 
    Legacy Parity Component based on USER SCREENSHOT evidence.
    Layout: Image -> Thumbnails -> Badges -> Checkbox/Code -> Name -> Price
--}}
@php
    // Image Logic (same as before)
    $imagePath = $product->image;
    if (!$imagePath && $product->images->isNotEmpty()) {
        $imgObj = $product->images->where('image_type', 'list1')->first() 
               ?? $product->images->where('image_type', 'view')->first()
               ?? $product->images->first();
        $imagePath = $imgObj ? $imgObj->image : '';
    }
    if (strpos($imagePath, '/data/goods/goods_img') !== false) {
        $imagePath = str_replace('/data/goods/goods_img', 'https://dmtusr.vipweb.kr/goods_img', $imagePath);
    } elseif ($imagePath && !Str::startsWith($imagePath, 'http')) {
        $imagePath = asset($imagePath); 
    }
    if (!$imagePath) $imagePath = asset('images/no_image.gif');

    // Thumbnail Logic (Get up to 3 distinct 'thumb' images)
    $thumbs = $product->images->where('image_type', 'like', 'thumb%')->take(3);
    
    // Price Logic (Fallback handling)
    $price = $product->price; 
    if(!$price && $product->option && $product->option->first()) {
        $price = $product->option->first()->price;
    }
@endphp

<dl class="goodsDisplayItemWrap" style="text-align: left !important; border: none !important;">
    <dt>
        <span class="goodsDisplayImageWrap" style="display:block; text-align:center; margin-bottom: 5px;">
            <a href="{{ route('goods.view', ['no' => $product->goods_seq]) }}" target="_self">
                <img src="{{ $imagePath }}" 
                     width="216" height="216" 
                     onerror="this.src='{{ asset('images/no_image.gif') }}'"
                     alt="{{ $product->goods_name }}" />
            </a>
            
            {{-- Quick Menu --}}
            <div class="goodsDisplayQuickMenu">
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayNew" onclick="window.open('{{ route('goods.view', ['no' => $product->goods_seq]) }}');"></span>
                    <span class="QuickIconComment">새창보기</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCart" onclick="add_to_cart('{{ $product->goods_seq }}');"></span>
                    <span class="QuickIconComment">장바구니</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCard" onclick="add_to_cart('{{ $product->goods_seq }}', 'direct');"></span>
                    <span class="QuickIconComment">바로구매</span>
                </span>
            </div>
        </span>
    </dt>

    {{-- Thumbnails Row --}}
    @if($thumbs->isNotEmpty())
    <dd class="goodsDisplayThumbList" style="text-align: center; margin-bottom: 5px;">
        @foreach($thumbs as $thumb)
            @php
                $tPath = $thumb->image;
                if (strpos($tPath, '/data/goods/goods_img') !== false) {
                    $tPath = str_replace('/data/goods/goods_img', 'https://dmtusr.vipweb.kr/goods_img', $tPath);
                }
            @endphp
            <img src="{{ $tPath }}" width="35" height="35" style="border:1px solid #ddd; margin:1px; cursor:pointer;" onmouseover="this.closest('dl').querySelector('.goodsDisplayImageWrap img').src='{{ $tPath }}'">
        @endforeach
    </dd>
    @endif

    {{-- Badges Row --}}
    <dd class="goodsDisplayIcon" style="min-height: 20px; padding: 0 5px;">
        @if($product->goods_status == 'runout')
            <img src="{{ asset('images/legacy/icon/goods_status/icon_list_soldout.gif') }}" />
        @endif
        {{-- Static Placeholders for Visual Parity (Logic needs real data) --}}
        <span style="display:inline-block; border:1px solid #dcdcdc; color:#5d5d5d; padding:0 3px; font-size:11px; margin-right:3px;">낱개</span>
        <span style="display:inline-block; border:1px solid #2e8b57; color:#2e8b57; padding:0 3px; font-size:11px;">무료배송</span>
    </dd>

    {{-- Checkbox + Code --}}
    <dd class="goodsDisplayCode" style="padding: 5px 5px 0;">
         <label class="hand" style="cursor:pointer; display:flex; align-items:center;">
            <input type="checkbox" class="list_goods_chk" name="goods_seq[]" value="{{ $product->goods_seq }}" style="margin-right:5px;">
            <span class="goods_scode" style="font-family:'Dotum'; font-size:11px; color:#888;">{{ $product->goods_scode }}</span>
        </label>
    </dd>

    {{-- Name --}}
    <dd class="goodsDisplayTitle" style="padding: 0 5px; margin-bottom: 5px;">
        <div class="list_price">
            <a href="{{ route('goods.view', ['no' => $product->goods_seq]) }}" target="_self" style="text-decoration: none;">
                <h6 style="color:#555; font-size:12px; font-weight:normal; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $product->goods_name }}</h6>
            </a>
        </div>
    </dd>

    {{-- Price --}}
    <dd class="goodsDisplaySalePrice" style="padding: 0 5px;">
        <div class="list_price" style="text-align:left;">
            <span style="color:#ff4e00; font-weight:bold; font-size:14px;">도매가 {{ number_format($price) }}원</span>
        </div>
    </dd>
</dl>
