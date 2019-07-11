/***
 * 微信查看信息js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-09-06
 */
//写出对应功能代码 
$(function(){
	/* $('#talkwords').keydown(function(e){
        if (e.keyCode == '13') {
        	fireMsg();
        }
    }); */
	
	$("#words").scrollTop($("#words")[0].scrollHeight);
    //定时任务
    window.setInterval("getNewContent()", 10000);
    searchInfo2() 
});


var fields2 =  [ [ {
    field : 'id',  
    width : 150, 
    checkbox : true, 
    title : 'ID'  
}, {  
    field : 'content',  
    width : 300,   
    title : '快捷短语内容'  
}, {  
    field : 'option_name',  
    width : 100,   
    title : '添加人'  
}
] ];

function searchInfo2() {
	var queryData = new Object();
	queryData['info[content]'] = $('#content').val();
	
	$('#dg2').datagrid({
		title:'',
		width:'100%',
		height:'auto',
		nowrap: true,
		autoRowHeight: true,
		striped: true,
	    url: '/index.php?m=Weixin&c=Weixin&a=shortcutPhrase&format=list',
	    remoteSort: true,
		singleSelect:false,
		idField:'id',
		loadMsg:'数据加载中......',  
		columns: fields2,
		pagination:true,
		rownumbers:false,
		queryParams:queryData,
	});
		
}


function shopSetadd() {
	var selectrow = $("#dg2").datagrid("getChecked");//获取的是数组，多行数据

	if (selectrow.length == 0) {
		$.messager.alert('提示', '请选择快捷短语！');
		return;
	}
	
	
	for(var i=0;i<selectrow.length;i++){
		var val = selectrow[i];
		$("#talkwords").val($("#talkwords").val()+val.content);	

	}
	$('#shortcutPhrase').window('close')
}

function fireMsg(){
    var is_over = $("#is_over").val();
    if (is_over == '1') {
    	$.messager.alert('提示', '根据微信规定，由于该用户48小时未与你互动，你不能再主动发消息给他。直到用户下次主动发消息给你才可以对其进行回复。');
		return;
    }
    var vals = $('#talkwords').val()
    if(vals == '')
    {
    	$.messager.alert('提示', '请输入回复内容');
        return
    }
    $.ajax({
    	url: "/?m=Weixin&c=Weixin&a=realtimeMsg&format=firemsg&id="+$("#id").val()+"&user_id="+$("#msg_id").val(),
        type:"post",
        data:"content="+vals,
        success:function(data){            
            if (data.code == '500') {
            	$.messager.alert('提示', data.msg);
        		return;
            } else {
                $.ajax({
                    url:"/index.php?m=Weixin&c=Weixin&a=detailMsg&format=getemoji&id="+$("#msg_id").val(),
                    type:"post",
                    data:"content="+vals,
                    success:function(data){
                        var str = '<div class="btalk">'+
                        	'<div style="color: #a5a3a3;font-size: 12px;margin-bottom: 5px;">'+getNowFormatDate()+'</div>'+
                        	'<span>'+data+'</span>  : 我'+
                        '</div>';
                        
                        // 原有内容的基础上加上 str
                        $("#words").append(str); 
                        $("#words").scrollTop($("#words")[0].scrollHeight);
                        $('#talkwords').val("");
                    }
                });  
            }
        }
    });
}


function fireGraphicMsg(graphic_id,title){
    var is_over = $("#is_over").val();
    if (is_over == '1') {
    	$.messager.alert('提示', '根据微信规定，由于该用户48小时未与你互动，你不能再主动发消息给他。直到用户下次主动发消息给你才可以对其进行回复。');
		return;
    }
   
    $.ajax({
    	url: "/?m=Weixin&c=Weixin&a=realtimeMsg&format=firemsg&id="+$("#id").val()+"&user_id="+$("#msg_id").val()+"&other_id="+graphic_id,
        type:"post",
        data:"1=1",
        success:function(data){    
        	$('#graphic_material').window('close');
            if (data.code == '500') {
            	$.messager.alert('提示', data.msg);
        		return;
            } else {
                var str = '<div class="btalk">'+
                	'<div style="color: #a5a3a3;font-size: 12px;margin-bottom: 5px;">'+getNowFormatDate()+'</div>'+
                	'<span> 图文：'+title+'</span>  : 我'+
                '</div>';
                
                // 原有内容的基础上加上 str
                $("#words").append(str); 
                $("#words").scrollTop($("#words")[0].scrollHeight);
            }
        }
    });
}


function getNewContent(){
	$.ajax({
    	url: "/?m=Weixin&c=Weixin&a=detailMsg&format=getContent&id="+$("#msg_id").val(),
        type:"post",
        data:"keyword=1",
        success:function(data){            
        	$("#id").val(data.last_reply_id);
			var Ahtml = '';
			if ($("#last_time").val() != data.last_time) {
            	if (data.content) {
                	$.each(data.content,function(key, value){
                		
                		if (value.msg_type != 'event') {
                    		if (value.msg_type == '1') {
                    			if (value.option_type != '2') {
                            		Ahtml+= '<div class="atalk">'+
                                        		'<div style="color: #a5a3a3;font-size: 12px">'+value.created_at+'</div>'+data.nickname+'：'+
                                        		'<span><b>'+ value.content+'</b></span>'+
                                    		'</div>';
                            	} else {
                            		Ahtml+= '<div class="atalk" style="text-align: center;">'+
                                        		'<div style="color: #a5a3a3;font-size: 12px;margin-bottom: 5px;">'+value.created_at+'</div>'+
                                        		'<b>'+ value.content+'</b>'+
                                    		'</div>';
                            	}
                            } else {
                            	Ahtml+= '<div class="btalk">'+
                                        	'<div style="color: #a5a3a3;font-size: 12px;margin-bottom: 5px;">'+value.created_at+'</div>'+
                                        	'<span>'+value.content+'</span>  : 我'+
                                    	'</div>';
                            }
                        }
                    });
                }
                
            	$("#words").html(Ahtml); 
                $("#words").scrollTop($("#words")[0].scrollHeight);
                $("#last_time").val(data.last_time);
            }
        }
    });
}


function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "-";
    var seperator2 = ":";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
            + " " + date.getHours() + seperator2 + date.getMinutes()
            + seperator2 + date.getSeconds();
    return currentdate;
}

function showemoji() {
	$(".emotion-wrapper2").show();
}

function addkeyword(name) {
	var emoji_name = $(name).attr('alt');
	var txt = $("#talkwords").val()+emoji_name;
	$("#talkwords").val(txt)
	$(".emotion-wrapper2").hide();
	
}

function addfans() {
	$("#talkwords").val($("#talkwords").val()+'#粉丝昵称#')
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

function addwxlink() {
	var link = $("#wx_link").val();
	if (!link) {
		$.messager.alert('提示', '请输入链接');
		return;
	}
	$("#talkwords").val($("#talkwords").val()+link);
	$(".hyperlink-wrapper").hide();
}