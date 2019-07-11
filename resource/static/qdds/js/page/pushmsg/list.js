/***
 * 优惠券js
 * @version v0.01
 * @author lqt
 * @time 2018-05-21
 */
var fields =  [ [ {
    field : 'id',  
    width : 70,  
    title : 'ID'  
}, {  
    field : 'mobile',  
    width : 100,  
    title : '手机号'  
}, {  
    field : 'content', 
    width : 450,  
    title : '发送内容'  
}, {  
    field : 'model_name',  
    width : 100,  
    title : '模板名称'  
}, {  
    field : 'type_txt',  
    width : 100,   
    title : '短信类型'  
}, {  
    field : 'status_txt',  
    width : 100,   
    title : '发送状态'  
} , {  
    field : 'created_at',  
    width : 140,  
    title : '发送时间'  
}]];
// function searchInfo() {
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Marketing&c=Pushmsg&a=list&format=list',
// 				remoteSort: false,
// 				singleSelect:true,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				pageList: [10,20,50],
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:true,
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

function updateSms() {
	$('#dd').dialog({
	    title: '修改短信签名',
	    width: 480,
	    height: 280,
	    closed: false,
	    cache: false,
	    modal: true
	});	
}
function closeSms() {
	$('#dd').dialog('close');	
}
function submitSms() {
	var sms_name = $('#sms_name').val();	
	$.ajax({
    	type: "POST",
    	dataType: "json",
        url: location.href+"&format=editSms",
        data: {sms_name:sms_name},
	    success:function(res){
	    	$.messager.alert('提示', res.msg);
	    	if (res.code == '200') {
	    		$('#priv_sms_name').html(sms_name);
	    		$('#dd').dialog('close');
	    	}
		}
	});	
}
