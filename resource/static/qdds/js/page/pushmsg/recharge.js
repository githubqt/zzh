/***
 * 优惠券js
 * @version v0.01
 * @author lqt
 * @time 2018-05-21
 */
var fields =  [ [ {
    field : 'id',  
    width : 70,  
    title : 'ID'  
}, {  
    field : 'recharge_num',  
    width : 100,  
    title : '充值条数'  
}, {  
    field : 'recharge_ament',  
    width : 100,  
    title : '充值金额'  
}, {  
    field : 'recharge_type_txt',  
    width : 180,   
    title : '充值类型'  
} , {  
    field : 'status_txt',  
    width : 180,  
    title : '状态'  
} , {
	field:'recharge_at',
	title:'充值时间',
	width: 180,
}, {
	field:'note',
	title:'备注',
	width: 280,
	align:'left', 
} , {
	field:'operate',
	title:'操作',
	width: 202,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '';//'<input type="button" onclick="location.href=\'/?m=Marketing&c=Seckill&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        
        if (row.status == '1') {
        	str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Pushmsg&a=pay&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="继续支付" >';
        }
        return str;  
    }
}  	
]];
   
// function searchInfo() {
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Marketing&c=Pushmsg&a=recharge&format=list',
// 				remoteSort: false,
// 				singleSelect:true,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				pageList: [10,20,50],
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:true,
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
