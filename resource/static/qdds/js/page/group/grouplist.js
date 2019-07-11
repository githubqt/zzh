/***
 * 团购列表js
 * @version v0.01
 * @author lqt
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'id',  
    width : 50,  
    title : 'ID'  
}, {  
    field : 'user_name',  
    width : 100,  
    title : '会员姓名'  
} , {  
    field : 'user_mobile',  
    width : 100,   
    title : '会员手机'  
} , {  
    field : 'status_txt',  
    width : 100,  
    title : '拼团状态'  
}, {  
    field : 'order_status_txt',  
    width : 100,   
    title : '支付状态'  
}, {  
    field : 'tuan_type_txt',  
    width : 100,    
    title : '团员类型'  
}, {  
    field : 'is_del_txt',  
    width : 100,   
    title : '是否取消'     
}  , {  
    field : 'created_at',  
    width : 140,   
    title : '参团时间'   
} , {  
    field : 'tuan_id',  
    width : 100,   
    title : '团长ID'  
} , {  
    field : 'dump_num',  
    width : 100,   
    title : '差几人'  
} , {
	field:'operate',
	title:'操作',
	width: 100,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '';
        if (row.can) {
        	str += '<input type="button" onclick="javascript:share('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="邀请拼团" >';
        }
        return str;  
    }    
}
] ];

function share(id) {
	$('#tuiguang').dialog({
	    title: '邀请拼团',
	    top:10,
	    width: 300,
	    height: 400,
	    closed: false,
	    cache: false,
	    href: '/?m=Marketing&c=Group&a=share&id='+id+'&is_menu=1',
	    modal: true
	});	
}

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[user_name]'] = $('#user_name').val();
// 	queryData['info[user_mobile]'] = $('#user_mobile').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[order_status]'] = $('#order_status').val();
// 	queryData['info[tuan_type]'] = $('#tuan_type').val();
// 	queryData['info[is_del]'] = $('#is_del').val();
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: location.href+'&format=list',
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
