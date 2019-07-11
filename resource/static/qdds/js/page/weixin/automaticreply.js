/***
 * 微信自动回复js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-08-29
 */

$(function(){

	$('#ff_five').form({
		success:function(data){
			var data = JSON.parse(data);
			if (data.code == '200') {
				location.reload();
			} else {
				$.messager.alert('提示', data.msg);
			}
		}
	});
});

$().ready(function(e) {
//	var timePicker = new HunterTimePicker({
//		target: '#timeChoice1'
//	});
//	timePicker.init();

//	$(".time-picker").show(function () {
//		var timePicker = new HunterTimePicker({
//		target: $(this)
//		});
//		timePicker.init();
//	});

	$("#timePicker").hunterTimePicker();
	$(".time-picker").hunterTimePicker();
});



function editweeklycontent(rule_id,keyword_id,weekly_num) {
	$('.reply_content_'+rule_id+'_'+keyword_id).html(weekly_num);
	$('#reply-content-'+rule_id+'-'+keyword_id).val(weekly_num);
	editsubmit(rule_id);
}

function addweekly() {
	var name = $(".input-medium").val();
	if (!name) {
		$.messager.alert('提示', '请输入名称');
		return;
	}
	
	var rule_id = parseInt($("#keyword_num").val())+parseInt(1);
	$("#keyword_num").val(rule_id);
	var Ahtml = '<div class="rule-group-container">'+
    				'<form id="ff_'+rule_id+'" method="post" action="">'+
                    	'<div id="menu_num_'+rule_id+'" onmouseover="addcha('+rule_id+')" onmouseout="deletecha('+rule_id+')" class="rule-group">'+
                    	'<div class="rule-meta" onmouseover="edit_blue('+rule_id+',1)" onmouseout="del_edit_blue('+rule_id+',1)">'+
                            '<h3>'+
                                '<em class="rule-id"></em>'+
                                '<span class="rule-name rule-name-'+rule_id+'">'+name+'</span><input type="hidden" name="info[name]" id="reply_name_'+rule_id+'" value="'+name+'">'+
                                '<span class="rule-opts opts-'+rule_id+'">'+
                                    '<a href="javascript:;" onclick="$(\'#menu_name\').val(\''+name+'\');$(\'#now_rule_name\').val('+rule_id+');$(\'#passw\').window(\'open\');" class="js-edit-rule">编辑</a>'+
                                    '<span>-</span>'+
                                    '<a href="javascript:;" onclick="delRule('+rule_id+',4)" class="js-delete-rule">删除</a>'+
                                '</span>'+
                            '</h3>'+
                        '</div>'+
                        '<div class="rule-body">'+
                            '<div class="long-dashed"></div>'+
                            '<div class="rule-keywords" style="border-right: 1px solid #ccc">'+
                                '<div class="rule-inner">'+
                                    '<h4>关键词：</h4>'+
                                    '<div class="keyword-container">'+
                                        '<div class="info keyword_info_'+rule_id+'">还没有任何关键字!</div>'+
                                        '<input type="hidden" value="1" id="keyword-num-'+rule_id+'">'+
                                        '<div class="keyword-list keyword-list-'+rule_id+'"></div>'+
                                    '</div>'+
                                    '<hr class="dashed">'+
                                    '<div class="opt">'+
                                        '<a href="javascript:;" onclick="showkeyword('+rule_id+',0)" class="js-add-keyword">+ 添加关键词</a>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="rule-replies" style="border-left: 1px solid #ccc;position: relative;margin-left: -1px;">'+
                                '<div class="rule-inner">'+
                                   '<h4>自动回复：'+
                                        '<span class="send-method"> 按周期发送</span>'+
                                    '</h4>'+
                                    '<div class="reply-container">'+
                                        '<input type="hidden" id="reply_content_num" value="1">'+
                                        '<ol class="reply-list reply-list-'+rule_id+'">';
										for(var i=1;i<8;i++) {
											if (i == '1') { var num = '周一';} else if (i == '2') { var num = '周二';} else if (i == '3') {
												var num = '周三';} else if (i == '4') { var num = '周四';} else if (i == '5') {var num = '周五';
											} else if (i == '6') { var num = '周六';} else if (i == '7') { var num = '周七';}
                                        Ahtml += '<li onmouseover="edit_blue('+rule_id+',3,'+i+')" onmouseout="del_edit_blue('+rule_id+',3,'+i+')" class="reply_txt_'+rule_id+' reply_txt_'+rule_id+'_'+i+'">'+
                                            '<div class="reply-cont">'+
                                                '<div class="reply-summary">'+
                                                    '<span class="label label-success">文本</span> '+
                                                    '<span class="reply_content_'+rule_id+'_'+i+'"> '+num+'</span>'+
                                                    '<input type="hidden" name="info[content]['+i+'][reply_content]" id="reply-content-'+rule_id+'-'+i+'" value="'+num+'">'+
                                                	'<input type="hidden" name="info[content]['+i+'][content_type]" value="2">'+
                                                	'<input type="hidden" name="info[content]['+i+'][weekly_num]" value="'+i+'">'+
                                                '</div>'+
                                            '</div>'+
                                            '<div class="reply-opts reply-opts_'+rule_id+'_'+i+'">'+
                                                 '<a class="js-edit-it" onclick="showreply3('+rule_id+','+i+')" href="javascript:;">编辑</a>'+
                                                 ' - '+
                                                 '<a class="js-delete-it" href="javascript:;" onclick="$(\'.reply_content_'+rule_id+'_'+i+'\').html(\' '+i+'\');$(\'.reply-content-'+rule_id+'-'+i+'\').val(\' '+num+'\');editsubmit('+rule_id+');">清空</a>'+
                                             '</div>';
                                            if(i != '1') {
                                        		Ahtml += '<span class="after"></span>';
                                          	}
                                            Ahtml += '</li>';
										}
                                        Ahtml += '</ol>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</form>'+
            '</div>';
	$(".weixin-normal").append(Ahtml);
	setSort(rule_id);
	addsubmitweekly(rule_id);
	$('.ui-popover').addClass('hide');
}
function showreply5(rule_id,keyword_id) {
	$('#replyContent').window('open');
	$("#now_rule_id").val(rule_id);
	$("#now_reply_id").val(keyword_id);
	$("#now_reply_type").val("5");
	if (keyword_id == '0') {
    	$(".js-txta").val("");
    	delgraphic();
	} else {
		var other_id = $("#other-id-"+rule_id+"-"+keyword_id).val();
		if (other_id) {
			$(".js-txta").val("");
			$(".complex-backdrop").css("display","block");
			var Ahtml = '<a href="javascript:;" class="close--circle js-delete-complex" onclick="delgraphic()">×</a>'+
		                '<div class="ng-item">'+
		                    '<span class="label label-success">图 文</span>'+
		                    '<div class="ng-title">'+
		                    	'<input type="hidden" value="'+other_id+'" id="graphic_id">'+
		                    	'<input type="hidden" value="'+$(".reply_content_"+rule_id+"_"+keyword_id).html()+'" id="graphic_name">'+
		                        '<a target="_blank" class="new-window"> '+$(".reply_content_"+rule_id+"_"+keyword_id).html()+'</a>'+
		                    '</div>'+
		                '</div>';
		    $(".ng-single").html(Ahtml);
		} else {
			$(".js-txta").val($("#reply-content-"+rule_id+"-"+keyword_id).val());
		}
	}
}

function showreply3(rule_id,keyword_id) {
	$('#replyContent').window('open');
	$("#now_rule_id").val(rule_id);
	$("#now_reply_id").val(keyword_id);
	$("#now_reply_type").val("4");
	if (keyword_id == '0') {
    	$(".js-txta").val("");
    	delgraphic();
	} else {
		var other_id = $("#other-id-"+rule_id+"-"+keyword_id).val();
		if (other_id) {
			$(".js-txta").val("");
			$(".complex-backdrop").css("display","block");
			var Ahtml = '<a href="javascript:;" class="close--circle js-delete-complex" onclick="delgraphic()">×</a>'+
		                '<div class="ng-item">'+
		                    '<span class="label label-success">图 文</span>'+
		                    '<div class="ng-title">'+
		                    	'<input type="hidden" value="'+other_id+'" id="graphic_id">'+
		                    	'<input type="hidden" value="'+$(".reply_content_"+rule_id+"_"+keyword_id).html()+'" id="graphic_name">'+
		                        '<a target="_blank" class="new-window"> '+$(".reply_content_"+rule_id+"_"+keyword_id).html()+'</a>'+
		                    '</div>'+
		                '</div>';
		    $(".ng-single").html(Ahtml);
		} else {
			$(".js-txta").val($("#reply-content-"+rule_id+"-"+keyword_id).val());
		}
	}
}

function showkeyword3(rule_id,keyword_id) {

	if (keyword_id == '0') {
		var keyword_id = parseInt($("#keyword-num-"+rule_id).val())+parseInt(1);
		$("#keyword-num-"+rule_id).val(keyword_id);
	}
	
	$("#now_rule_id").val(rule_id);
	$("#now_keyword_id").val(keyword_id);
	$("#now_type").val("4");
	var old_content = $("#keyword_content_"+rule_id+"_"+keyword_id).val();
	var old_type = $("#keyword_content_type_"+rule_id+"_"+keyword_id).val();

	$("#wei_link").val(old_content);

	if (old_type=='1') {
		$("#keyword_status-1").attr("checked",true);
	} else if (old_type=='2') {
		$("#keyword_status-2").attr("checked",true);
	} else {
		$("#keyword_status-1").attr("checked",true);
	}
	
	$('#keywordContent').window('open');
}

function addsubmitweekly(rule_id){
	var form = new FormData(document.getElementById("ff_"+rule_id));
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=addweekly",
        type:"post",
        data:form,
        processData:false,
        contentType:false,
        success:function(data){
            if (data.code == '200') {
				location.href='/index.php?m=Weixin&c=Weixin&a=automaticreply&type=4';
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });        
}

/*********************************************************************************************************/
function editwb() {
	var form = new FormData(document.getElementById("ff_wb"));
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=editwb",
        type:"post",
        data:form,
        processData:false,
        contentType:false,
        success:function(data){
        	if (data.code == '200') {
				location.reload();
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });       
}

function editstatus(id,status) {
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=editstatus&id="+id+"&status="+status,
        type:"post",
        data:'1=1',
        processData:false,
        contentType:false,
        success:function(data){
        	if (data.code == '200') {
				location.reload();
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });     
}

function editMsgStatus(id,status) {
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=editMsgStatus&id="+id+"&status="+status,
        type:"post",
        data:'1=1',
        processData:false,
        contentType:false,
        success:function(data){
        	if (data.code == '200') {
				location.reload();
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });     
}


function atoff(id,type) {
	if ($("#onoff").val() == '1') {
		$("#onoffbutton").removeClass('ui-switcher-off');
	  	$("#onoffbutton").addClass("ui-switcher-on"); 
	  	$("#onoff").val("2");
	  	$("#wswb").show();
	} else {
		$("#onoffbutton").removeClass('ui-switcher-on');
	  	$("#onoffbutton").addClass("ui-switcher-off"); 
	  	$("#onoff").val("1");
	  	$("#wswb").hide();
	}
	console.log(type)
	if (id) {
		if (type == 1) {
	  		editstatus(id,$("#onoff").val())
		} else if (type == 2) {
			editMsgStatus(id,$("#onoff").val())
		}
	}
}

function showkeyword2(rule_id,keyword_id) {

	if (keyword_id == '0') {
		var keyword_id = parseInt($("#keyword-num-"+rule_id).val())+parseInt(1);
		$("#keyword-num-"+rule_id).val(keyword_id);
	}
	
	$("#now_rule_id").val(rule_id);
	$("#now_keyword_id").val(keyword_id);
	$("#now_type").val("2");
	var old_content = $("#keyword_content_"+rule_id+"_"+keyword_id).val();
	var old_type = $("#keyword_content_type_"+rule_id+"_"+keyword_id).val();

	$("#wei_link").val(old_content);

	if (old_type=='1') {
		$("#keyword_status-1").attr("checked",true);
	} else if (old_type=='2') {
		$("#keyword_status-2").attr("checked",true);
	} else {
		$("#keyword_status-1").attr("checked",true);
	}
	
	$('#keywordContent').window('open');
}

function showreply2(rule_id,keyword_id) {
	$('#replyContent').window('open');
	$("#now_rule_id").val(rule_id);
	$("#now_reply_id").val(keyword_id);
	$("#now_reply_type").val("2");
	if (keyword_id == '0') {
    	$(".js-txta").val("");
    	delgraphic();
	} else {
		var other_id = $("#other-id-"+rule_id).val();
		if (other_id) {
			$(".js-txta").val("");
			$(".complex-backdrop").css("display","block");
			var Ahtml = '<a href="javascript:;" class="close--circle js-delete-complex" onclick="delgraphic()">×</a>'+
		                '<div class="ng-item">'+
		                    '<span class="label label-success">图 文</span>'+
		                    '<div class="ng-title">'+
		                    	'<input type="hidden" value="'+other_id+'" id="graphic_id">'+
		                    	'<input type="hidden" value="'+$(".reply_content_"+rule_id).html()+'" id="graphic_name">'+
		                        '<a target="_blank" class="new-window"> '+$(".reply_content_"+rule_id).html()+'</a>'+
		                    '</div>'+
		                '</div>';
		    $(".ng-single").html(Ahtml);
		} else {
			$(".js-txta").val($("#reply-content-"+rule_id).val());
		}
		
	}
}
function editsubmit2(){
	var form = new FormData(document.getElementById("ff"));
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=editreply",
        type:"post",
        data:form,
        processData:false,
        contentType:false,
        success:function(data){
        	if (data.code == '200') {
				//location.reload();
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });        
}









/***************************************************************************************/
function addsubmit(rule_id){
	var form = new FormData(document.getElementById("ff_"+rule_id));
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=add",
        type:"post",
        data:form,
        processData:false,
        contentType:false,
        success:function(data){
            if (data.code == '200') {
				location.href='/index.php?m=Weixin&c=Weixin&a=automaticreply&type=1';
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });        
}

function editsubmit(rule_id){
	var form = new FormData(document.getElementById("ff_"+rule_id));
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=edit&id="+rule_id,
        type:"post",
        data:form,
        processData:false,
        contentType:false,
        success:function(data){
        	if (data.code == '200') {
				//location.reload();
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });        
}


function delsubmit(rule_id,type){
	
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=delete&id="+rule_id,
        type:"post",
        data:rule_id,
        processData:false,
        contentType:false,
        success:function(data){
            if (data.code == '200') {
				location.href='/index.php?m=Weixin&c=Weixin&a=automaticreply&type='+type;
            } else {
            	$.messager.alert('提示', data.msg);
        		return;
            }
        }
    });        
}
$(function () {
    $("#select_name").keydown(function (e) {
        if (e.keyCode == 13) {
        	location.href='/index.php?m=Weixin&c=Weixin&a=automaticreply&type=1&name='+$("#select_name").val();
        }
    });
    $("#select_name2").keydown(function (e) {
        if (e.keyCode == 13) {
        	location.href='/index.php?m=Weixin&c=Weixin&a=automaticreply&type=4&name='+$("#select_name2").val();
        }
    });
});

function setSort(rule_id){
    var len = $(".rule-group-container").length;
    
    for(var i=0;i<=len;i++){
        var num = i;
        $('#menu_num_'+rule_id+' div h3 em').text(num+')');
    }
    
}
function addrulecontent() {
	var name = $(".input-medium").val();
	if (!name) {
		$.messager.alert('提示', '请输入名称');
		return;
	}
	var rule_id = parseInt($("#keyword_num").val())+parseInt(1);
	$("#keyword_num").val(rule_id);
	var Ahtml = '<div class="rule-group-container">'+
    				'<form id="ff_'+rule_id+'" method="post" action="">'+
                    	'<div id="menu_num_'+rule_id+'" onmouseover="addcha('+rule_id+')" onmouseout="deletecha('+rule_id+')" class="rule-group">'+
                    	'<div class="rule-meta" onmouseover="edit_blue('+rule_id+',1)" onmouseout="del_edit_blue('+rule_id+',1)">'+
                            '<h3>'+
                                '<em class="rule-id"></em>'+
                                '<span class="rule-name rule-name-'+rule_id+'">'+name+'</span><input type="hidden" name="info[name]" id="reply_name_'+rule_id+'" value="'+name+'">'+
                                '<span class="rule-opts opts-'+rule_id+'">'+
                                    '<a href="javascript:;" onclick="$(\'#menu_name\').val(\''+name+'\');$(\'#now_rule_name\').val('+rule_id+');$(\'#passw\').window(\'open\');" class="js-edit-rule">编辑</a>'+
                                    '<span>-</span>'+
                                    '<a href="javascript:;" onclick="delRule('+rule_id+',1)" class="js-delete-rule">删除</a>'+
                                '</span>'+
                            '</h3>'+
                        '</div>'+
                        '<div class="rule-body">'+
                            '<div class="long-dashed"></div>'+
                            '<div class="rule-keywords" style="border-right: 1px solid #ccc">'+
                                '<div class="rule-inner">'+
                                    '<h4>关键词：</h4>'+
                                    '<div class="keyword-container">'+
                                        '<div class="info keyword_info_'+rule_id+'">还没有任何关键字!</div>'+
                                        '<input type="hidden" value="1" id="keyword-num-'+rule_id+'">'+
                                        '<div class="keyword-list keyword-list-'+rule_id+'"></div>'+
                                    '</div>'+
                                    '<hr class="dashed">'+
                                    '<div class="opt">'+
                                        '<a href="javascript:;" onclick="showkeyword('+rule_id+',0)" class="js-add-keyword">+ 添加关键词</a>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="rule-replies" style="border-left: 1px solid #ccc;position: relative;margin-left: -1px;">'+
                                '<div class="rule-inner">'+
                                   '<h4>自动回复：'+
                                        '<span class="send-method" id="send-method-'+rule_id+'"> 随机发送</span>'+
                                        '<input type="hidden" id="automatic_type_'+rule_id+'" name="info[reply_type]" value="2">'+
                                        '<div class="reply-head-opts" onmouseover="edit_blue('+rule_id+',2)" onmouseout="del_edit_blue('+rule_id+',2)">'+
                                            '<a href="javascript:;" onclick="showEditAutomatic('+rule_id+',1)" style="margin-left: 5px;" class="js-edit-send-method js-edit-send-'+rule_id+'">编辑</a>'+
                                        '</div>'+
                                    '</h4>'+
                                    '<div class="reply-container">'+
                                        '<div class="info reply_info_'+rule_id+'">还没有任何回复！</div>'+
                                        '<input type="hidden" id="reply_content_num" value="1">'+
                                        '<ol class="reply-list reply-list-'+rule_id+'"></ol>'+
                                    '</div>'+
                                    '<hr class="dashed2">'+
                                    '<div class="opt">'+
                                        '<a class="js-add-reply js-add-reply-'+rule_id+' add-reply-menu" onclick="showreply('+rule_id+',0)" href="javascript:;">+ 添加一条回复</a>'+
                                        '<span class="disable-opt disable-opt-'+rule_id+' hide">最多十条回复</span>'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</form>'+
            '</div>';
	$(".weixin-normal").append(Ahtml);
	setSort(rule_id);
	addsubmit(rule_id);
	$('.ui-popover').addClass('hide');
}
 


function delreply(rule_id,keyword_id,type) {
	$(".reply_txt_"+rule_id+"_"+keyword_id).remove();
	if ($(".reply_txt_"+rule_id).length == '0') {
		$(".reply_info_"+rule_id).removeClass("hide");
	}
	if (type == '1'){
    	if ($(".reply_txt_"+rule_id).length < '10') {
    		$(".disable-opt_"+rule_id).addClass('hide');
    		$(".js-add-reply_"+rule_id).removeClass('hide');
    	}
    	editsubmit(rule_id)
	} else if (type == '2'){
		$(".js-add-reply-"+rule_id).show();
		editsubmit2();
	} else if (type == '5'){
		if ($(".reply_txt_"+rule_id).length < '10') {
    		$(".disable-opt_"+rule_id).addClass('hide');
    		$(".js-add-reply_"+rule_id).removeClass('hide');
    	}
	}
	
}
function showreply(rule_id,keyword_id) {
	$('#replyContent').window('open');
	$("#now_rule_id").val(rule_id);
	$("#now_reply_id").val(keyword_id);
	$("#now_reply_type").val("1");
	if (keyword_id == '0') {
    	$(".js-txta").val("");
    	delgraphic();
	} else {
		var other_id = $("#other-id-"+rule_id+"-"+keyword_id).val();
		if (other_id) {
			$(".js-txta").val("");
			$(".complex-backdrop").css("display","block");
			var Ahtml = '<a href="javascript:;" class="close--circle js-delete-complex" onclick="delgraphic()">×</a>'+
		                '<div class="ng-item">'+
		                    '<span class="label label-success">图 文</span>'+
		                    '<div class="ng-title">'+
		                    	'<input type="hidden" value="'+other_id+'" id="graphic_id">'+
		                    	'<input type="hidden" value="'+$(".reply_content_"+rule_id+"_"+keyword_id).html()+'" id="graphic_name">'+
		                        '<a target="_blank" class="new-window"> '+$(".reply_content_"+rule_id+"_"+keyword_id).html()+'</a>'+
		                    '</div>'+
		                '</div>';
		    $(".ng-single").html(Ahtml);
		} else {
			$(".js-txta").val($(".reply_content_"+rule_id+"_"+keyword_id).html());
		}
	}
}
function editreplyContent() {
	var content = $(".js-txta").val();
	var rule_id = $("#now_rule_id").val();
	var reply_id = $("#now_reply_id").val();
	var type = $("#now_reply_type").val();
	
	var graphic_id = $("#graphic_id").val();
	var graphic_name = $("#graphic_name").val();
	
	/* if (!content) {
		$.messager.alert('提示', '请输入回复内容');
		return;
	} */
	if (type == '1' || type == '4') {
    	var content_num = $("#reply_content_num").val();
    	if (reply_id == '0') {
    		var Ahtml = '<li onmouseover="edit_blue('+rule_id+',3,'+content_num+')" onmouseout="del_edit_blue('+rule_id+',3,'+content_num+')" class="reply_txt_'+rule_id+' reply_txt_'+rule_id+'_'+content_num+'">'+
                    '<div class="reply-cont">'+
                        '<div class="reply-summary">'+
                            '<span class="label label-success success_'+rule_id+'_'+content_num+'">文本</span>'+
                            '<span class="reply_content_'+rule_id+'_'+content_num+'"></span>'+
                            '<input type="hidden" name="info[content]['+content_num+'][reply_content]" id="reply-content-'+rule_id+'-'+content_num+'" value="">'+
                        	'<input type="hidden" name="info[content]['+content_num+'][content_type]" value="2">'+
                        	'<input type="hidden" name="info[content]['+content_num+'][content_status]" id="content-status-'+rule_id+'-'+content_num+'" value="1">'+
                        	'<input type="hidden" name="info[content]['+content_num+'][other_id]" id="other-id-'+rule_id+'-'+content_num+'" value="">'+
                        '</div>'+
                    '</div>'+
                    '<div class="reply-opts reply-opts_'+rule_id+'_'+content_num+'">'+
                         '<a class="js-edit-it" onclick="showreply('+rule_id+','+content_num+',1)" href="javascript:;">编辑</a>'+
                         ' - '+
                         '<a class="js-delete-it" href="javascript:;" onclick="delreply('+rule_id+','+content_num+',1)">删除</a>'+
                     '</div>';
                    if($(".reply_txt_"+rule_id).length != '0') {
    					Ahtml += '<span class="after"></span>';
                  	}
                    Ahtml += '</li>';
            $(".reply-list-"+rule_id).append(Ahtml);
            reply_id = content_num;
            $("#reply_content_num").val(parseInt(content_num)+parseInt(1));
            if ($(".reply_txt_"+rule_id).length == '10') {
        		$(".disable-opt_"+rule_id).removeClass('hide');
        		$(".js-add-reply_"+rule_id).addClass('hide');
        	}
    	}
    	if ($(".reply_txt_"+rule_id).length >= '1') {
    		$(".reply_info_"+rule_id).addClass("hide");
    	}
    
    	if (graphic_id) {
    		$(".success_"+rule_id+"_"+reply_id).html('图文');
    		content = graphic_name;
    		$("#content-status-"+rule_id+"-"+reply_id).val('2');
    		$("#other-id-"+rule_id+"-"+reply_id).val(graphic_id);
    	} else {
    		$(".success_"+rule_id+"_"+reply_id).html('文本');
        	$("#other-id-"+rule_id+"-"+reply_id).val("");
    		$("#content-status-"+rule_id+"-"+reply_id).val('1');
    	}
    	
    	$(".reply_content_"+rule_id+"_"+reply_id).html(content);
    	$("#reply-content-"+rule_id+"-"+reply_id).val(content);
    	$('#replyContent').window('close');
    	editsubmit(rule_id);
    	
	} else if (type == 2) {
		
		var content_num = $("#reply_content_num").val();
    	if (reply_id == '0') {
    		var Ahtml = '<li onmouseover="edit_blue('+rule_id+',3,1)" onmouseout="del_edit_blue('+rule_id+',3,1)" class="reply_txt_'+rule_id+' reply_txt_'+rule_id+'_1">'+
                    '<div class="reply-cont">'+
                        '<div class="reply-summary">'+
                            '<span class="label label-success success_'+rule_id+'">文本</span>'+
                            '<span class="reply_content_'+rule_id+'"></span>'+
                            '<input type="hidden" name="info[content][reply_content]" id="reply-content-'+rule_id+'" value="">'+
                        	'<input type="hidden" name="info[content][content_type]" value="2">'+
                        	'<input type="hidden" name="info[content][reply_type]" value="2">'+
                        '</div>'+
                    '</div>'+
                    '<div class="reply-opts reply-opts_'+rule_id+'_1" style="right:100px">'+
                         '<a class="js-edit-it" onclick="showreply2('+rule_id+',1,2)" href="javascript:;">编辑</a>'+
                         ' - '+
                         '<a class="js-delete-it" href="javascript:;" onclick="delreply('+rule_id+',1,2)">删除</a>'+
                     '</div>';
                    if($(".reply_txt_"+rule_id).length != '0') {
    					Ahtml += '<span class="after"></span>';
                  	}
                    Ahtml += '</li>';
            $(".reply-list-"+rule_id).append(Ahtml);
            reply_id = content_num;
            $("#reply_content_num").val(parseInt(content_num)+parseInt(1));
            if ($(".reply_txt_"+rule_id).length == '10') {
        		$(".disable-opt_"+rule_id).removeClass('hide');
        		$(".js-add-reply_"+rule_id).addClass('hide');
        	}
    	}
    	if ($(".reply_txt_"+rule_id).length >= '1') {
    		$(".reply_info_"+rule_id).addClass("hide");
    	}
    	if ($(".reply-list").length >= '1') {
			$(".js-add-reply-"+rule_id).hide();
		}
    
    	if (graphic_id) {
    		$(".success_"+rule_id).html('图文');
    		content = graphic_name;
    		$("#content-status-"+rule_id).val('2');
    		$("#other-id-"+rule_id).val(graphic_id);
    	} else {
    		$(".success_"+rule_id).html('文本');
        	$("#other-id-"+rule_id).val("");
    		$("#content-status-"+rule_id).val('1');
    	}
    	
    	$(".reply_content_"+rule_id).html(content);
    	$("#reply-content-"+rule_id).val(content);
    	$('#replyContent').window('close');
    	editsubmit2();
	} else if (type == 5) {
		
		var content_num = $("#reply_content_num").val();
    	if (reply_id == '0') {
    		var Ahtml = '<li onmouseover="edit_blue('+rule_id+',3,'+content_num+')" onmouseout="del_edit_blue('+rule_id+',3,'+content_num+')" class="reply_txt_'+rule_id+' reply_txt_'+rule_id+'_'+content_num+'">'+
                '<div class="reply-cont">'+
                    '<div class="reply-summary">'+
                        '<span class="label label-success success_'+rule_id+'_'+content_num+'">文本</span>'+
                        '<span class="reply_content_'+rule_id+'_'+content_num+'"></span>'+
                        '<input type="hidden" name="info[content]['+content_num+'][reply_content]" id="reply-content-'+rule_id+'-'+content_num+'" value="">'+
                    	'<input type="hidden" name="info[content]['+content_num+'][content_type]" value="2">'+
                    '</div>'+
                '</div>'+
                '<div class="reply-opts reply-opts_'+rule_id+'_'+content_num+'">'+
                     '<a class="js-edit-it" onclick="showreply5('+rule_id+','+content_num+',1)" href="javascript:;">编辑</a>'+
                     ' - '+
                     '<a class="js-delete-it" href="javascript:;" onclick="delreply('+rule_id+','+content_num+',5)">删除</a>'+
                 '</div>';
            if($(".reply_txt_"+rule_id).length != '0') {
				Ahtml += '<span class="after"></span>';
          	}
            Ahtml += '</li>';
            $(".reply-list-"+rule_id).append(Ahtml);
            reply_id = content_num;
            $("#reply_content_num").val(parseInt(content_num)+parseInt(1));
            if ($(".reply_txt_"+rule_id).length == '10') {
        		$(".disable-opt_"+rule_id).removeClass('hide');
        		$(".js-add-reply_"+rule_id).addClass('hide');
        	}
    	}
    	if ($(".reply_txt_"+rule_id).length >= '1') {
    		$(".reply_info_"+rule_id).addClass("hide");
    	}
    	if (graphic_id) {
    		$(".success_"+rule_id+"_"+reply_id).html('图文');
    		content = graphic_name;
    		$("#content-status-"+rule_id+"-"+reply_id).val('2');
    		$("#other-id-"+rule_id+"-"+reply_id).val(graphic_id);
    	} else {
    		$(".success_"+rule_id+"_"+reply_id).html('文本');
        	$("#other-id-"+rule_id+"-"+reply_id).val("");
    		$("#content-status-"+rule_id+"-"+reply_id).val('1');
    	}
    
    	$(".reply_content_"+rule_id+"_"+reply_id).html(content);
    	$("#reply-content-"+rule_id+"-"+reply_id).val(content);
    	$('#replyContent').window('close');
    	//editsubmit5();
	}
}
function addwxlink() {
	var link = $("#wx_link").val();
	if (!link) {
		$.messager.alert('提示', '请输入链接');
		return;
	}
	$(".js-txta").val($(".js-txta").val()+link);
	$(".hyperlink-wrapper").hide();
	delgraphic();
}

$(document).click(function(){
    $(".hyperlink-wrapper").css("display","none");
})
//为了防止点击 box1-right 也关闭box1-right 此处要防止冒泡
$(".hyperlink-wrapper").click(function()
{
    return false;
})
$(".js-open-wx_link").click(function()
{
    return false;
})

function showwindow() {
	$(".hyperlink-wrapper").show();
}

function showEditAutomatic(id,type) {
	$("#nowAutomatic_id").val(id);
	$("#nowtype").val(type);
	
	var old_type = $("#automatic_type_"+id).val();
	if (old_type=='1') {
		$("#automatic_status-1").attr("checked",true);
	} else if (old_type=='2') {
		$("#automatic_status-2").attr("checked",true);
	} else {
		$("#automatic_status-2").attr("checked",true);
	}
	
	$("#editAutomatic").window('open');
}
function closeEditAutomatic() {
	var id = $("#nowAutomatic_id").val();
	var type = $("#nowtype").val();
	var val = $('input:radio[name="automatic_status"]:checked').val();
	if (type != '5') {
    	if (val == '1') {
    		$("#send-method-"+id).html('全部发送');
    	} else if (val == '2') {
    		$("#send-method-"+id).html('随机发送');
    	}
	} else {
		if (val == '1') {
    		$("#send-method-"+id).html('全部回复列表中的所有内容');
    	} else if (val == '2') {
    		$("#send-method-"+id).html('随机回复列表中的一条内容');
    	}
	}
	$("#automatic_type_"+id).val(val);
	$("#editAutomatic").window('close');
	editsubmit(id);
}

function delkeyword(rule_id,keyword_id,type) {
	$('.keyword-this-'+rule_id+'-'+keyword_id).remove();
	if ($(".keyword_"+rule_id).length == '0') {
		$('.keyword_info_'+rule_id).removeClass('hide');
	}
	if (type == '1') {
		editsubmit(rule_id);
	} else if (type == '2') {
		editsubmit2();
	}
}

function editkeyword() {
	var content = $("#wei_link").val();
	var rule_id = $("#now_rule_id").val();
	var keyword_id = $("#now_keyword_id").val();
	var now_type = $("#now_type").val();
	
	if (!content) {
		$.messager.alert('提示', '请输入关键词');
		return;
	}
	var id = keyword_id;
	if (!$('.keyword-this-'+rule_id+'-'+keyword_id).html()) {
		id = '';
	}
	if (now_type == '1') {
    	$.ajax({
            url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=keyword_is_exist&reply_id="+rule_id,
            type:"post",
            data:"keyword="+content,
            success:function(data){            
                if (data.code == '500') {
                	$.messager.alert('提示', data.msg);
            		return;
                } else {
                	if (!$('.keyword-this-'+rule_id+'-'+keyword_id).html()) {
                		var Ahtml = '<div class="keyword_'+rule_id+' keyword input-append keyword-this-'+rule_id+'-'+keyword_id+'" onmouseover="adddel('+rule_id+','+keyword_id+')" onmouseout="deletedel('+rule_id+','+keyword_id+')">'+
                                        '<a href="javascript:;" id="cha-'+rule_id+'-'+keyword_id+'" class="close--circle" onclick="delkeyword('+rule_id+','+keyword_id+',1)">×</a>'+
                                        '<div onclick="showkeyword('+rule_id+','+keyword_id+')"><input type="hidden" id="keyword_content_'+rule_id+'_'+keyword_id+'" value=""/>'+
                                            '<input type="hidden" id="keyword_content_type_'+rule_id+'_'+keyword_id+'" value="1"/>'+
                                            '<span class="value" id="keyword_value_'+rule_id+'_'+keyword_id+'"></span>'+
                                            '<span class="add-on" id="keyword_value_name_'+rule_id+'_'+keyword_id+'"></span>'+
                                            '<input type="hidden" name="info[keyword]['+keyword_id+'][reply_content]" id="keyword-value-name-'+rule_id+'-'+keyword_id+'" value="">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][keyword_type]" id="keyword_type_'+rule_id+'_'+keyword_id+'" value="1">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][content_type]" value="1">'+
                                        '</div>'+
                                  '</div>';
                        $(".keyword-list-"+rule_id).append(Ahtml);
                	}
                	
                	$("#keyword_content_"+rule_id+"_"+keyword_id).val(content);
                	$("#keyword_value_"+rule_id+"_"+keyword_id).html("");
                	$.ajax({
                        type:"POST",
                        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=code",
                        data:{content:content},
                        datatype: "json",
                        success:function(data){
                        	$("#keyword_value_"+rule_id+"_"+keyword_id).html(data);
                        }      
                    });
                	var val=$('input:radio[name="keyword_status"]:checked').val();
                	$("#keyword_content_type_"+rule_id+"_"+keyword_id).val(val);
    
                	if (val == '1') {
                		$("#keyword_value_name_"+rule_id+"_"+keyword_id).html('全匹配');
                	} else if (val == '2') {
                		$("#keyword_value_name_"+rule_id+"_"+keyword_id).html('模糊');
                	}
    
                	$("#keyword-value-name-"+rule_id+"-"+keyword_id).val(content);
                	$("#keyword_type_"+rule_id+"_"+keyword_id).val(val);
    
                	if ($(".keyword_"+rule_id).length >='1') {
                		$('.keyword_info_'+rule_id).addClass('hide');
                	}
                	$('#keywordContent').window('close')
                	editsubmit(rule_id);
                }
            }
        });      
	} else if ($type='2') {
		$.ajax({
            url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=keyword_is_exist&reply_id="+rule_id+"&type=2",
            type:"post",
            data:"keyword="+content,
            success:function(data){            
                if (data.code == '500') {
                	$.messager.alert('提示', data.msg);
            		return;
                } else {
            		if (!$('.keyword-this-'+rule_id+'-'+keyword_id).html()) {
                		var Ahtml = '<div class="keyword_'+rule_id+' keyword input-append keyword-this-'+rule_id+'-'+keyword_id+'" onmouseover="adddel('+rule_id+','+keyword_id+')" onmouseout="deletedel('+rule_id+','+keyword_id+')">'+
                                        '<a href="javascript:;" id="cha-'+rule_id+'-'+keyword_id+'" class="close--circle" onclick="delkeyword('+rule_id+','+keyword_id+',2)">×</a>'+
                                        '<div onclick="showkeyword('+rule_id+','+keyword_id+')"><input type="hidden" id="keyword_content_'+rule_id+'_'+keyword_id+'" value=""/>'+
                                            '<input type="hidden" id="keyword_content_type_'+rule_id+'_'+keyword_id+'" value="1"/>'+
                                            '<span class="value" id="keyword_value_'+rule_id+'_'+keyword_id+'"></span>'+
                                            '<span class="add-on" id="keyword_value_name_'+rule_id+'_'+keyword_id+'"></span>'+
                                            '<input type="hidden" name="info[keyword]['+keyword_id+'][reply_content]" id="keyword-value-name-'+rule_id+'-'+keyword_id+'" value="">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][keyword_type]" id="keyword_type_'+rule_id+'_'+keyword_id+'" value="1">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][content_type]" value="1">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][reply_type]" value="2">'+
                                        '</div>'+
                                  '</div>';
                        $(".keyword-list-"+rule_id).append(Ahtml);
                	}
                	
                	$("#keyword_content_"+rule_id+"_"+keyword_id).val(content);
                	$("#keyword_value_"+rule_id+"_"+keyword_id).html("");
                	$.ajax({
                        type:"POST",
                        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=code",
                        data:{content:content},
                        datatype: "json",
                        success:function(data){
                        	$("#keyword_value_"+rule_id+"_"+keyword_id).html(data);
                        }      
                    });
                	var val=$('input:radio[name="keyword_status"]:checked').val();
                	$("#keyword_content_type_"+rule_id+"_"+keyword_id).val(val);
            
                	if (val == '1') {
                		$("#keyword_value_name_"+rule_id+"_"+keyword_id).html('全匹配');
                	} else if (val == '2') {
                		$("#keyword_value_name_"+rule_id+"_"+keyword_id).html('模糊');
                	}
            
                	$("#keyword-value-name-"+rule_id+"-"+keyword_id).val(content);
                	$("#keyword_type_"+rule_id+"_"+keyword_id).val(val);
            
                	if ($(".keyword_"+rule_id).length >='1') {
                		$('.keyword_info_'+rule_id).addClass('hide');
                	}
                	$('#keywordContent').window('close')
                	editsubmit2();
                }
            }
	   });
	} else if ($type='4') {
		$.ajax({
            url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=keyword_is_exist&reply_id="+rule_id,
            type:"post",
            data:"keyword="+content,
            success:function(data){            
                if (data.code == '500') {
                	$.messager.alert('提示', data.msg);
            		return;
                } else {
            		if (!$('.keyword-this-'+rule_id+'-'+keyword_id).html()) {
                		var Ahtml = '<div class="keyword_'+rule_id+' keyword input-append keyword-this-'+rule_id+'-'+keyword_id+'" onmouseover="adddel('+rule_id+','+keyword_id+')" onmouseout="deletedel('+rule_id+','+keyword_id+')">'+
                                        '<a href="javascript:;" id="cha-'+rule_id+'-'+keyword_id+'" class="close--circle" onclick="delkeyword('+rule_id+','+keyword_id+',2)">×</a>'+
                                        '<div onclick="showkeyword('+rule_id+','+keyword_id+')"><input type="hidden" id="keyword_content_'+rule_id+'_'+keyword_id+'" value=""/>'+
                                            '<input type="hidden" id="keyword_content_type_'+rule_id+'_'+keyword_id+'" value="1"/>'+
                                            '<span class="value" id="keyword_value_'+rule_id+'_'+keyword_id+'"></span>'+
                                            '<span class="add-on" id="keyword_value_name_'+rule_id+'_'+keyword_id+'"></span>'+
                                            '<input type="hidden" name="info[keyword]['+keyword_id+'][reply_content]" id="keyword-value-name-'+rule_id+'-'+keyword_id+'" value="">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][keyword_type]" id="keyword_type_'+rule_id+'_'+keyword_id+'" value="1">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][content_type]" value="1">'+
                                        	'<input type="hidden" name="info[keyword]['+keyword_id+'][reply_type]" value="2">'+
                                        '</div>'+
                                  '</div>';
                        $(".keyword-list-"+rule_id).append(Ahtml);
                	}
                	
                	$("#keyword_content_"+rule_id+"_"+keyword_id).val(content);
                	$("#keyword_value_"+rule_id+"_"+keyword_id).html("");
                	$.ajax({
                        type:"POST",
                        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=code",
                        data:{content:content},
                        datatype: "json",
                        success:function(data){
                        	$("#keyword_value_"+rule_id+"_"+keyword_id).html(data);
                        }      
                    });
                	var val=$('input:radio[name="keyword_status"]:checked').val();
                	$("#keyword_content_type_"+rule_id+"_"+keyword_id).val(val);
            
                	if (val == '1') {
                		$("#keyword_value_name_"+rule_id+"_"+keyword_id).html('全匹配');
                	} else if (val == '2') {
                		$("#keyword_value_name_"+rule_id+"_"+keyword_id).html('模糊');
                	}
            
                	$("#keyword-value-name-"+rule_id+"-"+keyword_id).val(content);
                	$("#keyword_type_"+rule_id+"_"+keyword_id).val(val);
            
                	if ($(".keyword_"+rule_id).length >='1') {
                		$('.keyword_info_'+rule_id).addClass('hide');
                	}
                	$('#keywordContent').window('close')
                	//editsubmit3();
                }
            }
	   });
	}
}

function showkeyword(rule_id,keyword_id) {

	if (keyword_id == '0') {
		var keyword_id = parseInt($("#keyword-num-"+rule_id).val())+parseInt(1);
		$("#keyword-num-"+rule_id).val(keyword_id);
	}
	
	$("#now_rule_id").val(rule_id);
	$("#now_keyword_id").val(keyword_id);
	$("#now_type").val("1");
	var old_content = $("#keyword_content_"+rule_id+"_"+keyword_id).val();
	var old_type = $("#keyword_content_type_"+rule_id+"_"+keyword_id).val();

	$("#wei_link").val(old_content);

	if (old_type=='1') {
		$("#keyword_status-1").attr("checked",true);
	} else if (old_type=='2') {
		$("#keyword_status-2").attr("checked",true);
	} else {
		$("#keyword_status-1").attr("checked",true);
	}
	
	$('#keywordContent').window('open');
}

function showemoji() {
	$(".emotion-wrapper").show();
}

function showemoji2() {
	$(".emotion-wrapper2").show();
}

function addkeyword(name,type) {
	var emoji_name = $(name).attr('alt');
	if (type == '1') {
    	var txt = $("#wei_link").val()+emoji_name;
    	
    	if (txt.length > 30) {
    		$.messager.alert('提示', '内容不能大于30个字符');
    		$(".emotion-wrapper").hide();
    		return;
    	}
    	$("#wei_link").val(txt)
    	$(".emotion-wrapper").hide();
	} else {
		var txt = $(".js-txta").val()+emoji_name;
		$(".js-txta").val(txt)
		$(".emotion-wrapper2").hide();
	}
	delgraphic();
}

function addfans() {
	$(".js-txta").val($(".js-txta").val()+'#粉丝昵称#')
	delgraphic();
}

function addRuleName() {
	var rule_id = $('#now_rule_name').val();
	var name = $("#menu_name").val();

	if (!name) {
		$.messager.alert('提示', '请输入名称');
		return;
	}
	$.ajax({
        url:"/index.php?m=Weixin&c=Weixin&a=automaticreply&format=name_is_exist&name="+name+"&id="+rule_id,
        type:"post",
        data:'',
        processData:false,
        contentType:false,
        success:function(data){
            var is_exist = data.code;
            if (data.code == '500') {
            	$.messager.alert('提示', data.msg);
        		return;
            } else {
            	$(".rule-name-"+rule_id).html(name);
            	$("#reply_name_"+rule_id).val(name);
            	$('#passw').window('close');
            	editsubmit(rule_id);
            }
        }
    });      
	
	
}

function delRule(rule_id,type) {
	$.messager.confirm({
	    width: 350,
	    height: 180, 
	    top:200,
	    title: '温馨提示',
	    msg: '您确定要删除吗?',
	    fn: function (data) {
	    	if (data == true) {
	    		$("#menu_num_"+rule_id).remove();
	    		delsubmit(rule_id,type);
    		}
	    }
	});
}
function addcha(id) {
	$('#menu_num_'+id).addClass('rule-group-color000');
}

function deletecha(id) {
	$('#menu_num_'+id).removeClass('rule-group-color000');
}
function adddel(id,mun) {
	$('#cha-'+id+'-'+mun).show();
}

function deletedel(id,mun) {
	$('#cha-'+id+'-'+mun).hide();
}
function edit_blue(id,type,son_id) {
	if (type == '1') {
		$('.opts-'+id+' a').css('color','#38f');
	} else if (type == '2') {
		$('.js-edit-send-'+id).css('color','#38f');
	} else if (type == '3') {
		$('.reply-opts_'+id+'_'+son_id+' a').css('color','#38f');
	}
}

function del_edit_blue(id,type,son_id) {
	if (type == '1') {
		$('.opts-'+id+' a').css('color','#ddd');
	} else if (type == '2') {
		$('.js-edit-send-'+id).css('color','#ddd');
	} else {
		$('.reply-opts_'+id+'_'+son_id+' a').css('color','#ddd');
	}
}
