/* [목차]
* 1. 임시 옵션 생성 관련
* 2. 빠른 상품 등록 페이지 처리
* 3. 실시간 저장 관련
*/
var domSaverObj				= '';
var cfg_scm_use				= 'N';
var current_scm_use			= 'N';
var defaultWarehouse		= '';	// 기본 창고 ( JSON )
var defaultLocation			= '';	// 기본 창고의 로케이션 ( JSON )
var defaultRunout			= '';	// 통합 재고에 따른 판매설정
var defaultAbleStockLimit	= '';	// 통합 가용재고 설정

// NULL 또는 undefined 처리
function null_exception(type, val){
	if	(!val){
		if	(type == 'int')	val	= '0';
		else				val	= '';
	}

	return val;
}

// dummy ui-dialog 제거
function removeDummyDialog(chk_label){
	$('body').find('div.ui-dialog').each(function(){
		if	($(this).attr('aria-labelledby') == chk_label)	$(this).remove();
	});
}

//---------------------------------- 1. 임시 옵션 생성 관련 ------------------------------------//

// 옵션 생성 팝업 오픈
function open_options_create_popup(id, goods_seq, submitFunc, tmp_seq){
	$.ajax({
		type		: 'post',
		url			: '../goods/create_option_popup',
		data		: 'popup_id=' + id + '&goods_seq=' + goods_seq + '&submitFunc=' + submitFunc + '&tmp_seq=' + tmp_seq, 
		success: function(result){
			$('div#' + id).html(result);
			$('div#' + id).find('input').each(function(){
				var thisInputObj = $(this);
				if(thisInputObj.attr('title') != thisInputObj.attr('placeholder') || !thisInputObj.attr('placeholder')) thisInputObj.attr('placeholder',thisInputObj.attr('title'));
			}); 
		}
	});

	openDialog("필수옵션", id, {"width":"1150","height":"500","show" : "fade","hide" : "fade"});
}

// 옵션 한줄 추가
function addOptionRow(obj){

	if	($(obj).closest('tbody').find('tr').length >= 5){
		openDialogAlert('옵션은 최대 5개까지만 지원합니다.', 400, 170, function(){});
		return false;
	}else{
		var clone	= $(obj).closest('tr').clone();
		clone.find("select[name='option_type[]']").find('option').attr('selected', false);
		clone.find("select[name='option_type[]']").find("option[value='direct']").attr('selected', true);
		clone.find('td.pmbtn').html('<span class="btn-minus btnplusminus"><button type="button" onclick="delOptionRow(this);"></button></span>');
		clone.find("input[name='option_title[]']").val('');
		clone.find("select[name='option_new_type[]']").find('option').attr('selected', false);
		clone.find("input[name='option_value[]']").val('');
		clone.find("input[name='option_price[]']").val('');
		clone.find("input[name='option_code[]']").val('');
		clone.find('span.option-type-direct-lay').show();
		clone.find('span.option-type-codeform-lay').hide();
		clone.find('span.option-type-codeform-lay.text-lay').html('');
		clone.find('div.option-color-box-lay').html('');
		clone.find('input').each(function(){
			var thisInputObj = $(this);
			if(thisInputObj.attr('title') != thisInputObj.attr('placeholder') || !thisInputObj.attr('placeholder')) thisInputObj.attr('placeholder',thisInputObj.attr('title'));
		}); 
		$(obj).closest('tbody').append(clone);
	}
}

// 옵션 현재줄 제거
function delOptionRow(obj){
	$(obj).closest('tr').remove();
}

// 옵션정보 가져오기 선택에 따른 처리
function select_option_type(obj){
	if	($(obj).val() == 'direct'){
		$(obj).closest('tr').find('span.option-type-direct-lay').show();
		$(obj).closest('tr').find('span.option-type-codeform-lay').hide();
		$(obj).closest('tr').find('span.option-type-codeform-lay.text-lay').html('');
		$(obj).closest('tr').find('div.option-color-box-lay').html('');
		$(obj).closest('tr').find("input[name='option_title[]']").val('');
		$(obj).closest('tr').find("input[name='option_value[]']").val('');
		$(obj).closest('tr').find("input[name='option_price[]']").val('');
		$(obj).closest('tr').find("input[name='option_code[]']").val('');
	}else{
		$(obj).closest('tr').find("input[name='option_title[]']").val($(obj).find('option:selected').attr('label_title'));
		$(obj).closest('tr').find("input[name='option_value[]']").val('');
		$(obj).closest('tr').find("input[name='option_price[]']").val('');
		$(obj).closest('tr').find("input[name='option_code[]']").val('');
		$(obj).closest('tr').find('span.option-type-codeform-lay.text-lay').html('');
		$(obj).closest('tr').find('div.option-color-box-lay').html('');
		$(obj).closest('tr').find('span.option-type-direct-lay').hide();
		$(obj).closest('tr').find('span.option-type-codeform-lay').show();
	}
}

// 정의된 옵션 코드 팝업 오픈
function select_load_option(obj){
	var option_type = $(obj).closest('tr').find("select[name='option_type[]']").val();
	if	(option_type == 'direct'){
		select_option_type($(obj).closest('tr').find("select[name='option_type[]']"));
	}else{
		var idx		= $("select[name='option_type[]']").index($(obj).closest('tr').find("select[name='option_type[]']"));
		var title	= $(obj).closest('tr').find("select[name='option_type[]']").attr('label_title');
		$('div#' + option_type).find('input.row-idx').val(idx);
		openDialog(title, option_type, {'width':600, 'height':600});
	}
}

// 선택된 옵션 코드 적용
function apply_load_optionform(obj){
	var wrap		= $(obj).closest('div.goodsoption_wrap');
	var value		= '';
	var price		= '';
	var code		= '';
	var color		= '';
	var colorBox	= '';
	var tmpPrice	= 0;
	var chkCnt		= 0;
	var newType		= wrap.find('input.chk-code').eq(0).attr('newType');
	wrap.find('input.chk-code').each(function(){
		if	($(this).attr('checked')){
			tmpPrice		= $(this).attr('price');
			if	(!tmpPrice)	tmpPrice	= '0';

			if	(newType == 'color'){
				color		+= (value)	? ',' + $(this).attr('color')	: $(this).attr('color');
				colorBox	+= '<div class="colorPickerBtn colorhelpicon" style="margin:0 2px;background-color:' + $(this).attr('color') + '" ></div>';
			}
			price	+= (value)	? ',' + tmpPrice				: tmpPrice;
			code	+= (value)	? ',' + $(this).attr('code')	: $(this).attr('code');
			value	+= (value)	? ',' + $(this).val()			: $(this).val();
		}
	});

	var parent	= $("select[name='option_type[]']").eq(wrap.find('input.row-idx').val());
	if	(newType == 'color')	$(parent).closest('tr').find("select[name='option_new_type[]']").find("option[value='color']").attr('selected', true);
	else						$(parent).closest('tr').find("select[name='option_new_type[]']").find("option[value='none']").attr('selected', true);
	$(parent).closest('tr').find("input[name='option_value[]']").val(value);
	$(parent).closest('tr').find("input[name='option_value[]']").closest('td').find('span.option-type-codeform-lay').html(value);
	$(parent).closest('tr').find("input[name='option_price[]']").val(price);
	$(parent).closest('tr').find("input[name='option_price[]']").closest('td').find('span.option-type-codeform-lay').html(price);
	$(parent).closest('tr').find("input[name='option_code[]']").val(code);
	$(parent).closest('tr').find("input[name='option_code[]']").closest('td').find('span.option-type-codeform-lay').html(code);
	if	(color){
		$(parent).closest('tr').find("input[name='option_color[]']").val(color);
		$(parent).closest('tr').find('div.option-color-box-lay').html(colorBox).show();
	}
	closeDialog(wrap.attr('id'));

	var chk_label	= 'ui-dialog-title-' + wrap.attr('id');
	removeDummyDialog(chk_label);
}

// 옵션 구분 변경에 따른 처리
function chg_option_type(obj){
	if	($(obj).val() == 'color'){
		$(obj).closest('tr').find("input[name='option_title[]']").val('색상');
		$(obj).closest('tr').find('div.option-color-box-lay').show();
	}else{
		$(obj).closest('tr').find("input[name='option_title[]']").val('');
		$(obj).closest('tr').find('div.option-color-box-lay').hide();
	}
	option_blur_event($(obj).closest('tr').find("input[name='option_value[]']"));
}


// 옵션값 입력에 따른 금액, 코드 기본생성
function option_blur_event(obj){
	var option_value	= $(obj).val();
	if	(option_value){
		var option_type		= $(obj).closest('tr').find("select[name='option_new_type[]']").val();
		var option_arr		= option_value.split(',');
		var option_len		= option_arr.length - 1;
		var option_price	= '0';
		var option_code		= '';
		var option_color	= '#fff';
		var colorpicker		= '<span style="margin:0 2px;"><input type="text" name="color" class="color-picker" value="#fff" /></span>';

		for	(var i = 0; i < option_len; i++){
			option_price	+= ',0';
			option_code		+= ',';
			if	(option_type == 'color'){
				option_color	+= ',#fff';
				colorpicker		+= '<span style="margin:0 2px;"><input type="text" name="color" class="color-picker" value="#fff" /></span>';
			}
		}

		if	(option_type == 'color'){
			$(obj).closest('tr').find("input[name='option_color[]']").val(option_color);
			$(obj).closest('tr').find('div.option-color-box-lay').html(colorpicker).show();
			$(obj).closest('tr').find('input.color-picker').unbind('change');
			$(obj).closest('tr').find('input.color-picker').customColorPicker();
			$(obj).closest('tr').find('input.color-picker').bind('change', function(){set_colorpicker_value(this);});
		}else{
			$(obj).closest('tr').find('input.color-picker').unbind('change');
			$(obj).closest('tr').find("input[name='option_color[]']").val('');
			$(obj).closest('tr').find('div.option-color-box-lay').html('').hide();
		}
		$(obj).closest('tr').find("input[name='option_price[]']").val(option_price);
		$(obj).closest('tr').find("input[name='option_code[]']").val(option_code);
	}
}

// color picker 선택된 color값 적용
function set_colorpicker_value(obj){
	var option_color	= $(obj).closest('tr').find("input[name='option_color[]']").val();
	var idx				= $(obj).closest('tr').find('input.color-picker').index(obj);
	var colorArr		= option_color.split(',');
	var colorCnt		= colorArr.length;
	var color			= '';
	var color_text		= '';
	for ( var i = 0; i < colorCnt; i++){
		color					= colorArr[i];
		if	(i == idx)	color	= $(obj).val();
		color_text		+= (color_text)	? ',' + color : color;
	}
	$(obj).closest('tr').find("input[name='option_color[]']").val(color_text);
}

// 옵션 생성 팝업 submit
function create_option_submit(){
	var frm		= $("form[name='optPopFrm']");
	var status	= true;
	var tmp		= new Array();
	var require	= new Array();
	var errMsg	= '';
	var chkCnt	= 0;
	var idx		= 0;
	frm.find("input[name='option_title[]']").each(function(){
		require[0]	= frm.find("input[name='option_title[]']").eq(idx).val();
		require[1]	= frm.find("input[name='option_value[]']").eq(idx).val();
		require[2]	= frm.find("input[name='option_price[]']").eq(idx).val();
		require[3]	= frm.find("input[name='option_code[]']").eq(idx).val();

		for	( var k in require ){
			errMsg	= (require[0]) ? require[0] + ' 옵션의 ' : '';

			if	(!require[k] ){
				openDialogAlert(errMsg + '옵션명, 옵션값, 옵션가격, 옵션코드를 입력해 주세요.', 500, 170, function(){});
				status	= false;
				break;
			}

			tmp		= require[k].split(',');
			if	(k > 1 && chkCnt != tmp.length){
				openDialogAlert(errMsg + '옵션값, 옵션가격, 옵션코드의 개수가 불일치합니다.', 500, 170, function(){});
				status	= false;
				break;
			}
			chkCnt	= tmp.length;
		}

		if	( !status )	return false;
		idx++;
	});

	if	(status){
		loadingStart();
		frm.submit();
	}
}

//---------------------------------- 2. 빠른 상품 등록 페이지 처리 -------------------------------//

// 전역변수 세팅 및 기본 load함수 호출
function quick_regist_js_init(loadStatus, runout, ableStockLimit, scm_use, set_default_date, warehouses, locations){
	if	(loadStatus == 'FAIL'){
		fail_load_tmp_data();
	}else{
		defaultRunout			= runout;
		defaultAbleStockLimit	= ableStockLimit;
		if	(!set_default_date)	scm_use	= 'N';
		current_scm_use			= scm_use;
		cfg_scm_use				= scm_use;
		defaultWarehouse		= warehouses;
		defaultLocation			= locations;

		chk_runout_policy(runout, ableStockLimit);
	}
}

// 임시 데이터를 불러오는데 실패한 경우
function fail_load_tmp_data(){
	openDialogConfirm('임시 데이터가 없습니다.<br/>데이터를 새로 로드하시겠습니까?', 400, 220, function(){
		location.replace('../goods/batch_regist');
	},function(){});
}

// 안내 팝업 오픈
function helpOpenDialog(type){
	var title	= '';
	var id		= 'help_' + type;
	var width	= 0;
	var height	= 0;
	switch(type){
		case 'goods_satatus':
			title	= '상태';
			width	= 650;
			height	= 220;
		break;
		case 'default_revision_date':
			title	= '기초재고 기초일자';
			width	= 520;
			height	= 120;
		break;
		case 'default_revision':
			title	= '기초재고';
			width	= 570;
			height	= 130;
		break;
	}
	openDialog(title, id, {'width':width,'height':height});
}

// 재고에 따른 판매 설정 팝업
function open_runout_setting_popup(){
	openDialog("재고 변화에 따른 상품판매 여부 설정", "popup_runout_setting", {"width":"98%","height":"510","show" : "fade","hide" : "fade"});
}

// 재고에 따른 판매 설정 변경 처리
function chk_runout_policy(runout, ableStockLimit){

	var msg		= '';
	$("table#shop_runout tr").removeClass('red');
	$("table#goods_runout tr").removeClass('red');
	if	( $("input[name='runout_type']:checked").val()=='goods' ){
		goodsRunout		= $("input[name='runout']:checked").val();
		runout			= $("input[name='runout']:checked").val();
		ableStockLimit	= $("input[name='ableStockLimit'").val();

		$("input[name='runout_policy']").val(runout);
		$("input[name='able_stock_limit']").val(ableStockLimit);
		$("input[name='runout']").attr('disabled', false);
		$("input[name='runout']:checked").closest('tr').addClass("red");
		$("input[name='runout']:checked").closest('tr').next().addClass("red");
		$("#ableStockLimitMsg").html(parseInt(ableStockLimit) + 1);

		msg = '[개별]';
	}else{
		goodsRunout		= '';
		runout			= defaultRunout;
		ableStockLimit	= defaultAbleStockLimit;
		$("input[name='runout_policy']").val('');
		$("input[name='able_stock_limit']").val('');
		$("input[name='runout']").attr('disabled', true);
		$("table#shop_runout tr").next().addClass("red");

		msg = '[통합]';
	}

	switch(runout){
		case 'ableStock':
			msg	+= ' 가용재고가 ' + (parseInt(ableStockLimit) + 1) + '개 이상 있을 때 판매함';
		break;
		case 'unlimited':
			msg	+= ' 재고와 상관없이 판매함';
		break;
		default:
		case 'stock':
			msg	+= ' 재고가 1개 이상 있을 때 판매함';
		break;
	}

	$('span.runout-msg-lay').html(msg);

	// 실시간 저장
	var data	= {
		'tmp_seq'			: $("input[name='tmp_seq']").val(), 
		'runout_policy'		: goodsRunout, 
		'able_stock_limit'	: ableStockLimit 
	};
	domSaverObj.requestSendData(data);
}

// 배송방법 선택
function open_shipping_setting_popup(popupFunc, returnFunc){

	$.ajax({
		type		: 'post',
		url			: '../goods/get_probider_shipping_data',
		data		: 'provider_seq=' + $("input[name='provider_seq']").val(), 
		dataType	: 'json',
		success: function(result){
			if	(result.status){
				if	(popupFunc){
					var func	= window[popupFunc];
					func(result.data, result.calcul_type_name, returnFunc);
				}
			}else{
				openDialogAlert(result.msg, 400, 170, function(){});
				return false;
			}
		}
	});
}

// 배송그룹 목록 생성
function set_provider_shipping_setting(data, calcul_type_name, returnFunc){
	$('div#popup_shipping_setting').find('tr.add-row').remove();
	if	(data.length > 0){
		var row				= '';
		var html			= '';
		var contents		= '';
		var default_html	= $('div#popup_shipping_setting').find('tr.default-row').html();
		for (var idx in data){
			row			= data[idx];
			contents	= default_html;
			contents	= contents.replace(/\[\:SHIPPING_GROUP_NAME\:\]/g, row.shipping_group_name);
			contents	= contents.replace(/\[\:SHIPPING_GROUP_SEQ\:\]/g, row.shipping_group_seq);
			if	(row.default_yn == 'Y'){
				contents	= contents.replace(/\[\:SHIPPING_DEFAULT_YN\:\]/g, '기본그룹');
			}else{
				contents	= contents.replace(/\[\:SHIPPING_DEFAULT_YN\:\]/g, '');
			}
			contents	= contents.replace(/\[\:SHIPPING_CALCULATE_TYPE\:\]/g, calcul_type_name[row.shipping_calcul_type]);

			html	= '<tr class="add-row">';
			html	+= contents
			html	+= '</tr>';

			$('div#popup_shipping_setting').find('tbody').append(html);
		}

		if	(returnFunc){
			$('div#popup_shipping_setting').find('button.shipping-apply-button').unbind('click');
			$('div#popup_shipping_setting').find('button.shipping-apply-button').bind('click', function(){
				var func	= window[returnFunc];
				func($(this));
			});
		}

		var height			= (data.length * 25) + 200;
		openDialog('배송그룹 선택', 'popup_shipping_setting', {'width':600,'height':height});
	}
}

// 배송그룹 선택 처리
function selected_provider_shipping_group(obj){
	var chkObj		= $(obj).closest('div#popup_shipping_setting').find("input[name='sel_shipping_group']:checked");
	var group_seq	= chkObj.val();
	var group_name	= chkObj.closest('tr').find('span.sel_shipping_group_name').text();
	var default_yn	= chkObj.closest('tr').find('font.sel_shipping_default_yn').text();

	$('font.shipping-group-name').html(group_name);
	$('span.shipping-group-seq').html(group_seq);
	if	(default_yn){
		$('font.shipping-default-yn').html('[' + default_yn + ']').show();
	}else{
		$('font.shipping-default-yn').html('').hide();
	}
	$("input[name='shipping_group_seq']").val(group_seq);

	closeDialog('popup_shipping_setting');

	// 실시간 저장
	var data	= {
		'tmp_seq'				: $("input[name='tmp_seq']").val(), 
		'shipping_group_seq'	: group_seq 
	};
	domSaverObj.requestSendData(data);
}

// 상품 row 단위 데이터 추가/복사/삭제/초기화
function save_tmp_goods_row(type){
	if	(type != 'add' && type != 'reset'){
		var chkCnt	= 0;
		$('input.chk').each(function(){
			if	($(this).attr('checked'))	chkCnt++;
		});
		if	(!chkCnt){
			openDialogAlert('선택된 상품이 없습니다.', 400, 170, function(){});
			return false;
		}
		if	(type == 'copy' && chkCnt > 1){
			openDialogAlert('복사할 상품을 1개만 선택해 주십시오.', 400, 170, function(){});
			return false;
		}
	}

	loadingStart();
	$("input[name='act_type']").val(type);
	$("form[name='batchRegistFrm']").submit();
}

// 창고 및 로케이션 선택에 따른 변경/저장
function select_warehouse(obj, type){
	var wh_seq		= $(obj).val();
	var idx			= $(obj).closest('tr').find('select.warehouse').index(obj);
	var chkWh		= true;
	$(obj).closest('tr.option-rows').find('select.warehouse').each(function(i){
		if	(idx != i){
			if	($(this).val() == wh_seq){
				openDialogAlert('중복된 창고입니다. 다른 창고를 선택해 주세요.', 400, 170, function(){});
				wh_seq	= '';
				$(obj).find('option').attr('selected', false);
				chkWh	= false;
				return false;
			}
		}
	});
	if	(!chkWh)	return false;

	if	(wh_seq > 0){
		var row			= '';
		var parent		= $(obj).parent();
		var location_w	= new Array();
		var location_l	= new Array();
		var location_h	= new Array();
		location_w[1] = location_l[1] = location_h[1] = 1;

		parent.find('select.location_w option').remove();
		parent.find('select.location_l option').remove();
		parent.find('select.location_h option').remove();

		$.ajax({
			type		: 'get',
			url			: '../scm/getWarehouseLocationList',
			data		: 'wh_seq=' + wh_seq, 
			dataType	: 'json',
			success: function(result){
				if	(result.length > 0){
					for	(var i in result){
						row		= result[i];
						location_w[row.location_x]	= row.location_w;
						location_l[row.location_y]	= row.location_l;
						location_h[row.location_z]	= row.location_z;
					}

					for	(var location_position in location_w)
						parent.find('select.location_w').append('<option value="' + location_position + '">' + location_position + '</option>');
					for	(var location_position in location_l)
						parent.find('select.location_l').append('<option value="' + location_position + '">' + location_position + '</option>');
					for	(var location_position in location_h)
						parent.find('select.location_h').append('<option value="' + location_position + '">' + location_position + '</option>');

					parent.find('select.location_w').attr('disabled', false).css('background-color', '#ffffff');
					parent.find('select.location_l').attr('disabled', false).css('background-color', '#ffffff');
					parent.find('select.location_h').attr('disabled', false).css('background-color', '#ffffff');
					$(obj).closest('tr').find('input.stock').eq(idx).attr('disabled', false).css('background-color', '#ffffff');
					$(obj).closest('tr').find('input.badstock').eq(idx).attr('disabled', false).css('background-color', '#ffffff');
					$(obj).closest('tr').find('input.supply_price').eq(idx).attr('disabled', false).css('background-color', '#ffffff');

					if	(type != 'nosave')	scmLocationSendData($(obj));
				}
			}
		});
	}else{
		openDialogAlert('창고를 선택해 주세요.', 400, 170, function(){});
		$("option[value='" + $(obj).attr('whSeq') + "']", obj).attr('selected', true);
		return false;
	}
}

// 기초조정 데이터 추가
function add_tmp_revision_data(obj){
	var tmp_seq		= $("input[name='tmp_seq']").val();
	var goods_seq	= $(obj).closest('tr.option-rows').attr('goodsSeq');
	var option_seq	= $(obj).closest('tr.option-rows').find('input.option_seq').val();
	var url			= '../scm_process/add_tmp_revision_data?return_func=add_tmp_revision_data_result';
	url				+= '&tmp_seq=' + tmp_seq + '&goods_seq=' + goods_seq + '&option_seq=' + option_seq;

	loadingStart();
	actionFrame.location.href	= url;
}

// 기초조정 데이터 삭제
function remove_tmp_revision_data(obj){
	var tmp_seq			= $("input[name='tmp_seq']").val();
	var goods_seq		= $(obj).closest('tr').find("input[name='goods_seq[]']").val();
	var option_seq		= $(obj).closest('tr').find('input.option_seq').val();
	var revision_seq	= $(obj).closest('div').attr('rseq');
	$('div.revision-row-' + revision_seq).remove();
	var url				= '../scm_process/remove_tmp_revision_data?revision_seq=' + revision_seq;
	url					+= '&tmp_seq=' + tmp_seq + '&goods_seq=' + goods_seq + '&option_seq=' + option_seq;
	actionFrame.location.href	= url;
}

// 금액 변경에 따른 정산금액 계산
function calculate_commission(obj){
	var commission_price	= 0;
	var commission_type		= $("input[name='commission_type']").val();
	var commission_rate		= $(obj).closest('tr.option-rows').find('input.commission_rate').val();
	var consumer_price		= $(obj).closest('tr.option-rows').find('input.consumer_price').val();
	var price				= $(obj).closest('tr.option-rows').find('input.price').val();

	commission_price		= 0;
	if	(commission_rate > 0){
		commission_price		= commission_rate;
		if			(commission_type == 'SUCO'){
			commission_price	= consumer_price * (commission_rate * 0.01);
		}else if	(commission_type == 'SACO'){
			commission_price	= price * (commission_rate * 0.01);
		}
	}

	$(obj).closest('tr.option-rows').find('span.commission_price_lay').html(comma(commission_price));

	domSaverObj.sendData(obj);
}

// 입점사 변경 처리
function chg_provider(obj){
	if	($(obj).val() != 1 && $(obj).find('option:selected').attr('providerStatus') != 'Y'){
		openDialogAlert('판매중지 상태입니다.', 400, 170, function(){});
		$(obj).find("option[value='1']").attr('selected', true).change();
		$(obj).combobox('destroy').combobox();
	}

	$("input[name='provider_seq']").val($(obj).val());
	$("input[name='commission_type']").val($("option:selected",obj).attr('commissionType'));
	$("input[name='provider_name']").val($("option:selected",obj).text());
	if	($("option:selected",obj).attr('commissionType') == 'SUPR')	$('span.commission-type-unit').html('원');
	else															$('span.commission-type-unit').html('%');

	if	($(obj).val() > 1)	current_scm_use	= 'N';
	else					current_scm_use	= cfg_scm_use;

	if	(current_scm_use == 'Y' && $(obj).val() == 1)	$("input[name='stock_type']").val('scm');
	else												$("input[name='stock_type']").val('');

	$('tbody.quick-goods-regist-tbody').find('tr').remove();

	// layout 변경
	var colspan	= 2;
	if	($(obj).val() > 1){
		$('.admin-box').hide();
		$('.seller-box').show();
	}else{
		colspan	= parseInt(colspan) + 1;
		$('.admin-box').show();
		$('.seller-box').hide();
	}
	if	(current_scm_use == 'Y'){
		colspan	= parseInt(colspan) + 1;
		$('.scm-box').show();
	}else{
		$('.scm-box').hide();
	}
	$('table.quick-goods-regist-table').find('th.option-box-title-lay').attr('colspan', colspan);

	// 입점사 변경 시 폼 초기화
	save_tmp_goods_row('reset');
}

//---------------------------------- 3. 실시간 저장 관련 -------------------------------//

// 실시간 자동 저장 시 필수 데이터 추가
function addRequireData(obj){
	var tmp_seq			= $("input[name='tmp_seq']").val();
	var goods_seq		= $(obj).closest('tr.option-rows').find("input[name='goods_seq[]']").val();
	var option_seq		= $(obj).closest('tr.option-rows').find('input.option_seq').val();
	var revision_seq	= $(obj).closest('div').attr('rseq');
	var data	= {
		'tmp_seq'		: tmp_seq, 
		'goods_seq'		: (goods_seq)		? goods_seq		: '0', 
		'option_seq'	: (option_seq)		? option_seq	: '0', 
		'revision_seq'	: (revision_seq)	? revision_seq	: '0' 
	};

	return data;
}

// 재고관리 분류 실시간 저장 수동 전송
function scmCategorySendData(obj){
	var data	= {
		'tmp_seq'		: $("input[name='tmp_seq']").val(), 
		'scm_category'	: $(obj).val() 
	};
	domSaverObj.requestSendData(data);
}

// 재고관리 분류 실시간 저장 수동 전송
function scmLocationSendData(obj){
	var wh_seq				= $(obj).closest('div').find('select.warehouse').val();
	var location_w			= $(obj).closest('div').find('select.location_w').val();
	var location_l			= $(obj).closest('div').find('select.location_l').val();
	var location_h			= $(obj).closest('div').find('select.location_h').val();
	var location_position	= location_w + '-' + location_l + '-' + location_h;

	// 실시간 저장
	var data	= {
		'revision_seq'		: $(obj).closest('div').find('input.revision_seq').val(), 
		'wh_seq'			: wh_seq, 
		'location_position'	: location_position 
	};
	domSaverObj.requestSendData(data);
}

// row 추가에 따른 domsaver bind 추가
function addRowSetBind(trObj){
	$(trObj).find('input, select').each(function(){
		domSaverObj.set_bind($(this));
	});
}

// 일괄 적용 및 저장
function allBatchSave(obj, type){
	var tmp_seq				= $("input[name='tmp_seq']").val();
	var stock_type			= $("input[name='stock_type']").val();
	var data				= {'tmp_seq':tmp_seq,'stock_type':stock_type};
	var tag					= '';
	var value				= '';
	var target				= '';
	var scm					= {};
	var parentObj			= $(obj).closest('div.all-batch-lay');
	var targetObj			= $('tbody.quick-goods-regist-tbody');
	if	(type == 'goods'){
		var goodsSeq		= $(obj).closest('tr.option-rows').attr('goodsSeq');
		parentObj			= $(obj).closest('td');
		targetObj			= $('tbody tr.option-row-' + goodsSeq);
		data['goods_seq']	= goodsSeq;
	}

	parentObj.find('input,select').each(function(){
		tag				= $(this).prop('tagName');
		value			= $(this).val();
		if	(type == 'goods'){
			target		= $(this).attr('class');
			if	(tag == 'SELECT')	target	= target.replace('simple', '').replace(/\s/, '');
		}else{
			target		= $(this).attr('class');
			target		= target.match(/all\-batch\-[^\s]*/);
			target		= target[0].replace('all-batch-', '');
		}
		data[target]	= value;

		// 창고 및 로케이션은 밑에서 일괄 처리
		if		(target == 'warehouse')		scm['warehouse']	= value;
		else if	(target == 'location_w')	scm['location_w']	= value;
		else if	(target == 'location_l')	scm['location_l']	= value;
		else if	(target == 'location_h')	scm['location_h']	= value;
		else{
			if	(tag == 'SELECT'){
				targetObj.find('select.' + target).find("option[value='" + value + "']").attr('selected', true);
			}else{
				targetObj.find('input.' + target).val(value);
				if	(target == 'commission_rate' || target == 'consumer_price' || target == 'price'){
					calculate_commission(targetObj.find('input.' + target));
				}
			}
		}
	});

	// 로케이션 일괄 적용
	if	(scm['warehouse']){
		$('tbody.quick-goods-regist-tbody').find('select.warehouse').each(function(){
			if	($(this).val() == scm['warehouse']){
				$(this).closest('div').find('select.location_w').find("option[value='" + scm['location_w'] + "']").attr('selected', true);
				$(this).closest('div').find('select.location_l').find("option[value='" + scm['location_l'] + "']").attr('selected', true);
				$(this).closest('div').find('select.location_h').find("option[value='" + scm['location_h'] + "']").attr('selected', true);
			}
		});
	}

	var domSet	= domSaverObj.getSettingData();
	domSaverObj.changeFormAttr('post', '../goods_process/tmp_save_all_data', 'actionFrame');
	domSaverObj.requestSendData(data);
	// 임시 form의 속성을 원래 값으로 되돌림
	domSaverObj.changeFormAttr(domSet.method, domSet.action, domSet.target);
}

// cell단위 실시간 저장 처리
function domSaverSendData(obj){
	// 상품명 입력에 대한 처리
	if	($(obj).hasClass('goods_name')){
		if	($(obj).val())	$(obj).closest('tr.option-rows').find("input[name='goods_seq[]']").attr('disabled', false);
		else				$(obj).closest('tr.option-rows').find("input[name='goods_seq[]']").attr('disabled', true);
	}
	domSaverObj.sendData(obj);
}

// 실 상품으로 저장
function saveGoodsData(){
	if	(!$('body').find("form[name='tmpSaveForm']").attr('action')){
		$('body').append('<form name="tmpSaveForm" method="post" action="../goods_process/save_batch_regist" target="actionFrame"></form>');
	}
	var stock_type		= $("input[name='stock_type']").val();
	var goodsSeqArr		= new Array();
	var goodsSeqCnt		= 0;
	var i				= 0;
	var idx				= 0;
	var chkStock		= true;
	var chkStockLoop	= true;
	var chkCnt			= 0;
	var that			= '';
	var frmObj			= $("form[name='tmpSaveForm']");
	frmObj.append('<input type="hidden" name="tmp_seq" value="' + $("input[name='tmp_seq']").val() + '" />');
	$("input[name='goods_seq[]']").each(function(){
		that			= this;
		chkStockLoop	= true;
		if	($(this).closest('tr').find('input.goods_name').val() == $(this).closest('tr').find('input.goods_name').attr('title'))
			$(this).closest('tr').find('input.goods_name').val('');
		if	(!$(this).closest('tr').find('input.goods_name').val())	$(this).attr('checked', false);
		if	($(this).attr('checked')){
			// 불량재고가 재고 보다 많게 입력됬는지 체크
			if	(stock_type == 'scm'){
				$(this).closest('tr').find('input.stock').each(function(){
					idx				= $(this).closest('tr').find('input.stock').index($(this));
					if	($(this).closest('tr').find('input.badstock').eq(idx).val() > $(this).val()){
						chkStock		= false;
						chkStockLoop	= false;
						return false;
					}
				});
				if	(!chkStockLoop){
					goodsSeqArr[i]	= $(this).val();
					i++;
				}
			}else{
				if	($(this).closest('tr').find('input.badstock').val() > $(this).closest('tr').find('input.stock').val()){
					goodsSeqArr[i]	= $(this).val();
					chkStock		= false;
					i++;
				}
			}
			frmObj.append('<input type="hidden" name="goods_seq[]" value="' + $(this).val() + '" />');
			chkCnt++;
		}
	});
	if	(!chkStock){
		openDialogAlert('불량재고는 재고보다 많을 수 없습니다.', 400, 170, function(){});
		goodsSeqCnt		= goodsSeqArr.length;
		// 불량재고 초기화
		for	( var i = 0; i < goodsSeqCnt; i++){
			$('tr.option-row-' + goodsSeqArr[i]).find('input.badstock').val('0');
		}
		frmObj.find('input').remove();
		return false;
	}

	if	(chkCnt > 0){
		loadingStart();
		frmObj.submit();
		frmObj.find('input').remove();
	}else{
		openDialogAlert('등록할 상품을 선택하세요.', 400, 170, function(){});
		frmObj.find('input').remove();
		return false;
	}
}

$(document).ready(function(){
	// 입점사 선택
	$( "select[name='provider_seq_selector']" ).combobox().change(function(){
		chg_provider(this);
	});

	// 전체 일괄 체크
	$('input.allChk').on('click', function(){
		if	($(this).attr('checked'))	$('input.chk').attr('checked', true);
		else							$('input.chk').attr('checked', false);
	});

	// 실시간 자동 저장
	domSaverObj		= $("form[name='batchRegistFrm']").find('input, select').fmdomsaver({
						'useBind'		: false, 
						'action'		: '../goods_process/tmp_save_cell_data',
						'target'		: 'actionFrame', 
						'addDataFunc'	: 'addRequireData', 
						'ignore'		: ['goods_seq', 
											'warehouse', 
											'location_w', 
											'location_l', 
											'location_h', 
											'provider_seq_selector', 
											'commission_rate', 
											'consumer_price', 
											'price'
											]
						});
});
