/***
 * 退货订单js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-18
 */
var fields =  [ [ {
    field : 'id',  
    width : 80,  
    sortable:true,
    title : 'ID'  
}, {  
    field : 'return_order_no',  
    width : 150,  
    title : '订单编号'  
}, {  
    field : 'status_name',  
    width : 100,  
    title : '状态'  
}, {  
    field : 'user_name',  
    width : 102,   
    title : '姓名'  
} , {  
    field : 'mobile',  
    width : 120,   
    title : '手机'  
} , {  
    field : 'num',  
    width : 100,   
    sortable:true,
    title : '退货数量'  
} , {  
    field : 'back_money',  
    width : 100,   
    sortable:true,
    title : '退货金额'  
} , {  
    field : 'created_at',  
    width : 150,   
    title : '提交时间'  
} , {
	field:'operate',
	title:'操作',
	width: 200,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Order&c=Orderreturn&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        if (row.status == 10) { 
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Orderreturn&a=handle&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="处理" >';  
        }
        if (row.status == 30) { 
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Orderreturn&a=collectProduct&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="确认收货" >';  
        }
        if (row.status == 31) { 
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Orderreturn&a=checkProduct&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="质检" >';  
        }
        if (row.status == 32) { 
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Orderreturn&a=inStock&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="入库" >';
        }
        if (row.status == 40) { 
        	str += '<input type="button"  onclick="javascript:refund('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="退款" >';
        }
    	
         return str;  
    }
}  
] ];

function refund(id) {
    $.messager.confirm('温馨提示', '您确定退货单无误并退款给用户吗?',function(res){
        if (res == true) {
            $.ajax({
                type: "POST",
                async:true,  // 设置同步方式
                url: "/?m=Order&c=Orderreturn&a=refund&id="+id,
                dateType: "json",
                success:function(data){
                    if (data.code == '200') {
                        location.href="/index.php?m=Order&c=Orderreturn&a=list"
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
// 	queryData['info[order_no]'] = $('#order_no').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[user_name]'] = $('#user_name').val();
// 	queryData['info[user_mobile]'] = $('#user_mobile').val(),
// 	queryData['info[start_price]'] = $('#start_price').val();
// 	queryData['info[end_price]'] = $('#end_price').val();
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
// 			    url: '/index.php?m=Order&c=Orderreturn&a=list&format=list',
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
