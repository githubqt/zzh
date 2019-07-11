/***
 * 商品列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields =  [ [ {
    field : 'id',  
    width : 120,  
    sortable:true,
    title : 'ID'  
}, {  
    field : 'self_code',  
    width : 200,  
    title : '商品编号'  
}, {  
    field : 'name',  
    width : 200,  
    title : '商品名称'  
}, {  
    field : 'brand_name',  
    width : 100,   
    title : '品牌'  
}, {  
    field : 'category_name',  
    width : 100,   
    title : '分类'  
} , {  
    field : 'market_price',  
    width : 100,   
    sortable:true,
    title : '公价'  
} , {  
    field : 'sale_price',  
    width : 100,   
    sortable:true,
    title : '销售价'  
} , {  
    field : 'channel_price',  
    width : 100,   
    sortable:true,
    title : '渠道价'  
} , {  
    field : 'stock',  
    width : 100,   
    sortable:true,
    title : '库存'  
} , {  
    field : 'created_at',  
    width : 130,   
    sortable:true,
    title : '添加时间' 
} , {
	field:'operate',
	title:'操作',
	width: 280,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Gift&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Gift&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';  
    	str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Gift&a=stock&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="调库存" >';  
    	str += '<input type="button" onclick="javascript:delproduct('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';  
         return str;  
    }
}  
] ];

function delproduct(id) {
	deleteInfo("/?m=Marketing&c=Gift&a=delete&id="+id,"/index.php?m=Marketing&c=Gift&a=list");
}
// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[self_code]'] = $('#code').val();
// 	var $input = $('input[name="info[brand_id][]"]');
//     if ($input.length >1){
//         var inputVal = [];
//         $input.each(function () {
//             if ($(this).val()) {
//                 inputVal.push($(this).val()) ;
//             }
//         });
//         queryData['info[brand_id][]'] = inputVal;
//     }else{
//     	queryData['info[brand_id][]'] = $input.val();
//     }
// 	queryData['info[on_status]'] = $('#on_status').val();
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
// 			    url: '/index.php?m=Marketing&c=Gift&a=list&format=list',
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
