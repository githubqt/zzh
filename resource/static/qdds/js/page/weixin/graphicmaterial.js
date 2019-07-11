/***
 * 微信实时信息js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-08-30
 */
var fields =  [ [ {
    field : 'title',  
    width : 350,   
    title : '标题'  
}, {  
    field : 'is_more_text',  
    width : 150,   
    title : '类型'  
}, {  
    field : 'created_at',  
    width : 150,   
    title : '创建时间'  
}, {
	field:'operate',
	title:'操作',
	width: 150,
	align:'left', 
    formatter:function(value, row, index){ 
    	if (row.is_more == '1') {//单图文
        	var str = '<input type="button" onclick="location.href=\'/?m=Weixin&c=Weixin&a=PhraseOneEdit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >'; 
        	str += '<input type="button" onclick="javascript:delproductone('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';   
    	} else {//多图文
        	var str = '<input type="button" onclick="location.href=\'/?m=Weixin&c=Weixin&a=PhraseMoreEdit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';
        	str += '<input type="button" onclick="javascript:delproducttwo('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';    
    	}
        
        return str;  
    }
}  
] ];

function delproductone(id) {
	deleteInfo("/?m=Weixin&c=Weixin&a=GraphicMaterial&format=delete&type=1&id="+id, "/index.php?m=Weixin&c=Weixin&a=GraphicMaterial");
}
function delproducttwo(id) {
	deleteInfo("/?m=Weixin&c=Weixin&a=GraphicMaterial&format=delete&type=2&id="+id, "/index.php?m=Weixin&c=Weixin&a=GraphicMaterial");
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
// 			    url: '/index.php?m=Weixin&c=Weixin&a=GraphicMaterial&format=list',
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








