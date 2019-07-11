/***
 * 商品列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields =  [ [ {
    field : 'id',  
    width : 60,  
    sortable:true,
    title : 'ID'  
}, {  
    field : 'self_code',  
    width : 120,  
    title : '商品编号'  
}, {  
    field : 'pname',  
    width : 200,  
    title : '商品名称'  
}, {  
    field : 'admin_name',  
    width : 80,   
    title : '操作人'  
}, {  
    field : 'stock_old',  
    width : 100,   
    sortable:true,
    title : '原始库存'  
} , {  
    field : 'stock_change',  
    width : 100,   
    sortable:true,
    title : '变动库存'  
} , {  
    field : 'stock_new',  
    width : 100,   
    sortable:true,
    title : '目前库存'  
} , {  
    field : 'type',  
    width : 100,   
    title : '操作'  
} , {  
    field : 'note',  
    width : 100,   
    title : '备注'  
} , {  
    field : 'created_at',  
    width : 130,   
    title : '操作时间' 
} 
] ];


// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[self_code]'] = $('#code').val(),
// 	queryData['info[brand_name]'] = $('#brand_name').combobox('getValues');
// 	queryData['info[note]'] = $('#remark').val();
// 	queryData['info[admin_name]'] = $('#opera_name').val();
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
// 			    url: '/index.php?m=Product&c=Stock&a=list&format=list',
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
