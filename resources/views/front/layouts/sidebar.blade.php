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
    /* Note: .goodsroll width is defined in parent container context usually, but we keep it here for reference or move to layout CSS */
    
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
    
    #leftCate .catelist .list a > span { 
        float: left; width: 57px; height: 48px; margin-right: 5px; 
        background-color: #f7f8f9; border-radius: 5px; overflow: hidden; 
        position: relative; 
    }
    #leftCate .catelist .list a > span img { 
        width: 100%; height: auto; 
        margin: 0 !important; 
        position: static !important;
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
        border-left: 1px solid #eee;
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
</style>
<div id="leftCate">
    <div class="catelist">
        @foreach($categories as $cat)
            <div class="list">
                <a href="{{ route('goods.catalog', ['code' => $cat->category_code]) }}">
                    <span>
                        @if($cat->main_category_image)
                            <img src="{{ $cat->main_category_image }}" loading="lazy"
                                 style="max-width: 100%; max-height: 100%;"
                                 onerror="this.src='{{ asset('images/no_image.gif') }}'"
                                 @if($cat->main_category_over_image)
                                 onmouseover="this.src='{{ $cat->main_category_over_image }}'"
                                 onmouseout="this.src='{{ $cat->main_category_image }}'"
                                 @endif
                            >
                        @endif
                    </span>
                    <h6>{{ $cat->title }}</h6>
                    <p>{{ $cat->description ?? '' }}</p>
                </a>
                
                @if($cat->children && $cat->children->count() > 0)
                    <div class="sub_catelist">
                        <div class="inner">
                            <ul class="list_wrap">
                                @foreach($cat->children as $index => $child)
                                    <li><a href="{{ route('goods.catalog', ['code' => $child->category_code]) }}">{{ $child->title }}</a></li>
                                    @if(($index + 1) % 16 == 0)
                                        </ul><ul class="list_wrap">
                                    @endif
                                @endforeach
                            </ul>
                            @if($cat->main_category_detail_image)
                                <div class="list_wrap adbnr_wrap">
                                    <a href="{{ route('goods.catalog', ['code' => $cat->category_code]) }}">
                                        <img src="{{ $cat->main_category_detail_image }}" alt="Hot Item" />
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- 고객만족센터 -->
    <div class="side_bnr" style="margin-top: 20px;">
        <a href="/service/cs" style="display:block; margin-bottom:5px;">
            <img src="{{ asset('images/legacy/main/side_bnr_cs.jpg') }}" alt="고객만족센터,주문상담" style="width:100%;">
        </a>
        <a href="/board/?id=bulkorder" style="display:block; margin-bottom:5px;">
            <img src="{{ asset('images/legacy/main/side_bnr_b2b.jpg') }}" alt="배송문의" style="width:100%;">
        </a>
        <a href="https://shopon.biz/" target='_self' style="display:block; margin-bottom:5px;">
            <img src="{{ asset('images/legacy/main/side_bnr_aca.jpg') }}" title="샵온 가입상담" alt="샵온 가입상담" style="width:100%;">
        </a>
        <div>
            <img src="{{ asset('images/legacy/main/side_bnr_csInfo.jpg') }}" alt="고객센터 운영정보" style="width:100%;">
        </div>
    </div>
</div>
