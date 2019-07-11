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
    title : '联系方式'  
}, {  
    field : 'name',  
    width : 150,  
    title : '汽车品牌'  
}, {  
    field : 'housing_year',  
    width : 150,  
    sortable:true,
    title : '行驶里程'  
}, {  
    field : 'purchase_price',  
    width : 150,  
    sortable:true,
    title : '购买价格'  
}, {  
    field : 'loan_price',  
    width : 150,  
    sortable:true,
    title : '借款金额'  
}, {  
    field : 'status_txt',  
    width : 100,  
    title : '状态'  
}, {  
    field : 'remark',  
    width : 300,   
    title : '备注'  
}  , {  
    field : 'on_card_time',  
    width : 170,   
    sortable:true,
    title : '首次上牌时间'  
}, {  
    field : 'created_at',  
    width : 180,   
    sortable:true,
    title : '添加时间'  
} , {
	field:'operate',
	title:'操作',
	width: 152,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Online&c=Carmortgage&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        
        if (row.status == '1') {
        	str += '<input type="button" onclick="location.href=\'/?m=Online&c=Carmortgage&a=handle&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="处理" >';
        	
        }
         return str;  
    }
}  
] ];

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[mobile]'] = $('#mobile').val();
// 	queryData['info[housing_area]'] = $('#housing_area').val();
// 	queryData['info[housing_year]'] = $('#housing_year').val();
// 	queryData['info[start_purchase_price]'] = $('#start_purchase_price').val();
// 	queryData['info[end_purchase_price]'] = $('#end_purchase_price').val();
// 	queryData['info[province_id]'] = $('#province').val();
// 	queryData['info[city_id]'] = $('#city').val();
// 	queryData['info[area_id]'] = $('#area').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[start_loan_price]'] = $('#start_loan_price').val();
// 	queryData['info[end_loan_price]'] = $('#end_loan_price').val();
// 	queryData['info[on_card_time_start_time]'] = $('#on_card_time_start_time').datebox('getValue');
// 	queryData['info[on_card_time_end_time]'] = $('#on_card_time_end_time').datebox('getValue');
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
// 			    url: '/index.php?m=Online&c=Carmortgage&a=list&format=list',
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
// 	//area_child(0,1);
// 	searchInfo();
// })
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });
