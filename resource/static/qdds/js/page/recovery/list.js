/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'id',
    width : 50,
    title : '编号'
}, {
    field : 'product_name',  
    width : 150,  
    title : '商品名称'  
}, {  
    field : 'category_name',  
    width : 150,  
    title : '分类'  
} , {  
    field : 'brand_name',  
    width : 150,   
    title : '品牌'  
} , {  
    field : 'offer_price',  
    width : 70,  
    title : '出价'  
}, {  
    field : 'sale_price',  
    width : 150,   
    title : '销售价'  
}  , {  
    field : 'status_txt',  
    width : 100,   
    title : '状态'  
}, {  
    field : 'created_at',  
    width : 150,   
    title : '提交时间'  
}, {  
    field : 'over_last_time',  
    width : 150,        
    title : '结束时间'  
}, {  
    field : 'last_day',  
    width : 80,        
    sortable:true,
    title : '有效时间'  
} , {
	field:'operate',
	title:'操作',
	width: 320,
	align:'left', 
    formatter:function(value, row, index){  
    	 var str = '<input type="button" onclick="location.href=\'/?m=Recovery&c=Recovery&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
         
        if (row.recovery_status == '10' || row.recovery_status == '40') {
        	str += '<input type="button" onclick="$(\'#examine\').window(\'open\');$(\'#examine_id\').val('+row.id+')"  class="easyui-linkbutton" data-options="selected:true" value="取消" >';   
        }
        //if (row.is_completion == '1' && (row.recovery_status == '20' || row.recovery_status == '30')) {
        if (row.is_completion == '1' &&  row.recovery_status == '30') {
        	str += '<input type="button" onclick="editStatus('+row.is_evaluation+','+row.id+',\''+row.last_time+'\')" class="easyui-linkbutton" data-options="selected:true" value="补全商品信息" >';
        }
        if (row.is_completion == '2' && row.recovery_status == '30' && row.is_purchase == '1') {
            str += '<input type="button" onclick="addPurchase('+row.id+')" class="easyui-linkbutton" data-options="selected:true" value="兜底售出" >';
        }
        if (row.is_completion == '2' && row.recovery_status == '30' && row.is_onstatus == '1') {
        	str += '<input type="button"  onclick="location.href=\'/?m=Product&c=Product&a=onstatus&channel=1&origin=recovery&id=' + row.product_id + '\'" class="easyui-linkbutton" data-options="selected:true" value="上架售卖" >';
        }
         return str;  
    }
}  
] ];

function addPurchase(id) {
    $.messager.confirm('温馨提示', '您确定要兜底售出吗?',function(res){
        if (res == true) {
            $.ajax({
                type:"POST",
                url:"/index.php?m=Recovery&c=Recovery&a=addpurchase&id="+id,
                datatype: "json",
                success:function(data){
                    $.messager.alert('提示', data.msg);
                    if (data.code == '200') {
                        searchInfo();
                    }
                }
            });
        }
    });
}

function editStatus(is_evaluation,id,over_time) {
	if (is_evaluation == '1' && over_time <= '0') {
		location.href='/?m=Recovery&c=Recovery&a=addproduct&id='+id;
	} else {
		$.messager.alert('提示', '系统正在清算中，请在'+over_time+'分钟后再试');
	}
}

//
// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[supplier_id]'] = $('#supplier_id').val();
// 	queryData['info[supplier_name]'] = $('#supplier_name').val();
// 	queryData['info[recovery_status]'] = $('#recovery_status').val();
// 	queryData['info[product_name]'] = $('#product_name').val();
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue');
// 	queryData['info[recovery_start_day]'] = $('#recovery_start_day').val();
// 	queryData['info[recovery_end_day]'] = $('#recovery_end_day').val();
//
// 	var $input = $('input[name="info[brand_id][]"]');
//     if ($input.length >1){
//         var inputVal = [];
//         $input.each(function () {
//             if ($(this).val() && !isNaN($(this).val())) {
//                 inputVal.push($(this).val()) ;
//             }
//         });
//         queryData['info[brand_id][]'] = inputVal;
//     }else{
//     	queryData['info[brand_id][]'] = $input.val();
//     }
//
//     var $input = $('input[name="info[category_id][]"]');
//     if ($input.length >1){
//         var inputVal = [];
//         $input.each(function () {
//             if ($(this).val()) {
//                 inputVal.push($(this).val()) ;
//             }
//         });
//         queryData['info[category_id][]'] = inputVal;
//     }else{
//     	queryData['info[category_id][]'] = $input.val();
//     }
//
// 	$('#dg').datagrid({
// 		title:'',
// 		width:'100%',
// 		height:'auto',
// 		nowrap: true,
// 		autoRowHeight: true,
// 		striped: true,
// 	    url: '/index.php?m=Recovery&c=Recovery&a=list&format=list',
// 		remoteSort: true,
// 		singleSelect:true,
// 		idField:'id',
// 		loadMsg:'数据加载中......',
// 		pageList: [10,20,50],
// 		columns: fields,
// 		pagination:true,
// 		rownumbers:true,
// 		queryParams:queryData
// 	});
//
// }
// $(function(){
// 	searchInfo();
// })
setInterval("$('.query').click()",'60000');


//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });



$(function(){
	
	$('#ff').form({
		success:function(data){
			var data = JSON.parse(data);
			if (data.code == '200') {
				location.href="/index.php?m=Recovery&c=Recovery&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
		}
	});
	
	$('#ff2').form({
		success:function(data){
			var data = JSON.parse(data);
			if (data.code == '200') {
				location.href="/index.php?m=Recovery&c=Recovery&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
		}
	});
});













