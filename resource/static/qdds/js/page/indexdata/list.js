/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'id',  
    width : 60,  
    title : 'ID'  
}, {  
    field : 'title_name',  
    width : 160,  
    title : '标题名称'  
} , {  
    field : 'status_txt',  
    width : 80,   
    title : '状态'  
} , {  
    field : 'description',  
    width : 150,  
    title : '说明'  
}, {  
    field : 'type_txt',  
    width : 102,   
    title : '是否长期'  
} , {  
    field : 'starttime',  
    width : 160,   
    title : '开始时间'  
} , {  
    field : 'endtime',  
    width : 160,   
    title : '结束时间'  
} , {
	field:'operate',
	title:'操作',
	width: 230,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Indexdata&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        
        if (row.status == '1') {
        	str += '<input type="button" onclick="editStatus('+row.id+',2,\'通过\')"  class="easyui-linkbutton" data-options="selected:true" value="通过" >';
        	str += '<input type="button" onclick="editStatus('+row.id+',4,\'取消\')"  class="easyui-linkbutton" data-options="selected:true" value="取消" >';  
        }
        if (row.status == '3') {
        	str += '<input type="button" onclick="editStatus('+row.id+',5,\'删除\')"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';   
        }
        if (row.status == '5') {
        	str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Indexdata&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';
        	str += '<input type="button" onclick="editStatus('+row.id+',3,\'失效\')"  class="easyui-linkbutton" data-options="selected:true" value="失效" >';   
        }
        if (row.status == '6') {
        	str += '<input type="button" onclick="editStatus('+row.id+',3,\'失效\')"  class="easyui-linkbutton" data-options="selected:true" value="失效" >';   
        }
         return str;  
    }
}  
] ];

function editStatus(id,type,name) {
	$.messager.confirm('温馨提示', '您确定要'+name+'吗?',function(res){
		if (res == true) {
			$.ajax({
		        type: "POST",
		        async:true,  // 设置同步方式
		        url: "/?m=Marketing&c=Indexdata&a=editStatus&id="+id,
		        dateType: "json",
		        data:'status='+type,
		        success:function(data){
		  			if (data.code == '200') {
						location.href="/index.php?m=Marketing&c=Indexdata&a=list"
					} else {
						$.messager.alert('提示', data.msg);
					}
		        }
		    });	
		}
	})
}


// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[title_name]'] = $('#title_name').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[data_type]'] = $('#data_type').val();
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
// 			    url: '/index.php?m=Marketing&c=Indexdata&a=list&format=list',
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
