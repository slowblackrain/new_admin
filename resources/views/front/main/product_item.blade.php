<dl class="goodsDisplayItemWrap">
    <dt class="goods-thumb" style="position: relative;">
        <span class="goodsDisplayImageWrap">
            <a href="{{ route('goods.view', ['no' => $product->goods_seq]) }}" target="_blank">
                @php
                    $imgSrc = asset('images/legacy/common/noimage.gif');
                    
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

                    // Legacy B2B UI Display Parity: Show the Wholesale price by default for '도매가'
                    if ($priceLabel === '도매가' && $product->mtype_discount > 0) {
                        $displayPrice = $displayPrice - $product->mtype_discount;
                    }

                    // --- Quick Menu Logic ---
                    $hasMultipleOptions = $product->option->count() > 1; // Basic checks
                    // If count is 1, check if it's a real option or just default price row (usually title is empty or '기본')
                    // For legacy, almost all goods have 1 option row.
                    // If there's 1 option row and it has no specific choice titles, it's "no option".
                    $firstOpt = $product->option->first();
                    $defaultOptionSeq = $firstOpt ? $firstOpt->option_seq : 0;
                    
                    // Legacy logic refinement: 
                    // If option1..5 are empty, treat as single option.
                    // Actually, if count > 0, we can try to add. 
                    // But if user needs to CHOOSE something (like Color/Size), we must redirect.
                    // Simple logic: if count > 1 OR (count==1 and option1 is not empty) => Redirect.
                    // (Assuming 'option1' holds the first option category name)
                    if ($firstOpt && !empty($firstOpt->option1)) {
                         $hasMultipleOptions = true; 
                    }
                    // ------------------------
                @endphp
                <img src="{{ $imgSrc }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='{{ asset('images/legacy/common/noimage.gif') }}'">
            </a>
            <div class="goodsDisplayQuickMenu">
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayNew" onclick="window.open('{{ route('goods.view', ['no' => $product->goods_seq]) }}');"></span>
                    <span class="QuickIconComment">새창보기</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCart" onclick="QuickMenu.cart({{ $product->goods_seq }}, {{ $defaultOptionSeq }}, {{ $hasMultipleOptions ? 'true' : 'false' }});"></span>
                    <span class="QuickIconComment">장바구니</span>
                </span>
                <span class="goodsDisplayQuickIcon">
                    <span class="goodsDisplayCard" onclick="QuickMenu.buy({{ $product->goods_seq }}, {{ $defaultOptionSeq }}, {{ $hasMultipleOptions ? 'true' : 'false' }});"></span>
                    <span class="QuickIconComment">바로구매</span>
                </span>
            </div>
        </span>
        @if(isset($rank))
            <img src="{{ asset('images/legacy/main/new_label.png') }}" class="best_label" style="position: absolute; z-index:98; left: -2px; top: 7px; width: 40px !important;" alt="Rank Label">
            <span class="best_no" style="position: absolute; z-index: 99; font-size: 15px; color: #fff; left: -2px; top: 11px; text-align: center; width: 40px;"><b>{{ $rank }}</b></span>
        @endif
    </dt>

    {{-- Thumbnail List (Hidden for Catalog Grid Parity) --}}
    {{-- 
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
    --}}

    <dd class="goodsDisplayCode" style="text-align: center; margin-bottom: 5px;">
        <span style="font-weight: bold; color: #444; font-size: 12px;">{{ $product->goods_scode }}</span>
    </dd>
    
    {{-- Product Icons --}}
    <dd class="goodsDisplayIcon" style="text-align: left; display: flex; justify-content: flex-start; align-items: flex-start; flex-wrap: wrap; padding: 0 10px; margin-bottom: 5px; height: auto; border: none; min-height: 20px;">
        @php
            $icons = [];
            $scode = $product->goods_scode ?? '';
            $iconBaseUrl = 'http://dometopia.com';
            
            // 1. Icons from fm_goods_icon (Best, New, etc.)
            if ($product->activeIcons && $product->activeIcons->count() > 0) {
                foreach($product->activeIcons as $icon) {
                    $icons[] = $iconBaseUrl . '/data/icon/goods/' . $icon->codecd . '.gif';
                }
            }

            // 2. Legacy Dynamic Icons based on scode, delivery, video
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

                // Video icon
                if (($product->defaultInfo && $product->defaultInfo->video_url) || (isset($product->video_use) && $product->video_use == 'Y')) {
                    $icons[] = 'https://dometopia.com/data/icon/common/vod_icon.gif';
                }

                // Third icon
                if (!in_array($s3, ['S', 'A', 'M', 'L', 'Y', 'D', 'C']) && !in_array($s1, ['K', 'F']) && $s3) {
                    $icons[] = 'https://dometopia.com/data/skin/beauty/images/icon/__' . $s3 . '.gif';
                }
            }
            
            // Tax Free
            if(isset($product->tax) && $product->tax == 'exempt') {
                $icons[] = 'https://dometopia.com/data/icon/goods_status/taxfree.gif';
            }

            // Out of stock icon
            $isSoldout = false;
            if (isset($product->goods_status)) {
                if ($product->goods_status == 'runout') {
                    $isSoldout = true;
                } elseif ($product->goods_status == 'purchasing') {
                    $icons[] = 'https://dometopia.com/data/icon/goods_status/icon_list_warehousing.gif';
                } elseif ($product->goods_status == 'unsold') {
                    $icons[] = 'https://dometopia.com/data/icon/goods_status/icon_list_stop.gif';
                }
            }
            if ($product->goods_status_info) {
                 $statusInfoArr = explode(',', rtrim($product->goods_status_info, ','));
                 if (in_array('soldout', $statusInfoArr)) {
                     $isSoldout = true;
                 }
            }
            
            if ($isSoldout) {
                $icons[] = 'https://dometopia.com/data/icon/common/end_icon.gif';
            }
        @endphp

        @foreach($icons as $iconSrc)
            <img src="{{ $iconSrc }}" alt="icon" style="vertical-align:top; margin-right: 2px;" />
        @endforeach
    </dd>

    <dd class="goodsDisplayTitle" style="margin-left: 0 !important; width: 100% !important; text-align: left;">
        <div class="list_price">
            <a href="{{ route('goods.view', ['no' => $product->goods_seq]) }}" target="_blank">
                <h6 style="text-align: left;">{{ $product->goods_name }}</h6>
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