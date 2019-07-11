/***
 * 商品列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields =  [ [ {
    field : 'id',  
    width : 70,  
    sortable:true,
    title : 'ID'  
}, {  
    field : 'self_code',  
    width : 200,  
    title : '商品编号'  
}, {  
    field : 'custom_code',  
    width : 130,  
    title : '自定义码'  
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
    width : 70,   
    sortable:true,
    title : '库存'  
}  , {  
    field : 'channel_status_txt',
    width : 90,   
    title : '是否上架'
} , {  
    field : 'created_at',  
    width : 130,   
    sortable:true,
    title : '添加时间' 
} , {
	field:'operate',
	title:'操作',
	width: 100,
	align:'center', 
    formatter:function(value, row, index){  
    	
        var str = '<input type="button" onclick="javascript:unstatus('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="下架" >';
        return str;  
    }
}  
] ];

function promoteProductnow(id) {

    $('#tuiguang').dialog({
        title: '推广',
        width: 600,
        height: 400,
        closed: false,
        cache: false,
        href: '/?m=Product&c=Productnow&a=promote&id='+id+'&is_menu=1',
        modal: true
    });
}

function unstatus(id) {
    $.messager.confirm('温馨提示', '您确定要下架该商品吗?',function(res){
        if (res == true) {
            $.ajax({
                type: "POST",
                async:true,  // 设置同步方式
                url: "/?m=Product&c=Productnow&a=unstatus&channel=2&id="+id,
                dateType: "json",
                success:function(data){
                    if (data.code == '200') {
                        location.href="/index.php?m=Product&c=Productnow&a=listchannel"
                    } else {
                        $.messager.alert('提示', data.msg);
                    }
                }
            });
        }
    });
}
// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[self_code]'] = $('#code').val();
// 	queryData['info[custom_code]'] = $('#custom_code').val();
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
// 	queryData['info[channel_status]'] = $('#channel_status').val();
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
// 			    url: '/index.php?m=Product&c=Productnow&a=listchannel&format=list',
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
//
// $(function(){
// 	searchInfo();
// })
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });



function preCode(id) {
	var html = '<div style="margin-left: 60px;margin-top: 40px;"><div style="margin: 10px auto 10px auto;"><a  style="font-size:16px ; color:red;">请输入需要打印的数量！</a></div><input class="easyui-numberbox"  data-options="required: true" type="text" id="num"    style="width:120px; height: 30px;"> 张<input  style="margin-left:10px" type="button"  onclick="javascript:printCode(\''+id+'\');"  class="easyui-linkbutton" data-options="selected:true" value="确定"/> </div>';
	    
	$('#all').dialog({
	    title: '打印数量选择',
	    width: 300,
	    height: 200,
	    closed: false,
	    cache: false,
	    modal: true,
	    content: html
	});
	
}



function printCode(id) {
	 
	 var num  = $("#num").val();
	 
	 if(num.length==0&&num.length<1){
		 return;
	 }
	 
	 $("#all").dialog('close');
	 
	$('#preview').dialog({
	    title: '打印预览',
	    width: 500,
	    height: 300,
	    closed: false,
	    cache: false,
	    href: '/?m=Product&c=Productnow&a=print&id='+id+'&num='+num+'&is_menu=1',
	    modal: true
	});
	
	
}



