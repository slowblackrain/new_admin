
// 기본 submit
function scmmemoSubmit(){
	loadingStart();
	$("form[name='scmmemoForm']").submit();
}

function sorder_doto_calc(step){
	var charge = $('[name="charge"]').val();
	var step_box = Number($('[name="scm_'+step+'_box"]').val());
	var step_ea = Number($('[name="scm_'+step+'_ea"]').val());
	var step_add = Number($('[name="scm_'+step+'_add"]').val());
	var step_supply = Number($('[name="scm_'+step+'_supply"]').val());
	var exchange_price = Number($('[name="exchange_price"]').val());
	if(!step_box) step_box = 0;
	// 총 수량 구하기
	var tot_ea = (step_box * step_ea) + step_add;
	if(tot_ea < 1){
		openDialogAlert('총 발주수량은 1개 이상이어야 합니다.', 400, 170, function(){});
		return false;
	}
	$('[name="scm_'+step+'_total"]').val(tot_ea);
	console.log(step_supply+'__'+exchange_price);
	var krw_supply = Math.round((step_supply*exchange_price)/10)*10;
	console.log(krw_supply);
	$('#scm_'+step+'_krw_price').text(krw_supply);

	var krw_sum_supply = Math.round(tot_ea*krw_supply);
	$('#scm_'+step+'_krw_tot_price').text(krw_sum_supply);

	$('[name="scm_'+step+'_krw_sum_supply"]').val(krw_sum_supply);
	$('[name="scm_'+step+'_krw_supply"]').val(krw_supply);
	$('[name="scm_'+step+'_supply_tax"]').val(Math.round(krw_sum_supply*0.1));
	$('[name="scm_'+step+'_charge"]').val(charge);

	// 총 금액 구하기
	$('[name="scm_'+step+'_tot_price"]').val(Math.round(tot_ea*step_supply));

	var step_supply = $('[name="scm_'+step+'_supply"]').val();	
}

function boxnum_check(){
	var chk_val = $('[name="offer_cn"]').val();
	if(chk_val.indexOf("~") != -1){
		var strArray = chk_val.split("~");
		var start = strArray[0].replace(/[^0-9]/g,'');
		var end = strArray[1].replace(/[^0-9]/g,'');
		if(end > 0){
			if(start < 1) start = 1;
			var box_number = end - start+1;
			$('#scm_box_number').text(box_number);
		}
	}
}


// 발주서 form submit
function submitSorder(){

	if	(!$("[name='trader_seq']").val()){
		openDialogAlert('거래처를 선택해 주세요.', 400, 170, function(){});
		return false;
	}

	if	($("[name='goods_sold']").val() == 'y'){
		openDialogAlert('품절, 단종으로 발주가 제한됩니다.', 400, 170, function(){});
		return false;
	}

	var msg	= '발주서를 등록하시겠습니까?';
	openDialogConfirm(msg, 600, 150, function(){
		submitProcSorder();
	}, function(){ });
	
}

function submitProcSorder(){
	loadingStart();
	$("form[name='detailForm']").submit();
}

// 발주 상태변경
function scmStatusChange(step,url){
	var chkCnt		= 0;
	var chkCntno	= 0;
	
	if($("input:checkbox[name='change']").is(":checked") !== true || !step){
		location.replace(url);         // 이동전 주소가 안보임. 
		return false;
	}
	

	if(step == 11){
		var in_chk = confirm("정말로 입고 하실껀가요?");
		if(in_chk == false){
			alert("취소되었습니다.");
			return false;
		}
	}

	if(step == 15){
		var del_chk = confirm("해당 기능은 발주건 삭제 기능입니다.\n삭제 진행하시겠습니까?");
		
		if (del_chk == false)
		{
			alert("취소되었습니다.");
			return false;
		} else {
			var del_chk2 = confirm("해당 기능은 취소되지 않습니다.\n정말 진행하시겠습니까?");
		if(del_chk2 == false){
			alert("취소되었습니다.");
			return false;
		}
		}
	}

	if(step){
		$("input:checkbox[name='offerSeqArr[]']:checked").each(function() {
			if	($(this).attr('checked')){
				chkCnt++;
				var sno = $(this).val();
				var order = $('[name="whs_ea['+sno+']"]').val();
				var price = Number($('[name="supply_price['+sno+']"]').val());
				var total = Number($('[name="tot_price['+sno+']"]').val());
				if(!order || !price || !total) chkCntno++;
			}
		});
		if	(chkCntno > 0 && step != 14){
			alert("입력필드를 확인해주세요.");
			return false;
		}
		
		//재조사의 경우 사유를 입력 받은 후 처리
		if	(chkCnt > 0){
			loadingStart();
			if(step == 3){
				var research_info = prompt('재조사 사유를 입력하세요.', '');
				if(!research_info){
					alert("재조사 사유를 필수로 입력해주세요.");
					return false;
				}
			}
			//alert('../scm_doto_process/status_change_order?doto=y&step='+step+'&research_value='+research_info);
			var orginAct = $("form[name='listFrm']").attr('action');
			$("form[name='listFrm']").attr('action', '../scm_doto_process/status_change_order?doto=y&step='+step+'&research_value='+research_info);
			$("form[name='listFrm']").submit();
			$("form[name='listFrm']").attr('action', orginAct);
		}else{
			if(step == "alba_N" || step == "alba_Y"){
				alert("발주를 선택해주세요.");
				return false;
			}
			location.replace("?sch_step="+step);         // 이동전 주소가 안보임. 
		}
	}
}


// 발주 상태변경
function scmStatus_return_Change(step,url){
	var chkCnt		= 0;
	
	if($("input:checkbox[name='change']").is(":checked") !== true || !step){
		location.replace(url);         // 이동전 주소가 안보임. 
		return false;
	}

	if(step){
		$("input:checkbox[name='offerSeqArr[]']:checked").each(function() {
			if	($(this).attr('checked')){
				chkCnt++;
				var sno = $(this).val();
			}
		});
		alert("A");
		if	(chkCnt > 0){
			loadingStart();

			var orginAct = $("form[name='listFrm']").attr('action');
			$("form[name='listFrm']").attr('action', '../scm_doto_process/status_change_return?doto=y&step='+step);
			$("form[name='listFrm']").submit();
			$("form[name='listFrm']").attr('action', orginAct);
		}else{
			location.replace("?sch_step="+step);         // 이동전 주소가 안보임. 
		}
	}
}

// 목록 체크박스 일괄 체크/해제
function scmAllCheck(obj){
	if	($(obj).attr('checked'))	$(obj).closest('table').find('input.chk').attr('checked', true);
	else							$(obj).closest('table').find('input.chk').attr('checked', false);
}
function scmAllCheck_li(obj){
	 if	($(obj).attr('checked'))	$('.chk').attr('checked', true);
	else							$('.chk').attr('checked', false);
}

//날짜 변경 프로그램
function set_scmonth(month){
		url = "?sc_month="+month;
		$(location).attr('href',url);
}