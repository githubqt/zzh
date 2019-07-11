/***
 * 优惠券js
 * @version v0.01
 * @author lqt
 * @time 2018-05-21
 */
var fields =  [ [ {
    field : 'id',  
    width : 60,  
    title : 'ID'  
}, {  
    field : 'user_name',  
    width : 150,  
    title : '客户名称'  
}, {  
    field : 'c_name',  
    width : 150,  
    title : '优惠券名称'  
}, {  
    field : 'content',  
    width : 150,   
    title : '优惠内容'  
} , {  
    field : 'give_at',  
    width : 100,  
    title : '领券时间'  
} , {  
    field : 'use_at',  
    width : 100,   
    title : '用券时间' 
} , {  
    field : 'order_price',  
    width : 100,   
    title : '订单金额'  
} , {  
    field : 'discount_price',  
    width : 100,   
    title : '抵扣金额'  
} , {  
    field : 'status_txt',  
    width : 100,   
    title : '用券状态'  
// } , {
	// field:'operate',
	// title:'操作',
	// width: 250,
	// align:'left', 
    // formatter:function(value, row, index){  
        // var str = '<input type="button" onclick="" class="easyui-linkbutton" data-options="selected:true" value="发短信" >';
        // str += '<input type="button" onclick=""  class="easyui-linkbutton" data-options="selected:true" value="发微信" >';              
        // return str;  
    // }
}  
] ];

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[user_name]'] = $('#user_name').val();
// 	queryData['info[status]'] = $('#status').combobox('getValue');
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
// 			    url: '/index.php?m=Marketing&c=Coupan&a=detail&format=list&id='+id,
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
