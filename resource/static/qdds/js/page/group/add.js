/***
 * 多人拼团
 * @version v0.01
 * @author lqt
 * @time 2018-07-23
 */

    $(function(){
    	
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Group&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    });
	
    //商品弹框	
	var fields =  [ [
	{ 
		field:'operate',
		title:'选择',
		width: 60,
		align:'left', 
		checkbox:true,
	},			
	{  
	    field : 'id',  
	    width : 60, 
	    sortable:true,
	    title : 'ID'  
	}, {  
	    field : 'name',  
	    width : 240,  
	    title : '商品名称'  
	}, {  
	    field : 'self_code',  
	    width : 133,   
	    title : '商品编码'  
	} , {  
	    field : 'brand_name',  
	    width : 133,   
	    title : '品牌名称'  
	}  , {  
	    field : 'category_name',  
	    width : 213,   
	    title : '分类名称' 
	}  , {  
	    field : 'market_price',  
	    width : 83,   
	    sortable:true,
	    title : '公价'  
	}   , {  
	    field : 'sale_price',  
	    width : 83,   
	    sortable:true,
	    title : '销售价'
	} , {  
	    field : 'stock',  
	    sortable:true,
	    width : 100,   
	    title : '库存'
	}  , {
		field : 'product_type_txt',
		width : 80,
		title : '商品类型'
	}
	] ];

var options = {
    url: '/index.php?m=Product&c=Product&a=list&format=addOrder',
    singleSelect:false,
    onCheck:function(index, row){
        selectProduct();
    },
    onUncheck:function(index, row){
        selectProduct();
    },
    onCheckAll:function(rows){
        selectProduct();
    },
    onUncheckAll:function(rows){
        selectProduct();
    }
};

var default_params = {
    'info[on_status]':2
};
	//
	// function searchInfo() {
	// 	var queryData = new Object();
	// 	queryData['info[name]'] = $('#product_name').val();
	// 	queryData['info[id]'] = $('#id').val(),
	// 	queryData['info[on_status]'] = 2;
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
	// 	var $input = $('input[name="info[category_id][]"]');
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
	// 	queryData['info[self_code]'] = $('#self_code').val();
	// 	queryData['info[not_in_pintuan]'] = '1';
	//
	// 	$('#dg').datagrid({
	// 		title:'',
	// 		width:'100%',
	// 		height:'auto',
	// 		nowrap: true,
	// 		autoRowHeight: true,
	// 		striped: true,
	// 	    url: '/index.php?m=Product&c=Product&a=list&format=addOrder',
	// 		remoteSort: true,
	// 		singleSelect:false,
	// 		idField:'id',
	// 		loadMsg:'数据加载中......',
	// 		pageList: [10,20,50],
	// 		columns: fields,
	// 		pagination:true,
	// 		rownumbers:true,
	// 		queryParams:queryData,
	// 		onLoadSuccess: function(data){
	//            var panel = $(this).datagrid('getPanel');
	//            var tr = panel.find('div.datagrid-body tr');
	//            tr.each(function(){
	//                var td = $(this).children('td[field="userNo"]');
	//                td.children("div").css({
	//                    //"text-align": "right"
	//                    "height": "50px"
	//                });
	//            });
	//        	},
	//        	onCheck:function(index, row){
	// 			selectProduct();
    //        	},
	//        	onUncheck:function(index, row){
	// 			selectProduct();
    //        	},
	//        	onCheckAll:function(rows){
	// 			selectProduct();
    //        	},
	//        	onUncheckAll:function(rows){
	// 			selectProduct();
    //        	}
	// 	});
	//
	// }
	//
	// function clearAttrForm(){
	// 	$('#attr').form('clear');
	// 	searchInfo();
	// }
	//
	// $(".more").click(function(){
	//     $(this).closest(".conditions").siblings().toggleClass("hide");
	// });
	
	//选择商品
	function selectProduct() {
		var rows = $('#dg').datagrid('getChecked');	
			
		var html = '';
		$.each(rows,function(i,item){
			var group_price = $("input[name='product["+item.id+"]']").val();
			if (!group_price) {
				group_price = item.sale_price;
			} 
			
			html +=
			'<tr id="addProduct_'+item.id+'">'+
				'<td class="kv-content">'+item.id+'</td>'+
				'<td class="kv-content">'+item.name+'</td>'+				    				
				'<td class="kv-content">'+item.sale_price+'</td>'+				    				
				'<td class="kv-content"><input value="'+group_price+'" onkeyup="this.value=this.value.replace(/[^0-9.]/gi,\'\');this.value=this.value >= '+group_price+'?'+group_price+':this.value;" name="product['+item.id+']"></td>'+				    				
				'<td class="kv-content"><input type="button" class="easyui-linkbutton" onclick="remove_product('+item.id+');" data-options="selected:false" value="删除" ></td>'+				    				
			'</tr>';	
		});	
		$('#product_list').html(html);		
	}	
		
	function remove_product(id){
		$('#addProduct_'+id).remove();
	}		
