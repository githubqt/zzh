/***
 * 微信实时信息js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-08-30
 */
var fields =  [ [ {
    field : 'head_url',  
    width : 90,  
    title : '头像'  
}, {  
    field : 'nickname',  
    width : 100,  
    title : '会员'  
}, {  
    field : 'sex_name',  
    width : 60,  
    title : '性别'  
}, {  
    field : 'address',  
    width : 150,   
    title : '地址' , 
    align :'content'
}, {  
    field : 'content',  
    width : 150,   
    title : '内容'  
}, {  
    field : 'note',  
    width : 100,   
    title : '备注'  
}, {  
    field : 'reply_txt',  
    width : 80,   
    title : '是否回复'  
}, {  
    field : 'is_star_txt',  
    width : 80,   
    title : '是否加星'  
}, {  
    field : 'msg_type_txt',  
    width : 80,   
    title : '消息类型'  
}, {  
    field : 'created_at',  
    width : 150,   
    title : '发送时间'  
} , {
	field:'operate',
	title:'操作',
	width: 260,
	align:'left', 
    formatter:function(value, row, index){  
    	var str = '<input type="button" onclick="location.href=\'/?m=Weixin&c=Weixin&a=detailMsg&id='+row.user_msg_id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
    	if (row.is_star == '1') {
    		str += '<input type="button" onclick="refund('+row.id+',2)"  class="easyui-linkbutton" data-options="selected:true" value="加星" >';  
    	} else {
    		str += '<input type="button" onclick="refund('+row.id+',1)"  class="easyui-linkbutton" data-options="selected:true" value="去星" >';  
    	}
    	str += '<input type="button" onclick="$(\'#edit_id\').val('+row.id+');$(\'#note_content\').val(\''+row.note+'\');$(\'#note\').window(\'open\')"  class="easyui-linkbutton" data-options="selected:true" value="备注" >'; 
        if (row.is_ok_reply != '1') {
        	str += '<input type="button" onclick="$(\'#user_id\').val('+row.user_msg_id+');$(\'#edit_id\').val('+row.id+');$(\'#replyContent\').window(\'open\')"  class="easyui-linkbutton" data-options="selected:true" value="快速回复" >';  
        }
        return str;  
    }
}  
] ];

function refund(id,type) {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Weixin&c=Weixin&a=realtimeMsg&format=edit&id="+id,
        dateType: "json",
        data:'is_star='+type,
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=Weixin&c=Weixin&a=realtimeMsg"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}

function editnote() {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Weixin&c=Weixin&a=realtimeMsg&format=edit&id="+$("#edit_id").val(),
        dateType: "json",
        data:'note='+$("#note_content").val(),
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=Weixin&c=Weixin&a=realtimeMsg"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}


// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[nickname]'] = $('#nickname').val();
// 	queryData['info[is_reply]'] = $('#is_reply').val();
// 	queryData['info[is_note]'] = $('#is_note').val();
// 	queryData['info[is_star]'] = $('#is_star').val(),
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue');
//
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Weixin&c=Weixin&a=realtimeMsg&format=list',
// 			    remoteSort: true,
// 				singleSelect:true,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:false,
// 				queryParams:queryData,
// 			});
//
// }
$(function(){
	//searchInfo();
	searchInfo2()
})

//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });


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
	$(".js-txta").val($(".js-txta").val()+link);
	$(".hyperlink-wrapper").hide();
}
function addfans() {
	$(".js-txta").val($(".js-txta").val()+'#粉丝昵称#')
}
function showemoji() {
	$(".emotion-wrapper").show();
}

function addkeyword(name) {
	var emoji_name = $(name).attr('alt');
	var txt = $(".js-txta").val()+emoji_name;
	
	/*if (txt.length > 30) {
		$.messager.alert('提示', '内容不能大于30个字符');
		$(".emotion-wrapper").hide();
		return;
	}*/
	$(".js-txta").val(txt)
	$(".emotion-wrapper").hide();
	
}

function firemsg() {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Weixin&c=Weixin&a=realtimeMsg&format=firemsg&id="+$("#edit_id").val()+"&user_id="+$("#user_id").val(),
        dateType: "json",
        data:'content='+$(".js-txta").val(),
        success:function(data){
        	
        	
  			if (data.code == '200') {
				location.href="/index.php?m=Weixin&c=Weixin&a=realtimeMsg"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}



var fields2 =  [ [ {
    field : 'id',  
    width : 150, 
    checkbox : true , 
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
		$(".js-txta").val($(".js-txta").val()+val.content);	

	}
	$('#shortcutPhrase').window('close')
}
























