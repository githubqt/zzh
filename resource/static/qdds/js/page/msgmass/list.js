/***
 * 短信群发js
 * @version v0.01
 * @author lqt
 * @time 2018-08-13
 */
var fields =  [ [ {
    field : 'id',  
    width : 100,  
    sortable:true,
    align:'center',
    title : 'ID'  
}, {  
    field : 'content',  
    width : 300,  
    align:'center',
    title : '短信内容'  
}, {  
    field : 'mobile_num_total',  
    width : 100,  
    sortable:true,
    align:'center',
    title : '发送人数'  
}, {  
    field : 'mobile_num_ok',  
    width : 100,  
    sortable:true, 
    align:'center',
    title : '到达人数'  
}, {  
    field : 'sms_num_total',  
    width : 100,   
    sortable:true,
    align:'center',
    title : '发送条数'  
} , {  
    field : 'sms_num_ok',  
    width : 100,   
    sortable:true,
    align:'center',
    title : '到达条数'  
} , {  
    field : 'send_time',  
    width : 140,   
    sortable:true,
    align:'center',
    title : '发送时间'  
} , {  
    field : 'status_txt',  
    width : 100,  
    align:'center', 
    title : '状态'  
} , {
	field:'operate',
	title:'操作',
	width: 280,
	align:'center', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Msgmass&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        if (row.status == '1') {
        	str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Msgmass&a=edit&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="编辑" >';  
    		str += '<input type="button" onclick="javascript:startmass('+row.id+');" class="easyui-linkbutton" data-options="selected:true" value="启动" >'; 
    		str += '<input type="button" onclick="javascript:delmass('+row.id+');" class="easyui-linkbutton" data-options="selected:true" value="删除" >'; 
    	}
        return str;  
    }
}  
] ];

function delmass(id) {
	deleteInfo("/?m=Marketing&c=Msgmass&a=delete&id="+id,"/index.php?m=Marketing&c=Msgmass&a=list");
}

function startmass(id) {
	$.messager.confirm('温馨提示', '您确定要启动吗?',function(res){
		if (res == true) {
			$.ajax({
		        type: "POST",
		        async:true,  // 设置同步方式
		        url: "/?m=Marketing&c=Msgmass&a=start&id="+id,
		        dateType: "json",
		        success:function(data){
		  			if (data.code == '200') {
						location.href='/index.php?m=Marketing&c=Msgmass&a=list';
					} else {
						$.messager.alert('提示', data.msg);
					}
		        }
		    });	
		}
	})	
}
// function searchInfo() {
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Marketing&c=Msgmass&a=list&format=list',
// 				singleSelect:true,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				pageList: [10,20,50],
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:true
// 			});
//
// }
//
// $(function(){
// 	searchInfo();
// })