/***
 * 角色列表js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-08
 */
var fields =  [ [ {
    field : 'id',  
    width : 120, 
    sortable:true, 
    title : 'ID'  
}, {  
    field : 'name',  
    width : 160,  
    title : '名称'  
}, {  
    field : 'admin_name',  
    width : 160,   
    title : '添加人'  
} , {  
    field : 'status',  
    width : 140,   
    title : '状态'  
} , {  
    field : 'created_at',  
    width : 160,   
    title : '添加时间' 
} , {  
    field : 'updated_at',  
    width : 160,   
    title : '更新时间' 
} , {
	field:'operate',
 
	title:'操作', 
	align:'center',
	width:$(this).width()*0.177,  
	formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/index.php?m=Auth&c=Role&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        str += '<input type="button" onclick="location.href=\'/index.php?m=Auth&c=Role&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';    
        if (row.is_bind != '1') {
        	str += '<input type="button" onclick="javascript:delbrand('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';  
        }
        return str;  
    }
}  
] ];


	
	function delbrand(id) {
		deleteInfo("/index.php?m=Auth&c=Role&a=delete&id="+id,"/index.php?m=Auth&c=Role&a=list");
	}
	
// 	function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[status]'] = $('#status').val(),
// 	queryData['info[note]'] = $('#note').val();
// 	queryData['info[id]'] = $('#id').val();
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue');
//
//
// 	$('#dg').datagrid({
// 		title:'',
// 		width:'100%',
// 		height:'auto',
// 		nowrap: true,
// 		autoRowHeight: true,
// 		striped: true,
// 	    url: '/index.php?m=Auth&c=Role&a=list&format=list',
// 		remoteSort: true,
// 		singleSelect:true,
// 		idField:'id',
// 		loadMsg:'数据加载中......',
// 		pageList: [10,20,50],
// 		columns: fields,
// 		pagination:true,
// 		rownumbers:true,
// 		queryParams:queryData,
// 		onLoadSuccess: function(data){
//           var panel = $(this).datagrid('getPanel');
//           var tr = panel.find('div.datagrid-body tr');
//           tr.each(function(){
//               var td = $(this).children('td[field="userNo"]');
//               td.children("div").css({
//                   //"text-align": "right"
//                   "height": "50px"
//               });
//           });
//        }
// 	});
//
// }
// $(function(){
// 	searchInfo();
// })
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });
