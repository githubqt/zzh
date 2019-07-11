/***
 * 多人拼团js
 * @version v0.01
 * @author lqt
 * @time 2018-07-23
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
    field : 'starttime',  
    width : 150,   
    title : '开始时间'  
} , {  
    field : 'endtime',  
    width : 150,  
    title : '结束时间'  
}, {  
    field : 'number',  
    width : 100,   
    sortable:true,
    title : '参团人数'     
}  , {  
    field : 'status_txt',  
    width : 70,   
    title : '状态'   
} , {  
    field : 'created_at',  
    width : 150,   
    title : '创建时间'  
} , {
	field:'operate',
	title:'操作',
	width: 300,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Group&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        
        if (row.status == '1' || row.status == '5') {
        	str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Group&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';
        }
        
        if (row.status == '1') {
        	str += '<input type="button" onclick="editStatus('+row.id+',2,\'通过\')"  class="easyui-linkbutton" data-options="selected:true" value="通过" >';
        	str += '<input type="button" onclick="editStatus('+row.id+',4,\'取消\')"  class="easyui-linkbutton" data-options="selected:true" value="取消" >';  
        } 
        if (row.status == '3') {
        	str += '<input type="button" onclick="editStatus('+row.id+',5,\'删除\')"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';   
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
		        url: "/?m=Marketing&c=Group&a=editStatus&id="+id,
		        dateType: "json",
		        data:'status='+type,
		        success:function(data){
		  			if (data.code == '200') {
						location.href="/index.php?m=Marketing&c=Group&a=list"
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
	    title: '推广多人拼团活动',
	    width: 600,
	    height: 400,
	    top: 10,
	    closed: false,
	    cache: false,
	    href: '/?m=Marketing&c=Group&a=promote&id='+id+'&is_menu=1',
	    modal: true
	});	
}

var options = {
    view: detailview,
    detailFormatter:function(index,row){
        return '<div style="padding:2px;position:relative;"><table class="ddv"></table></div>';
    },
    onExpandRow: function(index,row){
        var ddv = $(this).datagrid('getRowDetail',index).find('table.ddv');
        ddv.datagrid({
            url:'/index.php?m=Marketing&c=Group&a=productlist&format=list&id='+row.id,
            fitColumns:true,
            singleSelect:true,
            rownumbers:true,
            loadMsg:'加载中',
            height:'auto',
            columns:[[
                {field:'id',title:'活动ID',width:100},
                {field:'product_id',title:'商品ID',width:100},
                {field:'product_name',title:'商品名称',width:200,align:'right'},
                {field:'sale_price',title:'原价',width:100,align:'right'},
                {field:'group_price',title:'拼团价',width:100,align:'right'},
                {field:'order_sale_price',title:'订单金额',width:100,align:'right'},
                {field:'order_num',title:'订单数',width:100,align:'right'},
                {field:'order_people_num',title:'人数',width:100,align:'right'},
                {field:'oredr_product_num',title:'商品数',width:100,align:'right'},
                {
                    field:'operate',
                    title:'操作',
                    width: 180,
                    align:'left',
                    formatter:function(value1, row1, index1){
                        var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Group&a=grouplist&id='+row1.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="拼团详情" >';
                        if (row.status == '6' || row.status == '5' || row.status == '2') {
                            str += '<input type="button" onclick="javascript:promoteSeckill('+row1.id+');"  class="easyui-linkbutton" data-options="selected:true" value="推广" >';
                        }
                        return str;
                    }
                }
            ]],
            onResize:function(){
                $('#dg').datagrid('fixDetailRowHeight',index);
            },
            onLoadSuccess:function(){
                setTimeout(function(){
                    $('#dg').datagrid('fixDetailRowHeight',index);
                },0);
            }
        });
        $('#dg').datagrid('fixDetailRowHeight',index);
    }
};

//
// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#title_name').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue');
// 	queryData['info[note]'] = $('#note').val();
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Marketing&c=Group&a=list&format=list',
// 				singleSelect:true,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				pageList: [10,20,50],
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:true,
// 				queryParams:queryData,
//
// 				view: detailview,
//                 detailFormatter:function(index,row){
//                     return '<div style="padding:2px;position:relative;"><table class="ddv"></table></div>';
//                 },
//                 onExpandRow: function(index,row){
//                     var ddv = $(this).datagrid('getRowDetail',index).find('table.ddv');
//                     ddv.datagrid({
//                         url:'/index.php?m=Marketing&c=Group&a=productlist&format=list&id='+row.id,
//                         fitColumns:true,
//                         singleSelect:true,
//                         rownumbers:true,
//                         loadMsg:'加载中',
//                         height:'auto',
//                         columns:[[
//                             {field:'id',title:'活动ID',width:100},
//                             {field:'product_id',title:'商品ID',width:100},
//                             {field:'product_name',title:'商品名称',width:200,align:'right'},
//                             {field:'sale_price',title:'原价',width:100,align:'right'},
//                             {field:'group_price',title:'拼团价',width:100,align:'right'},
//                             {field:'order_sale_price',title:'订单金额',width:100,align:'right'},
//                             {field:'order_num',title:'订单数',width:100,align:'right'},
//                             {field:'order_people_num',title:'人数',width:100,align:'right'},
//                             {field:'oredr_product_num',title:'商品数',width:100,align:'right'},
// 							{
// 								field:'operate',
// 								title:'操作',
// 								width: 180,
// 								align:'left',
// 							    formatter:function(value1, row1, index1){
// 							    	var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Group&a=grouplist&id='+row1.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="拼团详情" >';
// 							    	if (row.status == '6' || row.status == '5' || row.status == '2') {
// 							        	str += '<input type="button" onclick="javascript:promoteSeckill('+row1.id+');"  class="easyui-linkbutton" data-options="selected:true" value="推广" >';
// 							        }
// 							        return str;
// 							    }
// 							}
//                         ]],
//                         onResize:function(){
//                             $('#dg').datagrid('fixDetailRowHeight',index);
//                         },
//                         onLoadSuccess:function(){
//                             setTimeout(function(){
//                                 $('#dg').datagrid('fixDetailRowHeight',index);
//                             },0);
//                         }
//                     });
//                     $('#dg').datagrid('fixDetailRowHeight',index);
//                 }
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
