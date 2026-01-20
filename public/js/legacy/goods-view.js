var gl_option_select_ver	= '';

$(document).ready(function(){

	// option js 동적 로드
	if	(!gl_option_select_ver){
		var dynamic_js	= document.createElement('script');
		dynamic_js.src	= '/app/javascript/js/goods-option.js';
		document.getElementsByTagName('head')[0].appendChild(dynamic_js);
	}
	
	// 가격대체문구 상품 필수/추가옵션 비활성화 처리 leewh 2015-01-15
	if(typeof gl_string_price_use != 'undefined' && gl_string_price_use){
		try{
			if(typeof gl_string_price_use != 'undefined' && gl_string_price_use){
				$("select").attr("disabled",true).selectbox("disable");
				return false;
			}
		}catch(e){};
	}
	
	// 쿠폰 관련 내용 임시제거 - 속도개선을 위해서 yongedit 230524
	/* 
	$('#couponDownload, #mbox, .couponDownload').bind("click",function() {
		var memberSeq = gl_member_seq;

		if( !memberSeq ){
			location.href="/member/login?return_url="+gl_request_uri;
			return false;
		}
		coupondownlist(gl_goods_seq,gl_request_uri);
	});

	$("button[name='couponDownloadButton']").die().live("click",function(){
		var url = '../coupon/download?goods='+$(this).attr('goods')+'&coupon='+$(this).attr('coupon');
		actionFrame.location.href = url;
	});
	
	if( $(".couponDownloadlay").html() ){
		
		$.getJSON('../coupon/goods_coupon_max?no='+gl_goods_seq, function(data) {
			if(data){
				$(".couponDownloadlay").show();

				var couponDetailpricelayhtml = '<div>';
				var couponDetailhelphtml = '<ul>';
				couponDetailhelphtml +=  "<li>- <b>" + data.download_enddatetitle + "</b> </li>";
				couponDetailhelphtml +=  "<li>- <b>" + data.issue_enddatetitle + "</b> </li>";
				if(data.sale_type == 'won'){
					var realprice = comma(data.won_goods_sale);

					couponDetailhelphtml +=  "<li>- <b>"+ realprice +"원 할인 </b></li>";
					$(".cb_percent").html( realprice + '원' );
					for (var i = 0; i < realprice.length ; i++) {
						var number = (realprice.substring(i,i+1));
						if(number == ',') {
							couponDetailpricelayhtml += "<img src='/data/coupon/coupon_i_comma.png' >";
						}else{
							couponDetailpricelayhtml += "<img src='/data/coupon/coupon_i_no"+number+".png' >";
						}
					}
					couponDetailpricelayhtml +=  "<img src='/data/coupon/coupon_i_won.png' >";
				}else{
					var realpercent = (data.percent_goods_sale);

					couponDetailhelphtml +=  "<li>- <b>" + realpercent + "% 할인 (최대 " + comma(data.max_percent_goods_sale) + "원)</b></li>";
					$(".cb_percent").html( realpercent + '%' );
					for (var i = 0; i < realpercent.length ; i++) {
						var number = parseInt(realpercent.substring(i,i+1));
						if(number >= 0 ) couponDetailpricelayhtml += "<img src='/data/coupon/coupon_i_no"+number+".png' >";
					}
					couponDetailpricelayhtml +=  "<img src='/data/coupon/coupon_i_per.png' >";
					couponDetailpricelayhtml +=  "<img src='/data/coupon/coupon_i_dc.png' >";
				}
				couponDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
				if(data.categoryhtml) {
					if(data.issue_type == 'except'){
						couponDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 불가</li>";
					}else if(data.issue_type == 'issue'){
						couponDetailhelphtml +=  "<li>- "+ data.categoryhtml +" 카테고리 사용 가능</li>";
					}else{
						couponDetailhelphtml +=  "<li>- 전체 상품 사용 가능</li>";
					}
				}

				//동시사용불가
				if( data.coupon_same_time  == 'N' ){
					couponDetailhelphtml +=  "<li>- 단독 쿠폰( 타 쿠폰과 함께 사용 불가)</li>";
				}

				//중복사용여부
				if( data.duplication_use  == '1' ){
					couponDetailhelphtml +=  "<li>- 중복 다운로드 가능(사용 후 다시 다운로드 가능)</li>";
				}

				couponDetailhelphtml +=  "</ul>";
				couponDetailpricelayhtml +=  "</div>";

				if( data.coupon_img == 4 ) {
					$(".lt").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');
					$(".rt").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');
					$(".lb").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');
					$(".rb").css('background', 'url(/data/coupon/' + data.coupon_image4 + ')');

					$("#couponDetailhelplay").html(couponDetailhelphtml);//우측 기본정보
				}else{
					$(".lt").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$(".rt").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$(".lb").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$(".rb").css('background', 'url(/data/coupon/coupon' + data.couponsametimeimg + '_skin_0' + data.coupon_img + '.gif)');
					$("#couponDetailhelplay").html(couponDetailhelphtml);//우측 기본정보
					$("#couponDetailpricelay").html(couponDetailpricelayhtml);//좌측 쿠폰이미지
				}
				$("#couponDetaillay").html(data.couponDetaillay);//
			}else{
				$(".couponDownloadlay").hide();
			}
		});
	} 

	$.getJSON('/promotion/goods_coupon_max?no='+gl_goods_seq+'&price='+gl_goods_price, function(data) {
		if(data){
			if(data.codenumber) $(".promotion_code_area").show();
			$(".promotion_code_area span.cb_codepercent").html(data.benifit);
			$(".promotion_code_area span.cb_codenumber").html(data.codenumber);
		}
	});
	*/

	$("#buy,#buy2").bind("click",function(){

		var memberSeq = gl_member_seq;
		var providerSeq = gl_provider_seq;
		var b_ck = 0;
		
		var b_num	= $('input[name="bundle_unit"]').val();
		var boxEa = $('#buy_num').val();
		if(b_num > 0){
			if( boxEa % b_num != 0 ){
				alert(b_num+'개 단위로 구매가능');
				return false;
			}
		}
		

		if( check_option() ){
			
			// 상품투자 상품 유무에 따라 경고창 발생
			if( memberSeq && providerSeq){
				$.ajax({
					'url' : '/goods/provider_cart_ck',
					'type' : 'post',
					'async': false,
					'data' : {'memberSeq':memberSeq},
					'success' : function(res){
						console.log(res);
						if(parseInt(res) > 0) {
							if(confirm('상품투자 상품이 장바구니에 있습니다.\n 바로구매 시 장바구니 상품이 초기화 됩니다.')){ 

							} else {
								b_ck = 1;							
							}
						} 
					}
				});	 

			}
			
			if(b_ck == 0){
				var f = $("form[name='goodsForm']");
				f.attr("action","../order/add?mode=direct");
				f.submit();
				f.attr("action","../order/add");
			} else {
				return false;
			}
		}


	});

	// 모바일2에서 사용 2014-01-13 lwh
	$('#addCart_option').bind("click",function(){
		if	($(this).hasClass('isopen')){
			var f = $("form[name='goodsForm']");
			if( check_option() ) f.submit();
		}else{
			showGoodsOptionLayer();
		}
	});

	$("#addCart,#addCart2,#addCartATS").bind("click",function(){

		var b_num	= $('input[name="bundle_unit"]').val();
		var boxEa = $('#buy_num').val();
		if(b_num > 0){
			if( boxEa % b_num != 0 ){
				alert(b_num+'개 단위로 구매가능');
				return false;
			}
		}

		var f = $("form[name='goodsForm']");
		if( check_option() ) f.submit();
	});

	$("#price_area").bind("mouseover",function(){
		$(this).closest("td").find("div").removeClass("hide");
		$(".goods_spec_table").find(".fb-like").css('z-index','1');
		$(".sale_price_layer").parent().css('z-index','2');
	}).bind("mouseout",function(){
		$(this).closest("td").find("div").addClass("hide");
		$(".goods_spec_table").find(".fb-like").css('z-index','100');
	});

	$(".gift_goods").bind("mouseover",function(){
		$(this).closest("td").find("div").removeClass("hide");
	}).bind("mouseout",function(){
		$(this).closest("td").find("div").addClass("hide");
	});

	$('#nointerest').toggle(function() {
		$(this).closest("td").find("div").removeClass("hide");
	}, function() {
		$(this).closest("td").find("div").addClass("hide");
	});

	$("#nointerest_event").bind("click",function(){
		$(this).closest("div").hide();
	});

	if(typeof gl_goods_seq!="undefined" && gl_goods_seq){
		setFacebooklikeopsave(gl_goods_seq);
	}
		

});

function getCouponDownlayerclose(){
	$('#couponDownloadDialog').dialog('close');
}


//좋아요박스 op 자체작업
function setFacebooklikeopsave(no){
	var datahost = $(".fblikeopengrapybtn").attr("data-host");
	
	if(typeof datahost!="undefined" && datahost){
		var url = (document.location.protocol == "https:")?'https://'+datahost:'http://'+datahost;
		url = url+'/snsredirect/setFacebooklikeopsave?no='+no;
		actionFrame.location.href = url;
	}
}

function goods_view_wish(goods_seq)
{
	$.ajax({
		'url' : '/mypage/wish_add_ajax_toggle',
		'data' : {'goods_seq':goods_seq},
		'dataType' : 'json',
		'global' : false,
		'success' : function(res){
			if(res.result == 'not_login'){
				parent.openDialogConfirm('회원만 사용가능합니다. 로그인하시겠습니까?',400,180,function(){
				parent.location.replace(res.url);
				},function(){});
			} else if(res.result == 'add'){				
				$(".icon_wish.on").show();
				$(".icon_wish.off").hide();
				alert('위시리스트에 추가되었습니다.');
			} else if(res.result == 'del'){
				$(".icon_wish.on").show();
				$(".icon_wish.on").hide();
				$(".icon_wish.off").show();
				alert('위시리스트에서 제거되었습니다.');				
			}
		}						
	});	
	
}

function add_provider_goods(goods_seq)
{
	$("input[name='provider_goods']").val(goods_seq);
}