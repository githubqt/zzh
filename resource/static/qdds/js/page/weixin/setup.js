/***
 * 微信设置js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-20
 */
$(function (){
	$("#button_no").attr("disabled", true);
	$("#button_no").addClass('l-btn-disabled');
})
function noBind() {
	var bind = $('#is_no_bind').is(':checked');
	if (bind) {
		$("#button_no").attr("disabled", false);
		 $("#button_no").removeClass('l-btn-disabled');
	} else {
		$("#button_no").attr("disabled", true);
		$("#button_no").addClass('l-btn-disabled');
	}
}

function delWeixin() {
	$.ajax({
    	type: "POST",
    	dataType: "json",
        url: "/index.php?m=Weixin&c=Weixin&a=delAuthorization",
	    success:function(res){
		    if (res.code == '200') {
		    	location.href="/index.php?m=Marketing&c=Marketing&a=index"
			} else {
				$.messager.alert('提示', res.msg);
			}
	    }
	});
}


function letWeixin() {
	$.ajax({
    	type: "POST",
    	dataType: "json",
        url: "/index.php?m=Weixin&c=Weixin&a=delAuthorization",
	    success:function(res){
		    if (res.code == '200') {
		    	location.href="/index.php?m=Weixin&c=Weixin&a=authorization"
			} else {
				$.messager.alert('提示', res.msg);
			}
	    }
	});
}
