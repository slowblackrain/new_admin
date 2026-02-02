
<style type="text/css">
/* Ported from Legacy right.html & Refined for Consistency */
#rightScrollLayer { position: absolute; left: 50%; margin-left: 620px; top: 180px; z-index: 90; }
@media (max-width: 1210px) {
    #rightScrollLayer { display: none !important; }
}
.rightQuickMenuWrap { margin-top:0px; } /* Removed negative margin */

/* Standardize Width */
.rightQuickMenu { width: 130px; }
.rightQuickMenu img { vertical-align: top; max-width: 100%; } /* Force images to fit */

/* Common Styles for Box Items */
.right_item_tel, 
.doto-right-item, 
.right_item_mypage, 
.right_item_recent { 
    width: 130px; 
    box-sizing: border-box; /* Ensure padding doesn't affect total width */
    background: #fff; 
    border: 1px solid #cfd5da; 
    text-align: center;
    margin-bottom: 5px; /* Consistent spacing */
}

/* Tel Section */
.right_item_tel { padding: 10px 0; border-top: none; }
.right_item_tel h4 { font-size: 12px; font-family: '맑은고딕','Malgun Gothic'; font-weight: bold; padding-bottom: 4px; margin: 0 0 10px 0; position: relative; }
.right_item_tel h4:after { content: ""; width: 62px; height: 1px; border-bottom: 1px dashed #cfd5da; position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); }
.right_item_tel p { font-size: 15px; color: #f8601d; font-weight: bold; margin: 0 0 10px 0; line-height: 1; }
.right_item_tel .question { display: inline-block; width: 84px; height: 22px; line-height: 20px; border: 1px solid #cfd5da; border-radius: 2px; font-size: 11px; }
.right_item_tel .question a { display: block; width: 100%; height: 100%; text-decoration: none; color: #333; }

/* B2B / Startup Section */
.doto-right-item { padding: 0; border-top: none; }
.doto-right-item a { display: block; padding: 10px 0; text-decoration: none; }
.doto-right-item a + a { border-top: 1px dashed #cfd5da; }
.doto-right-item p.tit { font-size: 11px; color: #999; margin-bottom: 5px; position: relative; display: inline-block; }
.doto-right-item p.tit i { margin-left: 5px; color: #f44336; font-size: 10px; } 
.doto-right-item p { font-size: 12px; color: #333; line-height: 1; margin: 0; }
.doto-right-item i.red_arrow { background: url(/images/legacy/icon/tiny_red.png); width: 3px; height: 5px !important; display: inline-block; margin-left: 5px; vertical-align: middle; }

/* MyPage Section */
.right_item_mypage { background-image: url(/images/legacy/common/quick-pattern.jpg); padding: 0; border-top: none; }
.right_item_mypage a { display: block; color: #FFF; font-size: 12px; text-decoration: none; line-height: 28px; }
.right_item_mypage i.red_arrow_mid { background: url(/images/legacy/icon/arrow_r_tiny_red.png); width: 4px; height: 7px !important; display: inline-block; margin-left: 10px; }
.right_item_mypage .quick_cart_total { color: #f8601d; margin-left: 10px; font-weight: bold; }
.right_item_line { border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid #2d313d; margin: 0; height: 0; }

/* Recent Items Section */
.right_item_recent { padding: 5px; border-top: none; }
.right_item_recent dl { margin: 0; padding: 5px 0 10px; }
.right_item_recent dt { font-size: 12px; font-weight: bold; cursor: pointer; display: inline-block; }
.right_item_recent dd { font-size: 13px; font-weight: bold; color: #f44336; display: inline-block; margin-left: 5px; }

.right_item_recent ul { padding: 0; margin: 0; list-style: none; }
.right_item_recent li { position: relative; margin-bottom: 10px; }

.right_quick_goods_box { display: block; }
.right_quick_goods img { border: 2px solid #fff; width: 100%; height: auto; display: block; box-sizing: border-box; } 

/* Detail Popup */
.rightQuickitemDetail {
    position: absolute;
    width: 162px;
    height: 90px;
    background: rgba(0,0,0,0.8);
    right: 125px; /* Adjusted for 130px width + gap */
    top: 0;
    color: #FFF;
    padding: 13px 20px;
    box-sizing: border-box;
    text-align: left;
    visibility: hidden;
    z-index: 100;
}
.rightQuickitemDetail .right_item_title { font-size: 12px; margin-bottom: 5px; line-height: 1.3; max-height: 2.6em; overflow: hidden; }
.rightQuickitemDetail .right_item_price { font-size: 15px; font-weight: bold; color: #fff; }

/* Paging */
.right_quick_paging { width: 100%; text-align: center; margin-top: 5px; }
.right_quick_paging img { width: auto; vertical-align: middle; cursor: pointer; }
.right_quick_paging span { font-size: 11px; color: #666; vertical-align: middle; margin: 0 2px; }

/* Spacing Helpers */
.rightBookMark, .special { margin-bottom: 5px; }
.rightBookMark img, .special img { width: 100%; display: block; }
.rightBookMark a, .special a { display: block; text-decoration: none; }
</style>

<div id="rightScrollLayer">
    <div id="rightQuickMenu" class="rightQuickMenu">
        
        <!-- Bookmark / Kakao -->
        <div class="rightBookMark">
            <a href="#" onclick="alert('Ctrl+D를 눌러 즐겨찾기에 추가하세요.'); return false;" title="즐겨찾기에 추가">
                <img src="/images/legacy/common/quick-bookmark.jpg" alt="북마크" style="border-radius: 3px 3px 0 0;"/>
            </a>
            <a href="http://pf.kakao.com/_AUxbuT/chat" target="_blank" title="카카오톡">
                <img src="/images/legacy/common/quick-kakaotalk.jpg" alt="카카오톡"/>
            </a>
        </div>

        <!-- Special Banners -->
        <div class="special">
             <a href="#"><img src="/images/legacy/common/member_special_quick.png" alt="기업회원 우대정책"></a>
        </div>

        <!-- Tel -->
        <div class="right_item_tel">
            <h4>대표전화</h4>
            <p>02-2026-2754</p>
            <span class="question"><a href="/board/?id=mbqna">1:1고객문의</a></span>
        </div>

        <!-- B2B / Shop -->
        <div class="doto-right-item">
            <a href="/board/?id=bulkorder">
                <p class="tit">B2B대량견적<i class="red_arrow"></i></p>
                <p>02-2026-2754</p>
            </a>
            <a href="http://pf.kakao.com/_GPpPK/chat" target="_blank">
                <p class="tit">쇼핑몰창업<br>가입상담<i class="red_arrow"></i></p>
                <p>02-2026-2759</p>
            </a>
        </div>

        <!-- My Page / Cart -->
        <div class="right_item_mypage">
            <a href="/mypage" class="mypage"><p class="patt_tit">마이페이지<i class="red_arrow_mid"></i></p></a>
            <p class="right_item_line"></p>
            <a href="/order/cart" class="cart"><p class="qcr_tit">장바구니<span class="quick_cart_total" id="right_cart_total">0</span></p></a>
            <p class="right_item_line"></p>
            <a href="/mypage/order_catalog" class="mypage"><p class="patt_tit">주문/배송</p></a>
        </div>

        <!-- Recent Items -->
        <div class="right_item_recent">
            <dl>
                <dt class="rightTitleMenu">최근본상품</dt>
                <dd class="right_recent_total" id="right_recent_total"><a href="#">0</a></dd>
            </dl>
            <div class="right_itemList">
                <ul>
                    <!-- AJAX Loaded Items -->
                </ul>
                <div id="right_page_div" class="right_quick_paging">
                    <a class="right_quick_btn_prev arrow"><img src="/images/legacy/common/right_quick_menu_left_icon.jpg" alt="prev" /></a>
                    <div class="right_page_box">
                        <span class="right_quick_current_page right_quick_count">1</span>
                        <span class="right_quick_separation">/</span>
                        <span class="right_quick_total_page right_quick_count">1</span>
                    </div>
                    <a class="right_quick_btn_next arrow"><img src="/images/legacy/common/right_quick_menu_right_icon.jpg" alt="next" /></a>
                </div>
            </div>
        </div>

    </div>

    <!-- Top Button -->
    <div id="rightQuickMenuBottom" class="rightQuickMenuBottom">
        <div class="rightTop center">
            <a href="javascript:;" onclick="$('body,html').animate({scrollTop:0},'fast')"><img src="/images/legacy/common/quick-top.jpg" alt="top" /></a>
        </div>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Config
    window.$set_right_recent = 2; // Items per page for Recent
    
    // Initialize
    setRightMenu();

    // Scroll Logic (Sticky)
    $("#rightScrollLayer").each(function(){
        var scrollLayerTopMargin = 10;
        var scrollLayerObj = $(this);
        var defaultScrollLayerTop = parseInt(scrollLayerObj.offset().top);
        var defaultMarginLeft = parseInt($(this).css('margin-left'));
        
        // Placeholder to prevent layout jump
        var divisionHiddenLayer = $("<div></div>").css({
            'position':'absolute',
            'width':$(this).width(),
            'height':$(this).height(),
            'z-index':1,
            'display':'none' // Initially hidden
        });

        $(window).scroll(function(){
            var scrollTop = parseInt($(document).scrollTop());
            
            // If scrolled past original top position
            if(scrollTop > defaultScrollLayerTop - scrollLayerTopMargin)
            {
                var scrollLeft = parseInt($(document).scrollLeft());
                // Stick to screen
                // Note: The logic here is simplified from legacy. 
                // Legacy calculated margin-left dynamically based on scrollLeft to handle horizontal scroll on fixed elements.
                // For now, simpler fixed positioning:
                
                scrollLayerObj.after(divisionHiddenLayer.show()); // Show placeholder
                scrollLayerObj.css({
                    'position' : 'fixed',
                    'z-index' : 90,
                    'top' : scrollLayerTopMargin + 'px',
                    'left' : '50%',
                    'margin-left' : defaultMarginLeft + 'px' // Maintain original offset from center
                });
            }
            else
            {
                // Restore absolute position
                scrollLayerObj.css({
                    'position' : 'absolute',
                    'top' : '180px', // Original top
                    'left' : '50%',
                    'margin-left' : defaultMarginLeft + 'px'
                });
                divisionHiddenLayer.hide();
            }
        });
    });

    // Event Bindings
    $(".rightTitleMenu").on("click", function(){
        if ($(this).next(".right_itemList").css("display")=="none") {
            $(".right_itemList").hide();
            //setRightClickBtn($(this).parent().attr('class'),1); // If we had multiple collapsible menus
        } else {
            // $(this).next(".right_itemList").hide();
        }
    });

    $(".right_quick_btn_prev").on("click", function(){
        var type = $(this).closest('.right_item_recent, .right_item_cart, .right_item_wish').attr('class').split(' ')[0]; // crude class cleaner
        // Actually closest('.right_item_recent') is safer
        type = "right_item_recent"; // Force for now as it's the only paginated one implemented
        
        var current_page = parseInt($("."+type+" #right_page_div .right_quick_current_page").text()) - 1;
        setRightClickBtn(type, current_page, 'move');
        return false;
    });

    $(".right_quick_btn_next").on("click", function(){
        var type = "right_item_recent";
        var current_page = parseInt($("."+type+" #right_page_div .right_quick_current_page").text()) + 1;
        setRightClickBtn(type, current_page, 'move');
        return false;
    });
});

function setRightMenu() {
    // Initial Load
    getRightItemTotal('right_item_cart');
    // getRightItemTotal('right_item_wish'); // Not yet implemented fully
    getRightItemTotal('right_item_recent'); // Get count first
    getRightItemTotal('right_item_recent'); // Get count first, then it triggers list load
}

function setRightClickBtn(type, page, act){
    var limit = (window.$set_right_recent) ? window.$set_right_recent : 2;
    var tot_item = parseInt($("#" + type.replace('item','recent') + "_total").text()); // #right_recent_total
    // Note: ID mapping might need adjustment if other types added. 
    // #right_recent_total for right_item_recent
    
    if (type == 'right_item_recent') tot_item = parseInt($("#right_recent_total").text());
    
    var tot_page = (tot_item) ? Math.ceil(tot_item/limit) : 0;
    
    if (tot_page) {
        if (page > tot_page) page = tot_page;
        else if (page < 1) page = 1;
        
        // If we need to load data
        getRightItemList(type, page, limit);

        $("."+type+" #right_page_div .right_quick_total_page").text(tot_page);
        $("."+type+" #right_page_div .right_quick_current_page").text(page);
        $("."+type+" .right_itemList").show();
    } else {
        // No items
        $("."+type+" .right_itemList ul").html(''); // Clear
        $("."+type+" #right_page_div .right_quick_total_page").text(1);
        $("."+type+" #right_page_div .right_quick_current_page").text(1);
        //$("."+type+" .right_itemList").hide(); // Keep container?
    }
}

function getRightItemList(type, page, limit) {
    $.ajax({
        'async' : true,
        'url' : '/common/get_right_display',
        'type' : 'GET',
        'data' : { type: type, page: page, limit: limit },
        'success' : function(html){
            if (html) {
                $("."+type+" .right_itemList ul").html(html);
                setRightMenuCss(); // Re-bind hover events
            } else {
                 $("."+type+" .right_itemList ul").html('');
            }
        }
    });
}

function getRightItemTotal(type) {
    var objCnt = null;
    if (type=="right_item_cart") objCnt = $("#right_cart_total");
    else if (type=="right_item_recent") objCnt = $("#right_recent_total");
    
    if(!objCnt) return;

    $.ajax({
        'async' : true,
        'url' : '/common/get_right_total',
        'type' : 'GET',
        'data' : { type: type },
        'success' : function(total){
            objCnt.text(total);
            // If recent, trigger list load now that we have the count
             if (type=="right_item_recent") {
                 setRightClickBtn("right_item_recent", 1);
             }
        }
    });
}

function rightDeleteItem(type, seq, obj) {
    $.ajax({
        'async' : true,
        'url' : '/goods/goods_recent_del', // POST route
        'type' : 'POST',
        'data' : { 
            goods_seq: seq,
            _token: '{{ csrf_token() }}'
        },
        'dataType': 'json',
        'success' : function(res){
            if (res.msg == "ok") {
                // Update total
                $("#right_recent_total").text(res.totalcnt);
                // Reload current page (or adjust if empty)
                var current_page = parseInt($("."+type+" #right_page_div .right_quick_current_page").text());
                setRightClickBtn(type, current_page, 'del');
            }
        }
    });
}

/* Hover Effects */
function setRightMenuCss(){
    // Unbind first to avoid duplicates if called multiple times
    $('.right_quick_goods_box').off('mouseenter mouseleave');

    $('.right_quick_goods_box').on("mouseenter", function() {
        var objGood = $(this).find('.right_quick_goods img');
        var objDel = $(this).find('.right_quick_btn_delete');
        var objDetail = $(this).find('.rightQuickitemDetail');

        objGood.css("border","2px solid #333");
        objDetail.css("visibility","visible");
        objDel.css("visibility","visible");
        
        // Dynamic Positioning (if needed, CSS abs position usually enough)
        // Legacy had setRightCommonCSS logic to ensure it pops to the left nicely
    }).on("mouseleave", function() {
        var objGood = $(this).find('.right_quick_goods img');
        var objDel = $(this).find('.right_quick_btn_delete');
        var objDetail = $(this).find('.rightQuickitemDetail');

        objGood.css("border","2px solid #fff");
        objDetail.css("visibility","hidden");
        objDel.css("visibility","hidden");
    });
}
</script>
