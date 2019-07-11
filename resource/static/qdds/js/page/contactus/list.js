/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'id',  
    width : 50,  
    sortable:true, 
    title : 'ID'  
}, {  
    field : 'mobile',  
    width : 150,  
    title : '手机号'  
}, {  
    field : 'status_txt',  
    width : 100,  
    title : '状态'  
}, {  
    field : 'note',  
    width : 300,   
    title : '备注'  
}  , {  
    field : 'admin_name',  
    width : 170,   
    title : '操作人'  
}, {  
    field : 'created_at',  
    width : 180,   
    title : '添加时间'  
} , {
	field:'operate',
	title:'操作',
	width: 152,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Contactus&c=Contactus&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        
        if (row.status == '1') {
        	str += '<input type="button" onclick="location.href=\'/?m=Contactus&c=Contactus&a=handle&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="处理" >';
        	
        }
         return str;  
    }
}  
] ];

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[mobile]'] = $('#mobile').val();
// 	queryData['info[note]'] = $('#note').val();
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
// 			    url: '/index.php?m=Contactus&c=Contactus&a=list&format=list',
// 				remoteSort: true,
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
