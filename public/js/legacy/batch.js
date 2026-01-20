var serialize;
var searchCount = 0;
$(document).ready(function() {

	$("#searchMemberBtn").click(function(){
		var callpage=$(this).attr("callpage");
		var wheres		= $("input[name='wheres']").val();
		$("input[name='mode']").val("search");
		openDialog("회원 검색 <span class='desc'>&nbsp;</span>", "memberSearchDiv", {"width":"100%","height":"800"});
		if($("#memberSearchDiv").html() == "" || callpage != $("#callPage").val() ) getSearchMember(wheres, 'start', callpage);
	});

	$("body").append('<div id="memberSearchDiv" class="hide"></div>');
	//getSearchMember('', 'start');
});

function searchSubmit(){
	var serialize = $("#batchMemberForm").serialize();
	getSearchMember(serialize, '');
}


function searchPaging(query_string){
	var serialize = $("#batchMemberForm").serialize() + query_string;
	getSearchMember(serialize, '');
}

//회원 검색 폼
function getSearchMember(query_string, callType, callPage){
	if(callType == "start" && (callPage == "batch_sms" || callPage == "batch_email" || callPage == "email" || callPage == "sms" || callPage == "emoney"|| callPage == "point")){
		query_string = "callPage="+callPage;
	}

	if(callPage == "status" || callPage == "grade"){
		query_string = query_string + "&callPage="+callPage;
	}

	$.ajax({
		type: "get",
		url: "/admin/batch/member_catalog",
		data: query_string,
		contentType: "application/x-www-form-urlencoded; charset=UTF-8", 
		success: function(result){
			$("#memberSearchDiv").html(result);
			apply_input_style();
			serialize = decodeURIComponent($("#batchMemberForm").serialize());
			searchCount = $("#batchMemberForm input[name='searchcount']").val();

			var selectMember = $("input[name='selectMember']").val();
			var selectMemberArray = new Array();
			selectMemberArray = selectMember.split(',');

			$('.member_chk').each(function(){
				for(i=0; i<selectMemberArray.length; i++){
					if($(this).val() == selectMemberArray[i]){
						$(this).attr("checked", true);
					}
				}
			});

		}
	});

}

//전체 검색 넣기
function serchMemberInput(call){
	if ($("input[name='keyword']").val()==$("input[name='keyword']").attr('title')) {
		$("input[name='keyword']").val('');
	}
	serialize = decodeURIComponent($("#batchMemberForm").serialize());

	var dormancy_count = parseInt($("input[name='dormancy_count']").val());

	var form = "";
	if(call == "status" || call == "grade"){
		form = "#gradeForm ";
	}

	if(call == "grade"){

		var member_grade_seq	= $("#batchMemberForm input[name='member_grade_seq']").val();
		var member_grade_name	= $("#batchMemberForm input[name='member_grade_name']").val();
		var member_old_grade	= $(form + "input[name='member_old_grade']").val();

		if(member_grade_seq == '' || (member_old_grade != '' && member_old_grade != member_grade_seq)){
			alert("같은 회원등급만 선택 가능합니다.");
			return;
		}else{
			$(form + "input[name='member_old_grade']").val(member_grade_seq);
			$(form + "input[name='member_old_grade_name']").val(member_grade_name);
		}
	}

	$("#searchSelectText").html("검색된");
	$("#serialize").val(serialize);
	$("#search_member").html(comma(searchCount-dormancy_count));
	$(form + "input[name='mcount']").val(searchCount-dormancy_count);
	$(form + "input[name='selectMember']").val('');
	$(form + "input[name='searchSelect']").val('search');
	
	var reciveTitle = "받는 사람";
	if(call == "emoney" || call == "status" || call == "grade") reciveTitle = "대상자";
	$('.member_chk').attr("checked", false);
	$('.all_member_chk').attr("checked", false);
	
	openDialogAlert('<span class=fx12>[받는사람-검색회원] 검색된 회원 '+comma(searchCount-dormancy_count)+'명이 '+reciveTitle+'에 들어 갔습니다. (휴면 회원 제외)</span>', 600, 150);
	closeDialog("memberSearchDiv");		
}

function serchMemberInputDown(){
	if ($("input[name='keyword']").val()==$("input[name='keyword']").attr('title')) {
		$("input[name='keyword']").val('');
	}
	serialize = decodeURIComponent($("#batchMemberForm").serialize());
	$("#serialize").val(serialize);
	$("#search_member").html(comma(searchCount));
	$("input[name='mcount']").val(searchCount);
	$("input[name='selectMember']").val('');
	$("input[name='searchSelect']").val('search');
	closeDialog("memberSearchDiv");		
	openDialog('회원정보 다운로드','admin_member_download', {'width':550,'height':420});
}

function selectMemberInputDown(){
	var selectMember = $("input[name='selectMember']").val();
	var selectMemberArray = new Array();
	selectMemberArray = selectMember.split(',');
	
	if(selectMember == ""){
		alert("선택된 회원이 없습니다.");
		return;
	}

	$("#search_member").html(comma(selectMemberArray.length));
	$("#searchSelectText").html("선택된");
	
	$("input[name='mcount']").val(selectMemberArray.length);
	$("input[name='searchSelect']").val('select');

	closeDialog("memberSearchDiv");		
	openDialog('회원정보 다운로드','admin_member_download', {'width':550,'height':420});
}


function selectMemberClick(obj){
	var selectMember = $("input[name='selectMember']").val();
	var selectMemberArray = new Array();
	if(selectMember != ""){
		selectMemberArray = selectMember.split(',');
	}	

	if($(obj).is(":checked")){
		
		var inBoolen = true;
		for(i=0; i<selectMemberArray.length; i++){
			if(selectMemberArray[i] == $(obj).val()){
				inBoolen = false;
			}
		}

		if(inBoolen){
			if(selectMemberArray.length){
				selectMember += ","+$(obj).val();
			}else{
				selectMember = $(obj).val();
			}
		}
		
		
	}else{
		var newSelectMember="";
		for(i=0; i<selectMemberArray.length; i++){
			if(selectMemberArray[i] != $(obj).val()){
				if(newSelectMember == "") newSelectMember = selectMemberArray[i];
				else newSelectMember += ","+selectMemberArray[i];
			}
		}
		selectMember = newSelectMember;
		//selectMember = selectMember.replace($(obj).val()+",","");
		//selectMember = selectMember.replace($(obj).val(),"");	
	}
	$("input[name='selectMember']").val(selectMember);
}

/*선택 추가*/
function selectMemberInput(call){

	var form = "";
	if(call == "status" || call == "grade"){ form = "#gradeForm "; }

	var selectError			= false;
	var selectMember		= $(form + "input[name='selectMember']").val();
	var selectMemberArray = new Array();
	var same_grade			= true;

	selectMemberArray = selectMember.split(',');
	
	if(selectMember == ""){
		alert("선택된 회원이 없습니다.");
		return;
	}

	/*등급변경시 같은 등급회원만 선택 가능*/
	if(call == "grade"){
		var list_grade_seq	= '';
		$('#batchMemberForm .member_chk').each(function(){

			if($(this).is(":checked") == true){

				if(list_grade_seq != '' && list_grade_seq != $(this).attr("grade")){ same_grade = false; }

				list_grade_seq	= $(this).attr("grade");
				list_grade_name = $(this).attr("grade_name");

			}
		});

		var member_grade_seq	= $(form + "input[name='member_old_grade']");
		var member_grade_name	= $(form + "input[name='member_old_grade_name']");

		if(same_grade == true){
			if(member_grade_seq.val() == ""){
				member_grade_seq.val(list_grade_seq);
				member_grade_name.val(list_grade_name);
			}else if(member_grade_seq.val() != "" && member_grade_seq.val() != list_grade_seq){
				same_grade = false;
			}
		}

		if(same_grade == false){
			$('#batchMemberForm .member_chk').each(function(){
				if(member_grade_seq.val() != $(this).attr("grade")){
					$(this).closest("tr").removeClass("checked-tr-background");
					$(this).attr("checked", false);
				}
				selectMemberClick($(this)); 
			});
			alert("같은 회원등급만 선택 가능합니다.");
		}

	}

	if(same_grade == true){
		$("#search_member").html(comma(selectMemberArray.length));
		$("#searchSelectText").html("선택된");
		
		$("input[name='mcount']").val(selectMemberArray.length);
		$("input[name='searchSelect']").val('select');
	
		var reciveTitle = "받는 사람";
		var params = new Array();
		params['yesMsg'] = "발송화면으로 가기";
		if(call == "emoney"){
			reciveTitle = "대상자";
			params['yesMsg'] = "지급화면으로 가기";		
		}else if(call == "status" || call == "grade"){
			reciveTitle			= "대상자";
			params['yesMsg']	= "일괄변경화면으로 가기";		
		}
	
		params['noMsg'] = "계속 선택하기";

		openDialogConfirm('<span class=fx12>[받는사람-선택회원] 선택된 회원 '+comma(selectMemberArray.length)+'명이 '+reciveTitle+'에 들어 갔습니다. (중복된 회원 제외)</span>',600, 150,function(){
		closeDialog("memberSearchDiv");
	},function(){
		
	}, params);
}
}

function allMemberClick(){
	$('#batchMemberForm .member_chk').each(function(){
		selectMemberClick($(this));
	});
}