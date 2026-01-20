
function academy_apply_list(boardseq)
{
	$.ajax({
		type: "get",
		url: "../board/academy_apply_list",
		data: "boardseq="+boardseq,
		success: function(result){
			$("div#academy_apply_list").html(result);
		}
	});
	openDialog("신청리스트", "academy_apply_list", {"width":"400","height":"600","show" : "fade","hide" : "fade"});
}