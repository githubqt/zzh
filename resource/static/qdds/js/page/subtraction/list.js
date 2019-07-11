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
    field : 'name',  
    width : 150,  
    title : '活动名称'  
} , {  
    field : 'product_name',  
    width : 150,   
    title : '商品名称'  
} , {  
    field : 'status_txt',  
    width : 70,  
    title : '活动状态'  
}, {  
    field : 'time',  
    width : 150,   
    title : '有效时间'  
}  , {  
    field : 'seckill_price',  
    width : 90,   
    sortable:true,
    title : '秒杀价格'  
}, {  
    field : 'order_sale_price',  
    width : 100,   
    sortable:true,
    title : '订单实付金额'  
} , {  
    field : 'order_num',  
    width : 70,   
    sortable:true,
    title : '付款订单数'  
} , {  
    field : 'order_people_num',  
    width : 70,   
    sortable:true,
    title : '付款人数'  
} , {
	field:'operate',
	title:'操作',
	width: 230,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Seckill&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        
        if (row.status == '1') {
        	str += '<input type="button" onclick="editStatus('+row.id+',2,\'通过\')"  class="easyui-linkbutton" data-options="selected:true" value="通过" >';
        	str += '<input type="button" onclick="editStatus('+row.id+',4,\'取消\')"  class="easyui-linkbutton" data-options="selected:true" value="取消" >';  
        }
        if (row.status == '3') {
        	str += '<input type="button" onclick="editStatus('+row.id+',5,\'删除\')"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';   
        }
        if (row.status == '5') {
        	//str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Seckill&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';
        }
        if (row.status == '6' || row.status == '5' || row.status == '2') {
        	str += '<input type="button" onclick="javascript:promoteSeckill('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="推广" >';   
        }
        if (row.status == '6' || row.status == '5') {
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
		        url: "/?m=Marketing&c=Seckill&a=editStatus&id="+id,
		        dateType: "json",
		        data:'status='+type,
		        success:function(data){
		  			if (data.code == '200') {
						location.href="/index.php?m=Marketing&c=Seckill&a=list"
					} else {
						$.messager.alert('提示', data.msg);
					}
		        }
		    });	
		}
	});
}



//推广
function promoteSeckill(id) {
	$('#tuiguang').dialog({
	    title: '推广限购秒杀',
	    width: 600,
	    height: 400,
	    closed: false,
	    cache: false,
	    href: '/?m=Marketing&c=Seckill&a=promote&id='+id+'&is_menu=1',
	    modal: true
	});	
}

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#title_name').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[product_name]'] = $('#product_name').val();
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
// 			    url: '/index.php?m=Marketing&c=Seckill&a=list&format=list',
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
