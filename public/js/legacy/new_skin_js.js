	// 퀵메뉴 상품이미지 전환
	function quick_menu_scroll(gap){
		
		var quick_menu_scroll = document.getElementById('quick_menu_scroll');
		quick_menu_scroll.scrollTop += gap;

	}


	//상단 고정 메뉴
	var currentScrollTop = 0;
		
	window.onload = function() {
		
	  //새로고침 했을 경우 실행
	  scrollController();
	   
	  //스크롤 하는 경우에 실행
	  jQuery(window).on('scroll', function() {
	      scrollController();
	  });
	}
	

	//메인 메뉴의 위치를 제어하는 함수
	function scrollController() {
		
		currentScrollTop = jQuery(window).scrollTop();
		
	  if(currentScrollTop > 170){
	  	jQuery("#top_fixed_menu").css('display','block');
	  }else {
	  	jQuery("#top_fixed_menu").css('display','none');
	  }
	  
	}
	
	
	//상품 리스트 레이어(해당상품 마우스 오버시 노출)
	function fnGoodsLayer(goods_list, type, num){

		if(type == 'open'){
			
			if(goods_list == 'best'){
				jQuery("#goods_layer_"+num).show();
			}else if(goods_list == 'general'){
				jQuery("#goods_list_over_layer_"+num).show();
			}
			
		}else {
			if(goods_list == 'best'){
				jQuery("#goods_layer_"+num).hide();
			}else if(goods_list == 'general'){
				jQuery("#goods_list_over_layer_"+num).hide();
			}
		}
		
	}
	
	
	
	//전체 카테고리 열기&닫기
	jQuery(document).ready(function(){
    jQuery("#allCategoryListBtn").click(function(){
    	
    	var allCategoryList_class = jQuery('[name="allCategoryListBtn"]').attr('class');
    	
    	if(allCategoryList_class == 'all_category_open_btn'){
    		
  			jQuery("#allCategoryListBtn").removeClass('all_category_open_btn');
      	jQuery("#allCategoryListBtn").addClass('all_category_close_btn');
      	
    	}else if(allCategoryList_class == 'all_category_close_btn'){
    		
    		jQuery("#allCategoryListBtn").removeClass('all_category_close_btn');
        jQuery("#allCategoryListBtn").addClass('all_category_open_btn');
        
    	}
    	
      jQuery("#allCategoryList").slideToggle(200);

    });
    
    jQuery("#all_category_btn_mini").click(function(){
    	
    	//console.log('open');
    	var allCategoryList_class = jQuery('[name="allCategoryListBtn"]').attr('class');
    	jQuery("#allCategoryList_2").slideToggle(200);
    	
    });
	});
	
	
	//number_format
	Number.prototype.number_format = function(round_decimal) {

    return this.toFixed(round_decimal).replace(/(\d)(?=(\d{3})+$)/g, "$1,");
    
	};


	//상품 구매수량 조절 
	//현재 value값, 업&다운 타입, seq(id값을 위해), 현재공급가액, 도매 1차 개수,도매 1차 적용가격, 도매 2차 개수, 도매 2차 적용가격, 출력칸 id
	function fnBuyCntValue_2(info_class, type, seq, price, ea_1, dcnt_price_1, ea, dcnt_price, price_id){
		
		var change_buy_cnt = "";
		var return_price = "";
		var buy_cnt = jQuery("#"+info_class+"_"+seq).val();
		buy_cnt = Number(buy_cnt);

		if(type == 'up'){
			
			change_buy_cnt = buy_cnt + 1;
			
		}else if(type == 'down'){
			change_buy_cnt = buy_cnt - 1;
			
			if(buy_cnt == 1){
				return false;
			}
			
		}
		
		jQuery("#"+info_class+"_"+seq).val(change_buy_cnt);
			
		//도매가격 적용
		if(change_buy_cnt >= ea){
			price = dcnt_price;
		}else if(change_buy_cnt >= ea_1){
			price = dcnt_price_1;
		}
		
		//console.log('현재가 : '+price);
		
		return_price = change_buy_cnt * price;
		return_price = return_price.number_format(0);
		jQuery("#"+price_id).html( return_price +'원' );
		
	}
	
	
	
	
	//상품 정보 탭메뉴 스크롤이동
	function fnMoveTabList(num){
		
		var offset = jQuery("#fnMoveTabList_" + num).offset();
		jQuery("html, body").animate({scrollTop : offset.top - 100}, 400);
	}
	
	//퀵메뉴 스크롤 탑 버튼
	function fnscrollTopBtn(){
		
		jQuery("html, body").animate({scrollTop : 0}, 400);
		
	}
	
	
	//상품리스트 기간선택
  jQuery( function() {
  	    
    jQuery( ".date_text" ).datepicker({
    	dateFormat: "yymmdd",
    	showMonthAfterYear:true,
	    dayNames: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
	    dayNamesMin: ['M', 'T', 'W', 'T', 'F', 'S', 'S']
		});

  });
  
  
  //카테고리 뎁스 리스트
	jQuery("#category_more_btn").click(function(){

		jQuery("#category_depth").slideToggle(200);
		
	});
	
	
	//전체 카테고리 메뉴
	jQuery(function(){
		
		fnCategoryMenuInitial('all','1');
		fnCategoryMenuInitial('all','2');
		
	});
	
	function fnCategoryMenuInitial(initial,menu){
		
		var categoey_class = "";
		if(menu == '1'){
			categoey_class = jQuery("#result_category_list");
		}else {
			categoey_class = jQuery("#result_category_list_2");
		}
		
		jQuery.ajax({
			
			type : "POST",
			url : "/main/category_search_initial",
			data : {initial : initial},
			success: function(data) {

				if(data == 'no_category'){

					categoey_class.html("<p>해당 카테고리가 존재하지 않습니다.</p>");

				}else {
					
					categoey_class.html(data);
						
				}

			},
			error:function(request,status,error){
				alert('잠시 후 다시 시도해주세요');
			}		
				
		});
		
	}
	
	
	//상품 상단정보 이미지 확대보기
	function fnGoodsImageZoom(img){

		jQuery("#big_img").attr('src',img);
		jQuery("#big_img").attr('data-magnify-src',img);
		
	}
	
	
	//상품 전체선택
	var chkAll = function(chk) {
		
		jQuery("input:checkbox[name='goods_code_chk[]']").each(function() {
			
			jQuery(this).prop("checked",chk);
			
		});
		
	}


	//상품리스트 레이어 체크시 리스트 체크박스 상태변경
	function fnGoodsCodeChked(seq){

		var checked_state = jQuery("input:checkbox[id='goods_code_chk_"+seq+"']").is(":checked");
		
		if(checked_state == true){
			jQuery("input:checkbox[id='goods_code_chk_state_"+seq+"']").prop("checked", true);			
		}else {
			jQuery("input:checkbox[id='goods_code_chk_state_"+seq+"']").prop("checked", false);
		}
		
	}
	
	
	//상품검색
	function fnSearchForm(btn){

		if(btn == '1'){
			var search_text = jQuery("#search_text").val();	
		
		}else {
			var search_text = jQuery("#search_text_2").val();	
		}

		if(search_text == '' || !search_text){
			
			alert('검색어를 입력 해주세요');
			return false;
			
		}else {
			
			document.getElementById("topSearchForm").submit();
			
		}
		
	}
	
	
	//날짜포맷( 두자릿수 앞에 0 붙이기)
	function date_pad(n, width) {
  n = n + '';
  return n.length >= width ? n : new Array(width - n.length + 1).join('0') + n;
	}
	
	
	//기간별 판매량순 날짜선택버튼
	function fnDateCheck(num,period){

		var today = new Date();

		if(period == 'day'){
			
			today.setDate(today.getDate() - num); 
			var month = today.getMonth() + 1;
			
		}else if(period == 'month'){
			
			var month = (today.getMonth() + 1) - num;
			
		}else {
			
			return false;
			
		}
		
		var year = today.getFullYear();
		var day = today.getDate();

		month = date_pad(month,2);
		day = date_pad(day,2);

		//console.log(year+'-'+month+'-'+day);
		
		jQuery("#date_search_start").val(year+month+day);


	}