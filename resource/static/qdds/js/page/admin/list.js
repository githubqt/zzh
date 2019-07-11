/***
 * 角色列表js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-08
 */
var fields =  [ [ {
    field : 'id',  
    width : 80,  
    sortable:true, 
    title : 'ID'  
}, {  
    field : 'name',  
    width : 120,  
    title : '账号'  
}, {  
    field : 'fullname',  
    width : 110,   
    title : '姓名'  
} , {  
    field : 'mobile',  
    width : 110,   
    title : '手机号'  
} , {  
    field : 'email',  
    width : 135,   
    title : '邮箱'  
}  , {  
    field : 'role_name',  
    width : 120,   
    title : '岗位'  
} , {  
    field : 'status',  
    width : 100,   
    title : '状态'  
}, {  
    field : 'created_at',  
    width : 150,   
    title : '添加时间' 
} , {
	field:'operate',
 
	title:'操作', 
	align:'center',
	width:$(this).width()*0.225,
	formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/index.php?m=Auth&c=Admin&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        if (row.role_id != '0') {
	        str += '<input type="button" onclick="location.href=\'/index.php?m=Auth&c=Admin&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';
            str += '<input type="button" onclick="location.href=\'/index.php?m=Auth&c=Admin&a=editPassword&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="修改密码" >';
            str += '<input type="button" onclick="javascript:delbrand('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';
		}
        return str;  
    }
}  
] ];


	
	function delbrand(id) {
		deleteInfo("/index.php?m=Auth&c=Admin&a=delete&id="+id,"/index.php?m=Auth&c=Admin&a=list");
	}
	
// 	function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[fullname]'] = $('#fullname').val();
// 	queryData['info[status]'] = $('#status').val(),
// 	queryData['info[mobile]'] = $('#mobile').val();
// 	queryData['info[id]'] = $('#id').val();
// 	queryData['info[role_id]'] = $('#role_id').val();
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
// 	    url: '/index.php?m=Auth&c=Admin&a=list&format=list',
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
