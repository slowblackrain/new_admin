@extends('layouts.front')

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/main/main.css') }}">
        <link rel="stylesheet" href="{{ asset('css/jquery.bxslider.css') }}">
        <style>
            /* Restore legacy designDisplay gallery grid widths */
            #category_nav01, .mdswrap, #section_gtbnr {
                float: right;
                width: 991px;
                clear: none;
            }
            #category_nav01 .clearfix,
            #category_nav02 .clearfix,
            #category_nav03 .clearfix,
            #category_nav04 .clearfix {
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 30px 14px;
                justify-content: space-between;
                width: 100%;
            }
            #category_nav01 .goodsDisplayItemWrap,
            #category_nav02 .goodsDisplayItemWrap,
            #category_nav03 .goodsDisplayItemWrap,
            #category_nav04 .goodsDisplayItemWrap {
                width: 100% !important;
                max-width: 100%;
                box-sizing: border-box;
                margin: 0 !important;
                float: none !important;
            }
            #category_nav01 .goodsDisplayImageWrap img,
            #category_nav02 .goodsDisplayImageWrap img,
            #category_nav03 .goodsDisplayImageWrap img,
            #category_nav04 .goodsDisplayImageWrap img {
                width: 100% !important;
                height: auto !important;
                aspect-ratio: 1 / 1;
            }
            
            #category_nav01 .goodsDisplayItemWrap dt.goods-thumb,
            #category_nav02 .goodsDisplayItemWrap dt.goods-thumb,
            #category_nav03 .goodsDisplayItemWrap dt.goods-thumb,
            #category_nav04 .goodsDisplayItemWrap dt.goods-thumb {
                width: 100% !important;
                height: auto !important;
                max-width: 100%;
            }

            #category_nav01 .goodsDisplayItemWrap .goodsDisplayQuickMenu,
            #category_nav02 .goodsDisplayItemWrap .goodsDisplayQuickMenu,
            #category_nav03 .goodsDisplayItemWrap .goodsDisplayQuickMenu,
            #category_nav04 .goodsDisplayItemWrap .goodsDisplayQuickMenu {
                width: 100% !important;
            }
            
            /* Fix vertical text bug caused by inline-flex + float wrapping */
            .marin-tit {
                display: flex !important;
                flex-wrap: wrap;
                width: 100%;
                clear: both;
            }

            /* bxSlider Pager Customization */
            .bx-wrapper {
                position: relative;
                margin: 0 auto;
                padding: 0;
                *zoom: 1;
                -ms-touch-action: pan-y;
                touch-action: pan-y;
                box-shadow: none !important;
                border: none !important;
                background: transparent !important;
            }
            .bx-wrapper img {
                max-width: 100%;
                display: block;
            }
            .bx-viewport {
                /* fix other elements on the page moving (on Chrome) */
                -webkit-transform: translatez(0);
                -moz-transform: translatez(0);
                -ms-transform: translatez(0);
                -o-transform: translatez(0);
                transform: translatez(0);
            }
            .bx-pager,
            .bx-controls-auto {
                position: absolute;
                bottom: -30px;
                width: 100%;
            }

            .bx-wrapper .bx-pager {
                text-align: center;
                font-size: .85em;
                font-family: Arial;
                font-weight: bold;
                color: #666;
                padding-top: 20px;
                bottom: 20px;
                /* Position over the banner */
                position: absolute;
                width: 100%;
                z-index: 50;
            }

            .bx-wrapper .bx-pager.bx-default-pager a {
                background: rgba(255, 255, 255, 0.5);
                text-indent: -9999px;
                display: block;
                width: 14px;
                height: 14px;
                margin: 0 5px;
                outline: 0;
                border-radius: 7px;
                border: 1px solid #fff;
            }

            .bx-wrapper .bx-pager.bx-default-pager a:hover,
            .bx-wrapper .bx-pager.bx-default-pager a.active {
                background: #fff;
                background-color: #ef305e;
                border: 1px solid #ef305e;
            }

            .bx-controls-direction a {
                position: absolute;
                top: 50%;
                margin-top: -16px;
                outline: 0;
                width: 32px;
                height: 32px;
                text-indent: -9999px;
                z-index: 9999;
            }
            
            /* Ensure the row below starts on a new line */
            .mt13 {
                clear: both;
            }

            /* Custom Fixes for Product Grid */
        </style>
    <style>
        /* Legacy Product List Styles */
        /* Legacy Product List Styles Refined for 5-Column Grid */
        #category_nav01 .goodsDisplayItemWrap,
        #category_nav04 .goodsDisplayItemWrap {
            border: 2px solid transparent !important;
            padding: 7px !important;
            background: #FFF;
            position: relative;
            z-index: 1;
            /* float: left; - handled by grid */
            width: 100% !important; /* Use 100% of grid column */
            height: 325px !important;
            margin: 0 !important;
            margin-bottom: -1px !important;
            box-sizing: border-box !important; 
        }

        /* Float resets not needed for CSS grid parent */
        
        /* ... Mobile Styles ... */
        @media (max-width: 768px) {
            #category_nav01 .clearfix,
            #category_nav02 .clearfix,
            #category_nav03 .clearfix,
            #category_nav04 .clearfix {
                display: block; /* Disable grid on mobile */
            }
            .goodsDisplayItemWrap {
                width: 50% !important;
                height: auto !important;
                float: left !important;
                clear: none !important; /* Reset nth-child clears if any */
            }
            .goodsDisplayItemWrap:nth-child(2n+1) {
                clear: both !important;
            }
            .goodsDisplayItemWrap:nth-child(2n) {
                clear: none !important;
            }
            .bx-wrapper .bx-viewport {
                overflow: hidden !important;
                height: auto !important;
                border: none !important;
                box-shadow: none !important;
                min-height: 200px !important; 
            }
            .mslide img {
                width: 100% !important;
                height: auto !important; 
            }
            .mslide > div {
                float: left !important; 
                display: block !important;
            }
            .section_main {
                width: 100% !important;
                float: none !important;
                height: auto !important;
            }
        }

        /* Reset DL/DT/DD */
        .goodsDisplayItemWrap dl, 
        .goodsDisplayItemWrap dt, 
        .goodsDisplayItemWrap dd {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
        }
        
        .goodsDisplayItemWrap dt.goods-thumb {
            display: block;
            width: 100%; 
            height: 172px;
            overflow: hidden !important;
            border: 1px solid #f2f3f4;
            margin: 0 auto !important; 
            box-sizing: border-box;
        }

        .goodsDisplayItemWrap dt.goods-thumb .goodsDisplayImageWrap {
            position: relative;
            width: 100%;
            display: block;
            height: 172px;
        }

        .goodsDisplayImageWrap > a > img {
            transform: scale(1);
            overflow: hidden !important;
            transition: all 0.6s;
            max-width: 100%;
            max-height: 100%;
            display: block;
            margin: 0 auto;
        }
        .goodsDisplayImageWrap:hover > a > img {
            transform: scale(1.2);
            overflow: hidden !important;
        }

        .goodsDisplayTitle {
            margin-top: 8px !important;
            display: block !important;
            width: 100% !important;
        }

        .goodsDisplayTitle h6 {
            margin: 0 !important;
            padding: 0 4px !important; 
            font-size: 13px !important; /* Legacy is 13px */
            line-height: 1.4 !important; /* Legacy is 1.4 */
            font-weight: 700 !important; /* Legacy is bold */
            height: 38px !important; /* Fixed height for 2 lines */
            display: -webkit-box !important; /* Required for line-clamp */
            word-wrap: break-word;
            overflow: hidden !important; 
            -webkit-line-clamp: 2; /* Limit to 2 lines */
            -webkit-box-orient: vertical; /* Required for line-clamp */
            text-overflow: ellipsis; /* Add ellipsis */
            color: #333 !important; /* Legacy is #333 */
            font-family: '맑은고딕', 'Malgun Gothic', 'Noto Sans KR', sans-serif !important;
            text-align: left;
            word-break: keep-all; /* Prevent mid-word breaks for Korean */
            text-decoration: none !important;
        }
        .goodsDisplayTitle h6 a {
            text-decoration: none !important;
            color: inherit !important;
        }

        .goodsDisplaySalePrice {
             text-align: left;
             margin-top: 5px !important;
             padding: 0 4px !important;
        }
        .goodsDisplaySalePrice .price_txt {
            color: #333; /* Legacy often uses dark gray for label */
            font-size: 12px !important;
            font-weight: normal;
        }
        .goodsDisplaySalePrice .price_num {
            color: #f8601d;
            font-size: 16px !important; /* Legacy is 16px */
            font-weight: 800;
            text-align: left;
            float: right; 
        }

        .goodsDisplayItemWrap:hover {
            border: 2px solid #ff5400 !important;
            /* box-shadow: 0px 0px 18px 3px rgba(0,0,0,0.15); REMOVED shadow to prevent layout overflow issues if any */
            z-index: 50; /* High z-index to show above others */
        }
        .goodsDisplayItemWrap:hover .goodsDisplayQuickMenu {
            bottom: -9px;
            opacity: 1;
            z-index: 60;
        }
        .goodsDisplayItemWrap:hover .goodsDisplayQuickMenu {
            bottom: -9px;
            opacity: 1;
            z-index: 10;
        }
        
        .goodsDisplayQuickMenu {
            width: 100%;
            height: 25px;
            font-size: 15px;
            color: #444;
            border-bottom: 1px solid #f2f3f4;
            margin-bottom: 8px;
            text-align: center;
            position: absolute;
            bottom: -20px;
            background: rgba(255, 255, 255, 0.95);
            transition: all .3s;
            left: -3px; /* Adjust for padding difference */
            opacity: 0;
            display: flex; /* Use flex for alignment */
            justify-content: center;
            align-items: center;
        }

        @media (max-width: 768px) {
            .goodsDisplayThumbList {
                display: none !important;
            }
        }

        .goodsDisplayQuickIcon {
            position: relative;
            width: 50px;
            display: inline-block;
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
            top: 5px;
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
        }
        .goodsDisplayQuickIcon:hover > span {
            opacity: 1;
        }

        /* Icon Images */
        .goodsDisplayNew { background: url('/images/legacy/icon/goodsDisplayNew.png') no-repeat center; }
        .goodsDisplayCart { background: url('/images/legacy/icon/goodsDisplayCart.png') no-repeat center; }
        .goodsDisplayCard { background: url('/images/legacy/icon/goodsDisplayCard.png') no-repeat center; }

        /* Tooltip */
        .QuickIconComment {
            position: absolute;
            background: #FFF;
            border: 1px solid #cfd5da;
            top: -25px;
            left: 0px;
            font-size: 11px;
            color: #9eabbb;
            display: none;
            height: 18px;
            width: 50px;
            line-height: 18px;
            text-align: center;
        }
        .goodsDisplayQuickIcon:hover .QuickIconComment {
            display: block;
        }
        
        /* Grid Layout Fixes */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Mobile Responsive Main Grid */
        @media (max-width: 768px) {
            #category_nav01 .clearfix,
            #category_nav02 .clearfix,
            #category_nav03 .clearfix,
            #category_nav04 .clearfix {
                display: block;
            }
            .goodsDisplayItemWrap {
                width: 50% !important;
                height: auto !important;
                float: left !important;
                clear: none !important; /* Reset nth-child clears if any */
            }
            .goodsDisplayItemWrap:nth-child(2n+1) {
                clear: both !important;
            }
            .goodsDisplayItemWrap:nth-child(2n) {
                clear: none !important;
            }
            
            /* Hide Quick Menu on Mobile */
            .goodsDisplayQuickMenu {
                display: none !important;
            }

            /* Adjust Image Container */
            .goodsDisplayItemWrap dt.goods-thumb {
                width: 100% !important;
                height: auto !important;
            }
            .goodsDisplayItemWrap dt.goods-thumb .goodsDisplayImageWrap {
                height: auto !important;
            }
            .goodsDisplayImageWrap > a > img {
                width: 100% !important;
                height: auto !important;
            }
            .goodsDisplayImageWrap > a > img {
                width: 100% !important;
                height: auto !important;
            }

            /* Responsive Banners */
            .middle_banner_wrap { width: 100% !important; margin-bottom: 10px; }
            .notice, .notice_list, .mds_recommand_roll, #right_banner_slider_container { 
                width: 100% !important; 
                height: auto !important; 
                background-size: cover !important;
            }
            .notice_list, .mds_recommand_roll {
                margin-top: 10px !important;
            }
            
            /* Bottom Banners: Stack vertically for legibility */
            #main-wrap .bnr_slide1 { display: block !important; }
            #main-wrap .bnr_slide1 a { display: block !important; width: 100% !important; margin-bottom: 10px; text-align: center; }
            #main-wrap .bnr_slide1 img { width: 100% !important; height: auto !important; }
            
            /* Hide large desktop-only images or sections if needed */
             .main_header > div:first-child { display: none !important; } /* Hide left tree image on mobile */

             /* General Container Fixes */
             .mobile-container-fix { width: 100% !important; padding: 0 10px; box-sizing: border-box; }
             
             /* Ensure middle banners have height even if image fails - REMOVED to fix white space issue */
             /* .middle_banner_wrap { min-height: 100px; background-color: #f5f5f5; } */
             .middle_banner_wrap { min-height: 0 !important; height: auto !important; }

             /* Force Even Count on Mobile for Recommended/New Products */
             /* Hide the last item if it's the odd one (orphan) to maintain a complete 2-column grid */
             .goodsroll.best .clearfix .goodsDisplayItemWrap:nth-child(odd):last-child {
                 display: none !important;
             }
        }
    </style>
    @endpush
    <div id="main-wrap" class="clearbox mb70">
        <!-- main_header -->
        <div class="main_header" style="margin-top: 10px; border-bottom:0 !important;">

            <div class="" style="margin-right: 12px; float: left;">
                <a href='/goods/catalog?code=002000230008' target='_self'>
                    <img src="{{ asset('images/legacy/main/main_top_left2.jpg') }}" alt="미니 가습기" title="미니 가습기">
                </a>
            </div>

            <!-- 메인배너 // 이벤트 -->
            <div class="section_main"
                style="width:689px; margin-right: 12px; float:left;">
                <div class="mslide">
                    @forelse($mainBanners as $banner)
                        @php
                            // Handle object vs model property
                            $imgUrl = $banner->image_url ?? (
                                $banner->image ? asset('data/design/' . $banner->image) : asset('images/no_image.gif')
                            );
                            // Legacy path might be relative 'images/banner/...' or absolute
                            if ($banner instanceof \App\Models\DesignBannerItem) {
                                // If it's a DB model, path is often relative to root or data/design
                                // Inspection showed: "images/banner/11/images_1.jpg"
                                // Local file structure: public/images/legacy/main/banner/images_X.jpg
                                // We need to map "images/banner/11/..." to "images/legacy/main/banner/..."
                                
                                if (Str::contains($banner->image, 'images/banner/11/')) {
                                    $filename = basename($banner->image);
                                    $imgUrl = asset('images/legacy/main/banner/' . $filename);
                                } elseif (!Str::startsWith($banner->image, '/')) {
                                    $imgUrl = '/' . $banner->image;
                                } else {
                                    $imgUrl = $banner->image;
                                }
                            }
                        @endphp
                        <div>
                            <a href="{{ $banner->link }}" target="{{ $banner->target ?? '_self' }}">
                                <img src="{{ $imgUrl }}" style="width:100%; height: 400px; object-fit: cover;"
                                    onerror="this.src='{{ asset('images/legacy/main/banner/images_1.jpg') }}'">
                            </a>
                        </div>
                    @empty
                        <div><img src="{{ asset('images/legacy/main/banner/images_1.jpg') }}" style="width:100%;"></div>
                    @endforelse
                </div>
            </div>

            <!-- GTD 베스트 // 롤링 (Legacy Parity) -->
            <div class="notice" style="width: 291px !important;margin:0 !important;background-color: transparent !important;box-shadow: none !important; float:left;">
                <div style="margin-top:0px;">
                    <a href="/goods/catalog?code=0177"><img src="{{ asset('images/legacy/main/img/main_top_right1_fruit.jpg') }}" onerror="this.src='https://dometopia.com/data/skin/beauty/main/img/main_top_right1_fruit.jpg'" style="width: 100%;"></a>
                </div>
                <div style="margin-top:12px;">
                    <a href="/goods/catalog?code=000500360008" target='_self'><img src="{{ asset('images/legacy/main/img/main_top_right2.jpg') }}" onerror="this.src='https://dometopia.com/data/skin/beauty/main/img/main_top_right2.jpg'" style="width: 100%;" title="썬캡" alt="썬캡"></a>
                </div>
            </div>
        </div>
        
        @include('front.main.mobile_quick_menu')

        <!-- 롤 배너2, 1200카테고리 배너 (Hidden to match Live Site) -->
        {{--
        <!-- 롤 배너2 (Main Middle Banners: B12 Left, B13 Right) -->
        <div class="mt13 middle-banner-container" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
            <!-- Left Banner (12) -->
            <div class="middle_banner_wrap" style="width: 594px; overflow: hidden;">
                @if(isset($middleBannerL) && $middleBannerL->isNotEmpty())
                    <ul class="middle_banner_slider">
                        @foreach($middleBannerL as $banner)
                             @php
                                $imgUrl = $banner->image;
                                if (!Str::startsWith($banner->image, '/') && !Str::startsWith($banner->image, 'http')) {
                                    $imgUrl = '/' . $banner->image;
                                }
                            @endphp
                            <li>
                                <a href="{{ $banner->link }}" target='_self'>
                                    <img src="{{ $imgUrl }}" title="{{ $banner->banner_title ?? '' }}" alt="{{ $banner->banner_title ?? '' }}" style="width:100%; display: block;">
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Right Banner (13) -->
            <div class="middle_banner_wrap" style="width: 594px; overflow: hidden;">
                @if(isset($middleBannerR) && $middleBannerR->isNotEmpty())
                     <ul class="middle_banner_slider">
                        @foreach($middleBannerR as $banner)
                            @php
                                $imgUrl = $banner->image;
                                if (!Str::startsWith($banner->image, '/') && !Str::startsWith($banner->image, 'http')) {
                                    $imgUrl = '/' . $banner->image;
                                }
                            @endphp
                            <li>
                                <a href="{{ $banner->link }}" target='_self'>
                                    <img src="{{ $imgUrl }}" title="{{ $banner->banner_title ?? '' }}" alt="{{ $banner->banner_title ?? '' }}" style="width:100%; display: block;">
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        --}}
        <!-- 롤 배너2, 1200카테고리 배너 (Hidden by default, used for Middle Banners in Legacy) -->
        <!-- Live site uses a specific 4-image banner section here instead of the dynamic middle banners -->
        <div class="mt13" style="padding-top: 12px !important; display: flex; justify-content: space-between; margin-bottom: 20px;">
            <a href="/goods/catalog?code=004200200001" target='_self'>
                <img loading="lazy" decoding="async" src="{{ asset('images/legacy/main/main_top_B1.jpg') }}" title="크리스마스 장식품" alt="크리스마스 장식품" style="width:100%;">
            </a>
            <a href="/goods/catalog?code=00420033" target='_self'>
                <img loading="lazy" decoding="async" src="{{ asset('images/legacy/main/main_top_B2.jpg') }}" title="산타복" alt="산타복" style="width:100%;">
            </a>
            <a href="/goods/search?search_text=칫솔" target='_self'>
                <img loading="lazy" decoding="async" src="{{ asset('images/legacy/main/main_top_B3.jpg') }}" title="수제 장식용 볼" alt="수제 장식용 볼" style="width:100%;">
            </a>
            <a href="/goods/catalog?code=0020002300010001" target='_self'>
                <img loading="lazy" decoding="async" src="{{ asset('images/legacy/main/main_top_B4.jpg') }}" title="LED 트리" alt="LED 트리" style="width:100%;">
            </a>
        </div>

        <!-- 사업부 4가지 -->
        <div class="bnr_slide1">
            <a href="/board/?id=bulkorder" target="_self"><img loading="lazy" decoding="async" src="{{ asset('images/legacy/main/n_bnr_tile01.jpg') }}"
                    title="띠배너01" alt="B2B 대량구매ㆍ대량견적 상담"></a>
            <a href="/page/index?tpl=etc/school_29.html#academy-3" target="_self"><img loading="lazy" decoding="async" 
                    src="{{ asset('images/legacy/main/n_bnr_tile02.jpg') }}" title="띠배너03" alt="쿠팡 로켓그로스 브랜드 개발"></a>
            <a href="/page/index?tpl=etc/school_29.html" target="_self"><img loading="lazy" decoding="async" 
                    src="{{ asset('images/legacy/main/n_bnr_tile03.jpg') }}" title="띠배너03" alt="오토셀러"></a>
            <a href="https://www.youtube.com/@dometopia-tv" target="_blank"><img loading="lazy" decoding="async" 
                    src="{{ asset('images/legacy/main/n_bnr_tile04.jpg') }}" title="띠배너03" alt="도토창업아카데미"></a>
        </div>

        <div class="clearfix mobile-container-fix" style="width: 1200px; margin: 0 auto; position: relative; padding-bottom: 50px;">
            <style>
                /* Responsive Styles for Left Category */
                @media (max-width: 1219px) { /* Slightly above 1200 to catch edge cases */
                    #leftCate { display: none !important; }
                    .goodsroll { width: 100% !important; float: none !important; }
                    #main-wrap { width: 100% !important; padding: 0; box-sizing: border-box; overflow-x: hidden; }
                    .main_header, .bnr_slide1, .mt13 { width: 100% !important; flex-wrap: wrap; height: auto !important; }
                    .section_main { width: 100% !important; float: none !important; margin-right: 0 !important; }
                    .notice { width: 100% !important; margin-left: 0 !important; float: none !important; display: flex; flex-direction: column; height: auto !important; background: none;}
                    .notice .notice_list, .notice .mds_recommand_roll { width: 100% !important; margin: 10px 0 !important; background-size: cover; }
                }

                #leftCate { float: left; width: 196px; z-index: 20; position: relative; }
                .goodsroll { float: right; width: 995px; } /* Increased to 995px to fit 5x199px items */
                
                #leftCate .catelist .list { 
                    width: 196px; 
                    height: 62px; 
                    border: 1px solid #eeeeea; 
                    border-top: none; 
                    background: #fff; 
                    box-sizing: border-box;
                    padding: 6px 5px;
                    position: relative;
                }
                #leftCate .catelist .list:first-child { border-top: 1px solid #eeeeea; }
                
                #leftCate .catelist .list > a { display: block; width: 100%; height: 100%; text-decoration: none !important; }
                
                /* Remove underlines globally for these sections */
                a { text-decoration: none; color: inherit; }
                
                #leftCate .catelist .list a > span { 
                    float: left; width: 57px; height: 48px; margin-right: 5px; 
                    background-color: #f7f8f9; border-radius: 5px; overflow: hidden; 
                    position: relative; /* Fix for image pos */
                }
                #leftCate .catelist .list a > span img { 
                    width: 100%; height: auto; 
                    margin: 0 !important; 
                    position: static !important; /* Override negative Y */
                    display: block;
                }
                
                #leftCate .catelist .list a > h6 { 
                    float: right; width: 122px; font-size: 13px; font-weight: bold; color: #333; 
                    margin: 0; padding-top: 5px;
                    line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                }
                #leftCate .catelist .list a > p { 
                    float: right; width: 122px; font-size: 11px; color: #999; 
                    margin: 0; padding-top: 2px;
                    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                }
                
                /* Submenu */
                #leftCate .catelist .list:hover .sub_catelist { 
                    visibility: visible; opacity: 1; left: 195px; 
                }
                .sub_catelist {
                    visibility: hidden; opacity: 0;
                    position: absolute; top: -1px; left: 190px;
                    border: 1px solid #9eabbb; background: #fff;
                    z-index: 100; padding: 15px;
                    box-shadow: 0px 0px 15px 2px rgba(46,47,49,0.15);
                    transition: left 0.1s, opacity 0.1s;
                    white-space: nowrap;
                }
                
                .sub_catelist .inner { display: flex; align-items: flex-start; }
                
                .sub_catelist .list_wrap { 
                    padding-right: 25px; 
                    list-style: none; margin: 0; padding-left: 0;
                    border-left: 1px solid #eee; /* Optional separator */
                    padding-left: 25px;
                }
                .sub_catelist .list_wrap:first-child { border-left: none; padding-left: 0; }
                .sub_catelist .list_wrap:last-child { padding-right: 0; }

                .sub_catelist .list_wrap li a {
                    display: block;
                    font-size: 13px; line-height: 2.1; 
                    text-align: left; color: #666;
                    text-decoration: none;
                }
                .sub_catelist .list_wrap li a:hover {
                    text-decoration: underline; font-weight: bold; color: #eb6506; 
                }

                /* Ad Banner Column */
                .sub_catelist .adbnr_wrap {
                    padding-left: 20px;
                    border-left: 1px solid #eee;
                    margin-left: 10px;
                }
                .sub_catelist .adbnr_wrap img { width: 100%; }

                /* Clearfix for container */
                .clearfix::after { content: ""; display: block; clear: both; }
            </style>
            
            <!-- 왼쪽 main_section -->
            <!-- 왼쪽 main_section -->
            @include('front.layouts.sidebar')

            <!-- 우측 main_section -->
            <div class="goodsroll best" id="category_nav01">
                <!-- 도매토피아 추천상품 -->
                <div class="marin-tit" style="padding: 0px 0 5px;"><b>★추천상품</b>
                    <p><a href="#">더보기</a><img src="{{ asset('images/legacy/icon/view_icon_info.gif') }}"
                            onerror="this.style.display='none'"></p>
                </div>

                <div class="clearfix">
                    @forelse($bestProducts as $product)
                        @include('front.main.product_item', ['product' => $product, 'rank' => $loop->iteration])
                    @empty
                        <div style="width:100%; text-align:center; padding:50px;">상품이 없습니다.</div>
                    @endforelse
                </div>
            </div> <!-- End of category_nav01 -->

            <!-- 판촉물, 해외직구 -->
            <div class="mdswrap clearfix">
                    <div class="section_mds right" id="category_nav03">
                        <div class="mdsbnr"><a href="/goods/catalog?code=0139"><img loading="lazy" decoding="async" 
                                    src="{{ asset('images/legacy/main/bnr_free_eve.jpg') }}" alt="프리세일 상품관"
                                    style="width:100%;" /></a></div>
                    </div>

                    <div class="section_mds left" id="category_nav02">
                        <div class="mdsbnr"><a href="/gift" target='_self'><img loading="lazy" decoding="async" 
                                    src="{{ asset('images/legacy/main/bnr_mds_gift.jpg') }}" alt="인쇄 판촉물"
                                    style="width:100%;" /></a></div>
                    </div>
                </div>

                <!-- 초저가 상품 ~ 땡처리 -->
                <div id="section_gtbnr" class="clearfix">
                    <div class="marin-tit"><b>다多다多 할인</b>&nbsp;
                        <p>직수입 특가, 땡처리 상품은 한정 기간에만 판매되는 할인 상품입니다.</p>
                    </div>

                    <div class="gtq" id="category_nav05">
                        <a href="/goods/catalog?code=0147" target='_self'>
                            <img loading="lazy" src="{{ asset('images/legacy/main/bnr_gth.jpg') }}" alt="직수입 특가" style="width:100%; display:block;" />
                        </a>
                        <div>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                @foreach($dadaDirectImportProducts->take(8) as $product)
                                    @php
                                        $imgSrc = asset('images/legacy/common/noimage.gif');
                                        $targetPath = null;
                                        if ($product->images && $product->images->count() > 0) {
                                            $targetImg = $product->images->where('image_type', 'main')->first();
                                            if (!$targetImg) $targetImg = $product->images->first();
                                            if ($targetImg) $targetPath = $targetImg->image;
                                        }
                                        if (empty($targetPath) && !empty($product->img_s)) $targetPath = $product->img_s;
                                        if (!empty($targetPath)) {
                                            $targetPath = trim($targetPath);
                                            if (preg_match('/^http/', $targetPath)) $imgSrc = $targetPath;
                                            elseif (Str::startsWith($targetPath, 'images/legacy_synced/')) $imgSrc = asset($targetPath);
                                            elseif (strpos($targetPath, 'goods_img') !== false) {
                                                $suffix = substr($targetPath, strpos($targetPath, 'goods_img') + 9);
                                                $imgSrc = "https://dmtusr.vipweb.kr/goods_img" . $suffix;
                                            }
                                            else $imgSrc = 'http://dometopia.com/data/goods/' . $targetPath;
                                        }
                                    @endphp
                                    <li class="goodsDisplayItemWrap" style="padding: 0 !important; border: 0 !important;">
                                        <div class="dada-thumb">
                                            <a href="/goods/view?no={{ $product->goods_seq }}" style="display: block; width: 100px; height: 100px;">
                                                <img src="{{ $imgSrc }}" loading="lazy" decoding="async" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='{{ asset('images/legacy/common/noimage.gif') }}'">
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div style="clear: both;"></div>
                        </div>
                    </div>

                    <div class="gtq2" id="category_nav06" style="margin-left:13px;">
                        <a href="https://dometopia.com/goods/catalog?code=0055" target='_self'>
                            <img loading="lazy" src="{{ asset('images/legacy/main/bnr_gtq.jpg') }}" title="땡처리 상품전" alt="땡처리 상품전" style="width:100%; display:block;" />
                        </a>
                        <div>
                            <ul style="list-style: none; padding: 0; margin: 0;">
                                @foreach($dadaClearanceProducts->take(8) as $product)
                                    @php
                                        $imgSrc = asset('images/legacy/common/noimage.gif');
                                        $targetPath = null;
                                        if ($product->images && $product->images->count() > 0) {
                                            $targetImg = $product->images->where('image_type', 'main')->first();
                                            if (!$targetImg) $targetImg = $product->images->first();
                                            if ($targetImg) $targetPath = $targetImg->image;
                                        }
                                        if (empty($targetPath) && !empty($product->img_s)) $targetPath = $product->img_s;
                                        if (!empty($targetPath)) {
                                            $targetPath = trim($targetPath);
                                            if (preg_match('/^http/', $targetPath)) $imgSrc = $targetPath;
                                            elseif (Str::startsWith($targetPath, 'images/legacy_synced/')) $imgSrc = asset($targetPath);
                                            elseif (strpos($targetPath, 'goods_img') !== false) {
                                                $suffix = substr($targetPath, strpos($targetPath, 'goods_img') + 9);
                                                $imgSrc = "https://dmtusr.vipweb.kr/goods_img" . $suffix;
                                            }
                                            else $imgSrc = 'http://dometopia.com/data/goods/' . $targetPath;
                                        }
                                    @endphp
                                    <li class="goodsDisplayItemWrap" style="padding: 0 !important; border: 0 !important;">
                                        <div class="dada-thumb">
                                            <a href="/goods/view?no={{ $product->goods_seq }}" style="display: block; width: 100px; height: 100px;">
                                                <img src="{{ $imgSrc }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='{{ asset('images/legacy/common/noimage.gif') }}'">
                                            </a>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <div style="clear: both;"></div>
                        </div>
                    </div>
                </div>

                <!-- 새로 만나는 신상품 -->
                <div class="goodsroll best" id="category_nav04" style="margin-top: 50px; clear: right; float: right; width: 991px;"> <!-- Clear right to avoid dropping below left sidebar -->
                    <div class="marin-tit">새로 만나는 <b>신상품</b>
                        <p><a href="/goods/search?sort=news">신상품 더 보기</a><img loading="lazy"
                                src="{{ asset('images/legacy/icon/view_icon_info.gif') }}"
                                onerror="this.style.display='none'"></p>
                    </div>

                    <div class="clearfix">
                        @forelse($newProducts as $product)
                            @include('front.main.product_item', ['product' => $product])
                        @empty
                            <div style="width:100%; text-align:center; padding:50px;">신상품이 없습니다.</div>
                        @endforelse
                    </div>
                </div>

            <!-- 카테고리 인기상품 (Full Width 1200px) -->
            <div style="clear: both; width: 100%; padding-bottom: 20px;">
                <div class="marin-tit"><b>카테고리</b>&nbsp; 인기상품</div>
                <div class="recommend_item" id="category_nav07" style="display: flex; flex-wrap: wrap; justify-content: space-between; width: 100%; margin-top: 10px;">
                    <style>
                        .designDisplay h5 { font-size:15px; font-weight:500; letter-spacing:-0.5px; margin:0 auto; text-align:left; margin-bottom:15px; color:#333; }
                        .designDisplay .container2 { margin:0 auto; overflow-y:hidden; }
                        .designDisplay .item2 { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; justify-content: space-between; overflow: hidden; }
                        .designDisplay .item2 li { width: 110px; margin-bottom: 25px; margin-left: 0 !important; margin-right: 0 !important; font-family:'맑은고딕', 'Malgun Gothic', sans-serif;}
                        .designDisplay .item2 .thumb2 { width:100px; height:100px; overflow:hidden; background-color:#ddd; margin-bottom:10px; border: 1px solid #ededed; }
                        .designDisplay .item2 .thumb2 img { width:100px; height:100px; object-fit: cover; }
                        .designDisplay .item2 .code { font-size:12px; line-height:12px; font-weight:bold !important; color:#444 !important; display:block; margin-bottom:2px; font-family:'맑은고딕', 'Malgun Gothic', sans-serif;}
                        .designDisplay .item2 li:hover h6.title { text-decoration:underline !important; }
                        .designDisplay .item2 .title {
                            font-size:12px; line-height:14px; font-weight:bold; height:28px; color:#444 !important;
                            display: -webkit-box; word-wrap:break-word; overflow-y:hidden;
                            -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin: 0;
                            font-family:'맑은고딕', 'Malgun Gothic', sans-serif;
                        }
                        .designDisplay .item2 .price { font-size:12px; color:#f7601d; font-weight:800; margin-top:5px; font-family:'맑은고딕', 'Malgun Gothic', sans-serif; word-spacing:-5px; text-align:center;}
                        .designDisplay .item2 .price b { font-size:12px !important; font-weight:bold !important; letter-spacing:-0.3px; color:#f7601d;}
                        .designDisplay .title_tab { display: flex; justify-content: space-between; margin: 15px 10px 5px 0; }
                        
                        /* Removed @media query to force fixed desktop layout parity */
                    </style>
                    @foreach([7155, 7158, 7156, 7159, 7154, 7157] as $groupId)
                        @if(isset($categoryPopularGroups[$groupId]) && $categoryPopularGroups[$groupId]['products']->isNotEmpty())
                            @php
                                $group = $categoryPopularGroups[$groupId];
                                $groupTitle = $group['title'];
                                $products = $group['products'];
                            @endphp
                            <div class="group middle" style="width: 32%; background-color: #fff; margin-bottom: 15px; border: 1px solid #e9ecef; padding-left: 12px; box-sizing: content-box;">
                                <div class="designDisplay">
                                    <div class='title_tab'>
                                        <div>
                                            <h5>{{ $groupTitle }}</h5>
                                        </div>
                                        <div>
                                            <!-- Fallback more link to main category page or search based on legacy UX -->
                                            <a href='/goods/catalog?code=0177'><img loading="lazy" src="{{ asset('data/icon/main_more.jpg') }}" alt="더보기" onerror="this.src='{{ asset('images/legacy/icon/main_more.jpg') }}'" style="margin-top:-3px;"></a>
                                        </div>
                                    </div>
                                    <table class="displayTabContentsContainer" width="100%" cellpadding="0" cellspacing="0" border="0" style="table-layout:fixed">
                                        <tr>
                                            <td>
                                                <div class="container2">
                                                    <ul class="item2">
                                                        @foreach($products as $product)
                                                            @php
                                                                $imgSrc = asset('images/legacy/common/noimage.gif');
                                                                $targetPath = null;
                                                                
                                                                if ($product->images && $product->images->count() > 0) {
                                                                    $targetImg = $product->images->where('image_type', 'main')->first();
                                                                    if (!$targetImg) $targetImg = $product->images->first();
                                                                    if ($targetImg) $targetPath = $targetImg->image;
                                                                }
                                                                
                                                                if (empty($targetPath) && !empty($product->img_s)) {
                                                                    $targetPath = $product->img_s;
                                                                }
                    
                                                                if (!empty($targetPath)) {
                                                                    $targetPath = trim($targetPath);
                                                                    if (Str::startsWith($targetPath, 'http')) {
                                                                        $imgSrc = $targetPath;
                                                                    } elseif (Str::startsWith($targetPath, 'images/legacy_synced/')) {
                                                                        $imgSrc = asset($targetPath);
                                                                    } elseif (strpos($targetPath, 'goods_img') !== false) {
                                                                        $suffix = substr($targetPath, strpos($targetPath, 'goods_img') + 9);
                                                                        $imgSrc = "https://dmtusr.vipweb.kr/goods_img" . $suffix;
                                                                    } else {
                                                                        $imgSrc = 'http://dometopia.com/data/goods/' . $targetPath;
                                                                    }
                                                                }
                                                            @endphp
                                                            <li class="goodsDisplayItemWrap2">
                                                                <a href="/goods/view?no={{ $product->goods_seq }}" style="display: block; text-decoration: none;">
                                                                    <div class="thumb2">
                                                                        <img src="{{ $imgSrc }}" onerror="this.src='{{ asset('images/legacy/common/noimage.gif') }}'">
                                                                    </div>
                                                                    <span class="code">{{ $product->goods_scode }}</span>
                                                                    <h6 class="title">{{ $product->goods_name }}</h6>
                                                                    <p class="price" style="text-align:center; word-spacing: normal;"><span style="color:#666; font-weight:normal;">도매가</span> <b>{{ number_format($product->price) }}</b>원</p>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <!-- // 카테고리 인기상품 끝 -->

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/jquery.bxslider.js') }}"></script>
     <script>
     $(document).ready(function () {
         // Main Visual Slider
         if ($('.mslide').length) {
             var isMobile = $(window).width() <= 768;
             $('.mslide').bxSlider({
                 mode: isMobile ? 'fade' : 'horizontal',
                 auto: true,
                 pager: true,
                 controls: false,
                 autoControls: false,
                 minSlides: 1,
                 maxSlides: 1,
                 moveSlides: 1,
                 slideWidth: 1200,
                 speed: 500,
                 pause: 6000,
                 infiniteLoop: true,
                 touchEnabled: true,
                 preventDefaultSwipeX: true,
                 oneToOneTouch: false,
                 responsive: true,
                 adaptiveHeight: true
             });
         }
         
         // 2026-01-28 Auto-hide broken middle banners to prevent white space
         $('.middle_banner_wrap').each(function() {
             var $wrap = $(this);
             var hasValidImg = false;
             $wrap.find('img').each(function() {
                 if (this.complete && this.naturalWidth > 0) {
                     hasValidImg = true;
                     return false; // break
                 }
             });
             
             // If images are not yet loaded, wait for them
             if (!hasValidImg) {
                 var $imgs = $wrap.find('img');
                 if ($imgs.length > 0) {
                      var loadedCount = 0;
                      $imgs.on('load error', function() {
                          loadedCount++;
                          if (this.naturalWidth > 0) $(this).closest('.middle_banner_wrap').show();
                          if (loadedCount === $imgs.length && $wrap.find('img').filter(function(){ return this.naturalWidth > 0; }).length === 0) {
                              $wrap.hide();
                          }
                      });
                      // Hide initially until confirmed valid
                      $wrap.hide(); 
                 } else {
                     $wrap.hide();
                 }
             }
         });
         
         // Fix bottom banner grid on mobile via JS backup
         if (isMobile) {
             $('.bnr_slide1 img').css({'width': '100%', 'height': 'auto'});
             $('.bnr_slide1 a').css({'display': 'block', 'width': '100%', 'margin-bottom': '10px'});
         }
         // Middle Banners Slider (Banner 12, 13)
         if ($('.middle_banner_slider').length) {
             $('.middle_banner_slider').bxSlider({
                 mode: 'horizontal',
                 auto: true,
                 pause: 4000,
                 controls: false,
                 pager: true,
                 autoHover: true
             });
         }

         // Right Side Best/New Slider
         if ($('.right_banner_slider').length) {
             $('.right_banner_slider').bxSlider({
                 mode: 'horizontal',
                 auto: true,
                 pause: 2500,
                 pager: false,
                 controls: false,
                 autoHover: true,
                 maxSlides: 1,
                 moveSlides: 1,
                 slideWidth: 291
             });
         }
     });
    </script>
@endpush