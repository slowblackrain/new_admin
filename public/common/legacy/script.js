/* 전체 카테고리 버튼 스크립트 */
function setCategoryAllBtnEvent(categoryNavigationKey,url){
	$("#"+categoryNavigationKey+" .categoryAllBtn").live('click',function(){
		if($("#"+categoryNavigationKey+" .categoryAllContainer").html()==""){
			$(".categoryAll").hide();
			$("#"+categoryNavigationKey+" .categoryAllContainer").load(url,function(){
				$("#"+categoryNavigationKey+" .categoryAll").stop(true,true).slideDown('fastest');
			});
		}else{
			if($("#"+categoryNavigationKey+" .categoryAll").stop(true,true).is(":visible")){
				$("#"+categoryNavigationKey+" .categoryAll").slideUp('fastest');
			}else{
				$(".categoryAll").hide();
				$("#"+categoryNavigationKey+" .categoryAll").slideDown('fastest');
			}
		}
		return false;
	});
	$("#"+categoryNavigationKey+" .categoryAllClose").live('click',function(){
		$("#"+categoryNavigationKey+" .categoryAll").stop(true,true).slideUp('fastest');
	});
}

/* 전체 브랜드 버튼 스크립트 */
function setBrandAllBtnEvent(categoryNavigationKey,url){
	$("#"+categoryNavigationKey+" .categoryAllBtn").live('click',function(){
		if($("#"+categoryNavigationKey+" .categoryAllContainer").html()==""){
			$(".categoryAll").hide();
			$("#"+categoryNavigationKey+" .categoryAllContainer").load(url,function(){
				$("#"+categoryNavigationKey+" .categoryAll").stop(true,true).slideDown('fastest');

				$("#"+categoryNavigationKey+" .categoryAllBrandListGroup").each(function(){
					if($(".categoryAllBrandListGroupItem",this).length){
						$(this).show();
					}else{
						$(this).hide();
					}
				});
			});
		}else{
			if($("#"+categoryNavigationKey+" .categoryAll").stop(true,true).is(":visible")){
				$("#"+categoryNavigationKey+" .categoryAll").slideUp('fastest');
			}else{
				$(".categoryAll").hide();
				$("#"+categoryNavigationKey+" .categoryAll").slideDown('fastest');
			}
		}
		return false;
	});
	$("#"+categoryNavigationKey+" .categoryAllClose").live('click',function(){
		$("#"+categoryNavigationKey+" .categoryAll").stop(true,true).slideUp('fastest');
	});
	$("#"+categoryNavigationKey+" .brandPrefixBtn").live('click',function(){
		$("#"+categoryNavigationKey+" .brandPrefixBtn.current").removeClass("current");
		$(this).addClass("current");
		var prefix = $(this).attr("prefix");
		if(prefix=="all"){
			$("#"+categoryNavigationKey+" .categoryAllBrandListGroup").each(function(){
				if($(".categoryAllBrandListGroupItem",this).length){
					$(this).show();
				}else{
					$(this).hide();
				}
			});
			//$("#"+categoryNavigationKey+" .categoryAllBrandListGroup").show();
		}else{
			$("#"+categoryNavigationKey+" .categoryAllBrandListGroup").hide().filter("*[prefix='"+prefix+"']").show();
		}
	});
}

/* 전체 지역 버튼 스크립트 */
function setLocationAllBtnEvent(locationNavigationKey,url){
	$("#"+locationNavigationKey+" .categoryAllBtn").live('click',function(){
		if($("#"+locationNavigationKey+" .categoryAllContainer").html()==""){
			$(".categoryAll").hide();
			$("#"+locationNavigationKey+" .categoryAllContainer").load(url,function(){
				$("#"+locationNavigationKey+" .categoryAll").stop(true,true).slideDown('fastest');
			});
		}else{
			if($("#"+locationNavigationKey+" .categoryAll").stop(true,true).is(":visible")){
				$("#"+locationNavigationKey+" .categoryAll").slideUp('fastest');
			}else{
				$(".categoryAll").hide();
				$("#"+locationNavigationKey+" .categoryAll").slideDown('fastest');
			}
		}
		return false;
	});
	$("#"+locationNavigationKey+" .categoryAllClose").live('click',function(){
		$("#"+locationNavigationKey+" .categoryAll").stop(true,true).slideUp('fastest');
	});
}

$(function(){
	
	/* 상품디스플레이 탭 스크립트 */
	$(".displayTabContainer").each(function(){
		var tabContainerObj = $(this);
		tabContainerObj.children('li').bind('mouseover',function(){
			tabContainerObj.children('li.current').removeClass('current');
			$(this).addClass('current');
			var tabIdx = tabContainerObj.children('li').index(this);
			tabContainerObj.closest('.designDisplay, .designCategoryRecommendDisplay').find('.displayTabContentsContainer').hide().eq(tabIdx).show();
		}).eq(0).trigger('mouseover');
		
		
	});
	
});