/*결제사 외부 스크립트 호출 (절대 수정하지 마시오)*/
$.getScript('https://stdpay.inicis.com/stdjs/INIStdPay.js');	
$.getScript('https://xpay.uplus.co.kr/xpay/js/xpay_crossplatform.js');	
/*결제사 외부 스크립트 호출 (절대 수정하지 마시오)*/


function check_shipping_method(){
	var idx = $("select[name='international'] option:selected").val();
	$("div.shipping_method_radio").each(function(){
		$(this).hide();
	});
	if(!idx)idx = 0;
	$("div.shipping_method_radio").eq(idx).show();

	if(idx == 0){
		$(".domestic").show();
		$(".international").hide();
	}else{
		$(".international").show();
		$(".domestic").hide();
	}
}

function order_price_calculate(){
	var f = $("form#orderFrm");
	action = "/order/calculate?mode="+gl_mode;
	// ssl 적용
	$.ajax({
		async: false,
		'url'		: '/common/ssl_action',
		'data'		: {'action':action},
		'type'		: 'get',
		'dataType'	: 'html',
		'success'	: function(res) {
		action = res;
		}
	});
	f.attr("action",action);
	f.attr("target","actionFrame");
	// jCryption 재적용 스킨의 orderFrm 에 ssl 링크가 없기에 js 영역에서 재선언
	moduleJcryption.resetJcryptionSubmit(f[0]);
	f.submit();
}

function set_pay_button(){
	$.ajax({
		'url' : '../order/settle_order_images',
		'dataType': 'json',
		'cache': false,
		'success': function(data) {
			
			if($("#pay>img").attr("src")=='/data/skin/'+gl_skin+'/images/buttons/btn_pay.gif' || $("#pay>img").attr("src")=='/data/skin/'+gl_skin+'/images/buttons/btn_order.gif'){
				var btn_order_pay1 = '/data/skin/'+gl_skin+'/images/buttons/btn_pay.gif';
				var btn_order_pay2 = '/data/skin/'+gl_skin+'/images/buttons/btn_order.gif';
			}else{
				var btn_order_pay1 = '/data/skin/'+gl_skin+'/images/buttons/btn_order_pay.gif';
				var btn_order_pay2 = '/data/skin/'+gl_skin+'/images/buttons/btn_order.gif';
		
				if(data.btn_order_pay1) btn_order_pay1 = data.btn_order_pay1;
				if(data.btn_order_pay2) btn_order_pay2 = data.btn_order_pay2;
			}

			$("#pay").html("<img src='"+btn_order_pay1+"' />");
			$("input[name='payment']:checked").each(function(){
				if( $(this).val() == "bank" ){
					$("#pay").html("<img src='"+btn_order_pay2+"' />");
				}else{
					//쿠폰의 무통장쿠폰인 경우 점검 
					if( eval('$("#coupon_sale_payment_b")') ){  
						var coupon_sale_payment_b = $("#coupon_sale_payment_b").val();
						if(coupon_sale_payment_b>0){ 
							openDialogAlert('현재 무통장 전용 쿠폰을 사용하셨습니다.<br />결제수단을 무통장으로 변경해 주세요!',400,150);
							return false;
						}
					}
				}

				if( gl_cashreceiptuse > 0 || gl_taxuse >0 ){
					$("#typereceiptcardlay").hide();
					$("#typereceipttablelay").show();
					
					if( $(this).val() == "card" || $(this).val() == "naverpay" || $(this).val() == "kaopay" || $(this).val() == "tosspay"){
						$("#card_var_div").show();

					} else {
						$("#card_var_div").hide();
					}

					if( $(this).val() == "card" || $(this).val() == "naverpay" || $(this).val() == "kaopay" || $(this).val() == "tosspay" ||  $(this).val() == "cellphone" || $(this).val() == "kakaopay"  || $(this).val() == "kakaopay" ||  $(this).val() == "kcp_npay_point" ||  $(this).val() == "kcp_npay_card"  ||  $(this).val() == "kcp_kakaopay" ||  $(this).val() == "kcp_payco" ||  $(this).val() == "kcp_sampay" ||  $(this).val() == "kcp_ssgpay" ){ 
						$("#typereceiptcardlay").show();
						$("#typereceipttablelay").hide();
						$(".typereceiptlay").hide();
						$("#typereceiptlay").hide();
						$("#cash_container").hide();
						$("input:radio[name='typereceipt']:radio[value='0']").attr("checked",true);
						
					}else{
						var b2b = $("#gubun_b2b").val();
						$("#typereceiptlay").show();
						if(b2b == 'Y') $(".typereceiptlay").show();
						//$("#tax_container").hide();
						
					}
				}
			});
			order_price_calculate(); // 결제수단 변경 후 다시 결제금액 계산
		}
	});
	
}

function reverse_pay_button(){
	$("div.pay_layer").eq(0).show();
	$("div.pay_layer").eq(1).hide();
}


/**
 * 매출증빙폼노출
 */
function check_typereceipt()
{
	var obj =  $("input[name='typereceipt']:checked");

	$('#cash_container').hide();
	$('#tax_container').hide();

	if(obj.val() == 0) {
		taxRemoveClass();
		cashRemoveClass();
	}
	// 세금계산서 신청일 경우
	else if(obj.val() == 1) {
		$('#tax_container').show();

		$('#co_name').attr('title', ' ').addClass('required');
		$('#co_ceo').attr('title', ' ').addClass('required');
		$('#busi_no').attr('title', ' ').addClass('required').addClass('busiNo');
		$('#co_zipcode').attr('title', ' ').addClass('required');
		$('#co_address').attr('title', ' ').addClass('required');
		$('#co_status').attr('title', ' ').addClass('required');
		$('#co_type').attr('title', ' ').addClass('required');

		cashRemoveClass();
	}
	// 현금영수증 신청일 경우
	else if(obj.val() == 2) {
		$('#cash_container').show();
		$('#creceipt_number').attr('title', ' ').addClass('required').addClass('numberHyphen');
		taxRemoveClass();
	}

	/*
	if( $("input[name='payment']:checked").val() == 'bank'){
		$("#duplicate_message").hide();
	}else{
		$("#duplicate_message").show();
	}
	*/
}

/**
 * 세금계산서 폼체크를 삭제한다.
 */
function taxRemoveClass() {
	$('#co_name').removeClass('required');
	$('#co_ceo').removeClass('required');
	$('#busi_no').removeClass('required');
	$('#co_zipcode').removeClass('required');
	$('#co_address').removeClass('required');
	$('#co_status').removeClass('required');
	$('#co_type').removeClass('required');
}

/**
 * 현금영수증 폼체크를 삭제한다.
 */
function cashRemoveClass() {
	$('#creceipt_number').removeClass('required');
}


//쿠폰적용하시기 단독쿠폰체크
function sametime_coupon_dialog(){
	getCouponAjaxList();
}


function getPromotionckloding(cartpromotioncode) {
	if( cartpromotioncode ) {
		$.ajax({
			'url' : '../promotion/getPromotionJson?mode='+gl_mode,
			'data' : {'cartpromotioncode':cartpromotioncode},
			'type' : 'post',
			'dataType': 'json',
			'cache': false,
			'success': function(data) {
				order_price_calculate();
			}
		});
	}
}

function getPromotionck(){
	var cartpromotioncode = $("#cartpromotioncode").val();
	if(!cartpromotioncode){
		openDialogAlert('할인코드를 정확히 입력해 주세요.','400','140');
		return false;
	}

	var mode	= $("input[name='mode']").val();

	$.ajax({
		'url' : '../promotion/getPromotionJson?mode='+mode,
		'data' : {'cartpromotioncode':cartpromotioncode},
		'type' : 'post',
		'dataType': 'json',
		'cache': false,
		'success': function(data) {

			if(data.result == false ){
				openDialogAlert(data.msg,'400','140',function(){getPromotionCartDel();});
				return false;
			}

			var promotionDetailhelphtml = '<div class="promotionlay" align="left" ><ul >';

			if( data.result == false ) {
				promotionDetailhelphtml +=  "<li class='red'>코드할인이 적용되지 않았습니다.</li>";
			}

			promotionDetailhelphtml +=  "<li><b>코드내용</b> :  " + data.promotion_desc + "(" + data.promotion_name + ") </li>";
			promotionDetailhelphtml +=  "<li><b>사용기간</b> :  " + data.issue_enddatetitle + " </li>";
			promotionDetailhelphtml +=  "<li> <b>할인내용</b> </li>";
			if(data.sale_type == 'shipping_free'){//기본배송비 무료
				promotionDetailhelphtml +=  "<li>- <b>기본배송비 무료</b></li>";// (최대 " + comma(data.max_percent_shipping_sale) + "원)
				promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
			}else if(data.sale_type == 'shipping_won'){//**원배송비 할인
				var realprice = comma(data.won_shipping_sale);

				promotionDetailhelphtml +=  "<li>- <b>기본배송비 "+ realprice +"원 할인 </b></li>";
				promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
			}else if(data.sale_type == 'won'){//**원 주문상품할인
				var realprice = comma(data.won_goods_sale);

				promotionDetailhelphtml +=  "<li>- <b>"+ realprice +"원 할인 </b></li>";
				promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
				if(data.issue_type == 'all') {
						promotionDetailhelphtml +=  "<li>- 전체 사용 가능</li>";
				}else{
					if(data.goodshtml) {
						if(data.issue_type == 'except'){
							promotionDetailhelphtml +=  "<li><b>상품 사용 불가</b></li>";
						}else if(data.issue_type == 'issue'){
							promotionDetailhelphtml +=  "<li><b>상품 사용 가능</b></li>";
						}

						var sArr = data.goodshtml.split(',');
						var cArr = data.goodshtmlcode.split(',');
						promotionDetailhelphtml += '<li><div style="border-left:1px #ececec;background-color:#f2f2f2;border-top:2px #eaeaea;padding:5px; width:100%; height:50px; border:0px;overflow:auto" class="" readonly>';
						for(var ii = 0;ii<sArr.length;ii++){
							promotionDetailhelphtml += "- <a href='../goods/view?no="+cArr[ii]+"' target='_blank' >"+sArr[ii]+"</a><br />";
							//promotionDetailhelphtml += "-"+sArr[ii]+"<br />";
						}
						promotionDetailhelphtml += "</div></li>";
					}

					if(data.brandhtml) {
						if(data.issue_type == 'except'){
							promotionDetailhelphtml +=  "<li><b>브랜드 사용 불가</b></li>";
						}else if(data.issue_type == 'issue'){
							promotionDetailhelphtml +=  "<li><b>브랜드 사용 가능</b></li>";
						}
						var sArr = data.brandhtml.split(',');
						var cArr = data.brandhtmlcode.split(',');
						promotionDetailhelphtml += '<li ><div style="border-left:1px #ececec;background-color:#f2f2f2;border-top:2px #eaeaea;padding:5px; width:100%; height:50px; border:0px;overflow:auto" class="" readonly>';
						for(var ii = 0;ii<sArr.length;ii++){
							promotionDetailhelphtml += "- <a href='../goods/brand?code="+cArr[ii]+"' target='_blank' >"+sArr[ii]+"</a><br />";
							//promotionDetailhelphtml += "-  "+sArr[ii]+"<br />";
						}
						promotionDetailhelphtml += "</div></li>";
					}

					if(data.categoryhtml) {
						if(data.issue_type == 'except'){
							promotionDetailhelphtml +=  "<li><strong>카테고리 사용 불가</strong> </li>";
						}else if(data.issue_type == 'issue'){
							promotionDetailhelphtml +=  "<li><strong>카테고리 사용 가능</strong></li>";
						}
						var sArr = data.categoryhtml.split(',');
						var cArr = data.categoryhtmlcode.split(',');
						promotionDetailhelphtml += '<li><div style="border-left:1px #ececec;background-color:#f2f2f2;border-top:2px #eaeaea;padding:5px; width:100%; height:50px; border:0px;overflow:auto" class="" readonly>';
						for(var ii = 0;ii<sArr.length;ii++){
							promotionDetailhelphtml += "- <a href='../goods/catalog?code="+cArr[ii]+"' target='_blank' >"+sArr[ii]+"</a><br />";
						}
						promotionDetailhelphtml += "</div></li>";
					}
				}
			}else{//**%할인(최대할인금액제한)
				var realpercent = (data.percent_goods_sale);

				promotionDetailhelphtml +=  "<li>- <b>" + realpercent + "% 할인</b></li>";// (최대 " + comma(data.max_percent_goods_sale) + "원)
				promotionDetailhelphtml +=  "<li>- "+ comma(data.limit_goods_price) +"원 이상 구매 시</li>";
				if(data.issue_type == 'all') {
						promotionDetailhelphtml +=  "<li>- 전체 사용 가능</li>";
				}else{
					if(data.goodshtml) {
						if(data.issue_type == 'except'){
							promotionDetailhelphtml +=  "<li><strong>상품 사용 불가</strong></li>";
						}else if(data.issue_type == 'issue'){
							promotionDetailhelphtml +=  "<li><strong>상품 사용 가능</strong></li>";
						}

						var sArr = data.goodshtml.split(',');
						var cArr = data.goodshtmlcode.split(',');
						promotionDetailhelphtml += '<li><div style="border-left:1px #ececec;background-color:#f2f2f2;border-top:2px #eaeaea;padding:5px; width:100%; height:50px; border:0px;overflow:auto" class="" readonly>';
						for(var ii = 0;ii<sArr.length;ii++){
							promotionDetailhelphtml += "- <a href='../goods/view?no="+cArr[ii]+"' target='_blank' >"+sArr[ii]+"</a><br />";
							//promotionDetailhelphtml += "-"+sArr[ii]+"<br />";
						}
						promotionDetailhelphtml += "</div></li>";
					}

					if(data.brandhtml) {
						if(data.issue_type == 'except'){
							promotionDetailhelphtml +=  "<li><strong>브랜드 사용 불가</strong></li>";
						}else if(data.issue_type == 'issue'){
							promotionDetailhelphtml +=  "<li><strong>브랜드 사용 가능</strong></li>";
						}

						var sArr = data.brandhtml.split(',');
						var cArr = data.brandhtmlcode.split(',');
						promotionDetailhelphtml += '<li ><div style="border-left:1px #ececec;background-color:#f2f2f2;border-top:2px #eaeaea;padding:5px; width:100%; height:50px; border:0px;overflow:auto" class="" readonly>';
						for(var ii = 0;ii<sArr.length;ii++){
							promotionDetailhelphtml += "- <a href='../goods/brand?code="+cArr[ii]+"' target='_blank' >"+sArr[ii]+"</a><br />";
							//promotionDetailhelphtml += "-  "+sArr[ii]+"<br />";
						}
						promotionDetailhelphtml += "</div></li>";

					}///goods/brand?code=

					if(data.categoryhtml) {
						if(data.issue_type == 'except'){
							promotionDetailhelphtml +=  "<li><strong>카테고리 사용 불가</strong></li>";
						}else if(data.issue_type == 'issue'){
							promotionDetailhelphtml +=  "<li><strong>카테고리 사용 가능</strong></li>";
						}

						var sArr = data.categoryhtml.split(',');
						var cArr = data.categoryhtmlcode.split(',');
						promotionDetailhelphtml += '<li><div style="border-left:1px #ececec;background-color:#f2f2f2;border-top:2px #eaeaea;padding:5px; width:100%; height:50px; border:0px;overflow:auto" class="" readonly>';
						for(var ii = 0;ii<sArr.length;ii++){
							promotionDetailhelphtml += "- <a href='../goods/catalog?code="+cArr[ii]+"' target='_blank' >"+sArr[ii]+"</a><br />";
						}
						promotionDetailhelphtml += "</div></li>";
					}
				}
			}

			promotionDetailhelphtml +=  "</ul></div>";
			var promotionwidth = ($("div#promotionalertDialog").width()>300)?$("div#promotionalertDialog").width()+100:400;
			var promotionheight = ($("div#promotionalertDialog").height()>100)?$("div#promotionalertDialog").height()+300:400;
			if(data.result){
				var title = '코드할인<span class="desc" >코드할인이 적용되었습니다.</span>';
			}else{
				var title = '코드할인<span class="desc" >코드할인이 적용되지 않았습니다.</span>';
			}
			if( data.result == false ) {
				openDialogAlerttitle(title,promotionDetailhelphtml,promotionwidth,promotionheight,function(){});
			}else{
				openDialogAlerttitle(title,promotionDetailhelphtml,promotionwidth,promotionheight,function(){$(".cartPromotionTh").show();$(".cartPromotionTd").show();$("#pricePromotionTd").show();$(".cartpromotioncodedellay").show();$(".cartpromotioncodeinputlay").hide();});
				$(".cartPromotionTh").show();$(".cartPromotionTd").show();$("#pricePromotionTd").show();$(".cartpromotioncodedellay").show();$(".cartpromotioncodeinputlay").hide();
			}
			order_price_calculate();
		}
	});
}



/* 프로모션코드 초기화하기 */
function getPromotionCartDel(){
	$.ajax({
		'url' : '/promotion/getPromotionCartDel',
		'cache': false,
		'success' : function(){
			$(".cartPromotionTh").hide();
			$(".cartPromotionTd").hide();
			$("#pricePromotionTd").hide();
			$(".cartpromotioncodedellay").hide();
			$(".cartpromotioncodeinputlay").show();
			order_price_calculate();
			openDialogAlert('미적용 처리되었습니다.', 400, 150, function(){});
		}
	});
}

// 단독쿠폰 선택 여부
function chkCouponSameTimeUse(){
	var chkCouponSameTimeUse	= false;
	$(".coupon_select").each(function(){
		if( $(this).find("option:selected").attr('couponsametime') == 'N' )
			chkCouponSameTimeUse	= true;
	});
	$(".shipping_coupon_select").each(function(){
		if( $(this).find("option:selected").attr('couponsametime') == 'N' )
			chkCouponSameTimeUse	= true;
	});

	if	(chkCouponSameTimeUse)	$.cookie( "couponsametimeuse", true );
	else						$.cookie( "couponsametimeuse", null );

	return chkCouponSameTimeUse;
}


//상품쿠폰선택
function getCouponselectnew(e){
	var obj = $(e);
	if( obj.find("option:selected").attr("value") ) {
		var oldidx = obj.parent().next().find("span").attr("oldidx");
		var oldsale = obj.parent().next().find("span").attr("oldsale");
		if( obj.find("option:selected").attr('couponsametime') == 'N' ) {//단독쿠폰
			if( $.cookie( "couponsametimeuse") ) {
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
			}else{
				var msg = "단독으로만 적용할 수 있는 쿠폰을 선택하셨습니다.<br/>기존에 적용된 쿠폰은 모두 해제됩니다. 적용하시겠습니까?";
			}
			openDialogConfirm(msg,400,150,function(){
				getCouponsametimeselect(obj,'goods');//선택해제
				getCouponselectreal(obj);//단독쿠폰 중복쿠폰여부
				$.cookie( "couponsametimeuse", true );
			},function(){
				if(oldidx){
					obj.find("option").eq(oldidx).attr("selected",true);
					obj.parents('tr').find("span.sale").html( comma( oldsale ) );
				}else{
					obj.val("").prop("selected", true); //IE7
					obj.find("option:selected").attr("selected",false);
					obj.parents('tr').find("span.sale").html( comma( 0 ) );
				}
				return false;});
		}else{//단독쿠폰아닌경우
			if( $.cookie( "couponsametimeuse") ) {//이전에 단독쿠폰 선택된 경우
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
				openDialogConfirm(msg,400,150,function() {
					getCouponsametimeselect(obj,'goods');//선택해제
					getCouponselectreal(obj);
					$.cookie( "couponsametimeuse", null );
				},function(){
				if(oldidx){
					obj.find("option").eq(oldidx).attr("selected",true);
					obj.parents('tr').find("span.sale").html( comma( oldsale ) );
				}else{
					obj.val("").prop("selected", true); //IE7
					obj.find("option:selected").attr("selected",false);
					obj.parents('tr').find("span.sale").html( comma( 0 ) );
				}
					return false;});
			}else{
				getCouponselectreal(obj);
			}
		}
	}else{
		obj.val("").prop("selected", true); //IE7
		obj.find("option").attr("selected",false); //선택제외
		obj.parents('tr').find("span.sale").html( comma( 0 ) );
		obj.parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",'');
		if( $.cookie( "couponsametimeuse") ) {
			$.cookie( "couponsametimeuse", null );
		}
	}
}

//배송비쿠폰선택
function getShippingCouponselectnew(e) {
	var obj = $(e); 
	if( obj.find("option:selected").attr("value") ) {
		var oldidx = obj.find("option").attr("oldidx");
		var oldsale = obj.find("option").attr("oldsale");
		if(!oldidx) {
			//obj.find("option").attr("oldidx", obj.find("option:selected").index() );
			//obj.find("option").attr("oldsale",obj.find("option:selected").attr("sale"));
		}

		if( obj.find("option:selected").attr('couponsametime') == 'N' ) {//단독쿠폰
			if( $.cookie( "couponsametimeuse") ) {
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
			}else{
				var msg = "단독으로만 적용할 수 있는 쿠폰을 선택하셨습니다.<br/>기존에 적용된 쿠폰은 모두 해제됩니다. 적용하시겠습니까?";
			}
			openDialogConfirm(msg,400,150,function(){
				getCouponshsametimeselect(obj,'');//선택해제
				getCouponshselectreal(obj);//단독쿠폰 중복쿠폰여부
				$.cookie( "couponsametimeuse", true );
				return true;
			},function(){
				if(oldidx){
					obj.find("option").eq(oldidx).attr("selected",true);
					obj.parents('tr').find("span.shipping_sale").html( comma( oldsale ) );
				}else{
					obj.val("").prop("selected", true); //IE7
					obj.find("option:selected").attr("selected",false);
					obj.parents('tr').find("span.shipping_sale").html( comma( 0 ) );
				}
				return false;});
		}else{//단독쿠폰아닌경우
			if( $.cookie( "couponsametimeuse") ) {//이전에 단독쿠폰 선택된 경우
				var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
				openDialogConfirm(msg,400,150,function() {
					getCouponshsametimeselect(obj,'');//선택해제
					getCouponshselectreal(obj);
					$.cookie( "couponsametimeuse", null );
					return true;
				},function(){
					if(oldidx){
						obj.find("option").eq(oldidx).attr("selected",true);
						obj.parents('tr').find("span.shipping_sale").html( comma( oldsale ) );
					}else{
						obj.val("").prop("selected", true); //IE7
						obj.find("option:selected").attr("selected",false);
						obj.parents('tr').find("span.shipping_sale").html( comma( 0 ) );
					}
					return false;
				});
			}else{
				getCouponshselectreal(obj);
			}
		}
	}else{
		obj.val("").prop("selected", true); //IE7
		obj.find("option").attr("selected",false); //선택제외
		obj.parents('tr').find("span.shipping_sale").html( comma( 0 ) );
		if( $.cookie( "couponsametimeuse") ) {
			$.cookie( "couponsametimeuse", null );
		}
	}
	//쿠폰정보정의
	var download_seq = obj.parents('tr').find("select.shipping_coupon_select option:selected").val();
	if(download_seq) { 
		var shipping_sale = obj.parents('tr').find("select.shipping_coupon_select option:selected").attr("sale");
		obj.parents('tr').find(".shippingcoupongoodsreviewbtn").attr("download_seq",download_seq);
		obj.parents('tr').find(".shipping_sale").html( comma( shipping_sale ) );
	}else{ 
		obj.parents('tr').find(".shippingcoupongoodsreviewbtn").attr("download_seq",'');
		obj.parents('tr').find("span.shipping_sale").html( comma( 0 ) );
	}
}

//단독쿠폰으로 중복이 아닌경우 선택된 정보이외에 모두 제외
function getCouponsametimeselect(obj, coupontype){
	if( coupontype == 'goods') {//상품쿠폰은 배송비쿠폰제외
		$("select.shipping_coupon_select").each(function(){
			//if( !$(this).find("option:selected").val() ) return true; //continue;
			$(this).val("").prop("selected", true); //IE7
			$(this).find("option").attr("selected",false); //선택제외
			$(".shippingcoupongoodsreviewbtn").attr("download_seq",'');
			$(".shipping_coupon_sale").html( comma( 0 ) );
		});
	}
	$("select.coupon_select").each(function(){
		if( !$(this).find("option:selected").val() ) return true; //continue;
		if( obj.attr('id') != $(this).attr("id") ) {
			$(this).val("").prop("selected", true); //IE7
			$(this).find("option").attr("selected",false); //선택제외
			$(this).parents('tr').find("span.sale").html( comma( 0 ) ); 
			$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",'');
		}
	});
}


//단독쿠폰으로 중복이 아닌경우 선택된 정보이외에 모두 제외
function getCouponshsametimeselect(obj, coupontype){
		$("select.shipping_coupon_select").each(function(){
			if( !$(this).find("option:selected").val() ) return true; //continue;
			if( obj.attr('id') != $(this).attr("id") ) {
				$(this).val("").prop("selected", true); //IE7
				$(this).find("option").attr("selected",false); //선택제외
			}
		});

	$("select.coupon_select").each(function(){
		//if( !$(this).find("option:selected").val() ) return true; //continue;
		//if( obj.attr('id') != $(this).attr("id") ) {
			$(this).val("").prop("selected", true); //IE7
			$(this).find("option").attr("selected",false); //선택제외
			$(this).parents('tr').find("span.sale").html( comma( 0 ) );
		//}
	});
}



//쿠폰선택
function getCouponselectreal(obj) {
	$("select.coupon_select").each(function(idx){//
		if( obj.find("option:selected").attr('duplication') == 1 ) {//중복쿠폰
			if( obj.attr('id') != $(this).attr("id") && !$(this).find("option:selected").val() ) {//선택하지 않는 상품인경우
				$("select#"+$(this).attr("id")+" option[value='"+obj.find("option:selected").val()+"']").attr("selected",true);
			}
			if( obj.attr('id') == $(this).attr("id") )  {
				$(this).parents('tr').find("span.sale").attr("oldidx",obj.find("option:selected").index());
				$(this).parents('tr').find("span.sale").attr("oldsale", $(this).find("option:selected").attr("sale"));
			}
				$(this).parents('tr').find("span.sale").html( comma( $(this).find("option:selected").attr("sale") ) );
				$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",$(this).find("option:selected").val());
		}else{

			if( obj.attr('id') != $(this).attr("id") && obj.find("option:selected").attr("value") ){
				if(obj.find("option:selected").val() == $(this).find("option:selected").val()){
					$(this).find("option").eq(0).attr("selected",true);
				}
			}

			if( obj.attr('id') == $(this).attr("id") ) {
				$(this).parents('tr').find("span.sale").attr("oldidx",$(this).find("option:selected").index());
				$(this).parents('tr').find("span.sale").attr("oldsale",$(this).find("option:selected").attr("sale"));
			}
			$(this).parents('tr').find("span.sale").html( comma( $(this).find("option:selected").attr("sale") ) );
			$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",$(this).find("option:selected").val());

		}
	});
}

//배송비쿠폰선택
function getCouponshselectreal(obj) {
	$("select.shipping_coupon_select").each(function(idx){//
		if( obj.find("option:selected").attr('duplication') == 1 ) {//중복쿠폰
			if( obj.attr('id') != $(this).attr("id") && !$(this).find("option:selected").val() ) {//선택하지 않는 상품인경우
				$("select#"+$(this).attr("id")+" option[value='"+obj.find("option:selected").val()+"']").attr("selected",true);
			}

			if( obj.attr('id') == $(this).attr("id") )  {
				$(this).parents('tr').find("span.shipping_sale").attr("oldidx",obj.find("option:selected").index());
				$(this).parents('tr').find("span.shipping_sale").attr("oldsale", $(this).find("option:selected").attr("sale"));
			}
			$(this).parents('tr').find("span.shipping_sale").html( comma( $(this).find("option:selected").attr("sale") ) );
			$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",$(this).find("option:selected").val());
		}else{

			if( obj.attr('id') != $(this).attr("id") && obj.find("option:selected").attr("value") ){
				if(obj.find("option:selected").val() == $(this).find("option:selected").val()){
					$(this).find("option").eq(0).attr("selected",true);
				}
			}

			if( obj.attr('id') == $(this).attr("id") ) {
				$(this).parents('tr').find("span.shipping_sale").attr("oldidx",$(this).find("option:selected").index());
				$(this).parents('tr').find("span.shipping_sale").attr("oldsale",$(this).find("option:selected").attr("sale"));
			}
			$(this).parents('tr').find("span.shipping_sale").html( comma( $(this).find("option:selected").attr("sale") ) );
			$(this).parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",$(this).find("option:selected").val());

		}
	});
}




// 쿠폰 사용 취소
function cancelCouponSelect(obj){
	obj.val("").prop("selected", true); //IE7
	obj.find("option:selected").attr("selected",false);
	obj.parents('tr').find("span.sale").html( comma( 0 ) ); 
	obj.parents('tr').find("button.ordercoupongoodsreviewbtn").attr("download_seq",''); 
	obj.blur();
}

// 배송 쿠폰 사용 취소
function cancelShippingCouponSelect(obj){
	obj.find("option").eq(0).attr("selected",true);
	obj.parents('tr').find("span.shipping_sale").html( comma( 0 ) );
}

// 쿠폰선택
function getCouponselect(e){
	var obj						= $(e);
	// 쿠폰 선택 시
	if( obj.find("option:selected").val() ) {
		// 단독 쿠폰의 경우
		if( obj.find("option:selected").attr('couponsametime') == 'N' ) {
			var msg = "단독으로만 적용할 수 있는 쿠폰을 선택하셨습니다.<br/>기존에 적용된 쿠폰은 모두 해제됩니다. 적용하시겠습니까?";
		}else if( chkCouponSameTimeUse() ){
			var msg = "이전에 적용한 쿠폰은 단독으로만 사용가능한 쿠폰입니다.<br/>본 쿠폰을 사용하시면 이전에 적용된 쿠폰은 모두 해제 됩니다. 적용하시겠습니까?";
		}

		if	(msg)	openDialogConfirm(msg,400,150,function(){useCouponSelect(obj)},function(){cancelCouponSelect(obj)});
		else		useCouponSelect(obj);
	}else{
		obj.parents('tr').find("span.sale").html( comma( 0 ) );
	}
}

/**
 * 쿠폰을 ajax로 검색한다.
 */
function getCouponAjaxList() {
	var f = $("form#orderFrm");
	var queryString = f.formSerialize();
	var mode	= $("input[name='mode']").val();
	$.ajax({
		type: 'post',
		url: './settle_coupon?mode='+mode,
		data: queryString,
		dataType: 'json',
		cache: false,
		success: function(data) {
			if( data ){
				if( data.coupon_error ){
					$('#coupon_goods_lay').html('');  
					closeDialog("coupon_apply_dialog");
					openDialogAlert('적용 가능한 쿠폰이 없습니다.','400','140');
				}else{
					openDialog("쿠폰 적용 하기", "coupon_apply_dialog", {"width":900,"height":600});
					if(data.coupongoods){
						$('#coupon_goods_lay').html(data.coupongoods);
					}
					if(data.checkshippingcoupons>0){
						$('#coupon_shipping_lay').show();
						$('#coupon_shipping_select').html(data.couponshipping);
					}else{
						$('#coupon_shipping_lay').hide();
					}
				}
			}
		}
	});
}

// 쿠폰사용
function getCouponuse(seqno){
	$(".couponlay_"+seqno).hide();
	$.getJSON('../coupon/goods_coupon_max?no='+seqno, function(data) {
		if(data){
			$(".couponlay_"+seqno).show();
			if(data.sale_type == 'won'){
				$(".couponlay_"+seqno+" .cb_percent").html('▶'+comma(data.won_goods_sale) + '원<br/>쿠폰받기');
			}else{
				$(".couponlay_"+seqno+" .cb_percent").html('▶'+comma(data.percent_goods_sale) + '%<br/>쿠폰받기');
			}
		}
	});
}

function getCouponDownlayerclose(){
	$('#couponDownloadDialog').dialog('close');
}

function inicis_mobile_popup(){
	var xpos = 100;
	var ypos = 100;
	var position = "top=" + ypos + ",left=" + xpos;
	var features = position + ", width=320, height=440";
	var wallet = window.open("", "BTPG_WALLET", features);
	wallet.focus();
}

function mobile_pay_layer(){
	var divLayer = $("#payprocessing").clone().wrapAll("<div/>").parent().html();
	divLayer = divLayer + '<iframe name="tar_opener" frameborder="0" border="0" width="350" height="100%" scrolling="auto" style="margin:0px auto;"></iframe>';
	$("#layer_pay").html(divLayer);
	$("#layer_pay").css("height","1000px");
	$("#layer_pay").css("display","block");
	window.parent.$("body").scrollTop(0);
}

function mobile_popup(){
	var xpos = 100;
	var ypos = 100;
	var position = "top=" + ypos + ",left=" + xpos;
	var features = position + ", width=320, height=440";
	var wallet = window.open("", "tar_opener", features);
	wallet.focus();
}
function use_cash(){
	if($("input[name='cash_view']").val() < 1){
		openDialogAlert('이머니를 정확히 입력해 주세요.','400','140');
		return false;
	}

	if($("input[name='cash_view']").val() > 0){
		$("input[name='cash']").val( $("input[name='cash_view']").val() );
		$(".cash_input_button").hide();
		$(".cash_all_input_button").hide();
		$(".cash_cancel_button").show();
	}
	order_price_calculate();
}

function use_all_cash(){
	$("input[name='cash_all']").val('y');
	$("input[name='cash']").val(0);
	$(".cash_input_button").hide();
	$(".cash_all_input_button").hide();
	$(".cash_cancel_button").show();

	order_price_calculate();
}

function cancel_cash(){
	$("input[name='cash']").val(0);
	$("input[name='cash_view']").val(0);
	$(".cash_cancel_button").hide();
	$(".cash_input_button").show();
	$(".cash_all_input_button").show();
	$("#priceCashTd").hide();
	order_price_calculate();
}

function use_order_cash(){
	if($("input[name='order_cash_view']").val() < 1){
		openDialogAlert('이머니를 정확히 입력해 주세요.','400','140');
		return false;
	}

	if($("input[name='order_cash_view']").val() > 0){
		$("input[name='order_cash']").val( $("input[name='order_cash_view']").val() );
		$(".order_cash_input_button").hide();
		$(".order_cash_all_input_button").hide();
		$(".order_cash_cancel_button").show();
	}
	order_price_calculate();
}

function use_all_order_cash(){
	$("input[name='order_cash_all']").val('y');
	$("input[name='order_cash']").val(0);
	$(".order_cash_input_button").hide();
	$(".order_cash_all_input_button").hide();
	$(".order_cash_cancel_button").show();

	order_price_calculate();
}

function cancel_order_cash(){
	$("input[name='order_cash']").val(0);
	$("input[name='order_cash_view']").val(0);
	$(".order_cash_cancel_button").hide();
	$(".order_cash_input_button").show();
	$(".order_cash_all_input_button").show();
	$("#priceCashTd").hide();
	order_price_calculate();
}
 
function use_emoney(){
	if($("input[name='emoney_view']").val() < 1 ){
		openDialogAlert('적립금을 정확히 입력해 주세요.','400','140');
		return false;
	}
	if($("input[name='emoney_view']").val() > 0){
		$("input[name='emoney']").val( $("input[name='emoney_view']").val() );
		$(".emoney_input_button").hide();
		$(".emoney_all_input_button").hide();
		$(".emoney_cancel_button").show();
	}

	// 적립금액 제한 조건 알림 추가 leewh 2014-07-01
	if ($("#default_reserve_limit").length) {
		if ($("#default_reserve_limit").val()==1) {
			alert("적립금 사용으로 적립금을 지급하지 않습니다.");
		} else if ($("#default_reserve_limit").val()==2) {
			alert("기대적립금에서 사용한 적립금을 제외하고 적립금을 지급합니다.");
		} else if ($("#default_reserve_limit").val()==3) {
			alert("사용한 적립금을 제외하고 결제금액을 기준으로 적립금을 지급합니다.");
		}
	}
	order_price_calculate();
}

function use_all_emoney(){
	$("input[name='emoney_all']").val('y');
	$("input[name='emoney']").val(0);
	$(".emoney_input_button").hide();
	$(".emoney_all_input_button").hide();
	$(".emoney_cancel_button").show();
	//if ($("input[name='gubun_b2b']").val()=='Y') {
		$("#typereceipt0").attr("checked","checked");
		$("#typereceipt1").attr("disabled","disabled");
		$("#typereceipt2").attr("disabled","disabled");
	//}

	// 적립금액 제한 조건 알림 추가 leewh 2014-07-01
	if ($("#default_reserve_limit").length) {
		if ($("#default_reserve_limit").val()==1) {
			alert("적립금 사용으로 적립금을 지급하지 않습니다.");
		} else if ($("#default_reserve_limit").val()==2) {
			alert("기대적립금에서 사용한 적립금을 제외하고 적립금을 지급합니다.");
		} else if ($("#default_reserve_limit").val()==3) {
			alert("사용한 적립금을 제외하고 결제금액을 기준으로 적립금을 지급합니다.");
		}
	}
	order_price_calculate();
}

function cancel_emoney(){
	$("input[name='emoney']").val(0);
	$("input[name='emoney_view']").val(0);
	$(".emoney_cancel_button").hide();
	$(".emoney_input_button").show();
	$(".emoney_all_input_button").show();
	$("#priceEmoneyTd").hide();
	if ($("input[name='gubun_b2b']").val()=='Y') {
		$("#typereceipt1").attr("disabled",false);
		$("#typereceipt2").attr("disabled",false);
	}
	order_price_calculate();
}

	function limit_chk(gift_seq, obj){

		var f = eval("document.orderFrm.gift_"+gift_seq)
		if(!f){
			f = $("input[name='gift_"+gift_seq+"[]']");
		}
		var cnt = 0;
		var f2 = eval("document.orderFrm.gift_"+gift_seq+"_limit");
		var limitCnt = f2.value;

		for(i=0; i<f.length; i++){
			if(f[i].checked == true){
				cnt++;
			}
		}

		if(cnt > limitCnt){
			alert("사은품을 최대 "+limitCnt+"개까지 선택하실 수 있습니다.");
			obj.checked = false;
		}

	}

var exception_sale	= 0;
function exception_saleprice(sale){
	if	(exception_sale != sale){
		exception_sale	= sale;
		openDialogAlert("할인금액이 상품금액을 초과하여 일부할인이 제외되었습니다.", 500, 150);
	}
}

function reverse_pay_layer(){

	$('#wrap').show();
	$('#layer_pay').hide();
	reverse_pay_button();
}

window.onload = function() {
	// PC 스킨 전용
	if(gl_set_mode == 'pc') {
		var mobile_new = '';

		$("#pay")
		.unbind('click')
		.bind("click",function(){
			$.ajax({
				'url' : '../order/getSettleConfig',
				'data': {'mode': gl_mode},
				'type' : 'post',
				'dataType': 'json',
				'success' : function(res){
					var isUser = res.isUser, orderFormAction = res.orderFormAction, pgCompany = res.pgCompany;

					if(isMobile.any()) {
						var toHTML = ''
						+'<!-- 결제창을 레이어 형태로 구현-->'
						+'<style type="text/css">'
						+'#layer_pay {position:absolute;top:0px;width:100%;height:100%;background-color:#ffffff;text-align:center;z-index:999999;}'
						+'#payprocessing {text-align:center;position:absolute;width:100%;top:150px;z-index:99999999px;}'
						+'</style>'
						+'<div id="layer_pay" class="hide"></div>'
						+'<div id="payprocessing" class="pay_layer hide">'
						+'<div style="margin:auto;"><img src="../images/design/img_paying.gif" /></div>'
						+'<div style="margin:auto;padding-top:20px;"><img src="../images/design/progress_bar.gif" /></div>'
						+'</div>';

						$('body').append(toHTML);
						$('[name="orderFrm"]').append('<input type="hidden" name="mobilenew" value="y" />');


						//레이어 결제창
						if((pgCompany == 'inicis') && $("input[name='mobilenew']")){
							$("input[name='mobilenew']").val('N');
						}	//이니시스는 iframe 사용 안함

						if($("#layer_pay").length > 0 && $("input[name='mobilenew']")) mobile_new = $("input[name='mobilenew']").val();
					}

					$("#actionFrame").attr("frameborder",0);
					$("#actionFrame").css("height",0);
					$("#actionFrame").removeClass("hide");

					var f = $("form#orderFrm");
					f.attr("action",orderFormAction);
					f.attr("target","actionFrame");

					if(pgCompany != 'inicis') {
						if(isMobile.any() && pgCompany && $("input[name='payment']:checked").val() != 'bank' ){
							f.attr("target","tar_opener");
						}else{
							f.attr("target","actionFrame");
						}
					}
					
					// 개인통관고유부호 수집 동의
					if($("input[name='agree_international_shipping1']").length > 0) {
						if( $("input[name='agree_international_shipping1']").attr("checked") && !$("input[name='agree_international_shipping1']").attr("checked") ){
							alert('개인통관고유부호 수집에 동의하셔야 합니다.');
							$(this).focus();
							return false;
						}
					}

					if($("input[name='agree_international_shipping2']").length > 0) {
						if( $("input[name='agree_international_shipping2']").attr("checked") && !$("input[name='agree_international_shipping2']").attr("checked") ){
							alert('개인통관고유부호 수집에 동의하셔야 합니다.');
							$(this).focus();
							return false;
						}
					}

					if($("input[name='agree_international_shipping3']").length > 0) {
						if( $("input[name='agree_international_shipping3']").attr("checked") && !$("input[name='agree_international_shipping3']").attr("checked") ){
							alert('관부가세 발생 관련 공지를 확인하셔야 합니다.');
							$(this).focus();
							return false;
						}
					}


					// 카카오페이 일경우 다른 PG 레이어를 타지 않음.
					var sel_payment	= $("input[name='payment']:checked").val();
					if(sel_payment == 'kakaopay'){
						f.attr("target","actionFrame");
						$("iframe[name='actionFrame']").hide();
					}else{			
						if(pgCompany != 'inicis' && pgCompany != 'kspay') {
							if(isMobile.any() && pgCompany && $("input[name='payment']:checked").val() != 'bank'){
								f.attr("target","tar_opener");
							}else{
								f.attr("target","actionFrame");
							}

							if(gl_mobile && $("input[name='payment']:checked").val() != 'bank'){
								if(isMobile.any()){
									mobile_pay_layer();
								}else{

									// 2014-10-23 iphone 버전이 8.1 일경우 결제팝업은 ssl 암호화 리턴 이후 띄운다. (app/controllers/order.php)
									var iphone_ver = 0;
									if(navigator.userAgent.match(/iPhone/i)){
										if(navigator.userAgent.match(/8_1/)) iphone_ver = 81;
									}
									if(iphone_ver == 0){
										if(pgCompany != 'inicis'){
											mobile_popup();
										}
									}
								}
							}
						}
					}


					if(!isUser) { //비회원 개인정보 동의
						if($("input[name='agree']:checked").val()!='Y'){
							alert('개인정보 수집ㆍ이용에 동의하셔야 합니다.');
							$("input[name='agree']").focus();
							return false;
						}
					}

					if($("input[name='cancellation']").length > 0) { //청약철회 관련방침
						if($("input[name='cancellation']:checked").val()!='Y'){
							alert('청약철회 관련방침에 동의하셔야 합니다.');
							$("input[name='cancellation']").focus();
							return false;
						}
					}

					if(gl_mobile) {
						f.submit();					
					} else {
						if( $("input[name='agree_international_shipping1']").attr("checked") ){
							openDialogConfirm('통관고유부호 및 관부가세관련 사항 등 해외구매대행 내용을 확인 하셨습니까?',530,140,function(){
								f.submit();
							},function(){

							});
						}else{
							f.submit();
						}
					}

				}
			});
		});
	}
}


/*PG 결제 스크립트 함수 (절대 수정하지 마시오)*/

/*이니시스*/
function pay() {
	INIStdPay.pay('SendPayForm_id');
}

/*LG U+*/
var LGD_window_type = 'iframe';

function launchCrossPlatform(CST_PLATFORM){
	lgdwin = openXpay(document.getElementById('LGD_PAYINFO'), CST_PLATFORM, LGD_window_type, null, "", "");
}

function getFormObject() {
		return document.getElementById("LGD_PAYINFO");
}

function payment_return() {
	var fDoc;
	
	fDoc = lgdwin.contentWindow || lgdwin.contentDocument;
	
		
	if (fDoc.document.getElementById('LGD_RESPCODE').value == "0000") {
		
			document.getElementById("LGD_PAYKEY").value = fDoc.document.getElementById('LGD_PAYKEY').value;
			document.getElementById("LGD_PAYINFO").target = "_self";
			document.getElementById("LGD_PAYINFO").action = "/lg/receive";
			document.getElementById("LGD_PAYINFO").submit();
	} else {
		alert("LGD_RESPCODE (결과코드) : " + fDoc.document.getElementById('LGD_RESPCODE').value + "\n" + "LGD_RESPMSG (결과메시지): " + fDoc.document.getElementById('LGD_RESPMSG').value);
		closeIframe();
	}
}



/*
 * PG 이니시스 결제 모듈 부분 절대 수정하지 마십시요 //모바일
 */
if(typeof gl_mobile_mode!="undefined" && gl_mobile_mode && typeof gl_set_mode!="undefined" && gl_set_mode.indexOf('mobile') > -1 ){
	document.write('<script type="application/x-javascript">addEventListener("load", function(){setTimeout(updateLayout, 0);}, false);var currentWidth = 0;function updateLayout(){if (window.innerWidth != currentWidth){currentWidth = window.innerWidth;var orient = currentWidth == 320 ? "profile" : "landscape";document.body.setAttribute("orient", orient);setTimeout(function(){window.scrollTo(0, 1);}, 100);}}setInterval(updateLayout, 400);</script>');
}

var width = 330;
var height = 480;
var xpos = (screen.width - width) / 2;
var ypos = (screen.width - height) / 2;
var position = "top=" + ypos + ",left=" + xpos;
var features = position + ", width=320, height=440";
var date = new Date();
var date_str = "testoid_"+date.getFullYear()+""+date.getMinutes()+""+date.getSeconds();
if( date_str.length != 16 )
{
    for( i = date_str.length ; i < 16 ; i++ )
    {
        date_str = date_str+"0";
    }
}


function on_app()
{
       	var order_form = document.ini;
		var paymethod;
		if(order_form.paymethod.value == "wcard")
			paymethod = "CARD";
		else if(order_form.paymethod.value == "mobile")
			paymethod = "HPP";
		else if(order_form.paymethod.value == "vbank")
			paymethod = "VBANK";
		else if(order_form.paymethod.value == "culture")
			paymethod = "CULT";
		else if(order_form.paymethod.value == "hpmn")
			paymethod = "HPMN";

       	param = "";
       	param = param + "mid=" + order_form.P_MID.value + "&";
       	param = param + "oid=" + order_form.P_OID.value + "&";
       	param = param + "price=" + order_form.P_AMT.value + "&";
       	param = param + "goods=" + order_form.P_GOODS.value + "&";
       	param = param + "uname=" + order_form.P_UNAME.value + "&";
       	param = param + "mname=" + order_form.P_MNAME.value + "&";
       	param = param + "mobile=000-111-2222" + order_form.P_MOBILE.value + "&";
       	param = param + "paymethod=" + paymethod + "&";
       	param = param + "noteurl=" + order_form.P_NOTI_URL.value + "&";
       	param = param + "ctype=1" + "&";
       	param = param + "returl=" + "&";
       	param = param + "email=" + order_form.P_EMAIL.value;
		var ret = location.href="INIpayMobile://" + encodeURI(param);
}

function on_web()
{
	var order_form	= document.ini;
	var paymethod	= order_form.paymethod.value;
	order_form.charset='euc-kr';
	//self.name = "BTPG_WALLET";
	/*
	var wallet = window.open("", "BTPG_WALLET", features);	
	if (wallet == null) 
	{
		if ((webbrowser.indexOf("Windows NT 5.1")!=-1) && (webbrowser.indexOf("SV1")!=-1)) 
		{    // Windows XP Service Pack 2
			alert("팝업이 차단되었습니다. 브라우저의 상단 노란색 [알림 표시줄]을 클릭하신 후 팝업창 허용을 선택하여 주세요.");
		} 
		else 
		{
			alert("팝업이 차단되었습니다.");
		}
		return false;
	}
	wallet.focus();
	order_form.target = "BTPG_WALLET";
	*/
	order_form.target = "_top";
	order_form.action = "https://mobile.inicis.com/smart/" + paymethod + "/";	
	order_form.submit();
}

function onSubmit()
{
	var order_form = document.ini;
	var inipaymobile_type = order_form.inipaymobile_type.value;
	if( inipaymobile_type == "app" )
		return on_app();
	else if( inipaymobile_type == "web" )
		return on_web();
}	
/*
 * PG 이니시스 결제 모듈 부분 절대 수정하지 마십시요 
 */	

