/***
 * 微信实时信息js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-08-30
 */
var fields =  [ [ {
    field : 'content',  
    width : 350,   
    title : '快捷短语内容'  
}, {  
    field : 'option_name',  
    width : 150,   
    title : '添加人'  
}, {  
    field : 'updated_at',  
    width : 150,   
    title : '编辑时间'  
}, {
	field:'operate',
	title:'操作',
	width: 150,
	align:'left', 
    formatter:function(value, row, index){ 
    	var str = '<input type="button" onclick="$(\'.js-txta\').val(\''+row.content+'\');$(\'#id\').val('+row.id+');$(\'#replyContent\').window(\'open\')"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';  
    	
    	str += '<input type="button" onclick="javascript:delproduct('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';  
        
        return str;  
    }
}  
] ];

function delproduct(id) {
	deleteInfo("/?m=Weixin&c=Weixin&a=shortcutPhrase&format=delete&id="+id, "/index.php?m=Weixin&c=Weixin&a=shortcutPhrase");
}

// function searchInfo() {
// 	var queryData = new Object();
// 	/*queryData['info[nickname]'] = $('#nickname').val();
// 	queryData['info[is_reply]'] = $('#is_reply').val();
// 	queryData['info[is_note]'] = $('#is_note').val();
// 	queryData['info[is_star]'] = $('#is_star').val(),
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue'); */
//
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Weixin&c=Weixin&a=shortcutPhrase&format=list',
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
// $(function(){
// 	searchInfo();
// })
//
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
        url: "/?m=Weixin&c=Weixin&a=shortcutPhrase&format=add&id="+$("#id").val(),
        dateType: "json",
        data:'content='+$(".js-txta").val(),
        success:function(data){
        	
  			if (data.code == '200') {
				location.href="/index.php?m=Weixin&c=Weixin&a=shortcutPhrase"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}







