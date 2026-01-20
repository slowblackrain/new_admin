$(function(){
	$('.goodsDisplayThumbList span').bind('click',function(){
		let imgTag = $(this).children('img')
		let selectImgSrc = $(imgTag).attr('src');
		let elementParent = $(imgTag).parents('dl.goodsDisplayItemWrap');
		let imgElement = $(elementParent).find('.goodsDisplayImageWrap img');
		$(imgElement).attr('src',selectImgSrc);
	});

	$('.goodsDisplayThumbList2 span').bind('click',function(){
		let imgTag = $(this).children('img')
		let selectImgSrc = $(imgTag).attr('src');
		let elementParent = $(imgTag).parents('div.goodsDisplayItemWrap');
		let imgElement = $(elementParent).find('.goodsDisplayImageWrap img');
		$(imgElement).attr('src',selectImgSrc);
	});

	$('.doto-goods-folding').bind('click',function(){
		let element = $(this).data('element');
		if($(element).css('display') == 'none'){
			$(this).removeClass('on')
			$(element).css('display','');
		}else{
			$(this).addClass('on')
			$(element).css('display','none');
		}
	});

	
});

function dotoFolding(obj)
{
	let element = $(obj).data('element');
	if($(element).css('display') == 'none'){
		$(obj).removeClass('on')
		$(element).css('display','');
	}else{
		$(obj).addClass('on')
		$(element).css('display','none');
	}
}


function set_date(start,end){
	$("input[name='goods_term_date[]']").eq(0).val(start);
	$("input[name='goods_term_date[]']").eq(1).val(end);
}

function chkAll(chk){
	if(chk == true){
		$(".list_goods_chk").attr("checked", true);
	}else if(chk == false){
		$(".list_goods_chk").attr("checked", false);
	}
}

function chkBasket(){
	return false;
	var chkbox = $(".list_goods_chk:checked");
	var form = $('<form>').attr({
		'method': 'post',
		'target': 'actionFrame',
		'action': '/order/addCartList',		
	});
	var gl_option_select_ver = $('<input>').attr({
		'type' : 'hidden',
		'value':  '0.1',
		'name' : 'gl_option_select_ver'
	});	
	$(chkbox).each(function() {         
		var formData = $('#listForm'+$(this).val()).serializeArray();
		$.each(formData, function(i, field){
			form.append($('<input type="hidden" name="'+field.name+'[]" value="'+field.value+'" >'));
		});
   });
   
	if(chkbox.length < 1){
		alert('선택된 상품이 없습니다.');
		return false;
	}
	form.appendTo('body'); 
	form.append(gl_option_select_ver);		
	form.submit();
}

function chkDirect(){
	return false;
	var chkbox = $(".list_goods_chk:checked");
	if(chkbox.length != 1){
		alert('바로구매는 하나의 상품만 구매가능합니다.');
		return false;
	}else{		
		goodSeqVal = chkbox.val();
		var form = $('#listForm'+goodSeqVal);
		var gl_option_select_ver = $('<input>').attr({
			'type' : 'hidden',
			'value':  '0.1',
			'name' : 'gl_option_select_ver'
		});					
		form.append(gl_option_select_ver);					
		form.submit();
	}
	
	return false;

}

function chkExcel(){
	var chkbox = $(".list_goods_chk:checked");
	if(chkbox.length < 1){
		alert('선택된 상품이 없습니다.');
		return false;
	}
	var form = $('<form>').attr({
		'method': 'post',
		'action': '/goods/excel_down',		
	});
	$(chkbox).each(function() {         
		form.append($('<input type="hidden" name="goods_seq[]" value="'+$(this).val()+'" >'));
   });
	form.appendTo('body'); 
	form.submit();
}

function chkEdit(){
	var chkbox = $(".list_goods_chk:checked");
	var form = $('<form>').attr({
		'method': 'post',
		//'target': 'actionFrame',
		'action': '/dotoprogram/promotion',		
	});
	$(chkbox).each(function() {         
		form.append($('<input type="hidden" name="goods_seq[]" value="'+$(this).val()+'" >'));
   });
	form.appendTo('body'); 
	form.submit();

}