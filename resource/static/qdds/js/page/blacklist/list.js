/***
 * 黑名单列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields =  [ [ {
    field : 'id',  
    width : 120,  
    title : 'ID',
}, {  
    field : 'company',  
    width : 150,  
    title : '商行',
}, {  
    field : 'user_name',  
    width : 80,  
    title : '姓名'
}, {  
    field : 'mobile',  
    width : 150,   
    title : '电话'  
}, {  
    field : 'id_card',  
    width : 220,   
    title : '身份证'
} , {  
    field : 'note',  
    width : 150,   
    title : '备注'  
} ,  {  
    field : 'created_at',  
    width : 150,   
    title : '添加时间' 
} , {
	field:'operate',
	title:'操作',
	width: 220,
	align:'center',
    formatter:function(value, row, index){  
        var str ="";
        str += '<input type="button" onclick="location.href=\'/?m=Blacklist&c=Blacklist&a=detail&id='+row.id+'\'" class="easyui-linkbutton"  data-options="selected:true" value="查看" >';
        if(row.supplier_id == row.dd_supplier_id){
    	str += '<input type="button" onclick="javascript:delproduct('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';  
        }
    	return str;  
    }
}  
] ];


function delproduct(id) {
	deleteInfo("/?m=Blacklist&c=Blacklist&a=delete&id="+id,"/index.php?m=Blacklist&c=Blacklist&a=list");
}

function deleteInfo(id) {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Blacklist&c=Blacklist&a=delete&id="+id,
        dateType: "json",
        success:function(data){
  			if (data.code == '200') {
				location.href='/?m=Blacklist&c=Blacklist&a=list'
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}
// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[company]'] = $('#company').val();
// 	queryData['info[user_name]'] = $('#user_name').val(),
// 	queryData['info[mobile]'] = $('#mobile').val();
// 	queryData['info[id_card]'] = $('#id_card').val();
// 	queryData['info[note]'] = $('#note').val();
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
// 			    url: '/index.php?m=Blacklist&c=Blacklist&a=list&format=list',
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
