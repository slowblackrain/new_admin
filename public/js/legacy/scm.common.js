var excelTableOption	= '';
var excelTableObj		= '';
var current_page		= '';
var current_list		= [];
var exchanges			= [];
var openerObj			= '';
var progObj				= '';
var common_no_img		= '/admin/skin/default/images/common/noimage_list.gif';

// 기본 submit
function scmSubmit(){
	loadingStart();
	$("form[name='detailForm']").submit();
}

//------------ ↑↑ common ↑↑------ ↓↓ excel table ↓↓--------------//

// 엑셀 테이블 공통 설정 부분
function commonExcelTable(kind, viewMode, goodsData, perpage, totalCount){

	current_page		= kind;
	excelTableOption	= {'viewMode' : viewMode, 'dataPer' : perpage, 'dataCount' : totalCount};
	var hideTh			= new Array();
	var tdHide			= new Array();
	if	(goodsData)	var data	= goodsDataArrayValues(goodsData);
	else			var data	= [];
	var thHeader		= ['checkbox', '상품번호', '옵션번호', '상품코드', '상품', '옵션', '로케이션', '수량', '단가', '금액', '부가세'];
	var thMerge			= [{'col':'5','span':'2'}, {'col':'8','span':'2'}, {'col':'9','span':'2'}, {'col':'11','span':'2'}];
	var tdWidth			= ['3%', '6%', '6%', '8%', '3%', '14%', '10%', '8%', '7%', '5%', '7%', '6%', '7%', '3%', '7%'];
	var tdBatch			= new Array();
	var tdClass			= 'its-td-align center';
	tdBatch[7]			= {'action':'batch_ea'};
	tdBatch[8]			= {'type':'supply_price','action':'batch_supply_price', 'currencylist':current_list};
	tdBatch[10]			= {'type':'tax', 'action':'batch_supply_tax'};
	var tdType			= [	{'type':'checkbox','boxName':'option_seq[]', 'tdClass':'its-td-align center'}, 
							{'type':'view','boxName':'goods_seq[]', 'tdClass':'its-td-align left pdl5'}, 
							{'type':'view','boxName':'optionSeq[]', 'tdClass':'its-td-align left pdl5'}, 
							{'type':'view','boxName':'goods_code[]', 'tdClass':'its-td-align left pdl5'}, 
							{'type':'image','boxName':'goods_image[]', 'tdClass':'its-td-align center','w':'30','h':'30','noimg':common_no_img}, 
							{'type':'autoComplete','boxName':'goods_name[]', 'tdClass':'its-td-align left pdl5','userFunc':'getGoodsData'}, 
							{'type':'autoComplete','boxName':'option_name[]', 'tdClass':'its-td-align left pdl5','userFunc':'getOptionData'}, 
							{'type':'hide','boxName':'location_position[]', 'tdClass':'its-td-align center'}, 
							{'type':'autoComplete','boxName':'location_code[]', 'tdClass':'its-td-align center','userFunc':'getLocationData'}, 
							{'type':'plain','boxName':'tmp_org_ea', 'tdClass':'its-td-align right pdr5'},
							{'type':'text','boxName':'ea[]', 'tdClass':'its-td-align right pdr5','userFunc':'calculateExcelTable'},
							{'type':'hide','boxName':'btn_lastdata[]', 'tdClass':'its-td-align center'}, 
							{'type':'view','boxName':'supply_price_type[]', 'tdClass':'its-td-align left pdl5'}, 
							{'type':'text','boxName':'krw_supply_price[]', 'tdClass':'its-td-align right pdr5','userFunc':'calculateExcelTable', 'focusFunc':'excelTableAfterInput'}, 
							{'type':'hide','boxName':'supply_price[]', 'tdClass':'its-td-align right pdr5'}, 
							{'type':'view','boxName':'krw_sum_supply_price[]', 'tdClass':'its-td-align right pdr5'}, 
							{'type':'checkbox','boxName':'tax[]', 'tdClass':'its-td-align center','userFunc':'calculateExcelTable','value':'과세','checked':true}, 
							{'type':'view','boxName':'supply_tax[]', 'tdClass':'its-td-align right pdr5'},
							{'type':'hide','boxName':'remain_ea[]', 'tdClass':'its-td-align right pdr5'}, 
							{'type':'hide','boxName':'add_reason[]', 'tdClass':'its-td-align left pdl5'}	];
	var totalRow		= [	['', '2', '수량', 'its-th-align center'],
							['', '1', '0', 'its-td-align right pdr5 total-ea'],
							['', '1', '금액', 'its-th-align center'],
							['', '2', '0', 'its-td-align right pdr5 total-supply-price'],
							['', '1', '부가세', 'its-th-align center'],
							['', '2', '0', 'its-td-align right pdr5 total-supply-tax'],
							['', '2', '합계금액', 'its-th-align center'],
							['', '4', '0', 'its-td-align right pdr5 total-price']];
	excelTableOption	= {
		'wrapWidth'			: '100%', 
		'viewMode'			: viewMode, 
		'rowController'		: false, 
		'tableClass'		: 'info-table-style', 
		'thClass'			: 'its-th-align center', 
		'tdClass'			: tdClass, 
		'colWidth'			: tdWidth, 
		'thHeader'			: thHeader, 
		'colBatch'			: tdBatch, 
		'colType'			: tdType, 
		'thMerge'			: thMerge, 
		'hideTh'			: hideTh, 
		'data'				: data, 
		'dataPer'			: perpage, 
		'dataCount'			: totalCount,
		'totalRow'			: totalRow, 
		'getNextDataFunc'	: 'getNextGoodsData', 
		'chgViewDataFunc'	: 'chgDisplayValue'
	};
	exceptExcelTable();
	excelTableObj		= $('div#excelTable').fmexceltable(excelTableOption);

	$('div#excelTable').find('tfoot').find('td').css({'border-top':'2px solid #000000','font-size':'15px', 'font-weight':'bold'});
	$('div#excelTable').find('tfoot').find('td.total-price').css({'color':'#d25900'});

	// 데이터가 있을 시
	$('div#excelTable').find("input[name='option_seq[]']").each(function(){
		if	($(this).val()){
			calculateExcelTable($(this).closest('tr').find("input[name='supply_price[]']"), '');
		}
	});

	area_help_tooltip($('div#excelTable'));
}


// 특정 페이지에 대한 예외처리
function exceptExcelTable(){
	// 발주 시 추가 정보
	if		(current_page == 'sorder'){
		excelTableOption.thHeader.push('미입고');
		excelTableOption.thHeader.push('비고');
		excelTableOption.hideTh[6]			= 'hidden';
		excelTableOption.colType[8].type	= 'hide';
		excelTableOption.colType[9].type	= 'hide';
		excelTableOption.thMerge[1]			= {'col':'8','span':'1'};
		excelTableOption.colWidth[11]		= '3%';
		excelTableOption.colWidth[13]		= '6%';
		excelTableOption.colType[18].type	= 'view';
		excelTableOption.colType[19].type	= 'view';
	}else if	(current_page == 'stockmove'){
		excelTableOption.colBatch[8]		= '';
		excelTableOption.colType[12].type	= 'view';
		excelTableOption.colType[13].type	= 'view';
		excelTableOption.colType[15].type	= 'view';
		excelTableOption.colType[17].type	= 'view';
	}else if	(current_page == 'warehousing'){
		if	($("input[name='except']").val() != 'E'){
			excelTableOption.colType[5].type		= 'view';
			excelTableOption.colType[6].type		= 'view';
			excelTableOption.colType[5].userFunc	= '';
			excelTableOption.colType[6].userFunc	= '';
		}
	}else if	(current_page == 'carryingout'){
		excelTableOption.hideTh[6]			= 'hidden';
		excelTableOption.colType[8].type	= 'hide';
		excelTableOption.colType[11].type	= 'view';
		excelTableOption.thMerge[2].span	= '3';
		excelTableOption.colWidth[7]		= '6%';
		excelTableOption.colWidth[8]		= '6%';
		excelTableOption.colWidth[9]		= '8%';
	}

	return excelTableOption;
}

// 상품 및 옵션 검색 전 필수값 체크
function chkBeforeSelectGoodsOptionData(params){

	var result	= {};

	// 거래처 선택이 있는 경우
	if	($("select[name='trader_seq']").attr('name')){
		if	(!$("select[name='trader_seq']").val()){
			openDialogAlert('거래처를 먼저 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		result['trader_seq']	= $("select[name='trader_seq']").val();
	}
	// 창고 선택이 있는 경우
	if	($("select[name='wh_seq']").attr('name')){
		if	(!$("select[name='wh_seq']").val()){
			openDialogAlert('창고를 먼저 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		params					+= '&wh_seq=' + $("select[name='wh_seq']").val();
		result['wh_seq']		= $("select[name='wh_seq']").val();
	}
	// 출고창고 선택이 있는 경우
	if	($("select[name='out_wh_seq']").attr('name')){
		if	(!$("select[name='out_wh_seq']").val()){
			openDialogAlert('출고창고를 먼저 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		params					+= '&out_wh_seq=' + $("select[name='out_wh_seq']").val();
		result['out_wh_seq']	= $("select[name='out_wh_seq']").val();
	}
	// 입고창고선택이 있는 경우
	if	($("select[name='in_wh_seq']").attr('name')){
		if	(!$("select[name='in_wh_seq']").val()){
			openDialogAlert('입고창고를 먼저 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		params					+= '&in_wh_seq=' + $("select[name='in_wh_seq']").val();
		result['in_wh_seq']		= $("select[name='in_wh_seq']").val();
	}
	// 발주서 검색 버튼이 있는 경우
	if	($('button#search_sorder_btn').attr('id')){
		if	(!$("input[name='sorder_seq']").val()){
			openDialogAlert('발주서를 먼저 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		params					+= '&sorder_seq=' + $("input[name='sorder_seq']").val();
		result['sorder_seq']		= $("select[name='sorder_seq']").val();
	}

	result['status']	= true;
	result['params']	= params;

	return result;
}

// 상품 검색
function getGoodsData(obj, process){

	var keyword		= encodeURIComponent($(obj).val());
	var chkVal		= chkBeforeSelectGoodsOptionData('');
	var params		= chkVal['params'];
	if	(!chkVal['status'])	return false;
	params			= 'keyword=' + keyword + params;

	$.ajax({
		type		: 'get',
		url			: '../scm/getGoodsListData',
		data		: params,
		dataType	: 'json', 
		global		: false,
		success		: function(result){
			var goods_name_list	= result.goods_name_list;
			var addParam		= result.data;
			process(goods_name_list, 'selectGoodsData', addParam);
		}
	});
}

// 상품 선택
function selectGoodsData(sIdx, obj, goodsName, params){

	// 초기화
	resetRowData(obj, '');

	// 상품 데이터 채우기
	$(obj).closest('tr').find("input[name='goods_seq[]']").val(params[sIdx].goods_seq);
	$(obj).closest('tr').find("input[name='goods_seq[]']").closest('td').find('span').html(params[sIdx].goods_seq);
	$(obj).closest('tr').find("input[name='goods_code[]']").val(params[sIdx].goods_code);
	$(obj).closest('tr').find("input[name='goods_code[]']").closest('td').find('span').html(params[sIdx].goods_code);
	$(obj).closest('tr').find("input[name='goods_name[]']").val(params[sIdx].goods_name);
	$(obj).closest('tr').find("input[name='goods_name[]']").closest('td').find('span').html(params[sIdx].goods_name);
	if	(params[sIdx].image){
		$(obj).closest('tr').find("input[name='goods_image[]']").val(params[sIdx].image);
		$(obj).closest('tr').find("input[name='goods_image[]']").closest('td').find('img').attr('src', params[sIdx].image);
	}else{
		$(obj).closest('tr').find("input[name='goods_image[]']").val(common_no_img);
		$(obj).closest('tr').find("input[name='goods_image[]']").closest('td').find('img').attr('src', common_no_img);
	}
}

// 옵션 검색
function getOptionData(obj, process){
	if	( $(obj).closest('tr').find("input[name='goods_seq[]']").val() > 0){
		var goods_seq	= $(obj).closest('tr').find("input[name='goods_seq[]']").val();
		var keyword		= encodeURIComponent($(obj).val());
		var chkVal		= chkBeforeSelectGoodsOptionData('');
		var params		= chkVal['params'];
		if	(!chkVal['status'])	return false;
		params			= 'goods_seq=' + goods_seq + '&keyword=' + keyword + params;
		if	(current_page == 'sorder'){
			params		+= '&trader_seq=' + chkVal['trader_seq'];
		}

		$.ajax({
			type		: 'get',
			url			: '../scm/getOptionListData',
			data		: params,
			dataType	: 'json', 
			global		: false,
			success		: function(result){
				var option_name_list	= result.option_name_list;
				var addParam			= result.data;
				process(option_name_list, 'selectOptionData', addParam);
			}
		});
	}else{
		openDialogAlert('먼저 상품을 검색하세요', 400, 150, function(){});
		process([], 'selectOptionData', []);
	}
}

// 옵션 선택
function selectOptionData(sIdx, obj, optionName, params){
	// 초기화
	resetRowData(obj, 'goods');
	var standVal		= params[sIdx].option_type + '|' + params[sIdx].option_seq;
	var chkDuple		= excelTableObj.chkDuplicateRow('option_seq[]', standVal);
	if	(chkDuple){
		openDialogAlert('중복된 상품입니다.', 400, 170, function(){});
		return false;
	}
	var option_code	= $(obj).closest('tr').find("input[name='goods_code[]']").val() + params[sIdx].option_code;

	$(obj).closest('tr').find("input[name='option_seq[]']").val(standVal);
	$(obj).closest('tr').find("input[name='optionSeq[]']").val(params[sIdx].option_seq);
	$(obj).closest('tr').find("input[name='optionSeq[]']").closest('td').find('span').html(params[sIdx].option_seq);
	$(obj).closest('tr').find("input[name='option_name[]']").val(params[sIdx].option_name);
	$(obj).closest('tr').find("input[name='option_name[]']").closest('td').find('span').html(params[sIdx].option_name);
	$(obj).closest('tr').find("input[name='goods_code[]']").val(option_code);
	$(obj).closest('tr').find("input[name='goods_code[]']").closest('td').find('span').html(option_code);
	$(obj).closest('tr').find("input[name='location_position[]']").val(params[sIdx].location_position);
	$(obj).closest('tr').find("input[name='location_code[]']").val(params[sIdx].location_code);
	$(obj).closest('tr').find("input[name='location_code[]']").closest('td').find('span').html(params[sIdx].location_code);
	$(obj).closest('tr').find("input[name='supply_price_type[]']").val('KRW');
	$(obj).closest('tr').find("input[name='supply_price_type[]']").closest('td').find('span').html('KRW');
	$(obj).closest('tr').find("input[name='ea[]']").val('1');
	$(obj).closest('tr').find("input[name='ea[]']").closest('td').find('span').html('1');

	var location_ea		= (params[sIdx].location_ea) ? params[sIdx].location_ea : '0';
	var location_badea	= (params[sIdx].location_badea) ? params[sIdx].location_badea : '0';
	var tmpHTML			= '<span class="location_ea" style="margin-right: 3px;">' + location_ea + '(' + location_badea + ')</span>';
	if	(!excelTableOption.viewMode){
		tmpHTML			+= '<span title="재고 (표시용 불량재고)<br/>재고 <= 창고에 있는 해당 상품의 재고수량<br/>(표시용 불량재고) <= 창고에 있는 해당 상품의 불량재고수량" class="helpicon"></span>';
	}
	$(obj).closest('tr').find('span.tmp_org_ea').html(tmpHTML);

	// 발주 페이지 추가 정보
	if			(current_page == 'sorder'){
		if	(params[sIdx].default_sorder_info){
			var supply_tax			= '0';
			var krw_supply_price	= params[sIdx].default_sorder_info.supply_price;
			var supply_price_type	= params[sIdx].default_sorder_info.supply_price_type;
			if	(supply_price_type != 'KRW'){
				krw_supply_price	= krw_exchange(supply_price_type, krw_supply_price);
			}
			if	(params[sIdx].default_sorder_info.use_supply_tax == 'Y'){
				supply_tax	= calculate_tax_price('KRW', krw_supply_price);
				$(obj).closest('tr').find("input[name='tax[]']").attr('checked', true);
			}else{
				$(obj).closest('tr').find("input[name='tax[]']").attr('checked', false);
			}

			$(obj).closest('tr').find("input[name='supply_price_type[]']").val(params[sIdx].default_sorder_info.supply_price_type);
			$(obj).closest('tr').find("input[name='supply_price_type[]']").closest('td').find('span').html(params[sIdx].default_sorder_info.supply_price_type);
			$(obj).closest('tr').find("input[name='supply_price[]']").val(params[sIdx].default_sorder_info.supply_price);
			$(obj).closest('tr').find("input[name='supply_price[]']").closest('td').find('span').html(params[sIdx].default_sorder_info.supply_price);
			$(obj).closest('tr').find("input[name='krw_supply_price[]']").val(krw_supply_price);
			$(obj).closest('tr').find("input[name='krw_supply_price[]']").closest('td').find('span').html(krw_supply_price);
			$(obj).closest('tr').find("input[name='krw_sum_supply_price[]']").val(krw_supply_price);
			$(obj).closest('tr').find("input[name='krw_sum_supply_price[]']").closest('td').find('span').html(krw_supply_price);
			$(obj).closest('tr').find("input[name='supply_tax[]']").val(supply_tax);
			$(obj).closest('tr').find("input[name='supply_tax[]']").closest('td').find('span').html(supply_tax);
		}
		if	(!$(obj).closest('tr').find("input[name='add_reason[]']").val()){
			$(obj).closest('tr').find("input[name='remain_ea[]']").val('0');
			$(obj).closest('tr').find("input[name='remain_ea[]']").closest('td').find('span').html('0');
			$(obj).closest('tr').find("input[name='add_reason[]']").val('수동');
			$(obj).closest('tr').find("input[name='add_reason[]']").closest('td').find('span').html('수동');
		}
	}else if	(!excelTableOption.viewMode && current_page == 'stockmove'){
		$(obj).closest('tr').find("input[name='supply_price[]']").val(params[sIdx].location_supply_price);
		$(obj).closest('tr').find("input[name='krw_supply_price[]']").val(params[sIdx].location_supply_price);
		$(obj).closest('tr').find("input[name='krw_supply_price[]']").closest('td').find('span').html(float_comma(params[sIdx].location_supply_price));
	}

	area_help_tooltip($(obj).closest('tr').find('span.tmp_org_ea').closest('td'));
	calculateExcelTable(obj, '');
}

// 입고 로케이션 검색
function getLocationData(obj, process){

	var goods_seq	= $(obj).closest('tr').find("input[name='goods_seq[]']").val();
	var optionStr	= $(obj).closest('tr').find("input[name='option_seq[]']").val();
	var keyword		= encodeURIComponent($(obj).val());
	var chkVal		= chkBeforeSelectGoodsOptionData('');
	var params		= chkVal['params'];
	if	(!chkVal['status'])	return false;
	var tmp			= optionStr.split('|');
	var option_type	= tmp[0];
	var option_seq	= tmp[1];
	if	(!goods_seq){
		openDialogAlert('상품을 선택해 주세요.', 400, 150, function(){});
		return false;
	}
	if	(!option_seq){
		openDialogAlert('옵션을 선택해 주세요.', 400, 150, function(){});
		return false;
	}
	params			= 'goods_seq=' + goods_seq + '&option_type=' + option_type + '&option_seq=' + option_seq + '&keyword=' + keyword + params;

	$.ajax({
		type		: 'get',
		url			: '../scm/getLocationList',
		data		: params,
		dataType	: 'json', 
		global		: false,
		success		: function(result){
			var code_list	= result.code_list;
			var addParam	= result.data;
			process(code_list, 'selectLocationData', addParam);
		}
	});
}

// 입고 로케이션 선택
function selectLocationData(sIdx, obj, location_code, positionList){
	$(obj).closest('tr').find("input[name='location_position[]']").val(positionList[sIdx]);
	$(obj).closest('tr').find("input[name='location_code[]']").val(location_code);
	$(obj).closest('tr').find("input[name='location_code[]']").closest('td').find('span').html(location_code);
}

// goodsData json 데이터의 key값을 리스트에 맞게 가공
function goodsDataArrayValues(data){
	var result	= [];
	if	(data.length > 0){

		for	( var d = 0; d < data.length; d++){
			var row						= data[d];
			var goods_seq				= row.goods_seq;
			var option_seq				= row.option_seq;
			var option					= row.option_type + '|' + option_seq;
			var goods_code				= (row.goods_code) ? row.goods_code : '';
			if	(row.option_code)	goods_code	+= row.option_code;
			var goods_image				= (row.image) ? row.image : common_no_img;
			var goods_name				= "["+row.goods_scode+"] "+row.goods_name;
			var option_name				= row.option_name;
			var location_position		= row.location_position;
			var location_code			= row.location_code;
			var ea						= (row.ea > 0) ? parseInt(row.ea) : 1;
			var location_ea				= (row.location_ea > 0) ? parseInt(row.location_ea) : 0;
			var location_badea			= (row.location_badea > 0) ? parseInt(row.location_badea) : 0;
			var whs_ea					= (row.whs_ea > 0) ? parseInt(row.whs_ea) : 0;
			var org_ea					= '<span class="location_ea" style="margin-right: 3px;">' + location_ea + '(' + location_badea + ')</span>';
			var supply_price_type		= row.supply_price_type ? row.supply_price_type : 'KRW';
			var krw_supply_price		= (row.krw_supply_price) ? row.krw_supply_price : '0';
			var supply_price			= (row.supply_price) ? row.supply_price : '0';
			var use_tax					= (row.use_tax == '과세') ? 'checked' : 'unchecked';
			var supply_tax				= (row.supply_tax) ? row.supply_tax : '0';
			var krw_sum_supply_price	= krw_supply_price * ea;
			var remain_ea				= '0';
			var add_reason				= '수동';
			if		(ea > 0)			remain_ea	= ea - whs_ea;
			if		(row.add_reason){
				if		(row.add_reason == '자동'){
					add_reason	= row.aooSeq;
				}else{
					add_reason	= row.add_reason;
				}
			}
console.log(excelTableOption.viewMode);
console.log(remain_ea);
			// 완료 상태가 아닌 경우
			if	(!excelTableOption.viewMode){
				org_ea					+= '<span title="재고 (표시용 불량재고)<br/>재고 <= 창고에 있는 해당 상품의 재고수량<br/>(표시용 불량재고) <= 창고에 있는 해당 상품의 불량재고수량" class="helpicon"></span>';
				if			(current_page == 'stockmove'){
					supply_price_type		= 'KRW';
					krw_supply_price		= (row.location_supply_price) ? row.location_supply_price : krw_supply_price;
					supply_price			= (row.location_supply_price) ? row.location_supply_price : supply_price;
					use_tax					= 'checked';
					supply_tax				= calculate_tax_price('KRW', (krw_supply_price * ea));
				}else if	(current_page == 'sorder'){
					remain_ea				= '-';
					if	(row.default_sorder_info){
						supply_price_type		= row.default_sorder_info.supply_price_type;
						supply_price			= row.default_sorder_info.supply_price;
						krw_supply_price		= row.default_sorder_info.supply_price;
						use_tax					= (row.default_sorder_info.use_supply_tax == 'Y') ? 'checked' : 'unchecked';
						if	(supply_price_type != 'KRW'){
							krw_supply_price	= krw_exchange(supply_price_type, krw_supply_price);
						}
						if	(use_tax == 'checked'){
							supply_tax	= calculate_tax_price('KRW', krw_supply_price);
						}
					}
				}
			}

			var tmp						= [	option, goods_seq, option_seq, 
											goods_code, goods_image, goods_name, 
											option_name, location_position, location_code, 
											org_ea, ea, '', supply_price_type, krw_supply_price, 
											supply_price, krw_sum_supply_price, use_tax, supply_tax,
											remain_ea,add_reason];
			result.push(tmp);
		}
	}

	return result;
}

// goodsData 다음페이지 데이터를 json형태로 가져온다.
function getNextGoodsData(page, perpage, process){
	var url		= '';
	var params	= '';
	switch(current_page){
		case 'revision':
			url		= '../scm/getRevisionGoodsData';
			params	= 'rno=' + $("input[name='revision_seq']").val() + '&wh_seq=' + $("select[name='wh_seq']").val();
		break;
		case 'stockmove':
			url		= '../scm/getRevisionGoodsData';
			params	= 'rno=' + $("input[name='revision_seq']").val() + '&wh_seq=' + $("select[name='wh_seq']").val();
		break;
		case 'sorder':
			url		= '../scm/getSorderGoodsData';
			params	= 'sono=' + $("input[name='sorder_seq']").val();
		break;
		case 'warehousing':
			url		= '../scm/getWarehousingGoodsData';
			params	= 'whsno=' + $("input[name='whs_seq']").val();
		break;
		case 'carryingout':
			url		= '../scm/getCarryingoutGoodsData';
			params	= 'crno=' + $("input[name='cro_seq']").val();
		break;
	}

	if	(url){
		$.ajax({
			type		: 'get',
			url			: url,
			data		: params + '&page=' + page + '&perpage=' + perpage,
			dataType	: 'json', 
			global		: false,
			success		: function(result){
				var data		= goodsDataArrayValues(result, '');
				process(data);
			}
		});
	}
}

// 노출용 값 변경
function chgDisplayValue(fld, val, obj){
	switch(fld){
		case 'goods_name[]':
			var supply_goods_name	= obj.closest('tr').find("input[name='supply_goods_name[]']").val();
			if	(supply_goods_name)	val	+= '<div style="color:#0269b5;">' + supply_goods_name + '</div>';
		break;
		case 'ea[]':
			val	= comma(val);
		break;
		case 'supply_price[]':
			val	= float_comma(val);
		break;
		case 'supply_tax[]':
			val	= float_comma(val);
		break;
	}

	return val;
}

// 현재 row 데이터 초기화
function resetRowData(obj, exceptType){

	var val				= '';
	var resetStatus		= true;
	var imgPattern		= new RegExp('image');
	var intPattern		= new RegExp('(price|ea)');
	var exceptPattern	= new RegExp('(' + exceptType + ')');

	$(obj).closest('tr').find('td').each(function(){
		val			= '';
		resetStatus	= true;
		if			(imgPattern.test($(this).find('input').attr('name'))){
			val	= common_no_img;
		}else if	(intPattern.test($(this).find('input').attr('name'))){
			val	= '0';
		}

		// 초기화 제외 대상 처리
		if	(exceptType){
			if	(exceptPattern.test($(this).find('input').attr('name'))){
				resetStatus	= false;
			}
		}

		if	(resetStatus){
			if	($(this).find('span'))	$(this).find('span').html(val);
			if	($(this).find('img'))	$(this).find('img').attr('src', val);
			if	($(this).find('input'))	$(this).find('input').html(val);
		}
	});

	calculateExcelTableTotal();
}

// 엑셀 테이블 상품 초기화
function resetExcelTableData(){
	if	($('select.out_wh_seq').attr('name') && $('select.in_wh_seq').attr('name')){
		if	($('select.out_wh_seq').val() == $('select.in_wh_seq').val()){
			openDialogAlert('출고창고와 입고창고를 서로 다른 창고로 선택해 주세요.', 400, 150, function(){});
			$('select.out_wh_seq').find('option:selected').attr('selected', false);
			$('select.in_wh_seq').find('option:selected').attr('selected', false);
			$('select.out_wh_seq').find('option').eq(0).attr('selected', 'selected');
			$('select.in_wh_seq').find('option').eq(0).attr('selected', 'selected');
		}
	}

	$("input[name='option_seq[]']").each(function(){
		$(this).closest('tr').remove();
	});
}

// 거래처/창고 변경에 따른 상품 목록 초기화
function resetExcelTableList(){
	resetExcelTableData();
	excelTableObj.addDefaultRow('', []);
	calculateExcelTableTotal();
}

// 화폐선택 및 단가입력
function openSupplyPrice(obj){
	var supply_type		= $(obj).closest('tr').find("input[name='supply_price_type[]']").val();
	var supply_price	= $(obj).closest('tr').find("input[name='supply_price[]']").val();
	var userParams		= $(obj).closest('tr').find("input[name='goods_seq[]']").val() + '|'
						+ $(obj).closest('tr').find("input[name='option_seq[]']").val();

	if	(!$(obj).closest('td').find('div.select-currency-popup').html()){
		var html		= '<div class="select-currency-popup" style="position:absolute;top:33px;left:0;width:170px;padding:3px;border:1px solid #555555;background-color:#fff;">';
		html			+= '<div style="font-weight:bold;text-align:right;padding-right:5px;"><span style="cursor:pointer;" onclick="closeSupplyPrice(this);">X</span></div>';
		html			+= '<select class="select-currency-selectbox" onchange="supplyTypeChange(this);">';
		for	( var currency in exchanges){
			html		+= '<option value="' + currency + '"';
			if	(supply_type == currency)	html		+= ' selected';
			html		+= '>' + currency + '</option>';
		}
		html			+= '</select>';
		html			+= '&nbsp;&nbsp;';
		html			+= '<input type="text" size="5" class="line select-currency-inputprice" value="' + supply_price + '" />';
		html			+= '&nbsp;&nbsp;&nbsp;';
		html			+= '<span class="btn small cyanblue"><button type="button" onclick="insertSupply(this, \'' + userParams + '\');">입력</button></span>';
		html			+= '</div>';
		$(obj).closest('td').append(html);
	}
	supplyTypeChange($(obj).closest('td').find('div.select-currency-popup').find('select.select-currency-selectbox'));
	$(obj).closest('td').css('position', 'relative');
	$(obj).closest('td').find('div.select-currency-popup').show();
}

// 화폐선택 및 단가입력 팝업 닫기
function closeSupplyPrice(obj){
	$(obj).closest('div.select-currency-popup').hide();
	$(obj).closest('td').css('position', '');
}

// 화폐선택에 따른 창 변경
function supplyTypeChange(obj){
	var parentObj	= $(obj).closest('div.select-currency-popup');
	if	(!parentObj.find('div.view-exchange-info').html()){
		var html	= '<div class="view-exchange-info" style="margin:3px 0;"></div>';
		parentObj.append(html);
	}
	if	($(obj).val() == 'KRW'){
		parentObj.find('div.view-exchange-info').html('1원 = 1').hide();
	}else{
		parentObj.find('div.view-exchange-info').html('1' + currencys[$(obj).val()].type_name + ' = ' + exchanges[$(obj).val()].currency_exchange).show();
	}
}

// 선택된 화폐와 금액 입력
function insertSupply(obj, optioninfo){
	var goods_seq		= '';
	var option_type		= '';
	var option_seq		= '';
	var supply_type		= '';
	var supply_price	= '';
	if		($(obj).closest('div').find('select.select-currency-selectbox').val())	supply_type		= $(obj).closest('div').find('select.select-currency-selectbox').val();
	else if	($(obj).closest('td').find('input.supply-type').val())					supply_type		= $(obj).closest('td').find('input.supply-type').val();
	if		($(obj).closest('div').find('input.select-currency-inputprice').val())	supply_price	= $(obj).closest('div').find('input.select-currency-inputprice').val();
	else if	($(obj).closest('td').find('input.supply-price').val())					supply_price	= $(obj).closest('td').find('input.supply-price').val();
	if			(optioninfo){
		var optionArr	= optioninfo.split('|');
		goods_seq		= optionArr[0];
		option_type		= optionArr[1];
		option_seq		= optionArr[2];
	}else if	($(obj).closest('td').find('input.goods-seq').val()){
		goods_seq		= $(obj).closest('td').find('input.goods-seq').val();
		option_type		= $(obj).closest('td').find('input.option-type').val();
		option_seq		= $(obj).closest('td').find('input.option-seq').val();

		if	($(obj).closest('td').find('input.target-id').val()){
			closeDialog($(obj).closest('td').find('input.target-id').val());
		}
	}

	var jsonData	= {	'goods_seq'		: goods_seq, 
						'option_type'	: option_type, 
						'option_seq'	: option_seq, 
						'supply_type'	: supply_type, 
						'supply_price'	: supply_price};
	setSupplyPrice($(obj).closest('tr'), jsonData);
	closeSupplyPrice(obj);
}

// 선택된 화폐 및 금액에 따른 입력 처리
function setSupplyPrice(trObj, jsonData){
	var goods_seq			= jsonData.goods_seq;
	var option_type			= jsonData.option_type;
	var option_seq			= jsonData.option_seq;
	var supply_type			= jsonData.supply_type;
	var supply_price		= jsonData.supply_price;
	var supply_type_str		= '';
	var krw_supply_price	= 0;
	if	(!trObj.find("input[name='option_seq[]']").attr('name')){
		trObj	= $('div#excelTable').find("input[value='" + option_type + "|" + option_seq + "']").closest('tr');
	}

	trObj.find("input[name='supply_price[]']").val(supply_price);
	trObj.find("input[name='supply_price_type[]']").val(supply_type);
	supply_type_str		= (supply_type == 'KRW') ? supply_type : supply_type + ' ' + supply_price;
	trObj.find("input[name='supply_price[]']").closest('td').find('div.display-supply-type').html(supply_type_str);
	krw_supply_price	= (supply_type == 'KRW') ? supply_price : krw_exchange(supply_type, supply_price);
	trObj.find("input[name='krw_supply_price[]']").val(krw_supply_price);
	trObj.find("input[name='krw_supply_price[]']").closest('td').find('span').html(comma(krw_supply_price));

	calculateExcelTable(trObj.find("input[name='supply_price[]']"), '');
}

// 입력 전 처리가 필요한 영역의 경우
function excelTableAfterInput(obj){
	if	(obj.attr('name') == 'krw_supply_price[]'){
		var supply_price		= obj.closest('tr').find("input[name='supply_price[]']").val();
		var supply_price_type	= obj.closest('tr').find("input[name='supply_price_type[]']").val();
		if	(supply_price_type != 'KRW'){
			obj.val(supply_price);
		}
	}
}

// 금액 변경에 따른 금액, 부가세, 합계 계산
function calculateExcelTable(obj, val){
	var goods_seq		= $(obj).closest('tr').find("input[name='goods_seq[]']").val();
	var optioninfo		= $(obj).closest('tr').find("input[name='option_seq[]']").val();
	var tmpArr			= optioninfo.split('|');
	var option_type		= tmpArr[0];
	var option_seq		= tmpArr[1];
	var currency		= $(obj).closest('tr').find("input[name='supply_price_type[]']").val();
	var ea				= $(obj).closest('tr').find("input[name='ea[]']").val();
	var supply_price	= $(obj).closest('tr').find("input[name='supply_price[]']").val();
	var chk_tax			= $(obj).closest('tr').find("input[name='tax[]']").attr('checked');	
	var krw_price		= $(obj).closest('tr').find("input[name='krw_supply_price[]']").val();
	if	(!currency)		currency	= 'KRW';
	if	($(obj).attr('name') == 'krw_supply_price[]')	supply_price	= val;
	if	(currency == 'KRW')	krw_price	= supply_price;
	else					krw_price	= krw_exchange(currency, supply_price);
	var sum_price		= krw_price * ea;
	var tax_price		= (chk_tax) ? calculate_tax_price('KRW', sum_price) : 0;
	var supply_type_str	= currency;
	if	(currency != 'KRW')	supply_type_str		= currency + ' ' + supply_price;

	var tmpHTML			= supply_type_str;
	if	(!excelTableOption.viewMode && current_page != 'stockmove'){
		tmpHTML				= '<div class="display-supply-type click-lay" onclick="openSupplyPrice(this);">' + supply_type_str + '</div>';
		var lastdataHTML	= '<div class="click-lay" onclick="openSelectWarehousing(\'lastWarehousing\', \'insertSupply\', \'' + goods_seq + '\', \'' + option_type + '\', \'' + option_seq + '\', \'5\');">최근입고</div>'
							+ '<div class="click-lay" onclick="openGoodsWarehouseStock(\'warehouseInfo\', \'insertSupply\', \'' + goods_seq + '\', \'' + option_type + '\', \'' + option_seq + '\');">평균단가</div>';
		$(obj).closest('tr').find("input[name='btn_lastdata[]']").closest('td').find('span').html(lastdataHTML);
	}
	$(obj).closest('tr').find("input[name='supply_price_type[]']").val(currency);
	$(obj).closest('tr').find("input[name='supply_price_type[]']").closest('td').find('span').eq(0).html(tmpHTML);
	$(obj).closest('tr').find("input[name='ea[]']").val(ea);
	$(obj).closest('tr').find("input[name='ea[]']").closest('td').find('span').html(comma(ea));
	$(obj).closest('tr').find("input[name='supply_price[]']").val(supply_price);
	$(obj).closest('tr').find("input[name='supply_price[]']").closest('td').find('span').html(comma(supply_price));
	$(obj).closest('tr').find("input[name='krw_supply_price[]']").val(krw_price);
	$(obj).closest('tr').find("input[name='krw_supply_price[]']").closest('td').find('span').html(comma(krw_price));
	$(obj).closest('tr').find("input[name='krw_sum_supply_price[]']").val(sum_price);
	$(obj).closest('tr').find("input[name='krw_sum_supply_price[]']").closest('td').find('span').html(comma(sum_price));
	$(obj).closest('tr').find("input[name='supply_tax[]']").val(tax_price);
	$(obj).closest('tr').find("input[name='supply_tax[]']").closest('td').find('span').html(comma(tax_price));

	// 발주 페이지 추가 정보
	if	(current_page == 'sorder'){
		if	($(obj).closest('tr').find("input[name='add_reason[]']").val() != '수동'){
			var tmp			= $(obj).closest('tr').find("input[name='add_reason[]']").val().split(',');
			var tmpLen		= tmp.length;
			var reason_str	= '<a href="/admin/order/catalog?keyword=' + goods_seq + '" target="_blank" style="color:#04baff;">주문(' + comma(tmpLen) + ')</a>';
			$(obj).closest('tr').find("input[name='add_reason[]']").closest('td').find('span').html(reason_str);
		}
	}

	calculateExcelTableTotal();
}

// 금액 변경에 따른 금액, 부가세, 합계 계산
function calculateExcelTableTotal(){
	var total_ea			= 0;
	var total_supply_price	= 0;
	var total_supply_tax	= 0;
	$('div#excelTable').find("input[name='option_seq[]']").each(function(){
		if	($(this).val()){
			total_ea			= parseInt(total_ea) + parseInt($(this).closest('tr').find("input[name='ea[]']").val());
			total_supply_price	= parseInt(total_supply_price) + parseInt($(this).closest('tr').find("input[name='krw_sum_supply_price[]']").val());
			total_supply_tax	= parseInt(total_supply_tax) + parseInt($(this).closest('tr').find("input[name='supply_tax[]']").val());
		}
	});
	var total_price			= parseInt(total_supply_price) + parseInt(total_supply_tax);

	$('div#excelTable').find('tfoot').find('td.total-ea').html(comma(total_ea));
	$('div#excelTable').find('tfoot').find('td.total-supply-price').html(comma(total_supply_price));
	$('div#excelTable').find('tfoot').find('td.total-supply-tax').html(comma(total_supply_tax));
	$('div#excelTable').find('tfoot').find('td.total-price').html(comma(total_price));
}

// 수량 일괄 변경
function batch_ea(obj, type){
	var ea	= $(obj).closest('th').find('input.excel_table_batch_val').val();
	$(obj).closest('table').find("input[name='option_seq[]']").each(function(){
		if	($(this).val()){
			$(this).closest('tr').find("input[name='ea[]']").val(ea);
			$(this).closest('tr').find("input[name='ea[]']").closest('td').find('span').html(comma(ea));

			calculateExcelTable($(this), '');
		}
	});
}

// 부가세 체크박스 일괄 변경
function batch_supply_tax(obj, userParams){
	var chk		= $(obj).attr('checked');
	$(obj).closest('table').find("input[name='option_seq[]']").each(function(){
		if	($(this).val()){
			$(this).closest('tr').find("input[name='tax[]']").attr('checked', chk);

			calculateExcelTable($(this), '');
		}
	});
}

// 매입가 일괄 변경
function batch_supply_price(obj, type){
	var supply_type		= $(obj).closest('th').find('select.excel_table_batch_supply_type').val();
	var supply_price	= $(obj).closest('th').find('input.excel_table_batch_val').val();
	var optionArr		= new Array();
	$(obj).closest('table').find("input[name='option_seq[]']").each(function(){
		if	($(this).val()){
			optionArr		= $(this).val().split('|');
			var jsonData	= {	'goods_seq'		: $(this).closest('tr').find("input[name='goods_seq[]']").val(), 
								'option_type'	: optionArr[0], 
								'option_seq'	: optionArr[1], 
								'supply_type'	: supply_type, 
								'supply_price'	: supply_price};
			setSupplyPrice($(this).closest('tr'), jsonData);
			calculateExcelTable($(this).closest('tr').find("input[name='supply_price[]']"), '');
		}
	});
}

// 상품 검색 팝업
function selectGoodsPopup(type){

	var chkVal		= chkBeforeSelectGoodsOptionData('');
	if	(!chkVal['status'])	return false;

	var params		= chkVal['params'];
	if	(current_page == 'sorder')
		params	+= (params) ? '&trader_seq=' + chkVal['trader_seq'] : 'trader_seq=' + chkVal['trader_seq'];
	selectGoodsPopupContents('1', params, type, '');

	openDialog('상품 검색', 'selectGoodsPopup', {'width':800,'height':800});
}

// 상품 검색 팝업 contents
function selectGoodsPopupContents(page, params, userParam1, userParam2){

	if	(params)		params	+= '&page=' + page;
	else				params	= 'page=' + page;
	if	(userParam1)	params += '&userParam1=' + userParam1;
	if	(userParam2)	params += '&userParam2=' + userParam2;
	params	+= '&pagedisplay=ajax&moveUserFunc=selectGoodsPopupContents&selectUserFunc=selectedPopupGoods';

	$.ajax({
		type	: 'get',
		url		: '../scm/select_goods_popup',
		data	: params,
		success	: function(result){
			$('div#' + 'selectGoodsPopup').html(result);
		}
	});
}

// 상품 검색 팝업 상품 선택 처리
function selectedPopupGoods(jsonData, userParam1, userParam2){
	var result	= [];
	result.push(jsonData);

	var excelResult		= goodsDataArrayValues(result);
	var chkDuple		= excelTableObj.chkDuplicateRow('option_seq[]', excelResult[0][0]);
	if	(chkDuple){
		openDialogAlert('중복된 상품입니다.', 400, 170, function(){});
		return false;
	}
	excelTableObj.delDefaultRow('option_seq[]');
	var trObj	= excelTableObj.addDefaultRow('datas', excelResult[0]);
	excelTableObj.addDefaultRow('', []);

	calculateExcelTable(trObj.find("input[name='option_seq[]']"), '');
	area_help_tooltip(trObj);
}

// 바코드 스캔 상품 데이터 입력 2016.05.24 pjw
function scanBarcodeData(type){
	var barcode_reader	= $('input[name="barcode_reader"]').val();
	if(barcode_reader == ''){
		openDialogAlert('바코드를 입력해 주세요.', 400, 150, function(){});
		return false;
	}

	var chkVal		= chkBeforeSelectGoodsOptionData('');
	if	(!chkVal['status'])	return false;

	var params		= chkVal['params'];
	if	(type)	params += '&userParam1=' + type;
	params			+= '&barcode_reader='+barcode_reader;
	if	(current_page == 'sorder')
		params	+= (params) ? '&trader_seq=' + chkVal['trader_seq'] : 'trader_seq=' + chkVal['trader_seq'];

	$.ajax({
		type	: 'get',
		url		: '../scm/get_goods_data_barcode',
		data	: params,
		async	: false,
		success	: function(result){
			if(result && result != 'null'){
				var jsonObj = JSON.parse(result);

				for(var i=0; i<jsonObj.length; i++){
					pushBarcodeData(JSON.parse(jsonObj[i]), type);	
				}
			}else{
				openDialogAlert('해당 창고에 바코드 상품이 없습니다.', 400, 150, function(){});
				return false;
			}
		}
	});
}

// 상품 검색 팝업 상품 선택 처리
function pushBarcodeData(jsonData, userParam1, addCondition){
	var result	= [];
	result.push(jsonData);

	var excelResult		= goodsDataArrayValues(result);
	var chkDuple		= excelTableObj.chkDuplicateRow('option_seq[]', excelResult[0][0]);
	if	(chkDuple){
		// 수량 증가
		var trObj		= $(excelTableObj).find("input[name='option_seq[]'][value='" + excelResult[0][0] + "']").closest('tr');
		var ea			= trObj.find("input[name='ea[]']").val();
		ea++;
		trObj.find("input[name='ea[]']").val(ea);
		trObj.find("input[name='ea[]']").closest('td').find('span').html(comma(ea));
	}else{
		// 상품 추가
		excelTableObj.delDefaultRow('option_seq[]');
		var trObj	= excelTableObj.addDefaultRow('datas', excelResult[0]);
		excelTableObj.addDefaultRow('', []);
	}

	calculateExcelTable(trObj.find("input[name='option_seq[]']"), '');
	area_help_tooltip(trObj);
}

// 선택된 상품 row 삭제
function delGoodsRow(){
	$('div#excelTable').find("input[name='option_seq[]']").each(function(){
		if	($(this).attr('checked'))	$(this).closest('tr').remove();
	});
	// 모두 삭제 시 빈 Row 추가
	if	(!($('div#excelTable').find("input[name='option_seq[]'][value='']").length > 0)){
		excelTableObj.addDefaultRow('', []);
	}

	calculateExcelTableTotal();
}

// page submit 시 excel table 관련 선처리
function excelTableSubmit(){
	var tax	= '';
	var cnt	= 0;
	$('div#excelTable').find("input[name='option_seq[]']").each(function(){
		if	($(this).val()){
			$(this).attr('checked', true);
			tax	= '비과세';
			if	($(this).closest('tr').find("input[name='tax[]']").attr('checked'))	tax	= '과세';
			if	($(this).closest('tr').find("input[name='hide_tax[]']").attr('name')){
				$(this).closest('tr').find("input[name='hide_tax[]']").val(tax);
			}else{
				$(this).closest('tr').find("input[name='tax[]']").closest('td').append('<input type="hidden" name="hide_tax[]" orgTagName="tax" value="' + tax + '" />');
			}
			cnt++;
		}else{
			$(this).attr('checked', false);
		}
	});

	if	(cnt > 0){
		scmSubmit();
	}else{
		openDialogAlert('선택된 상품이 없습니다.', 400, 150, function(){});
		return false;
	}
}

//------------ ↑↑ excel table ↑↑------ ↓↓ config ↓↓--------------//

// datepicker disable 
function selectDatePicker(obj, inst){
	if	($(obj).closest('td').find('input.use_default_revision_date').attr('checked'))	return true;
	else																				return false;
}

// input disable
function chk_open_date(obj){
	if	($(obj).attr('checked')){
		$(obj).closest('td').find('input.datepicker').css('background-color', '#ffffff');
	}else{
		$(obj).closest('td').find('input.datepicker').css('background-color', '#ececec');
	}
}

// 설정 저장 처리
function configSubmit(){
	var dateObj			= new Date();
	var year			= dateObj.getFullYear();
	var month			= dateObj.getMonth() + 1;
	if	(month < 10)	month	= '0' + month;
	var day				= dateObj.getDate();
	if	(day < 10)		day		= '0' + day;
	var toDate			= year + '' + month + '' + day;
	var toDate_view		= year + '-' + month + '-' + day;
	var tmpDate			= '';
	var confirmMsg		= '';
	var confirmHeight	= 300;

	if	($("input[name='use_scm_setting_default_date']").attr('checked')){
		if	(!confirmMsg)	confirmMsg	= '기초일자는 설정 후 수정이 불가합니다.<br/>';
		tmpDate		= $("input[name='scm_setting_default_date']").val().replace(/\-/g, '');
		if			(!$("input[name='scm_setting_default_date']").val()){
			openDialogAlert('기초재고 기초일자를 입력해 주세요', 400, 150, function(){});
			return false;
		}else if	(tmpDate >= toDate){
			openDialogAlert('기초재고 기초일자를<br/>오늘(' + toDate_view + ') 이전날짜로 입력해 주세요', 400, 150, function(){});
			return false;
		}
		confirmMsg	+= '<br/>지정한 기초재고 기초일자는 <br/> ' + $("input[name='scm_setting_default_date']").val() + ' 입니다.<br/>';
	}
	if	($("input[name='use_scm_setting_account_date']").attr('checked')){
		if	(!confirmMsg)	confirmMsg	= '기초일자는 설정 후 수정이 불가합니다.<br/>';
		tmpDate	= $("input[name='scm_setting_account_date']").val().replace(/\-/g, '');
		if			(!$("input[name='scm_setting_account_date']").val()){
			openDialogAlert('기초잔액 기초일자를 입력해 주세요', 400, 150, function(){});
			return false;
		}else if	(tmpDate >= toDate){
			openDialogAlert('기초잔액 기초일자를<br/>오늘(' + toDate_view + ') 이전날짜로 입력해 주세요', 400, 150, function(){});
			return false;
		}
		confirmMsg	+= '<br/>지정한 기초잔액 기초일자는 <br/> ' + $("input[name='scm_setting_account_date']").val() + ' 입니다.<br/>';
		if	($("input[name='use_scm_setting_default_date']").attr('checked')){
			confirmHeight	= 400;
		}
	}

	if	(confirmMsg){
		confirmMsg	+= '기초일자를 설정하시겠습니까?';
		openDialogConfirm(confirmMsg, 400, confirmHeight, function(){
			$("input[name='scm_setting_default_date']").attr('disabled', false);
			$("input[name='scm_setting_account_date']").attr('disabled', false);
			scmSubmit();
		}, function(){
			return false;
		});
	}else{
		scmSubmit();
	}
}

//------------ ↑↑ config ↑↑------ ↓↓ store ↓↓--------------//

// 매입기준정보 설정 전체 open/close
function allChkStoreWarehouse(obj){
	var chkStatus	= $(obj).attr('checked');
	$("input[name='chk_wh[]']").each(function(){
		if	(chkStatus) $(this).attr('checked', true);
		else			$(this).attr('checked', false);
	});
}

//------------ ↑↑ store ↑↑------ ↓↓ traders ↓↓--------------//

// 거래처 아이디 중복 체크
function chkTraderId(){

	var trader_id	= $("input[name='trader_id']").val();
	if	(!trader_id){
		openDialogAlert('아이디를 입력해 주세요.', 400, 150, function(){});
		return false;
	}
	$.ajax({
		type	: 'post',
		url		: '../scm_process/chk_duplication_trader_id',
		data	: 'trader_id=' + trader_id,
		success	: function(result){
			if	(result > 0){
				openDialogAlert('사용 가능한 아이디입니다.', 400, 150, function(){});
			}else{
				openDialogAlert('중복된 아이디입니다.', 400, 150, function(){$("input[name='trader_id']").focus();});
			}
		}
	});

	return false;
}

// 거래처 정보 저장
function submitTrader(){

	var that			= '';
	var submit_status	= true;
	var trader_seq		= $("input[name='trader_seq']").val();

	// 필수값 체크
	$("form[name='detailForm']").find('input,select,textarea').each(function(){
		if	($(this).attr('isrequired') && !$(this).val()){
			that	= this;
			openDialogAlert($(this).attr('isrequired') + '을(를) 입력해 주세요.', 400, 150, function (){$(that).focus();});
			submit_status	= false;
			return false;
		}
	});
	if	(submit_status){
		if	(!trader_seq || (trader_seq > 0 && $('input.chkPasswdModify').attr('checked'))){
			if	(!chkPasswdRules($("input[name='trader_pw']"))){
				submit_status	= false;
			}
			if	($("input[name='trader_pw']").val() != $("input[name='trader_pw_cf']").val()){
				var msg	= '비밀번호와 비밀번호 확인이 일치하지 않습니다.';
				if	(trader_seq > 0)	msg	= '비밀번호 변경과 비밀번호 변경 확인이 일치하지 않습니다.';
				openDialogAlert(msg, 500, 150, function(){});
				submit_status	= false;
			}
		}
	}
	if	(submit_status){
		if	(!trader_seq && $("input[name='use_trader_account']").attr('checked')){
			if	(!$("input[name='act_price']").val()){
				openDialogAlert('기초잔액을 입력해 주세요.', 400, 150, function (){
					$("input[name='act_price']").focus();
				});
				submit_status	= false;
				return false;
			}
		}
	}

	if	(submit_status){
		scmSubmit();
	}
}

// 거래처분류 직접입력 선택 시 form변경
function chgTraderGroup(obj){
	if	($(obj).val() == 'direct'){
		$(obj).closest('td').find('span').show();
		$("input[name='trader_group']").val('');
	}else{
		$(obj).closest('td').find('span').hide();
		$("input[name='trader_group']").val($(obj).val());
	}
}

// 비밀번호 변경 폼 open/close
function chgTraderPasswd(){
	if	($('input.chkPasswdModify').attr('checked')){
		$('div.chgPasswdLay').show();
		$('div.chgPasswdLay').find('input').attr('disabled', false);
		$('div.chgPasswdLay').find("input[name='manager_pw']").attr('isrequired', '현재 비밀번호');
		$('div.chgPasswdLay').find("input[name='trader_pw']").attr('isrequired', '비밀번호 변경');
		$('div.chgPasswdLay').find("input[name='trader_pw_cf']").attr('isrequired', '비밀번호 변경 확인');
	}else{
		$('div.chgPasswdLay').hide();
		$('div.chgPasswdLay').find('input').attr('disabled', true);
		$('div.chgPasswdLay').find("input[name='manager_pw']").removeAttr('isrequired');
		$('div.chgPasswdLay').find("input[name='trader_pw']").removeAttr('isrequired');
		$('div.chgPasswdLay').find("input[name='trader_pw_cf']").removeAttr('isrequired');
	}
}

// 비밀번호 유효성 체크
function chkPasswdRules(obj){
	var passwd	= $(obj).val();

	// 자릿수 체크
	if	(passwd.length < 10){
		openDialogAlert('비밀번호는 10자 이상 입력해 주시기 바랍니다.', 400, 150, function(){});
		return false;
	}
	if	(passwd.length > 20){
		openDialogAlert('비밀번호는 20자 이하로 입력해 주시기 바랍니다.', 400, 150, function(){});
		return false;
	}
	// 문자열 혼합 체크
	var mixCnt	= 0;
	if	(passwd.search(/[0-9]/) != -1)							mixCnt++;
	if	(passwd.search(/[a-zA-Z]/) != -1)						mixCnt++;
	if	(passwd.search(/[^0-9a-zA-Zㄱ-ㅎ가-힣ㅏ-ㅣ]/) != -1)		mixCnt++;
	if	(!(mixCnt > 1)){
		openDialogAlert('비밀번호는 영문 대소문자, 숫자, 특수문자 중 2가지 이상의 조합으로 입력해 주세요.', 600, 150, function(){});
		return false;
	}
	// 허용 문자열 체크
	if	(passwd.search(/[^0-9a-zA-Z\!\#\$\%\&\(\)\*\+\-\/\:\<\=\>\?\@\[\＼\]\^\_\{\|\}\~]/) != -1){
		openDialogAlert('허용되지 않는 문자가 있습니다.', 400, 150, function(){});
		return false;
	}

	return true;
}

// 거래처 기초 정산 사용 체크
function useTraderAccount(obj){
	if	($(obj).attr('checked')){
		if	(!$(obj).closest('tbody').find('input.set_account_date').val()){
			openDialogAlert('먼저 기초잔액 기초일자를 설정하세요.', 400, 180, function(){});
			$(obj).attr('checked', false);
			return false;
		}else{
			$(obj).closest('td').find("input[name='act_price']").attr('disabled', false);
			$(obj).closest('td').find("button").attr('disabled', false);
		}
	}else{
		$(obj).closest('td').find("input[name='act_price']").attr('disabled', true);
		$(obj).closest('td').find("button").attr('disabled', true);
	}
}

//------------ ↑↑ traders ↑↑------ ↓↓ warehouse ↓↓--------------//

// 창고 정보 저장
function submitWarehouse(){

	var that			= '';
	var submit_status	= true;

	// 필수값 체크
	$("form[name='detailForm']").find('input,select,textarea').each(function(){
		if	($(this).attr('isrequired') && !$(this).val()){
			that	= this;
			openDialogAlert($(this).attr('isrequired') + '을(를) 입력해 주세요.', 400, 150, function(){$(that).focus();});
			submit_status	= false;
		}
	});

	if	(submit_status){
		scmSubmit();
	}
}

// 창고분류 직접입력 선택 시 form변경
function chgWarehouseGroup(obj){
	if	($(obj).val() == 'direct'){
		$(obj).closest('td').find('span').show();
		$("input[name='wh_group']").val('');
	}else{
		$(obj).closest('td').find('span').hide();
		$("input[name='wh_group']").val($(obj).val());
	}
}

// 로케이션 설정 팝업 오픈
function openLocation(){
	var title	= '로케이션 생성';
	openDialog(title, 'set_location_lay', {'width':600,'height':250});
}

// 숫자에 해당하는 영문진수값 반환
function getAlpharToNum(num){
	var apArr	= new Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
							'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 
							'y', 'z');
	var apMax	= apArr.length;
	var result	= '';
	var r		= 0;
	var k		= 0;
	while ( num > apMax ){
		r		= num % apMax;
		num		= Math.floor(num / apMax);
		k		= r - 1;
		result	= apArr[k] + result;
	}
	k		= num - 1;
	result	= apArr[k] + result;

	return result;
}

// 다음 영문진수값 계산
function getNextAlphar(ord){
	var apArr	= new Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l',
							'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 
							'y', 'z');
	var apMax	= apArr.length;
	var ordLen	= ord.length;
	var ap		= '';
	var num		= 0;
	var upAdd	= 1;
	var result	= '';
	for	( var o = ordLen; o > 0; o--){
		ap		= ord.substring((o-1), o);
		num		= $.inArray(ap, apArr) + 1;
		if	(upAdd > 0){
			num++;
			upAdd	= 0;
		}
		if	(num > apMax){
			upAdd	= 1;
			num	= num - apMax;
		}
		num--;

		result	= apArr[num] + result;
	}
	if	(upAdd > 0){
		result	= 'a' + result;
	}

	return result;
}

// mouseover/mouseout에 따라 div show/hide 처리
function overlayonoff(obj, overlayClass, type){
	if	(type == 'on')	$(obj).find('div.' + overlayClass).show();
	else				$(obj).find('div.' + overlayClass).hide();
}

// 로케이션 박스 UI 노출
function crtLocationBox(){

	var w			= $("input[name='location_width']").val();
	var l			= $("input[name='location_length']").val();
	var h			= $("input[name='location_height']").val();
	var wt			= $("select[name='location_width_type']").val();
	var lt			= $("select[name='location_length_type']").val();
	var ht			= $("select[name='location_height_type']").val();
	var thead		= $('div#location_lay').find('thead.location-draw-lay');
	var tbody		= $('div#location_lay').find('tbody.location-draw-lay');
	var trObj		= '';
	var tdObj		= '';
	var ws			= '';
	var ls			= '';
	var hs			= '';
	var code		= '';
	var position	= '';

	if			( !(w > 0 && l > 0 && h > 0) ){
		openDialogAlert('가로, 세로, 높이는 최소 1이상이어야 합니다.', 400, 150, function(){});
		return false;
	}else if	(w > 100 || l > 100 || h > 50){
		openDialogAlert('가로 100, 세로 100, 높이 50이 최대입니다.', 400, 150, function(){});
		return false;
	}

	var tmpHTML		= '';

	// 가로 타이틀 생성
	thead.find('th.th-title-width').attr('colspan', w);
	thead.find('.tr-title-width').find('th').remove();

	for	(var iw = 1; iw <= w; iw++){
		tmpHTML		= '<th class="its-th-align center">' + iw + '</th>';
		thead.find('.tr-title-width').append(tmpHTML);
	}

	// 새로 생성
	tbody.html('');
	for	( var il = 1; il <= l; il++){
		if		(lt == 'A')	ls	= getAlpharToNum(il).toUpperCase();
		else if	(lt == 'a')	ls	= getAlpharToNum(il);
		else				ls	= il;

		tmpHTML		= '<tr>';
		// 새로 타이틀 생성
		if	(il == 1)	tmpHTML		+= '<th class="its-th-align center" rowspan="' + l + '">세로</th>';

		tmpHTML		+= '<th class="its-th-align center">' + il + '</th>';
		for	(var iw = 1; iw <= w; iw++){
			if		(wt == 'A')	ws	= getAlpharToNum(iw).toUpperCase();
			else if	(wt == 'a')	ws	= getAlpharToNum(iw);
			else				ws	= iw;

			tmpHTML	+= '<td class="its-td-align center">';
			tmpHTML	+= '<div class="location-select-over-lay"></div>';
			tmpHTML	+= '<table width="90%" class="info-table-style" align="center" style="margin:0 auto;border-top:1px solid #d7d7d7;">';
			tmpHTML	+= '<col width="60%" /><col />';
			tmpHTML	+= '<thead>';
			tmpHTML	+= '<tr>';
			tmpHTML	+= '<th class="its-th-align center">가로-세로</th>';
			tmpHTML	+= '<th class="its-th-align center">높이</th>';
			tmpHTML	+= '</tr>';
			tmpHTML	+= '</thead>';
			tmpHTML	+= '<tbody>';
			for	(var ih = 1; ih <= h; ih++){
				if		(ht == 'A')	hs	= getAlpharToNum(ih).toUpperCase();
				else if	(ht == 'a')	hs	= getAlpharToNum(ih);
				else				hs	= ih;

				tmpHTML	+= '<tr>';
				if	(ih == 1){
					code		= ws + '-' + ls;
					position	= iw + '-' + il;
					tmpHTML		+= '<td class="its-td-align center location-code-wl" code="' + code + '" position="' + position + '" rowspan="' + h + '">' + code + '<br/><span style="color:#005cc4;cursor:pointer;" onclick="getLocationDetail(this);">상품 검색 ></span></td>';
				}
				position	= iw + '-' + il + '-' + ih;
				tmpHTML		+= '<td class="its-td-align center location-code-h" code="' + hs + '"  position="' + position + '">' + hs + '</td>';
				tmpHTML		+= '</tr>';
			}
			tmpHTML	+= '</tbody>';
			tmpHTML	+= '</table>';
			tmpHTML	+= '</td>';
		}
		tmpHTML	+= '</tr>';
		tbody.append(tmpHTML);
	}
}

// 로케이션 정보 적용
function locationApply(){
	var w			= $('#set_location_lay').find('input.set_location_width').val();
	var l			= $('#set_location_lay').find('input.set_location_length').val();
	var h			= $('#set_location_lay').find('input.set_location_height').val();
	var wt			= $('#set_location_lay').find('select.set_location_width_type').find('option:selected').val();
	var lt			= $('#set_location_lay').find('select.set_location_length_type').find('option:selected').val();
	var ht			= $('#set_location_lay').find('select.set_location_height_type').find('option:selected').val();

	// 로케이션 구조 저굥
	var loc_struct	= '( 가로 ' + w + ' X 세로 ' + l + ' X 높이 ' + h + ' )';
	$('span.location-config-lay').html(loc_struct);
	$("input[name='location_width']").val(w);
	$("input[name='location_length']").val(l);
	$("input[name='location_height']").val(h);

	// 로케이션 레이블 적용
	$("select[name='location_width_type']").find("option[value='" + wt + "']").attr('selected', true);
	$("select[name='location_length_type']").find("option[value='" + lt + "']").attr('selected', true);
	$("select[name='location_height_type']").find("option[value='" + ht + "']").attr('selected', true);

	crtLocationBox();

	closeDialog('set_location_lay');
}

// 로케이션 상세 목록 ( 높이를 포함한 상품 매칭 목록 )
function getLocationDetail(req){
	if	(typeof(req) != 'string'){
		code	= jsTrim($(req).find('td.location-code-wl').text());
		if	(!code){
			code	= jsTrim($(req).closest('td').find('span.location-code-wl').text());
		}
	}else{
		code	= req;
	}

	$('input.src_location_code').val(code);

	ajaxSubmitLocationSearch(1);

	openDialog('창고별 상품검색', 'location_detail_lay', {'width':800,'height':550});
}

// 로케이션 상품 검색
function ajaxSubmitLocationSearch(current_page){

	var wh_seq	= $("select[name='src_wh_seq']").val();
	if	($('input.src_location_code').val() == $('input.src_location_code').attr('title'))	$('input.src_location_code').val('');
	if	($('input.src_goods_name').val() == $('input.src_goods_name').attr('title'))		$('input.src_goods_name').val('');

	var params		= 'src_wh_seq=' + $("select[name='src_wh_seq']").val()
					+ '&src_location_code=' + $('input.src_location_code').val()
					+ '&src_goods_name=' + encodeURIComponent($('input.src_goods_name').val())
					+ '&page=' + current_page;
	$.ajax({
		type		: 'get',
		url			: '../scm/get_location_goods',
		data		: params,
		dataType	: 'json', 
		success		: function(result){
			if	(result.data){
				// 목록 HTML 생성
				var datas			= '';
				var sumPrice		= 0;
				var ea				= 0;
				var supply_price	= 0;
				var listCnt			= result.data.length;
				$('#location_detail_lay').find('tbody.location-detail-list').html('');
				for	( var i = 0; i < listCnt; i++){
					datas		= result.data[i];

					if		(datas.location_supply_price > 0)	supply_price	= datas.location_supply_price;
					else if	(datas.supply_price > 0)			supply_price	= datas.supply_price;
					else										supply_price	= '0';
					if		(datas.location_ea > 0)				ea				= datas.location_ea;
					else if	(datas.ea > 0)						ea				= datas.ea;
					else										ea				= '0';
					sumPrice	= float_calculate('multiply', supply_price, ea);
					listHTML	= '<tr>'
								+ '<td class="its-td-align center">' + datas.goods_seq + '</td>'
								+ '<td class="its-td-align center">' + datas.option_seq + '</td>'
								+ '<td class="its-td-align left pdl5">' + datas.goods_name + '</td>'
								+ '<td class="its-td-align left pdl5">' + datas.option_name + '</td>'
								+ '<td class="its-td-align center">' + datas.location_code + '</td>'
								+ '<td class="its-td-align right pdr5">' + ea + '</td>'
								+ '<td class="its-td-align right pdr5">' + float_comma(supply_price) + '</td>'
								+ '<td class="its-td-align right pdr5">' + float_comma(sumPrice) + '</td>'
								+ '</tr>';
					$('#location_detail_lay').find('tbody.location-detail-list').append(listHTML);
				}

				// js paging HTML 생성
				$('#location_detail_lay').find('div.page-html-lay').html(getPagingHTML(result.page, 'ajaxSubmitLocationSearch'));

			}else{
				$('#location_detail_lay').find('tbody.location-detail-list').html('<tr><td class="its-td-align center" colspan="8">검색된 데이터가 없습니다.</td></tr>');
				$('#location_detail_lay').find('div.page-html-lay').html('');
			}
		}
	});
}

// 적재상품에 대한 정보 노출
function ajaxGetGoodsLocationLinkData(goods_seq){

	if	(goods_seq > 0){
		var params		= 'goods_seq=' + goods_seq;
		$.ajax({
			type		: 'get',
			url			: '../scm/get_location_goods_option',
			data		: params,
			dataType	: 'json', 
			success		: function(result){
				if	(result.status){

					// 초기화
					$('#goods_option_detail_lay').find('.option_list_thead tr').eq(0).find('th').eq(0).attr('colspan', '1');
					var thCnt	= $('#goods_option_detail_lay').find('.option_list_thead tr').eq(1).find('th').length;
					thCnt		= thCnt - 4;
					for	( var t = thCnt; t >= 0; t--){
						$('#goods_option_detail_lay').find('.option_list_thead tr').eq(1).find('th').eq(t).remove();
					}
					$('#goods_option_detail_lay').find('.option_list_tbody').html('');


					// 타이틀
					if	(result.division_title.length > 0){
						var thHTML	= '';
						var depth	= result.division_title.length;
						$('#goods_option_detail_lay').find('.option_list_thead tr').eq(0).find('th').eq(0).attr('colspan', depth);
						for	( var t = 0; t < depth; t++){
							thHTML	+= '<th class="its-th-align center">' + result.division_title[t] + '</th>';
						}
						$('#goods_option_detail_lay').find('.option_list_thead tr').eq(1).prepend(thHTML);
					}


					// 옵션
					var trHTML	= '';
					var optKey	= '';
					var whName	= '';
					var ea		= '';
					var badEa	= '';
					var data	= '';
					var rowSpan	= '';
					for	( var optSeq in result.data){
						data	= result.data[optSeq];
						whName	= (data.wh_name)	? data.wh_name	: '';
						ea		= (data.ea)			? data.ea		: '';
						badEa	= (data.bad_ea)		? data.bad_ea	: '';
						rowSpan	= '';

						if	(data.locdata.length > 1)	rowSpan	= ' rowspan="' + data.locdata.length + '"';


						trHTML	= '<tr>';
						for	( var t = 1; t <= depth; t++){
							optKey	= 'option' + t;
							trHTML	+= '<td class="its-td-align left pdl5" ' + rowSpan + '>' + data[optKey] + '</td>';
						}
						trHTML	+= '<td class="its-td-align left pdl5">' + whName + '</td>';
						trHTML	+= '<td class="its-td-align right pdr5">' + ea + '</td>';
						trHTML	+= '<td class="its-td-align right pdr5">' + badEa + '</td>';
						trHTML	+= '</tr>';

						if	(data.locdata.length > 1){
							for	( var l = 1; l < data.locdata.length; l++){
								whName	= (data.locdata[l].wh_name)		? data.locdata[l].wh_name	: '';
								ea		= (data.locdata[l].ea)			? data.locdata[l].ea		: '';
								badEa	= (data.locdata[l].bad_ea)		? data.locdata[l].bad_ea	: '';
								trHTML	+= '<tr>';
								trHTML	+= '<td class="its-td-align left pdl5">' + whName + '</td>';
								trHTML	+= '<td class="its-td-align right pdr5">' + ea + '</td>';
								trHTML	+= '<td class="its-td-align right pdr5">' + badEa + '</td>';
								trHTML	+= '</tr>';
							}
						}

						$('#goods_option_detail_lay').find('.option_list_tbody').append(trHTML);
					}

					openDialog('상품 재고 상세', 'goods_option_detail_lay', {'width':600,'height':400});
				}
			}
		});
	}
}

// 창고 로케이션 선택 팝업 생성
function selectReturnLocation(obj, return_item_seq){
	var scm_wh		= $("select[name='scm_wh']").val();
	var params		= 'wh_seq=' + scm_wh;
	var package_option_code = '';
	package_option_code = $(obj).attr('package_option_code');

	if	($('div#location_select_lay').find('tbody').attr('whSeq') != scm_wh){
		$('div#location_select_lay').find('tbody').attr('retItemSeq', return_item_seq);
		$('div#location_select_lay').find('tbody').attr('whSeq', scm_wh);
		$('div#location_select_lay').find('tbody').html('');
		$.ajax({
			type		: 'post',
			url			: '/scm/get_location_info',
			data		: params,
			dataType	: 'json', 
			success		: function(result){
				if	(result){
					var data	= '';
					var html	= '';
					for (var lPos in result){
						html			+= '<tr class="list-row">';
						for (var wPos in result[lPos]){
							data		= result[lPos][wPos];
							html		+= '<td class="its-td-align center">';
							html		+= data[1].location_w + '-' + data[1].location_l;
							html		+= '-<select class="select-location-position">'
							for (var hPos in data){
								html	+= '<option value="' + data[hPos].location_position + '" code="' + data[hPos].location_code + '">'
										+ data[hPos].location_h
										+ '</option>';
							}
							html		+= '</select>';
							html		+= ' <span style="color:#3399ff;cursor:pointer;" onclick="selectedLocation(this);">선택</span></td>';
						}
						html			+= '<tr>';
					}
					$('div#location_select_lay').find('tbody').html(html);
				}
			}
		});
	}else if	($('div#location_select_lay').find('tbody').attr('retItemSeq') != return_item_seq){
		$('div#location_select_lay').find('tbody').attr('retItemSeq', return_item_seq);
	}

	$('div#location_select_lay').find('tbody').attr('package_option_code', package_option_code);

	openDialog('로케이션 선택', 'location_select_lay', {'width':800,'height':600});
}

// 로케이션 선택에 대한 처리
function selectedLocation(obj){
	var retItemSeq	= $(obj).closest('tbody').attr('retItemSeq');
	var package_option_code	= $(obj).closest('tbody').attr('package_option_code');
	var optObj		= $(obj).closest('td').find('select.select-location-position option:selected');
	var tarObj		= $("input[name='location_position[" + retItemSeq + "]']");
	if( package_option_code ){
		tarObj		= $("input[name='location_position[" + retItemSeq + "][" + package_option_code + "]']");
	}
	tarObj.closest('tr').find('.location-code-title').html(optObj.attr('code'));
	tarObj.closest('tr').find('.location_code_val').val(optObj.attr('code'));
	tarObj.closest('tr').find('.location_position_val').val(optObj.val());

	closeDialog('location_select_lay');
}


//------------ ↑↑ warehouse ↑↑------ ↓↓ defaultinfo ↓↓--------------//

// 상품관리 설정 전체 open/close
function allChkDefaultinfo(obj){
	if	($(obj).attr('chkType') == 1){
		$(obj).attr('chkType', '2');
		$(obj).closest('span').addClass('black');
		$("input[name='chk_option[]']").each(function(){
			$(this).attr('checked', true);
		});
	}else{
		$(obj).attr('chkType', '1');
		$(obj).closest('span').removeClass('black');
		$("input[name='chk_option[]']").each(function(){
			$(this).attr('checked', false);
		});
	}
}

// 상품관리 일괄수정
function openDefaultinfoBatch(){
	var chk	= false;
	$("input[name='chk_option[]']").each(function(){
		if	($(this).attr('checked')){
			chk	= true;
			return true;
		}
	});
	if	(!chk){
		openDialogAlert('일괄등록할 옵션을 선택해 주세요', 400, 150, function(){});
		return false;
	}

	// 수정창 초기화
	$('div#defaultinfo_modify').find('tbody.defaultinfo-tbody').html('');
	$('div#defaultinfo_modify').find('div.btn-default-batchmode-lay').show();
	$('div#defaultinfo_modify').find('div.btn-default-modifymode-lay').hide();
	$('div#defaultinfo_modify').find("input[name='del_temp_default_seq[]']").remove();

	openDialog('발주 정보 일괄 등록', 'defaultinfo_modify', {'width':1200,'height':700});
}

// 상품관리 수정
var default_global_option_seq	= '';
var default_global_option_type	= '';
function openDefaultinfoModify(optionType, optionSeq){

	// 수정창 초기화
	$('div#defaultinfo_modify').find('tbody.defaultinfo-tbody').html('');
	$('div#defaultinfo_modify').find('div.btn-default-batchmode-lay').hide();
	$('div#defaultinfo_modify').find('div.btn-default-modifymode-lay').show();
	$('div#defaultinfo_modify').find("input[name='del_temp_default_seq[]']").remove();

	// 현재 데이터 수정창에 적용
	var html	= '';
	var idx		= 0;
	default_global_option_seq	= optionSeq;
	default_global_option_type	= optionType;
	var atChk_N = atChk_Y = auto_type = supply_price = supply_type = use_supply_tax = krw_supply_price = ''
	$('div#defaultinfo_' + optionType + '_' + optionSeq).find('tbody.defaultinfo-tbody tr').each(function(){
		addDefaultRow($(this));
	});

	openDialog('발주 정보 수정', 'defaultinfo_modify', {'width':1200,'height':700});
}

// 상품관리 한줄 추가
function addDefaultRow(currentTrObj){
	var modifyLay	= $('div#defaultinfo_modify');
	var idx			= 0;
	var default_seq = option_type = option_seq = use_status = main_trade_type = trader_seq = trader_name = '';
	var supply_goods_name = auto_type = supply_price_type = supply_price = use_supply_tax = '';
	if	(currentTrObj){
		rownum				= currentTrObj.find("input[name='rownum[]']").val();
		default_seq			= currentTrObj.find("input[name='default_seq[]']").val();
		option_type			= currentTrObj.find("input[name='option_type[]']").val();
		option_seq			= currentTrObj.find("input[name='option_seq[]']").val();
		use_status			= currentTrObj.find("input[name='use_status[]']").val();
		main_trade_type		= currentTrObj.find("input[name='main_trade_type[]']").val();
		trader_seq			= currentTrObj.find("input[name='trader_seq[]']").val();
		trader_name			= currentTrObj.find("input[name='trader_name[]']").val();
		supply_goods_name	= currentTrObj.find("input[name='supply_goods_name[]']").val();
		auto_type			= currentTrObj.find("input[name='auto_type[]']").val();
		supply_price_type	= currentTrObj.find("input[name='supply_price_type[]']").val();
		supply_price		= currentTrObj.find("input[name='supply_price[]']").val();
		krw_supply_price	= currentTrObj.find("input[name='krw_supply_price[]']").val();
		use_supply_tax		= currentTrObj.find("input[name='use_supply_tax[]']").val();
		min_box				= currentTrObj.find("input[name='min_box[]']").val();
	}else{
		// 기존row에 option_type과 option_seq가 있는 경우 추가한다.
		modifyLay.find('input.option_seq').each(function(){
			if	($(this).val() > 0){
				option_seq	= $(this).val();
				option_type	= $(this).closest('tr').find('input.option_type').val();
			}
		});

		// 기본값 정의
		supply_price	= '0';
		use_status		= 'N';
		main_trade_type	= 'N';
		auto_type		= 'N';
		use_supply_tax	= 'Y';
		rownum			= '1';
	}
	if	(!supply_price_type)	supply_price_type	= 'KRW';
	if	(auto_type == 'Y')		supply_price		= Math.round(supply_price);

	html	= '<tr>';
	html	+= '<td class="its-td-align center hand link-lay">'+rownum+'</td>';
	html	+= '<td class="its-td-align center hand link-lay" onclick="delSupplyInfoRow(this);">';
	html	+= '삭제';
	html	+= '<input type="hidden" class="default_seq" value="' + default_seq + '" />';
	html	+= '<input type="hidden" class="option_type" value="' + option_type + '" />';
	html	+= '<input type="hidden" class="option_seq" value="' + option_seq + '" />';
	html	+= '<input type="hidden" class="use_status" value="' + use_status + '" />';
	html	+= '<input type="hidden" class="main_trade_type" value="' + main_trade_type + '" />';
	html	+= '<input type="hidden" class="trader_seq" value="' + trader_seq + '" />';
	html	+= '<input type="hidden" class="trader_name" value="' + trader_name + '" />';
	html	+= '<input type="hidden" class="rownum" value="' + rownum + '" />';
	html	+= '</td>';
	html	+= '<td class="its-td-align left"><input type="text" class="supply_goods_name" value="' + supply_goods_name + '" /></td>';
	html	+= '<td class="its-td-align center hand link-lay use-status-td" onclick="setUseStatus(this);">';
	if	(use_status == 'Y')		html	+= '○';
	else						html	+= 'X';
	html	+= '</td>';
	html	+= '<td class="its-td-align center hand link-lay main-trade-type-td" onclick="setMainTrader(this);">';
	if	(main_trade_type == 'Y')	html	+= '○';
	else							html	+= 'X';
	html	+= '</td>';
	html	+= '<td class="its-td-align left hand link-lay trader_str_lay" onclick="openSelectTraders(this);">';
	if	(!trader_name && !trader_seq){
		html	+= '검색';
	}else{
		html	+= trader_name + '(' + trader_seq + ')';
	}
	html	+= '</td>';
	html	+= '<td class="its-td-align center">';
	html	+= '<select class="auto_type simple" onchange="chgAutoType(this);">';
	html	+= '<option value="N" ' + ((auto_type == 'N') ? 'selected' : '') + '>수동</option>';
	html	+= '<option value="Y" ' + ((auto_type == 'Y') ? 'selected' : '') + '>자동</option>';
	html	+= '</select>';
	html	+= '</td>';
	html	+= '<td class="its-td-align right supply-td">';
	html	+= '<span class="supply_type_y ' + ((auto_type == 'Y') ? '' : 'hide') + '">정가(부가세별도)의</span>';
	html	+= '<span class="supply_type_n ' + ((auto_type == 'Y') ? 'hide' : '') + '">';
	html	+= '<select class="supply_price_type simple">';
	for	(var type in exchanges){
		html	+= '<option value="' + type + '" ' + ((type == supply_price_type) ? 'selected' : '') + '>' + type + '</option>';
	}
	html	+= '</select></span>';
	html	+= '<input type="text" ';
	html	+= (auto_type == 'Y') ? 'size="3" ' : 'size="10"';
	html	+= 'class="supply_price" value="' + supply_price + '" onblur="supply_price_event(this);"/>';
	html	+= '<span class="supply_type_y ' + ((auto_type == 'Y') ? '' : 'hide') + '">%</span>';
	html	+= '</td>';
	html	+= '<td class="its-td-align center">';
	html	+= '<input type="checkbox" class="use_supply_tax" value="Y" ';
	html	+= (use_supply_tax == 'Y') ? 'checked' : '';
	html	+= ' onclick="supply_price_event(this);"></td>';
	html	+= '<td class="its-td-align supply_tax_td right">';
	if	(auto_type == 'Y'){
		if	(use_supply_tax == 'Y')	html	+= '있음';
		else						html	+= '없음';
	}else{
		if	(use_supply_tax == 'Y'){
			html	+= calculate_tax_price(supply_price_type, supply_price);
		}else{
			html	+= '0';
		}
	}
	html	+= '</td>';
	html	+= '<td class="its-td-align right"><input type="text" class="min_box" value="' + min_box + '" /></td>';
	html	+= '</tr>';
	
	modifyLay.find('tbody.defaultinfo-tbody').append(html);

	//번호 업데이트
	modifyLay.find('tbody.defaultinfo-tbody').find('tr').each(function(idx){
		var size = modifyLay.find('tbody.defaultinfo-tbody').find('tr').length;
		$(this).find('td:first-child').text(size - idx);
	});
}

function copyFirstRowData(){
	var modifyLay	= $('div#defaultinfo_modify');
	var firstTrObj	= modifyLay.find('tbody.defaultinfo-tbody').find('tr').eq(0);
	use_status			= firstTrObj.find('input.use_status').val();
	trader_seq			= firstTrObj.find('input.trader_seq').val();
	trader_name			= firstTrObj.find('input.trader_name').val();
	supply_goods_name	= firstTrObj.find('input.supply_goods_name').val();
	auto_type			= firstTrObj.find('select.auto_type').val();
	supply_price_type	= firstTrObj.find('select.supply_price_type').val();
	supply_price		= firstTrObj.find('input.supply_price').val();
	use_supply_tax		= (firstTrObj.find('input.use_supply_tax').attr('checked')) ? 'Y' : 'N';

	var cur_main_trade	= '';
	var cur_use_status	= '';
	modifyLay.find('tbody.defaultinfo-tbody').find('tr').each(function(){
		cur_main_trade	= $(this).find('input.main_trade_type').val();
		cur_use_status	= $(this).find('input.use_status').val();
		if	(cur_main_trade != 'Y' && cur_use_status != use_status){
			setUseStatus($(this).find('td.use-status-td'));
		}
		$(this).find('input.supply_goods_name').val(supply_goods_name);
		$(this).find('input.trader_seq').val(trader_seq);
		$(this).find('input.trader_name').val(trader_name);
		$(this).find('td.trader_str_lay').html(trader_name + '(' + trader_seq + ')');
		$(this).find('select.auto_type').val(auto_type);
		$(this).find('select.supply_price_type').val(supply_price_type);
		$(this).find('input.supply_price').val(supply_price);
		if	(use_supply_tax == 'Y')	$(this).find('input.use_supply_tax').attr('checked', true);
		else						$(this).find('input.use_supply_tax').attr('checked', false);

		chgAutoType($(this).find('select.auto_type'));
		supply_price_event($(this).find('input.supply_price'));
	});
}

// 상품관리 일괄등록 처리
function applyBatchDefaultinfo(){

	var lay_id		= '';
	var tmpArr		= new Array();
	var option_type	= '';
	var option_seq	= '';
	$('div.defaultinfo-lay').each(function(){
		if	($(this).find("input[name='chk_option[]']").attr('checked')){
			lay_id		= $(this).attr('id');
			tmpArr		= new Array();
			option_type	= '';
			option_seq	= '';
			if	(lay_id != 'defaultinfo_modify'){
				tmpArr		= lay_id.split('_');
				option_type	= tmpArr[1];
				option_seq	= tmpArr[2];
				if	(option_type && option_seq)	applyModifyDefaultinfo(option_type, option_seq);
			}

			// 주거래처가 한개도 없을 경우 첫번째 거래처를 주거래처로 강제 적용
			if	(!$(this).find("input.main_trade_type[value='Y']").val()){
				$(this).find("input.main_trade_type").eq(0).val('Y');
				$(this).find("td.main-trade-type-td").eq(0).html('○');
				$(this).find("input.use_status").eq(0).val('Y');
				$(this).find("td.use-status-td").eq(0).html('○');
			}
		}
	});

	closeDialog('defaultinfo_modify');
}

// 상품관리 수정 처리
function applyModifyDefaultinfo(option_type, option_seq){
	var trLay	= $('div#defaultinfo_modify').find('tbody.defaultinfo-tbody tr');

	// 일괄등록이 아닌 경우 부모영역은 초기화함.
	if	(!option_type && !option_seq){
		if	(!option_type)	option_type	= trLay.eq(0).find('input.option_type').val();
		if	(!option_seq)	option_seq	= trLay.eq(0).find('input.option_seq').val();
		if	(!option_type)	option_type	= default_global_option_type;
		if	(!option_seq)	option_seq	= default_global_option_seq;
		$('div#defaultinfo_' + option_type + '_' + option_seq).find('tbody.defaultinfo-tbody tr').remove();
		trLay.each(function(){
			applyDefaultInfo(option_type, option_seq, $(this));
		});
		// 주거래처가 한개도 없을 경우 첫번째 거래처를 주거래처로 강제 적용
		if	(!$('div#defaultinfo_' + option_type + '_' + option_seq).find("input.main_trade_type[value='Y']").val()){
			$('div#defaultinfo_' + option_type + '_' + option_seq).find("input.main_trade_type").eq(0).val('Y');
			$('div#defaultinfo_' + option_type + '_' + option_seq).find("td.main-trade-type-td").eq(0).html('○');
			$('div#defaultinfo_' + option_type + '_' + option_seq).find("input.use_status").eq(0).val('Y');
			$('div#defaultinfo_' + option_type + '_' + option_seq).find("td.use-status-td").eq(0).html('○');

		}

	}else{
		trLay.each(function(){
			if	($('div#defaultinfo_' + option_type + '_' + option_seq).find("input[name='chk_option[]']").attr('checked')){
				applyDefaultInfo(option_type, option_seq, $(this));
			}
		});
	}
	$('div#defaultinfo_modify').find("input[name='del_temp_default_seq[]']").each(function(){
		$("form[name='detailForm']").append('<input type="hidden" name="del_default_seq[]" value="' + $(this).val() + '" />');
	});

	closeDialog('defaultinfo_modify');
}

// 일괄등록 및 수정 팝업의 데이터를 부모로 이동
function applyDefaultInfo(option_type, option_seq, currentTrObj){

	if	(option_seq > 0 && ( option_type == 'option' || option_type == 'suboption' ) ){
		var parentLay	= $('div#defaultinfo_' + option_type + '_' + option_seq);
		var default_seq = use_status = main_trade_type = trader_seq = trader_name = html = '';
		var supply_goods_name = auto_type = supply_price_type = supply_price = use_supply_tax = '';
		if	(currentTrObj){
			// 부모영역에 주거래처가 있을 경우 일괄 등록에서 주거래처는 N
			var parent_main_trade = parentLay.find('tbody.defaultinfo-tbody').find('input.main_trade_type[value="Y"]');
			
			rownum				= currentTrObj.find('input.rownum').val();
			default_seq			= currentTrObj.find('input.default_seq').val();
			use_status			= currentTrObj.find('input.use_status').val();
			main_trade_type		= parent_main_trade.length > 0 ? 'N' : currentTrObj.find('input.main_trade_type').val();
			trader_seq			= currentTrObj.find('input.trader_seq').val();
			trader_name			= currentTrObj.find('input.trader_name').val();
			supply_goods_name	= currentTrObj.find('input.supply_goods_name').val();
			auto_type			= currentTrObj.find('select.auto_type').val();
			supply_price_type	= currentTrObj.find('select.supply_price_type').val();
			supply_price		= currentTrObj.find('input.supply_price').val();
			min_box				= currentTrObj.find('input.min_box').val();
			use_supply_tax		= (currentTrObj.find('input.use_supply_tax').attr('checked')) ? 'Y' : 'N';
		}

		html	= '<tr>';
		html	+= '<td class="its-td-align center hand link-lay">'+rownum+'</td>';
		html	+= '<td class="its-td-align center hand link-lay" onclick="delSupplyInfoRow(this);">';
		html	+= '삭제';
		html	+= '<input type="hidden" name="default_seq[]" value="' + default_seq + '" />';
		html	+= '<input type="hidden" name="option_type[]" value="' + option_type + '" />';
		html	+= '<input type="hidden" name="option_seq[]" value="' + option_seq + '" />';
		html	+= '<input type="hidden" name="use_status[]" value="' + use_status + '" class="use_status" />';
		html	+= '<input type="hidden" name="main_trade_type[]" value="' + main_trade_type + '" class="main_trade_type" />';
		html	+= '<input type="hidden" name="trader_seq[]" value="' + trader_seq + '" class="trader_seq" />';
		html	+= '<input type="hidden" name="trader_name[]" value="' + trader_name + '" class="trader_name" />';
		html	+= '<input type="hidden" name="supply_goods_name[]" value="' + supply_goods_name + '" />';
		html	+= '<input type="hidden" name="auto_type[]" value="' + auto_type + '" />';
		html	+= '<input type="hidden" name="supply_price_type[]" value="' + supply_price_type + '" />';
		html	+= '<input type="hidden" name="supply_price[]" value="' + supply_price + '" />';
		html	+= '<input type="hidden" name="use_supply_tax[]" value="' + use_supply_tax + '" />';
		html	+= '<input type="hidden" name="min_box[]" value="' + min_box + '" />';
		html	+= '<input type="hidden" name="rownum[]" class="rownum" value="' + rownum + '" />';
		html	+= '</td>';
		html	+= '<td class="its-td-align left">' + supply_goods_name + '</td>';
		html	+= '<td class="its-td-align center hand link-lay use-status-td" onclick="setUseStatus(this);">';
		html	+= (use_status == 'Y') ? '○' : 'X';
		html	+= '</td>';
		html	+= '<td class="its-td-align center hand link-lay main-trade-type-td" onclick="setMainTrader(this);">';
		html	+= (main_trade_type == 'Y') ? '○' : 'X';
		html	+= '</td>';
		html	+= '<td class="its-td-align left hand link-lay" onclick="openSelectTraders(this);">';
		html	+= trader_name + '(' + trader_seq + ')';
		html	+= '</td>';
		if	(auto_type == 'Y'){
			html	+= '<td class="its-td-align right">';
			html	+= '정가(부가세 별도)의 ' + supply_price + '%';
			html	+= '</td>';
			html	+= '<td class="its-td-align right">';
			html	+= (use_supply_tax == 'Y') ? '있음' : '없음';
			html	+= '</td>';
		}else{
			if	(supply_price_type == 'KRW'){
				html	+= '<td class="its-td-align right">';
				html	+= comma(supply_price);
				html	+= '</td>';
				html	+= '<td class="its-td-align right">';
				html	+= comma(calculate_tax_price('KRW', supply_price));
				html	+= '</td>';
			}else{
				var krw_supply_price	= krw_exchange(supply_price_type, supply_price);

				html	+= '<td class="its-td-align right">';
				html	+= '(' + supply_price_type + ' ' + supply_price + ' &nbsp;&nbsp;&nbsp; ';
				html	+= '환율 ' + float_comma(exchanges[supply_price_type]['currency_exchange']) + ') &nbsp;&nbsp;&nbsp; ';
				html	+= comma(krw_supply_price);
				html	+= '</td>';
				html	+= '<td class="its-td-align right">';
				html	+= comma(calculate_tax_price('KRW', krw_supply_price));
				html	+= '</td>';
			}
		}
		html	+= '<td class="its-td-align right">';
		html	+= comma(min_box);
		html	+= '</td>';
		html	+= '</tr>';

		parentLay.find('tbody.defaultinfo-tbody').append(html);
		parentLay.find('tbody.defaultinfo-tbody').find('tr').each(function(idx){
			var size = parentLay.find('tbody.defaultinfo-tbody').find('tr').length;
			$(this).find('td:first-child').text(size - idx);
		});
	}
}

// 상품관리 설정 Row 삭제
function delSupplyInfoRow(obj){
	// 본창에서 주거래처 체크
	if	($(obj).closest('div').attr('id') != 'defaultinfo_modify'){
		if	($(obj).find("input[name='main_trade_type[]']").val() == 'Y'){
			openDialogAlert('주거래는 삭제할 수 없습니다.', 400, 150, function(){});
			return false;
		}
	}

	if( $(obj).closest('div').attr('id') =='defaultinfo_modify' ){
		var seq		= $(obj).find('.default_seq').val();
		
		if	(seq > 0)	{
			$('#defaultinfo_modify').append('<input type="hidden" name="del_temp_default_seq[]" value="' + seq + '" />');
		}
	}else{
		var seq		= $(obj).find("input[name='default_seq[]']").val();
		if	(seq > 0)	$("form[name='detailForm']").append('<input type="hidden" name="del_default_seq[]" value="' + seq + '" />');
	}

	$(obj).closest('tr').remove();
}

// 상품관리 사용여부 변경
function setUseStatus(obj){
	if	($(obj).closest('tr').find('input.use_status').val() != 'Y'){
		$(obj).html('○');
		$(obj).closest('tr').find('input.use_status').val('Y');
	}else{
		if	($(obj).closest('tr').find('input.main_trade_type').val() == 'Y'){
			openDialogAlert('주거래처는 미사용으로 설정할 수 없습니다.', 400, 150, function(){});
			return false;
		}else{
			$(obj).html('X');
			$(obj).closest('tr').find('input.use_status').val('N');
		}
	}
}

// 상품관리 주거래처 변경
function setMainTrader(obj){

	var locType			= ($(obj).closest('tbody').hasClass('modify-tbody')) ? 'modify' : 'list';
	var now_main_type	= $(obj).closest('tr').find('input.main_trade_type').val();
	var chg_main_type	= (now_main_type == 'Y') ? 'N' : 'Y';

	if	(locType == 'list'){
		if	(now_main_type == 'Y'){
			openDialogAlert('현재 주거래처로 설정되어 있습니다.', 400, 150, function(){});
			return false;
		}
	}

	// 전체 주거래처를 초기화
	$(obj).closest('tbody').find('input.main_trade_type').each(function(){
		$(this).val('N');
		$(this).closest('tr').find('td.main-trade-type-td').html('X');
	});

	if	(chg_main_type == 'Y'){
		// 현재 매입정보를 주거래처로 변경
		$(obj).closest('tr').find('input.main_trade_type').val('Y');
		$(obj).closest('tr').find('input.use_status').val('Y');
		$(obj).closest('tr').find('td.use-status-td').html('○');
		$(obj).closest('tr').find('td.main-trade-type-td').html('○');
	}
}

// 매입가 계산 수동/자동 변경
function chgAutoType(obj){
	if	($(obj).val() == 'N'){
		$(obj).closest('tr').find('span.supply_type_y').hide();
		$(obj).closest('tr').find('span.supply_type_n').show();
		$(obj).closest('tr').find('input.supply_price').attr('size', '10');
	}else{
		$(obj).closest('tr').find('span.supply_type_y').show();
		$(obj).closest('tr').find('span.supply_type_n').hide();
		$(obj).closest('tr').find('input.supply_price').attr('size', '3');
	}

	// 부가세 처리
	supply_price_event($(obj).closest('tr').find('input.supply_price'));
}

// 매입가 event 설정
function supply_price_event(obj){
	var supply_tax			= '';
	var percent				= '';
	var supply_price_type	= 'KRW';
	var supply_price		= $(obj).closest('tr').find('input.supply_price').val();

	if	($(obj).closest('tr').find('select.auto_type').val() == 'Y'){
		percent				= parseInt(supply_price.replace(/[^0-9]/g, ''));
		if	(!percent)		percent	= 0;
		if	(percent > 100)	percent	= 100;
		$(obj).val(percent);

		if	($(obj).closest('tr').find('input.use_supply_tax').attr('checked'))	supply_tax	= '있음';
		else																	supply_tax	= '없음';
	}else{
		supply_price_type	= $(obj).closest('tr').find('select.supply_price_type').val();
		supply_price		= supply_price.replace(/[^0-9\.]/g, '');
		$(obj).val(supply_price);

		if	($(obj).closest('tr').find('input.use_supply_tax').attr('checked'))
			supply_tax	= calculate_tax_price(supply_price_type, supply_price);
		else
			supply_tax	= '0';
	}

	$(obj).closest('tr').find('td.supply_tax_td').html(supply_tax);
}

// 거래처 선택
function openSelectTraders(obj){
	openerObj	= obj;
	getTradersList('1');
	openDialog('거래처 검색', 'select_trader_lay', {'width':700,'height':400,'close':function(){openerObj = '';}});
}

// 거래처 검색
function getTradersList(page){
	var params		= 'sc_trader_use=Y&page=' + page;
	var keywordObj	= $('#select_trader_lay').find("input[name='keyword']");
	if	(keywordObj.attr('title') == keywordObj.val())	keywordObj.val('');
	if	(keywordObj.val()){
		params		+= '&trader_use=Y&keyword=' + encodeURIComponent($('#select_trader_lay').find("input[name='keyword']").val())
					+ '&keyword_sType=' + encodeURIComponent($('#select_trader_lay').find("input[name='keyword_sType']").val());
	}

	$.ajax({
		type		: 'get',
		url			: '../scm/getTraderData',
		data		: params,
		dataType	: 'json', 
		success		: function(result){
			$('#select_trader_lay').find('tbody.trader-list').html('');
			if	(result){
				var data	= '';
				var cnt		= result.record.length;
				var rowHtml	= '';
				for	( var i = 0; i < cnt; i++){
					data	= result.record[i];

					rowHtml	= '<tr>';
					rowHtml	+= '<td class="its-td-align center">' + data._rno + '</td>';
					rowHtml	+= '<td class="its-td-align center">' + data.trader_use_str + '</td>';
					rowHtml	+= '<td class="its-td-align center">' + data.trader_type_str + '</td>';
					rowHtml	+= '<td class="its-td-align center">' + data.trader_location_str + '</td>';
					rowHtml	+= '<td class="its-td-align center">' + data.trader_id + '</td>';
					rowHtml	+= '<td class="its-td-align center">' + data.trader_name + '</td>';
					rowHtml	+= '<td class="its-td-align center">' + data.company_owner + '</td>';
					rowHtml	+= '<td class="its-td-align center"><span class="btn small cyanblue"><button type="button" onclick="selectTrader(\'' + data.trader_seq + '\', \'' + data.trader_name + '\');">선택</button></span></td>';
					rowHtml	+= '</tr>';

					$('#select_trader_lay').find('tbody.trader-list').append(rowHtml);
				}

				$('#select_trader_lay').find('div.page-html-lay').html(getPagingHTML(result.page, 'getTradersList'));

			}else{
				rowHtml	= '<tr>';
				rowHtml	+= '<td class="its-td-align center" colspan="8" height="30px">등록된 거래처가 없습니다.</td>';
				rowHtml	+= '</tr>';
				$('#select_trader_lay').find('tbody.trader-list').append(rowHtml);
			}
		}
	});
}

// 거래처 선택 처리
function selectTrader(seq, name){

	$(openerObj).closest('tr').find('input.trader_seq').val(seq);
	$(openerObj).closest('tr').find('input.trader_name').val(name);

	var html		= name + '(' + seq + ')';
	$(openerObj).html(html);

	closeDialog('select_trader_lay');
}

// 자동 채우기
function default_all_copy(obj){

	var tbodyLay	= $(obj).closest('table').find('tbody.defaultinfo-tbody');

	switch($(obj).attr('id')){
		case 'supply_goods_name_all':
			var supply_goods_name	= $(obj).closest('th').find('input.supply_goods_name_all').val();
			tbodyLay.find('input.supply_goods_name').val(supply_goods_name);
		break;
		case 'select_trade_all':
			var trader_seq	= $(obj).closest('th').find('input.trader_seq').val();
			var trader_name	= $(obj).closest('th').find('input.trader_name').val();
			if	(trader_seq > 0){
				tbodyLay.find('input.trader_seq').val(trader_seq);
				tbodyLay.find('input.trader_name').val(trader_name);
				tbodyLay.find('td.trader_str_lay').html(trader_name + '(' + trader_seq + ')');
			}else{
				openDialogAlert('일괄 적용할 거래처를 선택해 주세요.', 400, 150, function(){});
				return false;
			}
		break;
		case 'supply_price_all':
			var auto_type			= $(obj).closest('th').find('select.auto_type_all').val();
			var supply_price_type	= $(obj).closest('th').find('select.supply_price_type_all').val();
			var supply_price		= $(obj).closest('th').find('input.supply_price_all').val();

			// 변경 시 이벤트 처리를 위해서 일일이 loop를 돌린다.
			tbodyLay.find('select.auto_type').each(function(){
				$(this).val(auto_type);
				$(this).closest('tr').find('select.supply_price_type').val(supply_price_type);
				$(this).closest('tr').find('input.supply_price').val(supply_price);

				chgAutoType($(this));
			});
		break;
		case 'use_supply_tax_all':
			var checkType	= $(obj).attr('checked');

			tbodyLay.find('input.use_supply_tax').each(function(){
				if	(checkType)	$(this).attr('checked', true);
				else			$(this).attr('checked', false);
				supply_price_event(this);
			});
		break;
	}
}

// 자동 채우기 (Grid 용)
function default_all_copy2(obj){

	var tbodyLay	= $(obj).closest('table').find('tbody.defaultinfo-tbody');

	switch($(obj).attr('id')){
		case 'supply_ea_all':
			var supply_ea	= $(obj).closest('th').find('input.excel_table_batch_val').val();
			
			tbodyLay.find('input[name="ea[]"]').closest('td').find('span').text(supply_ea);
			tbodyLay.find('input[name="ea[]"]').val(supply_ea);
		break;
		case 'supply_price_all':
			var supply_price_type	= $(obj).closest('th').find('select.excel_table_batch_supply_type').val();
			var supply_price		= $(obj).closest('th').find('input.excel_table_batch_val').val();
			
			tbodyLay.find('input[name="supply_price_type[]"]').closest('td').find('span').text(supply_price_type);
			tbodyLay.find('input[name="supply_price_type[]"]').val(supply_price_type);

			tbodyLay.find('input[name="supply_price[]"]').each(function(){
				$(this).closest('td').find('span').text(float_comma(supply_price));
				$(this).val(supply_price);

				var cellEa = $(this).closest('tr').find('input[name="ea[]"]').val();
				$(this).closest('tr').find('input[name="total_price[]"]').val(supply_price * cellEa);
				$(this).closest('tr').find('input[name="total_price[]"]').closest('td').find('span').text(supply_price * cellEa);
			});			
		break;
		case 'use_supply_tax_all':
			var checkType	= $(obj).attr('checked');

			tbodyLay.find('input[name="use_supply_tax[]"]').each(function(){
				if	(checkType)	$(this).attr('checked', true);
				else			$(this).attr('checked', false);
			});
		break;
		case 'location_position_all':
			var location_position	= $(obj).closest('th').find('input.excel_table_batch_val').val();
			
			tbodyLay.find('input[name="in_location_code[]"]').closest('td').find('span').text(location_position);
			tbodyLay.find('input[name="in_location_code[]"]').val(location_position);
		break;
	}

	sorderCalculateTotal();
}

// 자동발주상품 등록 팝업 오픈
function openAddAutoOrderGoodsPopup(){
	var optioninfo_list	= '';
	$("form[name='listFrm']").find('input.chk').each(function(){
		if	($(this).attr('checked')){
			optioninfo_list	+= $(this).val() + ',';
		}
	});

	if	(!optioninfo_list){
		openDialogAlert('자동발주상품을 선택해 주세요.', 400, 150, function(){});
		return false;
	}

	$('div#add_auto_order_goods').find("input[name='optioninfo_list']").val(optioninfo_list);
	openDialog('자동발주상품 등록', 'add_auto_order_goods', {'width':600,'height':270});
}

// 자동발주상품 등록
function addAutoOrderSubmit(){

	var frm	= $("form[name='autoOrderFrm']");

	// 선택된 상품이 있는지 확인
	if	(!frm.find("input[name='optioninfo_list']").val()){
		openDialogAlert('자동발주상품을 선택해 주세요.', 400, 150, function(){});
		return false;
	}

	// 수량 직접 입력 시 수량 체크
	if	(frm.find("input[name='add_ea_type']").eq(0).attr('checked') == 'checked'){
		var direct_ea	= frm.find("input[name='direct_ea']").val();

		if	(direct_ea.search(/[^0-9]/) != -1 ){
			openDialogAlert('자동발주 수량은 숫자로만 입력해주세요.', 400, 150, function(){});
			return false;
		}
		if	(direct_ea < 1){
			openDialogAlert('자동발주 수량을 1이상 입력해 주세요.', 400, 150, function(){});
			return false;
		}
	}

	frm.submit();
}
// 자동발주상품 등록 팝업 닫기
function addAutoOrderClose(){
	closeDialog('add_auto_order_goods');
}

//------------ ↑↑ defaultinfo ↑↑------ ↓↓ revision ↓↓--------------//

// 재고조정 저장
function submitRevision(){
	if	($("input[name='revision_status']").attr('org') && $("input[name='revision_status']").attr('org') != $("input[name='revision_status']").val()){
		$("input[name='revision_status']").val($("input[name='revision_status']").attr('org'));
	}
	excelTableSubmit();
}

// 재고조정 저장
function applyRevision(){
	$("input[name='revision_status']").attr('org', $("input[name='revision_status']").val());
	$("input[name='revision_status']").val('1');
	excelTableSubmit();
}

//------------ ↑↑ revision ↑↑------ ↓↓ stockmove ↓↓--------------//

// 재고이동 form submit
function submitStockmove(status){

	var chkVal		= chkBeforeSelectGoodsOptionData('');
	var params		= chkVal['params'];
	if	(!chkVal['status'])	return false;

	var submitStatus	= true;
	var loc_ea			= 0;
	var ea				= 0;
	$('div#excelTable').find("input[name='option_seq[]']").each(function(){
		if	($(this).val()){

			loc_ea	= $(this).closest('tr').find('.location_ea').text().replace(/\([0-9]*\)/, '');
			ea		= $(this).closest('tr').find("input[name='ea[]']").val();
			if			( !(parseInt(ea) > 0) ){
				openDialogAlert('이동수량은 1이상이어야 합니다. 이동수량을 확인해주세요.', 500, 150, function(){});
				submitStatus	= false;
				return false;
			}else if	(parseInt(loc_ea) < parseInt(ea)){
				openDialogAlert('해당 상품의 이동수량은 출고창고에 있는 재고수량을 초과할 수 없습니다.', 500, 150, function(){});
				submitStatus	= false;
				return false;
			}
		}
	});
	if	(!submitStatus)	return false;

	if	(status == '1' && !$("input[name='move_status']").attr('org')){
		var out_wh_name	= $("select[name='out_wh_seq'] option:selected").text();
		var in_wh_name	= $("select[name='in_wh_seq'] option:selected").text();
		openDialogConfirm('해당 상품의 재고를 ' + out_wh_name + '에서 → ' + in_wh_name + '으로 이동하시겠습니까?', 500, 150, function(){
			$("input[name='move_status']").val('1');
			excelTableSubmit();
		});
	}else{
		if	(status == '1' || $("input[name='move_status']").attr('org') == '1'){
			$("input[name='move_status']").val('1');
		}else{
			$("input[name='move_status']").val('0');
		}
		excelTableSubmit();
	}
}

//------------ ↑↑ stockmove ↑↑------ ↓↓ sorder ↓↓--------------//

// 발주서 인쇄
function sorderPrint(sono,mode){
	if(mode == 'multi'){
		var get_params	= sono;
	}else{
		var get_params	= "sono=" + sono
	}
	window.open('../scm/sorder_prints?' + get_params,'_print','width=1000,height=700,menubar=no,status=no,toobar=no,scrollbars=yes,resizable=no');
}

// 발주서 SMS/이메일 발송
function sorder_sender(sono,mode){
	
	var params	= {};
	params.sono	= sono;
	params.mode	= (mode) ? mode : 'mail';

	$.get('../scm/get_sorder_draft_form',params , function(response){

		if(mode == 'sms'){

			$('#sms_body_byte').text(response.replace_text.byteLength());

			$('#left_sms_count').text(response.sms_count_text);
			$('#to_cellphone_view').text(response.to_cellphone);
			$('#to_cellphone').val(response.to_cellphone);
			$('#sms_body').val(response.replace_text);

			openDialog('SMS 발송','sorderDraftSMSForm', {'width':600,'height':230});
		}else{
			

			$('#recent_email_list > option').remove();

			if(response.recent_email.length > 0){

				$('#recent_email_list').append('<option value="none"> = 최근 발송한 이메일 선택 =  </option>');
				$('#recent_email_list').append('<option value="now_sorder">선택한 발주서 양식</option>');

				$.each(response.recent_email, function(key,val){
					$('#recent_email_list').append('<option value="' + val.mail_seq + '" sono = "' + sono + '">' + val.subject + '</option>');
				});
			}else{
				$('#recent_email_list').append('<option value="none"> = 최근 발송한 이메일이 없습니다. =  </option>');
			}

			openDialog('EMAIL 발송','sorderDraftEmailForm', {'width':1000,'height':770});

			$('#email_title').val(response.replace_text);
			$('#sender_email').val(response.sender_email);
			$('#sender_name').val(response.sender_name);
			$('#to_email').val(response.to_email);
			$('#left_email_count').text(response.email_count_text);
			
			//다음 에디터 재 정의
			var config	= Editor.config;
			EditorJSLoader.ready(function (Editor) {
				var editor = new Editor(config);
			});

			Editor.modify({"content" : response.email_body});
		}
	},'json');	
}

// 발주서 form submit
function submitSorder(status){

	if	(!$("select[name='trader_seq']").val()){
		openDialogAlert('거래처를 선택해 주세요.', 400, 170, function(){});
		return false;
	}

	if	(status == '1'){
		var msg	= '발주완료 후에는 발주서를 수정할 수 없습니다.<br/>발주완료 시 해당 거래처에게 문자와 이메일이 자동 발송됩니다. (자동 발송 설정 시)<br/>해당 발주서를 발주완료 하시겠습니까?';
		openDialogConfirm(msg, 600, 200, function(){
			submitProcSorder(status);
		}, function(){ });
	}else{
		submitProcSorder(status);
	}
}

// 발주서 form submit
function submitProcSorder(status){
	$("input[name='sorder_status']").val(status);
	excelTableSubmit();
}

// 발주서 복사
function copy_sorder(sorder_seq){
	if	(sorder_seq){
		loadingStart();
		var tmpForm	= $('<form method="post" action="../scm_process/copy_sorder" target="actionFrame"></form>').appendTo($('body'));
		tmpForm.append('<input type="hidden" name="sorder_seq" value="' + sorder_seq + '" />');
		tmpForm.submit();
		tmpForm.remove();
	}
}

// 발주서 인쇄
function sorderPrints(obj){
		var print_sono_list	= '';
		var print_sono_cnt	= 0;
		
		$.each($('input[name="sorder_seq[]"]:checked'), function(){
			print_sono_list		+= (print_sono_list != '') ? '&sono[]=' + this.value : 'sono[]=' + this.value;
			print_sono_cnt++;
		});

		if(print_sono_cnt < 1){
			openDialogAlert('발주서를 선택하세요');
			return;
		}
		
		sorderPrint(print_sono_list,'multi');
}

// 발주서 삭제
function sorderRemove(obj){
	var print_sono_list	= '';
	var print_sono_cnt	= 0;
	var flag = false;

	$.each($('input[name="sorder_seq[]"]:checked'), function(){
		var stat = $(this).closest('td').find('input[name="sorder_status[]"]').val();
		if(stat > 0){			
			flag = true;
			return false;
		}

		print_sono_list		+= (print_sono_list != '') ? '&sono[]=' + this.value : 'sono[]=' + this.value;
		print_sono_cnt++;
	});

	if(flag){
		alert('대기 상태만 삭제 가능합니다.');
		return false;
	}

	if(print_sono_cnt < 1){
		openDialogAlert('발주서를 선택하세요');
		return;
	}

	openDialogConfirm('선택하신 ' + print_sono_cnt + '개의 발주대기 내역을 삭제하시겠습니까?<br/>',400,170,function(){
			loadingStart();
			var orginAct = $("form[name='listFrm']").attr('action');
			$("form[name='listFrm']").attr('method', 'post');
			$("form[name='listFrm']").attr('action', '../scm_process/remove_sorder');
			$("form[name='listFrm']").submit();
			$("form[name='listFrm']").attr('method', 'get');
			$("form[name='listFrm']").attr('action', orginAct);
		});
}	

//------------ ↑↑ sorder ↑↑------ ↓↓ warehousing ↓↓--------------//

// 발주목록 조회
function searchSorderList(data){
	if	(!$("select[name='in_wh_seq']").val()){
		openDialogAlert('입고창고를 선택해 주세요.', 400, 150, function(){});
		return false;
	}

	var params	= (data) ? data : 'in_wh_seq=' + $("select[name='in_wh_seq']").val();

	// 초기화
	$('div#sorder_search_popup').html('').hide();

	$.ajax({
		type		: 'get',
		url			: '../scm/getAjaxSorderList',
		data		: params,
		dataType	: 'json', 
		global		: false,
		success		: function(result){
			if	(result.status){
				makeSorderPopup(result.data, result.page);
			}else{
				openDialogAlert('검색된 발주서가 없습니다.', 800, 150, function(){});
				return false;
			}
		}
	});
}

// 발주서 선택 팝업
function makeSorderPopup(data, page){
	// 초기화
	$('div#sorder_search_popup').html('').hide();

	var row		= '';
	var divObj	= '';
	var html	= '';
	var cnt		= data.length;
	if	(cnt > 0){

		html	= '<table class="info-table-style" style="width:100%">';
		html	= html + '<colgroup>';
		html	= html + '	<col width="5%" />';
		html	= html + '	<col width="15%" />';
		html	= html + '	<col width="10%" />';
		html	= html + '	<col width="*" />';
		html	= html + '	<col width="7%" />';
		html	= html + '	<col width="7%" />';
		html	= html + '	<col width="8%" />';
		html	= html + '	<col width="8%" />';
		html	= html + '	<col width="6%" />';
		html	= html + '	<col width="6%" />';
		html	= html + '</colgroup>';
		html	= html + '<tbody>';
		html	= html + '	<tr>';
		html	= html + '		<th class="its-th-align center">구분</th>';
		html	= html + '		<th class="its-th-align center">발주번호</th>';
		html	= html + '		<th class="its-th-align center">거래처</th>';
		html	= html + '		<th class="its-th-align center">상품</th>';
		html	= html + '		<th class="its-th-align center">미입고</th>';
		html	= html + '		<th class="its-th-align center">발주수량</th>';
		html	= html + '		<th class="its-th-align center">발주가액</th>';
		html	= html + '		<th class="its-th-align center">부가세</th>';
		html	= html + '		<th class="its-th-align center">상태</th>';
		html	= html + '		<th class="its-th-align center">관리</th>';
		html	= html + '	</tr>';

		var sorder_status	= '대기';
		var sorder_type		= '비정규';
		for	( var d = 0; d < cnt; d++){
			row			= data[d];
			if	(row.sorder_status == '1')	sorder_status	= '완료';
			sorder_type		= '비정규';
			if	(row.sorder_type == 'A')	{
				sorder_type		= '정규';
			}else if (row.sorder_type == 'T') {
				sorder_type		= '비정규[임]';
			}
			html	= html + '	<tr>';
			html	= html + '		<td class="its-td-align left pdl5">' + sorder_type + '</td>';
			html	= html + '		<td class="its-td-align left pdl5">' + row.sorder_code + '</td>';
			html	= html + '		<td class="its-td-align left pdl5">' + row.trader_name + '</td>';
			html	= html + '		<td class="its-td-align left pdl5">' + row.goods_name + '</td>';
			html	= html + '		<td class="its-td-align right pdr5 red">' + row.remain_ea + '</td>';
			html	= html + '		<td class="its-td-align right pdr5">' + row.oea + '</td>';
			html	= html + '		<td class="its-td-align right pdr5">' + comma(parseInt(row.krw_total_supply_price)) + '</td>';
			html	= html + '		<td class="its-td-align right pdr5">' + comma(parseInt(row.krw_total_supply_tax)) + '</td>';
			html	= html + '		<td class="its-td-align center">' + sorder_status + '</td>';
			html	= html + '		<td class="its-td-align center">';
			html	= html + '			<input type="hidden" name="trader_seq[]" value="'+row.trader_seq+'"/>';
			html	= html + '			<input type="hidden" name="trader_name[]" value="'+row.trader_name+'"/>';
			html	= html + '			<input type="hidden" name="whs_seq[]" value="'+row.whs_seq+'"/>';
			html	= html + '			<input type="hidden" name="remain_ea[]" value="'+row.remain_ea+'"/>';
			html	= html + '			<input type="hidden" name="sorder_seq[]" value="'+row.sorder_seq+'"/>';
			html	= html + '			<input type="hidden" name="sorder_code[]" value="'+row.sorder_code+'"/>';
			html	= html + '			<span class="btn small cyanblue"><button type="button" onclick="sorderSelectEvent(this);">선택</button></span>';
			html	= html + '		</td>';
			html	= html + '	</tr>';			
		}

		html	= html + '</tbody>';
		html	= html + '</table>';	
		
		// 2016.07.20 페이징 추가 pjw
		if(page != null){
			html	= html + '<div class="paging_navigation" style="margin:auto;">'+page.html+'</div>';
		}

		$('#sorder_search_popup').html(html);
		openDialog('발주서 조회', 'sorder_search_popup', {'width': '1000', 'height' : '500'});
		
	}
}

// 발주서 선택 이벤트
function sorderSelectEvent(obj, target){
	var parentObj = $(obj).closest('td');

	if($(parentObj).find('[name="whs_seq[]"]').val() > 0){
		alert('해당 발주에 대한 입고대기 상태의 입고건이 존재합니다.\n입고대기 상태의 입고건을 먼저 입고완료 처리해 주세요.');
		return false;
	}

	if($(parentObj).find('[name="remain_ea[]"]').val() > 0){
		var seq			= $(parentObj).find('[name="sorder_seq[]"]').val();
		var code		= $(parentObj).find('[name="sorder_code[]"]').val();
		var trader_seq	= $(parentObj).find('[name="trader_seq[]"]').val();
		var trader_name	= $(parentObj).find('[name="trader_name[]"]').val();

		$('form[name="detailForm"]').find('input[name="sorder_seq"]').val(seq);
		$('form[name="detailForm"]').find('input[name="sorder_code"]').val(code);
		$('span.trader_name').html(trader_name);
		$('form[name="detailForm"]').find('input[name="trader_seq"]').val(trader_seq);

		var targetObj = this;
		if(target == 'parent') targetObj = parent;

		autoAddSorderGoods(seq, targetObj);
		targetObj.closeDialog('sorder_search_popup');
	}else{
		alert('미입고 수량이 없습니다.');
		return false;
	}
}

// 발주서 선택에 따른 상품 자동 채우기
function autoAddSorderGoods(sorder_seq, target){

	if	(!$("select[name='in_wh_seq']").val()){
		openDialogAlert('입고창고를 선택해 주세요.', 400, 150, function(){});
		return false;
	}

	if	(sorder_seq > 0){
		$.ajax({
			type		: 'get',
			url			: '../scm/getSorderGoodsData',
			data		: 'sono=' + sorder_seq + '&in_wh_seq=' + $("select[name='in_wh_seq']").val(),
			dataType	: 'json', 
			global		: false,
			success		: function(result){
				target = target == null ? this : target;
				if	(result.length > 0){
					resetExcelTableData();
					var data	= target.goodsDataArrayValues(result, 'sorder');
					for	( var r = 0; r < data.length; r++){
						var tmp_tr = target.excelTableObj.addDefaultRow('datas', data[r]);
						calculateExcelTable(tmp_tr.find("input[name='option_seq[]']"), '');
						area_help_tooltip(tmp_tr);
					}
					target.excelTableObj.addDefaultRow('', []);
				}
			}
		});
	}
}

// 거래처 변경에 따른 form 초기화
function resetWarehousingForm(){
	// 초기화
	$('div#sorder_list_lay').html('').hide();
	$("input[name='sorder_seq']").val('');
	$("input[name='sorder_code']").val('');
	$('span#sorder_detail').html('');

	resetExcelTableList();
}

// 입고 저장
function submitWarehousing(nowStatus, status){
	if	(!nowStatus || nowStatus == '0'){
		$("input[name='status']").val(status);
		var except	= $("input[name='except']").val();
		
		// 거래처 체크
		if	(!$("select[name='trader_seq']").val() && !$("input[name='trader_seq']").val()){
			openDialogAlert('거래처를 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		// 입고창고 체크
		if	(!$("select[name='in_wh_seq']").val()){
			openDialogAlert('입고창고를 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		
		if	(except != 'E'){
			// 발주서 체크
			if	(!$("input[name='sorder_seq']").val()){
				openDialogAlert('발주서를 선택해 주세요.', 400, 150, function(){});
				return false;
			}
		}
	}

	if	(status == '1'){
		var msg	= '입고완료 후에는 입고상품을 수정할 수 없습니다.<br/>해당 입고를 입고완료 하시겠습니까?';
		openDialogConfirm(msg, 500, 170, function(){
			excelTableSubmit();
		}, function(){ });
	}else{
		excelTableSubmit();
	}
}


// 입고서 인쇄
function wareHousingPrint(print_list, mode){

	if(mode == 'multi'){
		var print_list	= '';
		var print_cnt	= 0;

		$.each($('input[name="whsSeqArr[]"]:checked'), function(){
			print_list		+= (print_list != '') ? '&whno[]=' + this.value : 'whno[]=' + this.value;
			print_cnt++;
		});

		if(print_cnt < 1){
			openDialogAlert('입고내역을 선택하세요');
			return;
		}
	}
	var get_params	= print_list;

	window.open('../scm/warehousing_prints?' + get_params,'_print','width=1000,height=700,menubar=no,status=no,toobar=no,scrollbars=yes,resizable=no');
}

// 자동 입고데이터 삭제
function autoRemoveWarehousing(){
	var print_list	= '';
	var print_cnt	= 0;
	var flag = false;
	
	$.each($('input[name="whsSeqArr[]"]:checked'), function(){
		var stat = $(this).closest('td').find('input[name="whs_status[]"]').val();
		if(stat > 0){
			flag = true;
			return false;
		}

		print_list		+= (print_list != '') ? '&whno[]=' + this.value : 'whno[]=' + this.value;
		print_cnt++;
	});

	if(flag){
		alert('대기 상태만 삭제 가능합니다.');
		return false;
	}

	if(print_cnt < 1){
		openDialogAlert('입고내역을 선택하세요');
		return;
	}
	
	openDialogConfirm('선택하신 ' + print_cnt + '개의 입고대기 내역을 삭제하시겠습니까?<br/>',400,170,function(){
		loadingStart();
		var orginAct = $("form[name='listFrm']").attr('action');
		$("form[name='listFrm']").attr('method', 'post');
		$("form[name='listFrm']").attr('action', '../scm_process/remove_auto_warehounsing');
		$("form[name='listFrm']").submit();
		$("form[name='listFrm']").attr('method', 'get');
		$("form[name='listFrm']").attr('action', orginAct);
	});
	
}

//------------ ↑↑ warehousing ↑↑------ ↓↓ carryingout ↓↓--------------//

// 최근 입고내역에서 매입단가 선택 팝업
function openSelectWarehousing(targetID, returnFunc, goods_seq, option_type, option_seq, limit){
	var params	= 'goods_seq=' + goods_seq + '&option_type=' + option_type + '&option_seq=' + option_seq + '&returnFunc=' + returnFunc + '&targetID=' + targetID;
	var height	= 170;
	if	(limit > 0){
		params	+= '&limit=' + limit;
		height	= parseInt(height) + (limit * 30);
	}

	$.ajax({
		type	: 'get',
		url		: '../scm/get_lastwarehousing',
		data	: params,
		global	: false,
		success	: function(result){
			$('div#' + targetID).html(result);
			openDialog('최근 입고내역', targetID, {'width':600,'height':height});
		}
	});
}

// 상품에 대한 창고별 재고 팝업
function openGoodsWarehouseStock(id, returnFunc, goods_seq, option_type, option_seq){
	if	(id && goods_seq > 0){
		var params	= 'targetID=' + id + '&goods_seq=' + goods_seq;
		if	(option_type && option_seq){
			params	+= '&option_type=' + option_type + '&option_seq=' + option_seq;
		}
		if	(returnFunc)
			params	+= '&returnFunc=' + returnFunc;

		// 새로 가져옴
		$.ajax({
			type	: 'get',
			url		: '../scm/scm_warehouse_stock',
			data	: params,
			success	: function(result){
				$('div#' + id).html(result);
				var width		= $('div#' + id).find('table').width() + 60;
				if	(width > 1000)	width	= 1000;
				var rowCount	= $('div#' + id).find('table tbody tr').length;
				var height		= 220 + (rowCount * 25);
				openDialog('창고별 재고', id, {'width':width,'height':height,'overflow-x':'scroll'});
			}
		});
	}else{
		openDialogAlert('상품이 없습니다.', 400, 150, function(){});
		return false;
	}
}

// 반출 저장
function submitCarryingout(nowStatus, status){
	if	(!nowStatus || nowStatus == '0'){
		$("input[name='status']").val(status);
		// 반출창고 체크
		if	(!$("select[name='wh_seq']").val()){
			openDialogAlert('반출창고를 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		// 거래처 체크
		if	(!$("select[name='trader_seq']").val()){
			openDialogAlert('거래처를 선택해 주세요.', 400, 150, function(){});
			return false;
		}

		// 입고상품 체크
		var optCnt		= 0;
		var chkStatus	= true;
		$("input[name='option_seq[]']").each(function(){
			if	($(this).val()){
				optCnt++;
				if	( !(parseInt($(this).closest('tr').find("input[name='ea[]']").val()) > 0) ){
					chkStatus	= false;
					openDialogAlert('반출수량은 1이상이어야 합니다. 반출수량을 확인해 주세요.', 400, 150, function(){});
					return false;
				}
				if	( parseInt($(this).closest('tr').find("input[name='ea[]']").val()) > parseInt($(this).closest('tr').find("input[name='stock[]']").val()) ){
					chkStatus	= false;
					openDialogAlert('반출수량은 반출창고에 있는 재고수량을 초과할 수 없습니다.', 400, 150, function(){});
					return false;
				}
				var tax	= '';
				if	($(this).closest('tr').find("input[name='tax[]']").attr('checked')){
					tax	= '과세';
				}
				if	($(this).closest('td').find("input[name='hide_tax[]']").attr('orgTagName') == 'tax'){
					$(this).closest('td').find("input[name='hide_tax[]']").val(tax);
				}else{
					$(this).closest('td').append('<input type="hidden" name="hide_tax[]" orgTagName="tax" value="' + tax + '" />');
				}
			}
		});
		if	(!optCnt){
			chkStatus	= false;
			openDialogAlert('반출상품을 선택해 주세요.', 400, 150, function(){});
			return false;
		}
		if	(!chkStatus){
			return false;
		}
	}

	if	(status == '1'){
		var msg	= '반출완료 후에는 반출상품을 수정할 수 없습니다.<br/>반출완료 하시겠습니까?';
		openDialogConfirm(msg, 500, 170, function(){
			$('div#excelTable').find("input[name='option_seq[]']").each(function(){
				$(this).attr('checked', true);
			});

			loadingStart();
			$("form[name='detailForm']").submit();
		}, function(){ });
	}else{
		$('div#excelTable').find("input[name='option_seq[]']").each(function(){
			$(this).attr('checked', true);
		});

		loadingStart();
		$("form[name='detailForm']").submit();
	}
}

// 반출 명세서 인쇄
function carryingoutPrints(obj){
	var print_crono_list	= '';
	var print_crono_cnt	= 0;
	
	$.each($('input[name="cro_seq[]"]:checked'), function(){
		print_crono_list		+= (print_crono_list != '') ? '&crono[]=' + this.value : 'crono[]=' + this.value;
		print_crono_cnt++;
	});

	if(print_crono_cnt < 1){
		openDialogAlert('반출명세서를 선택하세요');
		return;
	}

	carryingoutPrint(print_crono_list,'multi');
}

// 반출 명세서 인쇄
function carryingoutPrint(crono,mode){
	if(mode == 'multi'){
		var get_params	= crono;
	}else{
		var get_params	= "crono[]=" + crono;
	}
	window.open('../scm/carryingout_prints?' + get_params,'_print','width=1000,height=700,menubar=no,status=no,toobar=no,scrollbars=yes,resizable=no');
}

//------------ ↑↑ carryingout ↑↑------ ↓↓ autoorder ↓↓--------------//

// 자동 발주서 등록
function autoOrderSubmit(){
	var chkCnt		= 0;
	var noTraderCnt	= 0;
	var chkTrader	= true;
	$('input.chk').each(function(){
		if	($(this).attr('checked')){
			if	(!($(this).closest('tr').find('input.trader_seq').val() > 0))	noTraderCnt++;
			chkCnt++;
		}
	});
	if	(noTraderCnt > 0){
		if	($("input[name='substitute_trader_seq']").val() > 0)	chkTrader	= true;
		else														chkTrader	= false;
	}

	if	(chkTrader){
		if	(chkCnt > 0){
			loadingStart();
			$("form[name='listFrm']").submit();
		}else{
			openDialogAlert('선택된 상품이 없습니다.', 400, 150, function(){});
			return false;
		}
	}else{
		$('span#no_trader').html(comma(noTraderCnt));
		openDialog('발주서 등록', 'regist_fail_popup', {'width':600,'height':200});
	}
}

// 대체 거래처 등록
function set_substitute_trader_seq(){
	var trader_seq	= $('div#regist_fail_popup').find("select[name='select_trader_seq']").val();
	if	(trader_seq){
		$("input[name='substitute_trader_seq']").val(trader_seq);
	}else{
		openDialogAlert('대체 거래처를 선택해 주세요.', 400, 150, function(){});
		return false;
	}
	autoOrderSubmit();
}

// 자동 발주서 삭제
function autoRemoveOrder(){
	var chkCnt		= 0;
	$('input.chk').each(function(){
		if	($(this).attr('checked'))	chkCnt++;
	});

	if	(chkCnt > 0){
		loadingStart();
		var orginAct = $("form[name='listFrm']").attr('action');
		$("form[name='listFrm']").attr('action', '../scm_process/remove_auto_order');
		$("form[name='listFrm']").submit();
		$("form[name='listFrm']").attr('action', orginAct);
	}else{
		openDialogAlert('선택된 상품이 없습니다.', 400, 150, function(){});
		return false;
	}
}

//자동발주조건 팝업
function auto_cond_popup(){
	openDialog('자동발주상품조건', 'auto_condition_popup', {'width':600,'height':450});
}

//발주서 등록 결과 팝업 (정상)
function regist_success_popup(){
	openDialog('발주서 등록', 'regist_success_popup', {'width':600,'height':350});
}

//거래처 등록 시 선택 여부 확인
function chkTrader(obj){
	var groupVal = $(obj).find('select[name="sc_trader_group"] option:selected').val();
	var traderVal = $(obj).find('select[name="sc_trader"] option:selected').val();

	if(groupVal == '' || traderVal == ''){
		alert('거래처를 선택해 주세요.');
		return false;
	}

	closeDialog('regist_fail_popup');
	return true;
}

function chgHighLight(targetClass, pointClass, obj){
	$(obj).closest('div.highlight-lay').find('.' + targetClass).removeClass(pointClass);
	$(obj).addClass(pointClass);
}

//------------ ↑↑ autoorder ↑↑------ ↓↓ sorder_whs ↓↓--------------//

// 월 선택에 따른 검색값 변경
function chgSorderWhsSearchMonth(m){
	loadingStart();
	$("input[name='sc_month']").val(m);
	$("form[name='listSrcForm']").submit();
}

//------------ ↑↑ sorder_whs ↑↑------ ↓↓ traderaccount ↓↓--------------//

// 거래명세서 인쇄
function openTraderAccountPrint(trader_seq){
	var url				= '../scm_warehousing/traderaccount_print?ispopup=y&' + QUERY_STRING;
	if		(trader_seq > 0)	url		+= '&sc_trader_seq=' + trader_seq;

	window.open(url, 'TRADERACCOUNT_PRINT', 'width=900px,height=800px,titlebar=no,toolbar=no,scrollbars=yes');
}

// 지급 등록 팝업
function openAddAccount(){
	var params		= '';

	$.ajax({
		type		: 'get',
		url			: './traderaccount_add',
		data		: params, 
		success		: function(result){
			$('div#traderaccount_add').html(result);
			openDialog('지급 내역 등록', 'traderaccount_add', {'width':600,'height':400});
		}
	});
}

// 입력폼 초기화
function inputFormReset(){
	$("select[name='trader_group'] option").eq(0).attr('selected', true).change();
	$("input[name='modify_idx']").val('none');
	$("input[name='org_type']").val('KRW');
	$("input[name='exchange_price']").val('0');
	$("input[name='act_price']").val('0');
	$("input[name='act_memo']").val('');
	$("input[name='act_price']").attr('readonly', false).css('background-color', '#ffffff');
	$('span.exchage-btn').removeClass('cyanblue').addClass('gray');
	$('span.description').html('');
}

// 외화 입력팝업 오픈
function openExchange(obj){
	$('div#input_exchange_lay').find('select.price-type').find('option').eq(0).attr('selected', true);
	$('div#input_exchange_lay').find('input.exchange_price').val('0').attr('disabled', true);
	$('div#input_exchange_lay').find('input.price').val('0');
	$('div#input_exchange_lay').find('input.krw_price').val('0');
	openDialog('외화 입력', 'input_exchange_lay', {'width':400,'height':300});
}

// 환율에 따른 한화 금액 계산
function calculateNormalExchange(obj){
	var price_type	= $(obj).closest('div').find('select.price-type').val();
	var price		= $(obj).closest('div').find('input.price').val();
	var exchange	= $(obj).closest('div').find('input.exchange_price').val();

	if	(price_type == 'KRW'){
		var result		= Math.round(price);
		$(obj).closest('div').find('input.exchange_price').val(0).attr('disabled', true);
	}else{
		// 화폐가 변경된 경우
		if	(!$(obj).hasClass('exchange_price')){
			exchange	= exchanges[price_type]['currency_exchange'];
		}
		var result			= Math.round(float_calculate('multiply', price, exchange));
		$(obj).closest('div').find('input.exchange_price').val(exchange).attr('disabled', false);
	}

	$(obj).closest('div').find('input.krw_price').val(result);
}

// 외화 입력적용
function applyExchange(){
	var obj	= $('div#input_exchange_lay');
	var description	= '';
	$("input[name='org_type']").val(obj.closest('div').find('select.price-type').val());
	$("input[name='org_price']").val(obj.closest('div').find('input.price').val());
	$("input[name='exchange_price']").val(obj.closest('div').find('input.exchange_price').val());
	$("input[name='act_price']").val(obj.closest('div').find('input.krw_price').val());
	if	(obj.closest('div').find('select.price-type').val() == 'KRW'){
		$("input[name='act_price']").attr('readonly', false).css('background-color', '#ffffff');
		$('span.exchage-btn').removeClass('cyanblue').addClass('gray');
	}else{
		$("input[name='act_price']").attr('readonly', true).css('background-color', '#e6e6e6');
		$('span.exchage-btn').removeClass('gray').addClass('cyanblue');
		description	= obj.closest('div').find('select.price-type').val() + ' '
					+ obj.closest('div').find('input.price').val()
					+ ' (환율 ' + obj.closest('div').find('input.exchange_price').val() + ')';
	}
	$('span.description').html(description);

	closeDialog('input_exchange_lay');
}

// 거래처 선택에 따른 처리
function choice_trader(obj){
	var optObj		= $(obj).find('option:selected');
	var bankHTML	= '';
	if	(optObj.attr('bank_name') && optObj.attr('bank_owner') && optObj.attr('bank_number')){
		bankHTML	= bankList[optObj.attr('bank_name')] + ' ' + optObj.attr('bank_number')
					+ ' ' + optObj.attr('bank_owner');
	}
	$("input[name='act_memo']").val(bankHTML);
}

// 임시 목록에 추가
function addAccountTmp(obj){
	if(!trader_account_form_check()){
		return false;
	}
	var tmpObj			= $('div.add_pay_tmp_list');
	var trader_group	= $("select[name='trader_group']").val();
	var trader_seq		= $("select[name='trader_seq']").val();
	var trader_name		= $("select[name='trader_seq']").find('option:selected').attr('trader_name');
	var org_type		= $("input[name='org_type']").val();
	var org_price		= $("input[name='org_price']").val();
	var exchange_price	= $("input[name='exchange_price']").val();
	var act_price		= $("input[name='act_price']").val();
	var act_memo		= $("input[name='act_memo']").val();
	var act_date		= $("input[name='act_date']").val();

	// 추가모드
	if	($("input[name='modify_idx']").val() == 'none'){
		var cnt				= 0;
		var total			= 0;
		var appendHTML		= '';
		appendHTML			+= '<tr>';
		appendHTML			+= '<td class="its-td-align left pdl5">' + trader_name + '</td>';
		appendHTML			+= '<td class="its-td-align right pdr5">' + comma(act_price) + '</td>';
		appendHTML			+= '<td class="its-td-align center">';
		appendHTML			+= '<input type="hidden" name="tmp_trader_group[]" value="' + trader_group + '" />';
		appendHTML			+= '<input type="hidden" frmname="거래처" name="tmp_trader_seq[]" value="' + trader_seq + '" />';
		appendHTML			+= '<input type="hidden" name="tmp_org_type[]" value="' + org_type + '" />';
		appendHTML			+= '<input type="hidden" name="tmp_org_price[]" value="' + org_price + '" />';
		appendHTML			+= '<input type="hidden" name="tmp_exchange_price[]" value="' + exchange_price + '" />';
		appendHTML			+= '<input type="hidden" frmname="금액" isprice="1" name="tmp_act_price[]" value="' + act_price + '" />';
		appendHTML			+= '<input type="hidden" name="tmp_act_memo[]" value="' + act_memo + '" />';
		appendHTML			+= '<input type="hidden" frmname="기준일자" name="tmp_act_date[]" value="' + act_date + '" />';
		appendHTML			+= '<span class="btn small"><button type="button" onclick="delAccountTmp(this);">삭제</button></span>';
		appendHTML			+= '<span class="btn small"><button type="button" onclick="modAccountTmp(this);">변경</button></span>';
		appendHTML			+= '</td>';
		appendHTML			+= '</tr>';
		tmpObj.find('tbody').append(appendHTML);
		if	(tmpObj.find('tbody tr').length){
			tmpObj.find('tbody tr').each(function(){
				cnt++;
				total		= parseInt(total) + parseInt($(this).find("input[name='tmp_act_price[]']").val());
			});
		}
		$('span.tmp-list-count').html(comma(cnt) + '건');
		$('span.total-price').html(comma(total));

		tmpObj.show();

	// 변경모드
	}else{
		var trObj	= tmpObj.find('tbody tr').eq($("input[name='modify_idx']").val());
		trObj.find('td').eq(0).html(trader_name);
		trObj.find('td').eq(1).html(comma(act_price));
		trObj.find('td').find("input[name='tmp_trader_group[]']").val(trader_group);
		trObj.find('td').find("input[name='tmp_trader_seq[]']").val(trader_seq);
		trObj.find('td').find("input[name='tmp_org_type[]']").val(org_type);
		trObj.find('td').find("input[name='tmp_org_price[]']").val(org_price);
		trObj.find('td').find("input[name='tmp_exchange_price[]']").val(exchange_price);
		trObj.find('td').find("input[name='tmp_act_price[]']").val(act_price);
		trObj.find('td').find("input[name='tmp_act_memo[]']").val(act_memo);
		trObj.find('td').find("input[name='tmp_act_date[]']").val(act_date);
		trObj.find('button').eq(1).html('변경');
		modAccountFlag = false;
	}

	inputFormReset();
}

// 임시 지급내역 삭제
function delAccountTmp(obj){
	if (modAccountFlag){
		alert('변경 중엔 지급 건수를 삭제하실 수 없습니다.');
		return false;
	}
	var tmpObj	= $('div.add_pay_tmp_list');
	var cnt		= 0;
	var total	= 0;
	$(obj).closest('tr').remove();
	if	(tmpObj.find('tbody tr').length){
		tmpObj.find('tbody tr').each(function(){
			cnt++;
			total		= parseInt(total) + parseInt($(this).find("input[name='tmp_act_price[]']").val());
		});
		$('span.tmp-list-count').html(comma(cnt) + '건');
		$('span.total-price').html(comma(total));
		tmpObj.show();
	}else{
		tmpObj.hide();
	}
}

// 임시 지급내역 수정
var modAccountFlag = false;
function modAccountTmp(obj){	
	if	($(obj).html() == '변경취소'){
		modAccountFlag = false;
		inputFormReset();
		$("input[name='modify_idx']").val('none');
		$(obj).html('변경');
	}else{
		if (modAccountFlag){
			alert('변경 중인 지급내역이 있습니다.');
			return false;
		}
		modAccountFlag = true;
		var idx		= $('div.add_pay_tmp_list tbody tr').index($(obj).closest('tr'));
		$("input[name='modify_idx']").val(idx);

		var trader_group	= $(obj).closest('tr').find("input[name='tmp_trader_group[]']").val();
		var trader_seq		= $(obj).closest('tr').find("input[name='tmp_trader_seq[]']").val();
		var org_type		= $(obj).closest('tr').find("input[name='tmp_org_type[]']").val();
		var org_price		= $(obj).closest('tr').find("input[name='tmp_org_price[]']").val();
		var exchange_price	= $(obj).closest('tr').find("input[name='tmp_exchange_price[]']").val();
		var act_price		= $(obj).closest('tr').find("input[name='tmp_act_price[]']").val();
		var act_memo		= $(obj).closest('tr').find("input[name='tmp_act_memo[]']").val();
		var act_date		= $(obj).closest('tr').find("input[name='tmp_act_date[]']").val();

		$("select[name='trader_group']").find("option[value='" + trader_group + "']").attr('selected', true).change();
		$("select[name='trader_seq']").find("option[value='" + trader_seq + "']").attr('selected', true);
		$("input[name='org_type']").val(org_type);
		$("input[name='org_price']").val(org_price);
		$("input[name='exchange_price']").val(exchange_price);
		$("input[name='act_price']").val(act_price);
		$("input[name='act_memo']").val(act_memo);
		$("input[name='act_date']").val(act_date);
		if	(org_type == 'KRW'){
			$("input[name='act_price']").attr('readonly', false).css('background-color', '#ffffff');
			$('span.exchage-btn').removeClass('cyanblue').addClass('gray');
			$('span.description').html('');
		}else{
			$("input[name='act_price']").attr('readonly', true).css('background-color', '#e6e6e6');
			$('span.exchage-btn').removeClass('gray').addClass('cyanblue');
			var description	= org_type + ' ' + org_price + ' (환율 ' + exchange_price + ')';
			$('span.description').html(description);
		}
		$(obj).html('변경취소');
	}
}

// 거래내역 링크 처리
function openAccountDetailInfo(obj){
	if	($(obj).find('input.act_type').val() == 'pay'){
		var priceHTML	= comma($(obj).find('input.act_price').val());
		if	($(obj).find('input.org_type').val() != 'KRW'){
			priceHTML	+= ' (' + $(obj).find('input.org_type').val() + ' '
						+ float_comma($(obj).find('input.org_price').val()) + ' / '
						+ '환율 ' + float_comma($(obj).find('input.exchange_price').val());
		}
		$('div#account_pay_detail').find('td.act-date').html($(obj).find('input.act_date').val());
		$('div#account_pay_detail').find('td.trader-name').html($(obj).find('input.trader_name').val());
		$('div#account_pay_detail').find('td.act-price').html(priceHTML);
		$('div#account_pay_detail').find('td.act-memo').html($(obj).find('input.act_memo').val());

		openDialog('지급 내역', 'account_pay_detail', {'width':500,'height':270});
	}else{
		if	($(obj).find('input.act_fkey').val() > 0){
			var url	= './warehousing_regist?whsno=' + $(obj).find('input.act_fkey').val();
			if	($(obj).find('input.act_type').val() == 'carryingout')
				url	= './carryingout_regist?crono=' + $(obj).find('input.act_fkey').val();
			window.open(url);
		}else{
			openDialogAlert('해당 내역을 찾을 수 없습니다.', 400, 150, function(){});
			return false;
		}
	}
}

// 거래처 지급내역 등록
function trader_account_submit(){
	if(trader_account_form_check(true)){
		$('form[name="traderAccountFrm"]').submit();
	}	
}

// 거래처 지급 폼검사
function trader_account_form_check(issubmit){
	var frmObj = $('form[name="traderAccountFrm"]');
	var flag = true;
	
	if(issubmit && $(frmObj).find('input[name*="tmp_"], select[name*="tmp_"]').length > 0) return true;
	$(frmObj).find('input, select').each(function(){
	
		if($(this).attr('frmname') && $(this).val() == ''){
			openDialogAlert($(this).attr('frmname')+'을(를) 입력하세요.', '400', '150');
			flag = false;
			return false;			
		}else if($(this).attr('isprice') && $(this).val() == '0'){
			openDialogAlert($(this).attr('frmname')+'은(는) 0 이상 입력해 주세요.', '400', '150');
			flag = false;
			return false;
		}
	});

	return flag;
}

//------------ ↑↑ traderaccount ↑↑------ ↓↓ ledger ↓↓--------------//

// 기타 검색조건 변경시
function searchformchange(){
	$("form[name='listSrcForm']").submit();
}

// 선택시 결과 처리
function sel_ledger(goods_seq,option_type,option_seq,out_supply_price){
	var wh_seq		= $("select[name='sc_wh_seq']").val();
	var url			= './ledger_detail'
					+ '?goods_seq=' + goods_seq
					+ '&option_type=' + option_type
					+ '&option_seq=' + option_seq
					+ '&out_supply_price=' + out_supply_price
					+ '&' + server_query_string;
	var popup_name	= 'LEDGER_DETAIL_' + goods_seq + option_type + option_seq;
	window.open(url, popup_name, 'width=1000,height=800,titlebar=n,toolbar=n,scrollbars=yes');
}

// 재고수불부 인쇄
function ledgerPrint(){
	var chk		= false;
	var params	= server_query_string;
	$("input[name='optioninfo[]']").each(function(){
		if	($(this).attr('checked')){
			chk	= true;
			params	+= '&optioninfo[]=' + $(this).val();
		}
	});
	if	(!chk){
		openDialogAlert('선택된 상품이 없습니다.', 400, 150, function(){});
		return false;
	}

	var url	= './ledger_print?' + params;
	window.open(url, 'LEDGER_PRINT', 'width=600,height=800,titlebar=n,toolbar=n,scrollbars=yes');
}

// 검색 년도 선택에 따른 처리
function chgYearVal(obj){
	$(obj).closest('td').find('span.vYear').html($(obj).val());
}

// 월/분기 선택 영역 변경
function chgMonthQuaterArea(obj){
	var className	= 'sc-' + $(obj).val() + '-select-area';
	$(obj).closest('td').find('span.sc-month-select-area').hide();
	$(obj).closest('td').find('span.sc-quater-select-area').hide();
	$(obj).closest('td').find('span.' + className).show();
}

// 월 선택 처리
function selected_sc_month(obj){
	$('span.sc-month-select-area').find('span.cyanblue').removeClass('cyanblue');
	$(obj).closest('span.btn').addClass('cyanblue');
	$(obj).closest('span.sc-month-select-area').find('input.sc_month').val($(obj).attr('m'));
}

// 분기 선택 처리
function selected_sc_quater(obj){
	$('span.sc-quater-select-area').find('span.cyanblue').removeClass('cyanblue');
	$(obj).closest('span.btn').addClass('cyanblue');
	$(obj).closest('span.sc-quater-select-area').find('input.sc_quater').val($(obj).attr('q'));
}

//------------ ↑↑ ledger ↑↑------ ↓↓ util ↓↓--------------//

// javascript Trim 처리
function jsTrim(val){
	return val.replace(/(^\s*)|(\s*$)/gi, '');
}

// js 페이징 생성 ( pages = result.page, clickFunc = string )
function getPagingHTML(pages, clickFunc){
	var page			= 0;
	var pageHTML		= '';
	var pageCnt			= pages.page.length;

	if	(pageCnt > 1){

		// 이전 페이징 블럭
		if	(pages.page[0] > 1)								pageHTML	+= '<span onclick="' + clickFunc + '(\'' + (pages.page[0] - 10) + '\');">[이전]</span>';

		for	( var i = 0; i < pageCnt; i++){
			page	= pages.page[i];
			if	(pages.nowpage == page)						pageHTML	+= '<span class="current">' + page + '</span>';
			else											pageHTML	+= '<span class="page-' + page + '"  onclick="' + clickFunc + '(\'' + page + '\');">' + page + '</span>';
		}

		// 다음 페이징 블럭
		if	(pages.totalpage > pages.page[(pageCnt - 1)])	pageHTML	+= '<span onclick="' + clickFunc + '(\'' + (pages.page[0] + 10) + '\');">[다음]</span>';

	}

	return pageHTML;
}

// 해당 금액의 부가세 계산
function calculate_tax_price(currency, price){
	var tax_rate	= 0.1;
	// currency에 따라 tax_rate는 조정될 수 있다. ( 조정될 시 조정되는 값은 cfg_exchange에 넣을 예정 )

	var tax_price	= float_calculate('multiply', price, tax_rate);
	tax_price		= Math.floor(tax_price * 100) * 0.01;	// 소숫점 둘째자리 이하는 버림.

	return tax_price;
}

// 절사 설정에 따른
function calculate_cut_price(currency, price){
	var result	= price;
	var cfg		= exchanges[currency];
	if	(cfg.use_status == 'Y'){
		result		= eval('Math.' + cfg.cut_type + '(result / cfg.cut_unit)');
		result		= float_calculate('multiply', result, cfg.cut_unit);
	}

	return result;
}

// javascript 소숫점 4칙연산에 버그가 있어서 별도 처리함. ( ex: 1.2 + 0.12 = 1.31999999999998이됨. )
function float_calculate(type, num1, num2){
	// 소숫점이 있는지 확인
	var tmp							= new Array();
	var dec = decCnt = dec1 = dec2 = result = 0;
	num1			= num1 + '';
	num2			= num2 + '';
	if	(num1.search(/\./) != -1){
		tmp		= num1.split('.');
		dec1	= tmp[1].length;
	}
	if	(num2.search(/\./) != -1){
		tmp		= num2.split('.');
		dec2	= tmp[1].length;
	}
	decCnt		= dec1;
	if	(type == 'multiply'){
		decCnt		= parseInt(dec1) + parseInt(dec2);
	}else{
		if	(dec1 < dec2)	decCnt	= dec2;
	}
	dec			= Math.pow(10, decCnt);

	// 사칙연산 처리
	switch(type){
		// 더하기
		case 'plus':
			result	= ( ( num1 * dec ) + ( num2 * dec ) ) / dec;
		break;
		// 빼기
		case 'minus':
			result	= ( ( num1 * dec ) - ( num2 * dec ) ) / dec;
		break;
		// 곱하기
		case 'multiply':
			result	= ( ( num1 * dec ) * ( num2 * dec ) ) / Math.pow(dec, 2);
		break;
		// 나누기
		case 'divide':
			result	= ( num1 * dec ) / ( num2 * dec );
		break;
	}

	result	= result + '';
	if	(result.search(/\./) != -1){
		tmp		= result.split('.');
		if	(decCnt < tmp[1].length)	result	= new Number(result).toFixed(decCnt);
	}

	return result;
}

// 환율에 따른 환전 ( 외화 -> 한화 )
function krw_exchange(type, price){
	var currency	= type.toUpperCase();
	var cfg			= exchanges[currency];
	var krw_cfg		= exchanges['KRW'];
	var result		= price;

	// 환전
	if	(currency != 'KRW'){
		result		= float_calculate('multiply', price, cfg['currency_exchange']);
	}

	result			= calculate_cut_price('KRW', result);

	return result;
}

// 한화를 해당 외화로 환전 ( 한화 -> 외화 )
function exchange_krw(type, price){
	var currency	= type.toUpperCase();
	var cfg			= exchanges[currency];
	var krw_cfg		= exchanges['KRW'];
	var result		= price;

	// 환전
	if	(currency != 'KRW'){
		result		= float_calculate('divide', price, cfg['currency_exchange']);
	}

	result			= calculate_cut_price(currency, result);

	return result;
}

// 진행바 생성
function setProgressBar(title, link){
	$('body').append('<div id="progressbar"></div>');
	var useTitle	= false;
	if	(title)	useTitle	= true;

	progObj		= $("#progressbar").fmprogressbar({
		'debugMode'			: false, 
		'useDetail'			: false, 
		'loadMode'			: false, 
		'useTitle'			: useTitle, 
		'zIndex'			: '1000', 
		'barHeight'			: '16', 
		'barOutPadding'		: '10', 
		'titleBarText'		: title, 
		'defaultLink'		: link, 
		'procgressEnd'		: 'completeSendStock' 
	});
}

// 진행바 오픈
function openProgress(){
	if	(progObj)	progObj.openProgress();
}

// 진행바 종료
function closeProgress(){
	if	(progObj)	progObj.closeProgress();
	progObj	= '';
}

// 처리 페이지 추가
function addProcFrame(link){
	if	(progObj)	progObj.addProcFrame(link);
}

// 처리 페이지 URL 변경
function chgProcFrameSrc(link, num){
	if	(progObj)	progObj.chgProcFrameSrc(link, num);
}

// 처리 퍼센트 증가
function addProgPercent(addPer){
	if	(progObj)	progObj.addPercent(addPer);
}

// 처리 로그 추가
function addDetailLog(str){
	if	(progObj)	progObj.addDetailLog(str);
}

// 하위 매장에 재고 전송 완료 후 메시지 및 이동 URL
var endTitle	= '';
var returnURL	= '';
function setSendCompleteBack(title, url){
	endTitle	= title;
	returnURL	= url;
}

// 하위 매장에 재고 전송 완료
function completeSendStock(){
	if	(returnURL == 'close'){
		window.close();
	}else{
		var msg	= '다른 매장에 재고정보 전송이 완료되었습니다.';
		if	(endTitle)	msg		= msg + '<br/>' + endTitle;
		closeProgress();
		openDialogAlert(msg, 400, 150, function(){
			if			(returnURL == 'reload'){
				location.reload();
			}else if	(returnURL){
				location.href	= returnURL;
			}
		});
	}
}

// 목록 삭제용 submit
function deleteSubmit(){
	var frmObj		= $("form[name='listFrm']");
	var chkStatus	= false;
	frmObj.find('input.chk').each(function(){
		if	($(this).attr('checked')){
			chkStatus	= true;
			return false;
		}
	});

	if	(!chkStatus){
		openDialogAlert('선택된 항목이 없습니다.', 400, 150, function(){});
		return false;
	}else{
		loadingStart();
		frmObj.submit();
	}
}

// 중료표시 일괄 처리
function chk_favorite_all(obj, type){
	if	($(obj).hasClass('checked'))	var chk	= '0';
	else								var chk	= '1';

	var params	= 'type=' + type + '&chk=' + chk;
	$('.select-star').each(function(){
		if	(($(this).hasClass('checked') && chk === '0') || (!$(this).hasClass('checked') && chk === '1')){
			params	= params + '&seq[]=' + $(this).attr('seq');
			if	($(this).hasClass('checked') && chk === '0')	$(this).removeClass('checked');
			else												$(this).addClass('checked');
		}
	});

	$.ajax({
		type	: 'post',
		url		: '../scm/chk_favorite',
		data	: params,
		global	: false,
		success	: function(result){
			if	(chk === '1')	$(obj).addClass('checked');
			else				$(obj).removeClass('checked');
		}
	});
}

// 중요표시 처리
function chk_favorite(obj, type){
	if	($(obj).attr('seq') > 0 && type){
		var params	= 'type=' + type + '&seq=' + $(obj).attr('seq');
		if	($(obj).hasClass('checked'))	var chk	= '0';
		else								var chk	= '1';
		params	= params + '&chk=' + chk;

		$.ajax({
			type	: 'post',
			url		: '../scm/chk_favorite',
			data	: params,
			global	: false,
			success	: function(result){
				if	(chk === '1')	$(obj).addClass('checked');
				else				$(obj).removeClass('checked');
			}
		});
	}
}

// 목록 검색 폼 submit
function list_src_form_submit(){
	$("form[name='listSrcForm']").submit();
}

// 엑셀 다운로드
function excel_download(type){
	if	(type == 'select'){

		// 선택다운, 검색다운 값 목록에 추가
		if	($("form[name='listFrm']").find("input[name='excel_type']").attr('name')){
			$("form[name='listFrm']").find("input[name='excel_type']").val('select');
		}else{
			var addInput	= '<input type="hidden" name="excel_type" value="select" />';
			$("form[name='listFrm']").append(addInput);
		}

		// 검색에 정렬값이 있는 경우 목록에 정렬값 추가
		if	($("form[name='listSrcForm']").find("select[name='orderby']").val()){
			if	($("form[name='listFrm']").find("input[name='orderby']").val()){
				$("form[name='listFrm']").find("input[name='orderby']").val($("form[name='listSrcForm']").find("select[name='orderby']").val());
			}else{
				var addInput	= '<input type="hidden" name="orderby" value="' + $("form[name='listSrcForm']").find("select[name='orderby']").val() + '" />';
				$("form[name='listFrm']").append(addInput);
			}
		}

		// 엑셀 양식이 있는 경우 목록에 양식값 추가
		if	($("form[name='listSrcForm']").find("select[name='excel_form']").val()){
			if	($("form[name='listFrm']").find("input[name='excel_form']").val()){
				$("form[name='listFrm']").find("input[name='excel_form']").val($("form[name='listSrcForm']").find("select[name='excel_form']").val());
			}else{
				var addInput	= '<input type="hidden" name="excel_form" value="' + $("form[name='listSrcForm']").find("select[name='excel_form']").val() + '" />';
				$("form[name='listFrm']").append(addInput);
			}
		}
		var tmp_target = $("form[name='listFrm']").attr('target');
		var tmp_method = $("form[name='listFrm']").attr('method');
		var tmp_action = $("form[name='listFrm']").attr('action');

		$("form[name='listFrm']").attr('target', 'actionFrame');
		$("form[name='listFrm']").attr('method', 'post');
		$("form[name='listFrm']").attr('action', $("input[name='actionURL']").val());
		$("form[name='listFrm']").submit();
		$("form[name='listFrm']").attr('target', tmp_target);
		$("form[name='listFrm']").attr('method', tmp_method);
		$("form[name='listFrm']").attr('action', tmp_action);
	}else{
		if	($("form[name='listSrcForm']").find("input[name='excel_type']").attr('name')){
			$("form[name='listSrcForm']").find("input[name='excel_type']").val('');
		}else{
			var addInput	= '<input type="hidden" name="excel_type" value="" />';
			$("form[name='listSrcForm']").append(addInput);
		}
		
		$("form[name='listSrcForm']").attr('target', 'actionFrame');
		$("form[name='listSrcForm']").attr('method', 'post');
		$("form[name='listSrcForm']").attr('action', $("input[name='actionURL']").val());
		$("form[name='listSrcForm']").submit();

		$("form[name='listSrcForm']").attr('target', '');
		$("form[name='listSrcForm']").attr('method', 'get');
		$("form[name='listSrcForm']").attr('action', '');
	}
}

// 창고 선택 시 하위 로케이션 selectbox 완성 ( 하위 로케이션 class sc-location )
function search_select_warehouse(obj){
	var wh_seq		= $(obj).val();
	$('select.sc-location').html('<option value="">전체</option>');
	if	(wh_seq > 0){
		$.ajax({
			type	: 'get',
			url		: '../scm/getWarehouseLocationList',
			data	: 'wh_seq=' + wh_seq,
			dataType: 'json',
			success	: function(result){
				if	(result.length > 0){
					for	(var i = 0; i < result.length; i++){
						$('select.sc-location').append('<option value="' + result[i].location_position + '">' + result[i].location_code + '</option>');
					}
				}
			}
		});
	}
}

// 분류관리 팝업 오픈 jtree menu event 호출 문제로 iframe으로 처리함
function openScmCategoryPopup(){
	openDialog('분류관리', 'scm_category', {'width':800, 'height':492});
	$('div#scm_category').css({'padding':'0','overflow':'hidden'});
}

// 거래처 그룹 선택 시 해당 그룹의 거래처 목록 추출
function get_trader_to_group(obj){
	var groupName	= $(obj).val();
	var needData	= $(obj).attr('needData');
	var traderObj	= $(obj).closest('td').find('select').eq(1);
	var appendHTML	= '';

	if	(excelTableObj)	resetExcelTableList();

	traderObj.find('option').each(function(){
		if	($(this).attr('value'))	$(this).remove();
	});
	if	(groupName){
		// 새로 가져옴
		$.ajax({
			type	: 'get',
			url		: '../scm/getTraderData',
			data	: 'perpage=99999999&sc_trader_group=' + encodeURIComponent(groupName),
			dataType: 'json',
			success	: function(result){
				if	(result.record.length > 0){
					for	(var i = 0; i < result.record.length; i++){
						appendHTML	= '<option value="' + result.record[i].trader_seq + '" ';
						if	(needData == 'y'){
							for	(var key in result.record[i]){
								appendHTML	+= ' ' + key + '="' + result.record[i][key] + '"';
							}
						}
						appendHTML	+= '>' + result.record[i].trader_name + '</option>';

						traderObj.append(appendHTML);
					}
				}
			}
		});
	}
}

// 거래처 그룹 선택 시 해당 그룹의 거래처 목록 추출 후 선택 처리
function get_trader_to_group_select(obj, selectKey){
	var groupName	= $(obj).val();
	var needData	= $(obj).attr('needData');
	var traderObj	= $(obj).closest('td').find('select').eq(1);
	var appendHTML	= '';
	// 초기화
	traderObj.find('option').each(function(){
		if	($(this).attr('value'))	$(this).remove();
	});
	if	(groupName){
		// 새로 가져옴
		$.ajax({
			type	: 'get',
			url		: '../scm/getTraderData',
			data	: 'perpage=99999999&getType=ajax&sc_trader_group=' + encodeURIComponent(groupName),
			dataType: 'json',
			success	: function(result){
				if	(result.length > 0){
					for	(var i = 0; i < result.length; i++){
						appendHTML	= '<option value="' + result[i].trader_seq + '" ';
						appendHTML  += selectKey == result[i].trader_seq ? 'selected="selected"' : '';
						if	(needData == 'y'){
							for	(var key in result[i]){
								appendHTML	+= ' ' + key + '="' + result[i][key] + '"';
							}
						}
						appendHTML	+= '>' + result[i].trader_name + '</option>';

						traderObj.append(appendHTML);
					}
				}
			}
		});
	}
}

// 분류 선택 팝업
function setScmCategory(id){
	var scm_category	= $('input#input_' + id).val();
	// 새로 가져옴
	$.ajax({
		type	: 'get',
		url		: '../scm/scm_category',
		data	: 'return_func=chgScmCategory&return_id=' + id + '&category=' + scm_category,
		success	: function(result){
			$('div#div_' + id).html(result);
		}
	});
	openDialog('분류연결', 'div_' + id, {'width':800,'height':400});
}

// 분류 선택 처리
function chgScmCategory(id, code, name){
	var category_name	= name.replace(/^\>/, '').replace(/\>\>/, '').replace(/\>/g, ' > ');
	$('span#span_' + id).html(category_name);
	$('input#input_' + id).val(code);

	closeDialog('div_' + id);
}

// 상품에 대한 매장별 판매정보 팝업
function openGoodsStoreSaleInfo(id, goods_seq, option_type, option_seq){
	if	(id && goods_seq > 0){
		var params	= 'goods_seq=' + goods_seq;
		if	(option_type && option_seq)
			params	+= '&option_type=' + option_type + '&option_seq=' + option_seq;

		// 새로 가져옴
		$.ajax({
			type	: 'get',
			url		: '../scm/scm_stores_sale_info',
			data	: params,
			success	: function(result){
				$('div#' + id).html(result);
				var width	= $('div#' + id).find('table').width() + 60;
				if	(width > 1000)	width	= 1000;
				var height	= Math.round(width * 0.8);
				openDialog('매장별 안전재고', id, {'width':width,'height':height,'overflow-x':'scroll'});
			}
		});
	}else{
		openDialogAlert('상품이 없습니다.', 400, 150, function(){});
		return false;
	}
}

// 환율정보창 on/off
function onoffExchangeinfo(obj){
	var infoLay	= $(obj).find('div');
	if	(infoLay.css('display') == 'none'){
		infoLay.show();
	}else{
		infoLay.hide();
	}
}

// 하위 분류를 추출해서 select에 추가해 줌
function getChildScmCategory(obj, selectorClass){
	var category_code	= $(obj).val();
	var depth			= $(obj).attr('depth');
	if	(category_code && depth < 4){
		// 하위 분류 초기화
		for	( var c = depth; c < 4; c++){
			$('select.' + selectorClass).eq(c).find('option').remove();
			$('select.' + selectorClass).eq(c).append('<option value="">' + ( parseInt(c) + 1 ) + '차 분류</option>');
		}

		// 새로 가져옴
		$.ajax({
			type		: 'get',
			url			: '../scm/scm_child_category',
			data		: 'category=' + category_code,
			dataType	: 'json', 
			success		: function(result){
				if	(result.status){
					var data	= '';
					var len		= result.category.length;
					for	( var c = 0; c < len; c++ ){
						data	= result.category[c];
						$('select.' + selectorClass).eq(depth).append('<option value="' + data.category_code + '">' + data.title + '</option>');
					}
				}
			}
		});
	}
}

// 하위 분류를 추출해서 select에 추가해 줌 (검색옵션용)
function getChildScmCategoryName(obj, selectorName, selectCode){
	var category_code	= $(obj).val();
	var depth			= $(obj).attr('depth');
	if	(category_code && depth < 4){
		// 하위 분류 초기화
		for	( var c = depth; c < 4; c++){
			$('select[name="' + selectorName + '"]').eq(c).find('option').remove();
			$('select[name="' + selectorName + '"]').eq(c).append('<option value="">' + ( parseInt(c) + 1 ) + '차 분류</option>');
		}

		// 새로 가져옴
		$.ajax({
			type		: 'get',
			url			: '../scm/scm_child_category',
			data		: 'category=' + category_code,
			dataType	: 'json', 
			success		: function(result){
				if	(result.status){
					var data	= '';
					var len		= result.category.length;

					for	( var c = 0; c < len; c++ ){
						data		= result.category[c];
						select_attr = '';
						if(selectCode){
							select_attr = data.category_code == selectCode[depth] ? 'selected="selected"' : '';
						}

						$('select[name="' + selectorName + '"]').eq(depth).append('<option value="' + data.category_code + '" '+select_attr+'>' + data.title + '</option>');						
					}

					getChildScmCategoryName($('select[name="' + selectorName + '"][depth="'+(parseInt(depth)+1)+'"]'), selectorName, selectCode);
				}
			}
		});
	}
}

// 카테고리 선택에 따른 하위 카테고리 추출
function selectScmCategory(obj){
	var depth	= $(obj).attr('depth');
	var code	= $(obj).val();

	if	(depth < 4){
		// 선택된 분류 하위 분류 선택박스 초기화
		$('select[name="sc_scm_category[]"]').each(function(){
			if($(this).attr('depth') > depth){
				var opt = $(this).find('option:first-child');
				$(this).html(opt);
			}
		});

		$.ajax({
			type		: 'get',
			url			: '../scm/scm_child_category',
			data		: 'category=' + code,
			dataType	: 'json', 
			success		: function(result){
				if	(result.status){
					var data	= '';
					var html	= '';
					var len		= result.category.length;
					for	( var i = 0; i < len; i++){
						data	= result.category[i];
						html	= '<option value="' + data.category_code + '" >' + data.title + '</option>';
						$('select[name="sc_scm_category[]"]').eq(depth).append(html);
					}
				}
			}
		});
	}
}

// 기간 간편설정
function set_date(obj, selectedType){
	if	(selectedType == 'all'){
		var sDate	= '';
		var eDate	= '';
	}else{
		var dateRes	= getRangeDate(selectedType);
		var sDate	= dateRes[0];
		var eDate	= dateRes[1];
	}

	$(obj).closest('span.btn-list').find('span.lightblue').removeClass('lightblue');
	$(obj).closest('span').addClass('lightblue');
	$("input[name='date_selected']").val(selectedType);
	$("input[name='sc_sdate']").val(sDate);
	$("input[name='sc_edate']").val(eDate);
}

// 종류에 따른 시작일과 종료일 계산
function getRangeDate(kind){
	var d1		= new Date();
	var d2		= new Date();
	var w		= d1.getDay();
	switch(kind){
		case 'yesterday':
			d1.setDate(d1.getDate() - 1);
			d2.setDate(d2.getDate() - 1);
		break;
		case 'calendar_thisweek':	// 달력기준 ( 일 ~ 토 )
			if	(w == 0)	w	= 1;
			else			w++;
			d1.setDate(d1.getDate() - (w - 1));
			d2.setDate(d2.getDate() + (7 - w));
		break;
		case 'calendar_lastweek':	// 달력기준 ( 일 ~ 토 )
			if	(w == 0)	w	= 1;
			else			w++;
			d1.setDate(d1.getDate() - (7 + w));
			d2.setDate(d2.getDate() - (w + 1));
		break;
		case 'work_thisweek':		// 회계기준 ( 월 ~ 일 )
			if	(w == 0)	w	= 7;
			d1.setDate(d1.getDate() - (w - 1));
			d2.setDate(d2.getDate() + (7 - w));
		break;
		case 'work_lastweek':		// 회계기준 ( 월 ~ 일 )
			if	(w == 0)	w	= 7;
			d1.setDate(d1.getDate() - (6 + w));
			d2.setDate(d2.getDate() - w);
		break;
		case 'thismonth':
			y	= d1.getFullYear();
			m	= d1.getMonth() + 1;
			d	= d1.getDate();
			d1	= new Date(m + '/1/' + y);
			d2	= new Date(m + '/1/' + y);
			d2.setMonth(d2.getMonth() + 1);
			d2.setDate(d2.getDate() - 1);
 		break;
		case 'threemonth':
			y	= d1.getFullYear();
			m	= d1.getMonth()-3;
			d	= d1.getDate();
			d1	= new Date(m + '/1/' + y);
			d2	= new Date(m + '/1/' + y);
			d2.setMonth(d2.getMonth()+3);
			d2.setDate(d2.getDate() - 1);
 		break;
		case 'sixmonth':
			y	= d1.getFullYear();
			m	= d1.getMonth()-6;
			d	= d1.getDate();
			d1	= new Date(m + '/1/' + y);
			d2	= new Date(m + '/1/' + y);
			d2.setMonth(d2.getMonth()+6);
			d2.setDate(d2.getDate() - 1);
 		break;
		case 'lastmonth':
			y	= d1.getFullYear();
			m	= d1.getMonth() + 1;
			d	= d1.getDate();
			d1	= new Date(m + '/1/' + y);
			d2	= new Date(m + '/1/' + y);
			d1.setMonth(d1.getMonth() - 1);
			d2.setDate(d2.getDate() - 1);
		break;
	}
	var ret		= new Array();
	var y		= '';
	var m		= '';
	var d		= '';
	y			= d1.getFullYear();
	m			= (d1.getMonth() < 9) ? '0' + (d1.getMonth() + 1) : (d1.getMonth() + 1);
	d			= (d1.getDate() < 10) ? '0' + d1.getDate() : d1.getDate();
	ret[0]		= y + '-' + m + '-' + d;
	y			= d2.getFullYear();
	m			= (d2.getMonth() < 9) ? '0' + (d2.getMonth() + 1) : (d2.getMonth() + 1);
	d			= (d2.getDate() < 10) ? '0' + d2.getDate() : d2.getDate();
	ret[1]		= y + '-' + m + '-' + d;

	return ret
}

// 목록 체크박스 일괄 체크/해제
function scmAllCheck(obj){
	if	($(obj).attr('checked'))	$(obj).closest('table').find('input.chk').attr('checked', true);
	else							$(obj).closest('table').find('input.chk').attr('checked', false);
}

$(document).ready(function(){
	$('input#chkAll').click(function(){
		scmAllCheck($(this));
	});

	$("input[name='barcode_reader']").bind('keyup', function(e){
		var evt	= e || window.event;
		if	(evt.keyCode == 13){
			$(this).closest('div').find('button').click();
			$(this).val('');
			$(this).focus();
		}
	});

});