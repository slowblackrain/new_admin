jQuery(document).ready(function () {

	var topmenuloc = $(".doto_scrollmenu").offset();
	if (topmenuloc.top == 0) {
		topmenuloc.top = 150;
	}
	$(window).scroll(function () {

		if ($(document).scrollTop() >= topmenuloc.top) {
			$("#doto_scrollmenu").addClass('scrollfixed');
			$("#doto_scrollmenu .hidden_sch").removeClass('hide');
			$("#doto_scrollmenu .menu_box").removeClass('ml30');
		} else {
			$("#doto_scrollmenu").removeClass('scrollfixed');
			$("#doto_scrollmenu .menu_box").addClass('ml30');
			$("#doto_scrollmenu .hidden_sch").addClass('hide');
		}

	});

	$('#allCategoryListBtn,#leftCategoryBtn,#leftCategoryadd').bind('click', function () {
		if ($(this).attr('class') == 'scrollHomeNavBtn') {
			location.href = '/';
			return false;
		}
		var category_display = $('#allCategoryList').css('display');
		if (category_display == 'block') {
			$(".doto_scrollmenu").removeClass('modal_line');
			$('#allCategoryList').css('display', 'none');
			doto_modal_end();
		} else {
			$(".doto_scrollmenu").addClass('modal_line');
			$('#allCategoryList').css('display', 'block');
			fnCategoryMenuInitial('header_recom', '0');
			fnCategoryMenuInitial('all', '1');
			doto_modal_event('dometopia_header');
		}

	});



});

/*
 * 
 *	"header_modal": 60, //모달효과 div
 *	"dometopia_header": 70, //상단 헤더 
 *  "doto_scrollmenu": 70, //스크롤 이동시 고정 헤더
 */
function doto_modal_event(objId, option) {
	var objzindex = $('#' + objId).css("z-Index");
	$('#doto_header_modal').css('z-index', (objzindex - 1));
	$('#doto_header_modal').css('display', 'block');
}
function doto_modal_end() {
	$('#doto_header_modal').css('display', 'none');
}
function fnCategoryMenuInitial(initial, menu, obj) {
	var categoey_class = "";
	var depth = 2; // Default to main categories (Depth 2)

	if (menu == '0') {
		// Recommend category (not used currently or special)
		categoey_class = jQuery("#recommend_category_list");
	} else if (menu == '1') {
		// All Categories List
		categoey_class = jQuery("#result_category_list");
		depth = 2;
	} else {
		// Other lists
		categoey_class = jQuery("#result_category_list_2");
		depth = 2;
	}

	jQuery.ajax({
		type: "POST",
		url: "/main/category_search_initial",
		data: {
			code: initial,
			depth: depth
		},
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		success: function (data) {
			/*
			if(obj){
				$(obj).parent().children('.on').removeClass('on');
				$(obj).addClass('on');
			}
			*/
			if (data == 'no_category') {
				if (initial == 'header_recom') {
					categoey_class.parent().css('display', 'none');
				}
				categoey_class.html("<p>해당 카테고리가 존재하지 않습니다.</p>");
			} else {
				categoey_class.html(data);
			}
		},
		error: function (request, status, error) {
			console.log(error);
		}
	});

}

