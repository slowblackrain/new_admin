//ie8이하 start


if(!Array.indexOf){
 Array.prototype.indexOf = function(obj){
  for(var i=0; i<this.length; i++){
   if(this[i]==obj){
    return i;
   }
  }
  return -1;
 }
}

if (!Object.keys) Object.keys = function(o) {
 if (o !== Object(o))
  throw new TypeError('Object.keys called on a non-object');
 var k=[],p;
 for (p in o) if (Object.prototype.hasOwnProperty.call(o,p)) k.push(p);
 return k;
}

if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, ''); 
  }
}
//ie8이하 end

// 함수부만 따로 호출
document.write('<script type="text/javascript" src="/app/javascript/js/common-function.js?v=230407"></script>');

$(function(){	
	/* 스타일적용 */
	apply_input_style();
	
	//상품디스플레이의 동영상클릭시 -> 동영상자동실행설정되어있어야함
	$(".goodsDisplayVideoWrap").bind("click",function(e){
		$(this).find("img").addClass("hide");
		$(this).find(".thumbnailvideo").hide();
		$(this).find(".mobilethumbnailvideo").hide();
		$(this).find("iframe").removeClass("hide");
		$(this).find("embed").removeClass("hide");
	});
	
	//동영상넣기의 동영상클릭시-> 동영상자동실행설정되어있어야함
	$(".DisplayVideoWrap").bind("click",function(e){
		$(this).find("img").addClass("hide");
		$(this).find(".thumbnailvideo").hide();
		$(this).find(".mobilethumbnailvideo").hide();
			$(this).find("iframe").removeClass("hide");
			$(this).find("embed").removeClass("hide"); 
	});

	/* 동영상넣기/상품디스플레이 동영상이미지체크 */ 
	$(".thumbnailvideo").each(function(){
		var width = ($(this).attr("width"))?$(this).attr("width"):400;
		var height = ($(this).attr("height"))?$(this).attr("height"):200;
		$(this).css({'width':width});
		$(this).css({'height':height});
	});
	
	$(".mobilethumbnailvideo").each(function(){
		var width = ($(this).attr("width"))?$(this).attr("width"):150;
		var height = ($(this).attr("height"))?$(this).attr("height"):50;
		$(this).css({'width':width});
		$(this).css({'height':height});
	});
});

$(window).load(function() {
	/* 스타일적용 */
	chk_small_goods_image();
	/*	
	$('img.small_goods_image').each(function() {	 
		if (!this.complete ) {// image was broken, replace with your new image
			this.src = '/data/icon/goods/error/noimage_list.gif';
		}
	});
	*/
	
	/* 동영상넣기/상품디스플레이 동영상이미지체크 */ 
	$(".thumbnailvideo").each(function(){
		var width = ($(this).attr("width"))?$(this).attr("width"):400;
		var height = ($(this).attr("height"))?$(this).attr("height"):200;
		$(this).css({'width':width});
		$(this).css({'height':height});
	});
	
	$(".mobilethumbnailvideo").each(function(){
		var width = ($(this).attr("width"))?$(this).attr("width"):150;
		var height = ($(this).attr("height"))?$(this).attr("height"):50;
		$(this).css({'width':width});
		$(this).css({'height':height});
	});

});

String.prototype.replaceAll = function (str1,str2){
	var str    = this;     
	var result   = str.replace(eval("/"+str1+"/gi"),str2);
	return result;
}

//통계서버로 통계데이터 전달 사용안함
function statistics_firstmall(act,goods_seq,order_seq,review_point)
{
	return;
	/*
	var url = '/_firstmallplus/statistics';
	var allFormValues = "act="+act+"&goods_seq="+goods_seq;
	if( order_seq ) allFormValues += "&order_seq="+order_seq;
	if( review_point ) allFormValues += "&review_point="+review_point;
	
	if(act == 'order' && !order_seq) return false;
	if(act == 'review' && !review_point) return false;
	if(!goods_seq) return false;
	$.ajax({
		cache:false,
		timeout:1000,  
		type:"POST",
		url:url,
		data:allFormValues,
		error:function(){},
		success:function(response){}
	});
	return true;
	*/
}

// 사은품 지급 조건 상세 2015-05-14 pjm
$(".gift_log").bind('click', function(){
	$.ajax({
		type: "post",
		url: "./gift_use_log",
		data: "order_seq="+$(this).attr('order_seq')+"&item_seq="+$(this).attr('item_seq'),
		success: function(result){
			if	(result){
				$("#gift_use_lay").html(result);
				openDialog("사은품 이벤트 정보", "gift_use_lay", {"width":"450","height":"250"});
			}
		}
	});
});


//문자열 바이트 체크(utf-8도 가능)
String.prototype.byteLength = function(mode){
	mode	= (!mode) ? 'euc-kr' : mode;
	text	= this;
	byte	= 0;
	switch(mode){
		case	'utf-8' :
			for(byte=i=0;char=text.charCodeAt(i++);byte+=char>>11?3:char>>7?2:1);
			break;
		
		default :
			for(byte=i=0;char=text.charCodeAt(i++);byte+=char>>7?2:1);
		
	}
    return byte
};

/*
 * form RSA 암호화 프로세스
 *  - form 내에 file이 있을 경우 기존 프로세스에서도 file 데이터 전송은 동작하지 않았음.
 * 확인된 submit 예외 사항
 * - front script 레벨에서 form을 생성한 후 body에 추가하지 않고 submit
 *  -> 이 경우는 https://www.w3.org/TR/html5/forms.html#constraints 4.10.22.3 를 위반하여 일부 브라우저에서 submit이 발생하지 않음.
 * - ajax나 iframe을 통해 새로운 페이지를 생성한 후 document.sslForm.submit() 를 통해 submit
 *  -> DOM 객체로 submit 호출과 동일
 * - 스크립트 호출과 바인딩이 이루어지기 전 $(document).ready() 와 동시에 submit
 */ 
// RSA 전역 변수 선언
var getPublicKeyUrl = ["/ssl/getRSAPublicKey","/RSA/ssl/getRSAPublicKey"];
var handshakeUrl = ["/ssl/getRSAHandShake","/RSA/ssl/getRSAHandShake"];
var arrCheckActions = ["/ssl/relayRsa?action=", "/RSA/ssl/setRSAreturnPost/"];
var jcryptionReloadDelayTime = 500;	// 0.5 초 후 다시 리로드, 지연 발생 시 1초씩 증가
// 동적 스크립트 호출
$.loadScript = function (url, callback) {$.ajax({url: url,dataType: 'script',success: callback,async: true});}
$(document).ready(function(){
	// order_price_calculate 주문서 계산 함수에서 ssl 통신을 이용하고 있지 않고 현재 페이지가 주문서 작성 페이지라면 ssl_url로 치환
	if( window.location.pathname.indexOf('/order/settle') > -1
			&& order_price_calculate.toString().indexOf("/common/ssl_action")==-1){
		console.log("order_price_calculate convert");
		order_price_calculate = function () {
			var f = $("form#orderFrm");
			action = "/order/calculate?mode="+gl_mode;
			// ssl 적용
			$.ajax({
				async: false,
				'url'		: '/common/ssl_action',
				'data'		: {'action':action},
				'type'		: 'get',
				'dataType'	: 'html',
				'success'	: function(res) {
					action = res;
				}
			});
			f.attr("action",action);
			f.attr("target","actionFrame");
			// jCryption 재적용 스킨의 orderFrm 에 ssl 링크가 없기에 js 영역에서 재선언
			moduleJcryption.resetJcryptionSubmit(f[0]);
			f.submit();
		};
	}
	
    // jquery 버전이 1.7 이하 일경우 관리자에서 사용중이므로 https 강화를 제외한다.
    if($().jquery >= "1.7"){
        $.loadScript("/app/javascript/plugin/jcryption/jquery.jcryption.3.1.0_custom.js", function(){
            initJcryption();
        });
    }
	// ajax 호출 후 새로 생성된 form에도 적용
	$(document).ajaxComplete(function() {
		// 모든 폼 엘리먼트에 이벤트를 바인딩 한다
		$("body form").each(function (){
			var domEl = this;
			moduleJcryption.convertJcryptionSubmit(domEl);
		});
	});
});
// 암호화 적용 기능 모듈화
var moduleJcryption = {
    // 폼에서 프로토콜을 포함한 host name을 얻는다.
    getHostNameFromForm : function (formObj) {
        var formActionUrl = formObj.attr("action");
        return moduleJcryption.getHostNameFromUrl(formActionUrl);
    }
    , getHostNameFromUrl : function (url){
        var arr = url.split("/");
        var result = arr[0]+"//"+arr[2];
        return result;
    }
    // SSL 적용 폼인지 여부 확인
	, checkSSLForm : function (formObj){
        var formActionUrl = formObj.attr("action");
        if(formActionUrl){
            for(var i in arrCheckActions){
                if(formActionUrl.indexOf(arrCheckActions[i])>-1){
                    return i;
                }
            }
        }
        return -1;
    }
    // 이벤트가 바인드 된 폼인지 확인
    , checkBindEventForm : function (formObj){
        var data = (formObj.data("jCryptionInit") === true);
        if(data){
            return true;
        }
        return false;
    }
    // 이벤트가 치환된 된 폼인지 확인
    , checkBindEventJcryptionForm : function (formObj){
        var data = (formObj.data("jCryptionAlready") === true);
        if(data){
            return true;
        }
        return false;
    }
    // 속성을 확인한다
    , getAttributes : function ( $node ) {
        var attrs = {};
        $.each( $node[0].attributes, function ( index, attribute ) {
            attrs[attribute.name] = attribute.value;
        } );

        return attrs;
    }
	, destroyJcryptionSubmit : function(domEl){
		$(domEl).data("jCryptionInit",false);
		$(domEl).data("jCryptionAlready",false);
		$(domEl).off("submit");
	}
	, resetJcryptionSubmit : function(domEl){
		moduleJcryption.destroyJcryptionSubmit(domEl);
		moduleJcryption.convertJcryptionSubmit(domEl);
	}
	, convertJcryptionSubmit : function(domEl){
		// 이미 치환된 폼은 중복 치환하지 않음.
		if(moduleJcryption.checkBindEventJcryptionForm($(domEl))){
			// console.log("already submit convert ", $(domEl));
		}else{
			// console.log("submit convert event binding!", $(domEl));
            $(domEl).data("jCryptionAlready",true);
			// URL 이 SSL 적용 폼인지 확인
			// console.log($(domEl),$(domEl).attr("action"),moduleJcryption.checkSSLForm($(domEl)));
			if(moduleJcryption.checkSSLForm($(domEl))>-1){
				// 기본 dom 객체를 우선 치환한 후 jquery 객체 submit 이벤트 바인딩.
				// jquery객체 서브밋이 발생한다면 preventDefault 로 인해 dom객체의 서브밋은 발생하지 않음.
				domEl.submit = function (event){
					// console.log("DOM el submit");
					moduleJcryption.convertSubmit(domEl);
				};
				// validate 플러그인이 적용되어 있을 시 별도의 submithandle를 이용하므로 jquery 객체 바인딩 제외
				if(typeof $(domEl).data("validator") !== "undefined"){
				}else{
					$(domEl).on("submit", function(event){
						// console.log("jquery el submit");
						event.preventDefault();
						moduleJcryption.convertSubmit(domEl);
					});
				}
			}
		}
	}
	// 세션키 유지를 위한 action url 추가
	, convertActionUrl : function ($formEl){
        // console.log("convertActionUrl!", $formEl);
        var action = $formEl.attr("action");
        var sessionKey = $.jCryption.getAESSessionKey($formEl);
		var actionDomain = moduleJcryption.getHostNameFromForm($formEl);
		var domain = window.location.hostname;
		if(domain.indexOf("m.")==0){
			domain = domain.replace("m.","");
		}
		if(domain.indexOf("www.")==0){
			domain = domain.replace("www.","");
		}
        if(actionDomain.indexOf(domain)==-1 && moduleJcryption.checkSSLForm($formEl)>-1 && action.indexOf(sessionKey)==-1){
            action = action+"/"+sessionKey;
        }
        $formEl.attr("action",action);
	}
	// 암호화 서브밋 처리
    , convertSubmit : function(thisDom){
        var $formEl = $(thisDom);
        // submit 전용 폼인지 체크
        if(moduleJcryption.checkBindEventForm($formEl)){
            // console.log("already!", $formEl);
            moduleJcryption.convertActionUrl($formEl);
            return true;
        }else{
            // SSL 적용폼인지 체크
            if(moduleJcryption.checkSSLForm($formEl)>-1){
                // 스크립트가 로드되었는지 체크
                if(typeof $.jCryption === "function"){
                    // rsa 폼 삭제
                    $(".rsaForm").remove();

                    // 암호화 적용
                    var AESEncryptionKey = $.jCryption.getAESEncryptionKey($formEl);
                    // console.log(AESEncryptionKey);
                    var hostName = moduleJcryption.getHostNameFromForm($formEl);

                    var $submitElement = $formEl.find(":input:submit");
                    var $encryptedElement = $("<input />",{
                      type:'hidden',
                      name:'jCryption'
                    });

                    // 암호화 submit 전용 form 
                    var $submitRSAForm = $("<form class='rsaForm'/>");
                    var formAttrs = moduleJcryption.getAttributes($formEl);
                    for (var i in formAttrs){
                        if(i!="id" && i!="name"){
                            $submitRSAForm.attr(i,formAttrs[i]);
                        }
                    }
                    var remakeHandshakeUrl = handshakeUrl[moduleJcryption.checkSSLForm($formEl)];
                    if(moduleJcryption.checkSSLForm($formEl)!=0){
                        remakeHandshakeUrl = remakeHandshakeUrl+"/"+$.jCryption.getAESSessionKey($submitRSAForm);
                    }

                    $.jCryption.authenticate(
                        AESEncryptionKey, 
                        hostName+getPublicKeyUrl[moduleJcryption.checkSSLForm($formEl)],
                        hostName+remakeHandshakeUrl, 
                        function(AESEncryptionKey) {
                            var toEncrypt = $formEl.serialize();
                            // console.log(toEncrypt);
                            // console.log($formEl);
                            if ($submitElement.is(":submit")) {
                                toEncrypt = toEncrypt + "&" + $submitElement.attr("name") + "=" + $submitElement.val();
                            }
                            $encryptedElement.val($.jCryption.encrypt(toEncrypt, AESEncryptionKey));
                            // console.log($submitRSAForm);
                            $submitRSAForm.append($encryptedElement);
                            $("body").append($submitRSAForm);
                            $submitRSAForm.data("jCryptionInit",true);
            				moduleJcryption.convertActionUrl($submitRSAForm);
                            $submitRSAForm.submit();
                        },
                        function() {
                            // Authentication with AES Failed ... sending form without protection
                            confirm("Authentication with Server failed, are you sure you want to submit this form unencrypted?", function() {
                                $formEl.submit();
                            });
                        }
                    );
                }else{
					var delayTime = jcryptionReloadDelayTime;
                    console.log("필수 스크립트가 로드되지 않았습니다. "+(delayTime/1000)+"초 후 다시 시도합니다.");
                    setTimeout(function(){
						console.log($formEl,"리로드"+delayTime);
						moduleJcryption.resetJcryptionSubmit(thisDom);
						$formEl.submit();
					}, delayTime);
					jcryptionReloadDelayTime += 1000;	// 1초씩 증가
					// $formEl.submit();
                }
                return false;
            }else{
                return true;
            }
        }
    }
};

// 암호화 적용
var initJcryption = function(){
    // 모든 폼 엘리먼트에 이벤트를 바인딩 한다
	$("body form").each(function (){
		var domEl = this;
		moduleJcryption.convertJcryptionSubmit(domEl);
	});
    
	// 아이디 체크의 경우 SSL 통신이 없었으므로 강제로 적용
    function setupJoinMemberPageCheckId(){
        var url = location.href;
        var tmp_url = url.split("?");
        var domain = moduleJcryption.getHostNameFromUrl(tmp_url[0]);
        var sub_url = tmp_url[0].replace(domain,"");
        
        // 회원가입페이지 일 경우
        if(sub_url=="/member/register"){
            
            // 현재 회원가입 폼의 action 을 통해 유료/무료 SSL을 확인한다.
            var registFrmAction = $("#registFrm").attr("action");
            var registFrmHost = moduleJcryption.getHostNameFromUrl(registFrmAction);
            if(registFrmHost.indexOf("http")>-1){
                var sslSubUrlIndex = 0;
                if(registFrmHost == "https://ssl.gabiafreemall.com"){
                    sslSubUrlIndex = 1;
                }
                var idCheckFormUrl = registFrmHost+arrCheckActions[sslSubUrlIndex];

                var idCheckCallbackUrl = domain+"/member/"+"../member_process/id_chk";
                var encodeIdCheckCallbackUrl = Base64.encode(idCheckCallbackUrl);
                encodeIdCheckCallbackUrl = encodeIdCheckCallbackUrl.replace(/[\+]/g,"-");
                encodeIdCheckCallbackUrl = encodeIdCheckCallbackUrl.replace(/[\/]/g,"_");
                var idCheckFormAction = idCheckFormUrl+encodeIdCheckCallbackUrl;

                $("input[name='userid']").unbind("blur");
                $("input[name='userid']").blur(function() {

                    if($(this).val()){
                        // rsa 폼 삭제
                        $("#idchkform").remove();
                        $(".rsaForm").remove();
                        $formEl = $("<form id='idchkform' method='post' target='actionFrame' action='"+idCheckFormAction+"'/>");
                        var idval = $("<input type='hidden' name='userid' value='"+$(this).val()+"'>");
                        $formEl.append(idval);
                        $("body").append($formEl);

                        // 암호화 적용
                        var AESEncryptionKey = $.jCryption.getAESEncryptionKey($formEl);
                        // console.log(AESEncryptionKey);
                        var hostName = moduleJcryption.getHostNameFromForm($formEl);

                        var $submitElement = $formEl.find(":input:submit");
                        var $encryptedElement = $("<input />",{
                          type:'hidden',
                          name:'jCryption'
                        });

                        // 암호화 submit 전용 form 
                        var $submitRSAForm = $("<form class='rsaForm'/>");
                        var formAttrs = moduleJcryption.getAttributes($formEl);
                        for (var i in formAttrs){
                            if(i!="id" && i!="name"){
                                $submitRSAForm.attr(i,formAttrs[i]);
                            }
                        }
                        var remakeHandshakeUrl = handshakeUrl[moduleJcryption.checkSSLForm($formEl)];
                        if(moduleJcryption.checkSSLForm($formEl)!=0){
                            remakeHandshakeUrl = remakeHandshakeUrl+"/"+$.jCryption.getAESSessionKey($submitRSAForm);
                        }

                        $.jCryption.authenticate(
                            AESEncryptionKey, 
                            hostName+getPublicKeyUrl[moduleJcryption.checkSSLForm($formEl)],
                            hostName+remakeHandshakeUrl, 
                            function(AESEncryptionKey) {
                                var toEncrypt = $formEl.serialize();
                                // console.log(toEncrypt);
                                // console.log($formEl);
                                if ($submitElement.is(":submit")) {
                                    toEncrypt = toEncrypt + "&" + $submitElement.attr("name") + "=" + $submitElement.val();
                                }
                                $encryptedElement.val($.jCryption.encrypt(toEncrypt, AESEncryptionKey));
                                // console.log($submitRSAForm);
                                $submitRSAForm.append($encryptedElement);
                                $("body").append($submitRSAForm);
                                $submitRSAForm.data("jCryptionInit",true);
            					moduleJcryption.convertActionUrl($submitRSAForm);
                                $submitRSAForm.submit();
                            },
                            function() {
                                // Authentication with AES Failed ... sending form without protection
                                confirm("Authentication with Server failed, are you sure you want to submit this form unencrypted?", function() {
                                    $formEl.submit();
                                });
                            }
                        );

                    }
                });
            }
        }
    }
    setupJoinMemberPageCheckId();
}
function callbackIdChk(json){
    var response = $.parseJSON(json);
    var text = response.return_result;
    var userid = response.userid;
    $("#id_info").html(text);
    $("input[name='userid']").val(userid);
}

// 비밀번호 규칙 체크
function init_check_password_validation(obj){
	obj.off("focusout");
	obj.on("focusout", function(){
		call_check_password_validation($(this));
	});
}

function init_check_password_validation_data(data, password){
	
	var jsonObj = [];
	
	jsonObj.push({
		name: 'password',
		value: password
	});

	for(i=0;i<data.length; i++){

		var formEl = data[i].name;

		if(formEl.match(/^(mtype)/)){
			jsonObj.push({
				name: 'mtype',
				value: data[i].value
			});
		}else if(formEl.match(/^(member_|info_|provider_|manager_)*(seq)/)){
			jsonObj.push({
				name: 'seq',
				value: data[i].value
			});
		}
		else if(formEl.match(/^(?!.*cell).*(phone)/) && formEl != 'mphone' && formEl != 'info_phone'){	// 관리자&입점사 제외
			if(formEl.match(/\W/)){
				jsonObj.push({
					name: 'phone[]',
					value: data[i].value
				});
			}else {
				jsonObj.push({
					name: 'phone',
					value: data[i].value
				});
			}
		}else if(formEl.match(/^\w*(cellphone)/) && formEl != 'mcellphone'){	// 관리자&입점사 제외
			if(formEl.match(/\W/)){
				jsonObj.push({
					name: 'cellphone[]',
					value: data[i].value
				});
			}else {
				jsonObj.push({
					name: 'cellphone',
					value: data[i].value
				});
			}
		}else if(formEl.match(/^\w*(birthday)/)){
			jsonObj.push({
				name: 'birthday',
				value: data[i].value
			});
		}
	}

	return jsonObj;
}

function call_check_password_validation(obj){
    var action = "/common/check_password_validation";
	
	var password = obj.val();
	var form = obj.closest("form");
	if (form.length == 0) {
		form = $("form[name=registFrm]");
	}
	var data = form.serializeArray();
	jsonObj = init_check_password_validation_data(data, password);
	if(typeof password !== 'undefined' && password != ''){
		$.ajax({
			type: "post",
			async: false,
			url: action,
			data: jsonObj,
			success: function(result){
				try{
					result = JSON.parse(result);
					draw_check_password_validation(obj, result.alert_code);
				}catch(e){
					init_draw_check_password_validation(obj);
					obj.parent().find(".password_alert_msg").html(result);
				}
			}
		});
	}
}
function draw_check_password_validation(obj, alert_code){
    init_draw_check_password_validation(obj);
    var msg = '';
    if(alert_code != ''){
        msg = alert_code;
    }
    if(msg){
        obj.parent().find(".password_alert_msg").html(msg);
    }else{
        obj.parent().find(".password_alert_msg").remove();
    }
}
function init_draw_check_password_validation(obj){
    if(obj.parent().find(".password_alert_msg").length == 0){
        var password_alert_msg = $('<div class="password_alert_msg" style="color:red;"></div>');
        obj.parent().append(password_alert_msg);
    }
}

$(window).load(function() {
	$(".class_check_password_validation").each(function(){
		init_check_password_validation($(this));
	});
});
