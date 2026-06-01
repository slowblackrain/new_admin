// Desktop category sidebar mouseover hover script

//?? 留??곗???ㅻ??? 蹂댁?ъ＜?? ?ㅽ?щ┰??????)

 $(".left_cate").mouseover(function() {

	var code = $(this).attr("data-value");
	var items = $(".left_cate");

	$.ajax({
		url: "/goods/get_cate",
		data: "category_code="+code,
		context: this,
		success: function(data) {
			$(".cate2").html(data);
			
			var cnt = $(".cate_count").val();
			
			if(cnt > 0)
			{
				items.not($(this)).removeClass('active');
				$(".left_cate").css( "color", "" );
				$(".left_cate").css( "background-color", "" );
				$(".cate1").css("color", "#fff");
				$(".cate2").css("display", "block");
				$(this).addClass("active");
				//$( this ).css("background-color", "#3ba0ff" );
				//$( this ).css("color", "#fff" );
			} else {
				$(".left_cate").css( "color", "" );
				$(".left_cate").css( "background-color", "" );
				$(".cate1").css("display", "");
				//$(".cate2").css("display", "none");
				//$(".cate3").css("display", "none");
			}
		}
	});
});

/*
let showDelay = 300, hideDelay = 1000;

	let allMenuItems = document.querySelectorAll('.left_cate');

	for (let i = 0; i < allMenuItems.length; i++) {
		allMenuItems[i].addEventListener('mouseenter', function() {
			let thisItem = this;


			for (let j = 0; j < allMenuItems.length; j++) {
				$(".left_cate").css( "color", "" );
				$(".left_cate").css( "background-color", "" );
				$(".cate1").css("display", "");
				$(".cate2").css("display", "none");
				$(".cate3").css("display", "none");
				$(".arrow").css("display", "none");
			}

			menuEnterTimer = setTimeout(function() {
				
				var code = thisItem.dataset.value;

				$.ajax({
					url: "/goods/get_cate",
					data: "category_code="+code,
					context: this,
					success: function(data) {
						$(".cate2").html(data);
						
						var cnt = $(".cate_count").val();
						if(cnt > 0)
						{
							$(".left_cate").css( "color", "" );
							$(".left_cate").css( "background-color", "" );
							$(".cate1").css("color", "#fff");
							$(".cate2").css("display", "block");
							thisItem.style.cssText ="color:#fff; background-color: #3ba0ff";
						}
					}
				});

			}, showDelay);
		});
				allMenuItems[i].addEventListener('mouseleave', function() {
					
					let thisItem = this;
					clearTimeout(menuEnterTimer);
					menuLeaveTimer = setTimeout(function() {
						$(".left_cate").css( "color", "" );
						$(".left_cate").css( "background-color", "" );
						$(".cate1").css("display", "");
						$(".cate2").css("display", "none");
						$(".cate3").css("display", "none");
					}, hideDelay);
				}); 
	}
*/

$('html').click(function(e) {   
	if(!$(e.target).hasClass("sidebar") && !$(e.target).hasClass("cate2") && !$(e.target).hasClass("cate3")) {
		$(".cate2").css("display", "none");
		$(".cate3").css("display", "none");
	}
})

// ?? 留??곗???ㅻ??? 蹂댁?ъ＜?? ?ㅽ?щ┰????


