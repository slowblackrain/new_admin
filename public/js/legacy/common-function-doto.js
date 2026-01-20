$(document).ready(function(){
	//dotoprogram
	$('.parentsTrRemove').live('click',function(){
		$(this).parents('tr').remove();
	});
	//도매토피아 메인 랭킹탭 
	
	$("ul.doto-rank-tabs li").click(function () {
		$(this).prevAll("li").removeClass('active');
		$(this).nextAll("li").removeClass('active');
		$(this).addClass('active');
		let tab_content = $(this).parents().next().children(".tab_content");
		$(tab_content).hide();

		let activeTabId = $(this).attr("rel");
		let activeTab = $(tab_content).filter("#" + activeTabId);
		$(activeTab).fadeIn();				
		/*개발 해놓으면 뭐함 존나 레거시코드만 늘고 개짜증남-_-
		$.ajax({
			type: "get",
			dataType: 'json',
			url: "../statistic/get_ajax_mainRanking",
			data: {"group":group, "sort":sort},
			beforeSend: function() {},
			success: function(result){
				let contents = '<dl>';
				let goods = result.list;
				$.each(goods[group].list,function(index,value){							
					contents += '<dd>';
					contents += '<span class="r_number'+(index+1)+'"></span>';
					contents += '<p onclick="location.href=\'../goods/view?no='+value.goods_seq+'\'">'+value.order_goods_name+'</p>';
					contents += '<a href="#"><i class="fas fa-cart-arrow-down"></i></a>';
					contents += '</dd>';
				});
				contents += '</dl>';
				contents += '<button type="button"><a href="../statistic/goods?group='+group+'&sort='+sort+'">모두 보러가기</a></button>';						
				$(activeTab).html(contents);
			},
			complete:function(){}
		});	
		*/
	});
	

});

function set_goods_list(displayId,inputGoods,ptype) 
{
	window.open("/goods/user_select?ptype="+ptype+"&dotoEdit=1&page=1&inputGoods="+inputGoods+"&displayId="+displayId, "dotoEdit", "height=820, width=1100, menubar=no, scrollbars=yes, resizable=yes, toolbar=no, status=no, left=50, top=50");
	/*
	$.ajax({
		type: "get",
		url: "/goods/user_select",
		data: "ptype="+ptype+"&dotoEdit=1&page=1&inputGoods="+inputGoods+"&displayId="+displayId,
		success: function(result){
			$("div#"+displayId).html(result);
		}
	});
	openDialog("상품 검색", displayId, {"width":"1100","height":"820","show" : "fade","hide" : "fade"});
	*/
}

function programGoodsListReturn(obj,ptype){
	var str = '';
	if(ptype == 'promotion'){
		var readOnly = '';
	}else if(ptype =='detail'){
		var readOnly = 'readonly';
	}
	str += '<tr class="goods">';
	str += '	<input type="hidden" name="goods_seq[]" value="'+obj.goodsSeq+'" />';
	str += '	<td class="scode">'+obj.scode+'</td>';
	//str += '	<td class="code">'+obj.code+'</td>';
	str += '	<td class="image">'+obj.img+'</td>';
	str += '	<td class="upimg"><input type="file" style = "width:150px;" name="upimg[]" /></td>';
	str += '	<td class="subject"><textarea name="goodsName[]" '+readOnly+' >'+obj.goodsName+'</textarea>';
	str += '	<td class="price"><input type="text" class="onlynumber" name="ori_goodsPrice[]" '+readOnly+' onkeyup="" size="20" value="'+obj.ori_goodsPrice+'"><div></div><input type="text" class="onlynumber" name="goodsPrice[]" '+readOnly+' onkeyup="" size="20" data-regprice="'+obj.regPrice+'" value="'+obj.goodsPrice+'"></td>';
	str += '	<td class="description"> <textarea id="content_'+obj.goodsSeq+'" name="description[]" '+readOnly+'>'+obj.description+'</textarea></td>';
	str += '	<td class="detail"><label>';
	if(obj.goodInfoVal){
		str += '		<input type="checkbox" name="goodInfoVal[]" value="'+obj.goodsSeq+'" checked>';
	}else{
		str += '		<input type="checkbox" name="goodInfoVal[]" value="'+obj.goodsSeq+'">';
	}
	str += '		사용함</label></td>';
	str += '	<td class="delete"><button type="button" class="parentsTrRemove"></button></td>';
	str += '</tr>';

	return str;
}

function getExcelGoodsList(list,ptype)
{
	$("table#goods_seq .voidTr").remove();
	var cnt = 0;
	$.each(list,function(){
		if(!this.ori_price) this.ori_price = 0;
		let goodObj = { 
			'img': '<a href="javascript:popup(\'promotion_pop_img?goods_seq='+this.goods_seq+'&cnt='+cnt+'\',500,500)"><input class = "imgsrc'+cnt+'" type = "hidden" name = "image[]" value = "'+this.image+'"><img src="'+this.image+'" class="imgViewsrc'+cnt+'" width="60" height="60" alt=""></a>',
			'goodsName'   : this.goods_name,
			'ori_goodsPrice'  : this.ori_price,
			'goodsPrice'  : this.price,
			'regPrice'    : this.regPrice,
			'goodsSeq'    : this.goods_seq,
			'code'        : this.goods_scode,
			'scode'       : this.goods_code,
			'description' : this.contents,
			'goodInfoVal' : this.goods_seq,
			};
		let tr = programGoodsListReturn(goodObj,ptype);
		cnt = cnt + 1;
		$("table#goods_seq").append(tr);
	});	
}

function programSampleView(type,key)
{
	openDialog("미리보기", "example_"+type+key, {"width":"930","height":"820","show" : "fade","hide" : "fade"});
}