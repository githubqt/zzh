/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-31
 */
var fields =  [ [ {
    field : 'id',  
    width : 60,  
    title : 'ID'  
}, {  
    field : 'name',  
    width : 90,  
    title : '用户名称'  
}, {  
    field : 'mobile',  
    width : 120,  
    title : '用户手机号'  
}, {  
    field : 'status_txt',  
    width : 100,  
    title : '状态'  
}, {  
    field : 'proposal',  
    width : 300,   
    title : '建议内容'  
}  , {  
    field : 'admin_name',  
    width : 130,   
    title : '操作人'  
}, {  
    field : 'created_at',  
    width : 180,   
    title : '反馈时间'  
} , {
	field:'operate',
	title:'操作',
	width: 122,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Proposal&c=Proposal&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        
        if (row.status == '1') {
        	str += '<input type="button" onclick="location.href=\'/?m=Proposal&c=Proposal&a=handle&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="处理" >';
        	
        }
         return str;  
    }
}  
] ];

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[mobile]'] = $('#mobile').val();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[admin_name]'] = $('#admin_name').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue');
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Proposal&c=Proposal&a=list&format=list',
// 				remoteSort: false,
// 				singleSelect:true,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				pageList: [10,20,50],
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:true,
// 				queryParams:queryData
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
