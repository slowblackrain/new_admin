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
    $thumbs = $product->images->filter(function($img) {
        return \Illuminate\Support\Str::startsWith($img->image_type, 'thumb');
    })->take(3);
    
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
            
@php
    // Option Logic for Quick Menu (Single vs Multi)
    $optionSeq = 0;
    $hasMultipleOptions = true;
    
    // Check if options exist
    if ($product->option && $product->option->isNotEmpty()) {
        // If only 1 option and it looks like a default/single option
        if ($product->option->count() === 1) {
             // You might strictly check if title is 'default' or similar if needed, 
             // but usually count=1 implies single choice.
             $opt = $product->option->first();
             $optionSeq = $opt->option_seq;
             $hasMultipleOptions = false; 
        } else {
             // > 1 options
             $hasMultipleOptions = true;
        }
    }
 @endphp
            {{-- Quick Menu --}}
            <div class="goodsDisplayQuickMenu">
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayNew" onclick="window.open('{{ route('goods.view', ['no' => $product->goods_seq]) }}');"></span>
                    <span class="QuickIconComment">새창보기</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCart" onclick="add_to_cart('{{ $product->goods_seq }}', 'cart', '{{ $optionSeq }}', {{ $hasMultipleOptions ? 'true' : 'false' }});"></span>
                    <span class="QuickIconComment">장바구니</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCard" onclick="add_to_cart('{{ $product->goods_seq }}', 'direct', '{{ $optionSeq }}', {{ $hasMultipleOptions ? 'true' : 'false' }});"></span>
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
                } elseif ($tPath && !\Illuminate\Support\Str::startsWith($tPath, 'http')) {
                    $tPath = asset($tPath);
                }
            @endphp
            <img src="{{ $tPath }}" width="35" height="35" style="border:1px solid #ddd; margin:1px; cursor:pointer;" onmouseover="this.closest('dl').querySelector('.goodsDisplayImageWrap img').src='{{ $tPath }}'">
        @endforeach
    </dd>
    @endif

    {{-- Badges Row --}}
    @php
        $icons = [];
        $scode = $product->goods_scode ?? '';
        
        $delivery = 'N';
        if ($product->shipping_policy == 'goods' && $product->unlimit_shipping_price == 0 && $product->postpaid_delivery_cost_yn != 'y') {
            $delivery = 'Y';
        }

        $isExcludedScodePrefix = in_array(substr($scode, 0, 3), ['OOO', 'CCC', 'DDD', 'BTB', 'BBB', 'GBC']);
        
        if (!$isExcludedScodePrefix && $scode) {
            $s1 = substr($scode, 0, 1);
            $s2 = substr($scode, 1, 1);
            $s3 = substr($scode, 2, 1);

            // First icon
            if ($s1 == 'X' || $s1 == 'E') {
                $icons[] = 'https://dometopia.com/data/skin/beauty/images/icon/G.gif';
            } elseif (!in_array($s1, ['M', 'A', 'K', 'F', 'C']) && $s2) {
                $icons[] = 'https://dometopia.com/data/skin/beauty/images/icon/' . $s1 . '.gif';
            }

            // Second icon
            if (!in_array($s2, ['T', 'K', 'B']) && $s1 != 'F') {
                $icons[] = 'https://dometopia.com/data/skin/beauty/images/icon/_' . $s2 . '.gif';
            }

            // Free delivery icon
            if ($delivery == 'Y') {
                $icons[] = 'https://dometopia.com/data/icon/common/free_delivery.gif';
            }

            // Video icon (Assuming $product->defaultInfo->video_url or similar exists. For now, checking if relation is loaded)
            if ($product->defaultInfo && $product->defaultInfo->video_url) {
                $icons[] = 'https://dometopia.com/data/icon/common/vod_icon.gif';
            }

            // Third icon
            if (!in_array($s3, ['S', 'A', 'M', 'L', 'Y', 'D', 'C']) && !in_array($s1, ['K', 'F']) && $s3) {
                $icons[] = 'https://dometopia.com/data/skin/beauty/images/icon/__' . $s3 . '.gif';
            }
        }
        
        // Out of stock icon
        $isSoldout = false;
        if ($product->goods_status == 'runout') {
             $isSoldout = true;
        } elseif ($product->goods_status_info) {
             $statusInfoArr = explode(',', rtrim($product->goods_status_info, ','));
             if (in_array('soldout', $statusInfoArr)) {
                 $isSoldout = true;
             }
        }
        
        if ($isSoldout) {
            $icons[] = 'https://dometopia.com/data/icon/common/end_icon.gif';
        }
    @endphp

    <dd class="goodsDisplayIcon" style="min-height: 20px; padding: 0 5px; margin-top:5px;">
        @foreach($icons as $iconSrc)
            <img src="{{ $iconSrc }}" alt="icon" style="vertical-align:top; margin-right: 2px;" />
        @endforeach
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
