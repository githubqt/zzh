/***
 * 添加商品
 * @time 2018-05-09
 */
    
	function clearForm(){
		$('#ff').form('clear');
	}
	
	
	function editType() {
		var type = $("#data_type").val();
		if (type == '1') {
			$("#type_one").show();
			$("#type_two").hide();
			$("#type_three").hide();
		} else if (type == '2') {
			$("#type_one").hide();
			$("#type_two").show();
			$("#type_three").hide();
		} else if (type == '3') {
			$("#type_one").hide();
			$("#type_two").hide();
			$("#type_three").show();
		} 
	}
	
	
	
	function timeType(type) {
		if (type == 1) {
			$("#hide_num").hide();
		} else {
			$("#hide_num").show();
		}
	}
	
	
	
	
	function clearAttrForm(){
		$('#attr').form('clear');
		searchInfo();
	}
	
	
	
	var fields =  [ [
	 { 
		field:'operate',
		title:'选择',
		width: 40,
		align:'left', 
	    formatter:function(value, row, index){  
	        var str = '<input type="radio" style="width: 16px;height: 16px" name="status" value="'+row.id+'" data-name="'+row.name+'" data-money="'+row.market_price+'" />';
	         return str;  
	    }
	} , {  
	    field : 'id',  
	    width : 50, 
	    title : 'ID'  
	}, {  
	    field : 'name',  
	    width : 200,  
	    title : '商品名称'  
	}, {  
	    field : 'self_code',  
	    width : 100,   
	    title : '商品编码'  
	} , {  
	    field : 'brand_name',  
	    width : 100,
	    title : '品牌名称'  
	}  , {  
	    field : 'category_name',  
	    width : 180,   
	    title : '分类名称'  
	}  , {  
	    field : 'market_price',  
	    width : 80,
	    title : '公价'  
	}   , {  
	    field : 'sale_price',  
	    width : 80,
	    title : '销售价'
	}  , {
		field : 'product_type_txt',
		width : 80,
		title : '商品类型'
	}
	] ];

	function productSetadd() {
		var id = $("input[name='status']:checked").val();
		var name = $("input[name='status']:checked").data("name");
		var money = $("input[name='status']:checked").data("money");
		
		if (!id) {
			$.messager.alert('提示', '请选择后保存');
			return;
		}
		
		$("#product_id").val(id);
		$("#product_name").val(name);
		$("#shop_price").html(money);
		$("#bigding_price").html(money)
		$("#shop_name").html(name);
		$('#product').window('close');
	}

var options = {
    url: '/index.php?m=Product&c=Product&a=list&format=addOrder',
};

var default_params = {
    'info[on_status]':2
};

	// 	function searchInfo() {
	// 		var queryData = new Object();
	// 		queryData['info[name]'] = $('#name').val();
	// 		queryData['info[id]'] = $('#id').val();
	// 		queryData['info[on_status]'] = 2;
	// 		var $input = $('input[name="info[brand_id][]"]');
	// 	    if ($input.length >1){
	// 	        var inputVal = [];
	// 	        $input.each(function () {
	// 	            if ($(this).val()) {
	// 	                inputVal.push($(this).val()) ;
	// 	            }
	// 	        });
	// 	        queryData['info[brand_id][]'] = inputVal;
	// 	    }else{
	// 	    	queryData['info[brand_id][]'] = $input.val();
	// 	    }
	// 		var $input = $('input[name="info[category_id][]"]');
    //         if ($input.length >1){
    //             var inputVal = [];
    //             $input.each(function () {
    //                 if ($(this).val()) {
    //                     inputVal.push($(this).val()) ;
    //                 }
    //             });
    //             queryData['info[category_id][]'] = inputVal;
    //         }else{
    //         	queryData['info[category_id][]'] = $input.val();
    //         }
	//
	// 		queryData['info[self_code]'] = $('#self_code').val();
	// 		//queryData['info[not_in]'] = '1';
	//
	//
	// 		$('#dg').datagrid({
	// 			title:'',
	// 			width:'100%',
	// 			height:'auto',
	// 			nowrap: true,
	// 			autoRowHeight: true,
	// 			striped: true,
	// 		    url: '/index.php?m=Product&c=Product&a=list&format=addOrder',
	// 			remoteSort: false,
	// 			singleSelect:true,
	// 			idField:'id',
	// 			loadMsg:'数据加载中......',
	// 			pageList: [10,20,50],
	// 			columns: fields,
	// 			pagination:true,
	// 			rownumbers:true,
	// 			queryParams:queryData,
	// 			onLoadSuccess: function(data){
	// 	          var panel = $(this).datagrid('getPanel');
	// 	          var tr = panel.find('div.datagrid-body tr');
	// 	          tr.each(function(){
	// 	              var td = $(this).children('td[field="userNo"]');
	// 	              td.children("div").css({
	// 	                  "height": "50px"
	// 	              });
	// 	          });
	// 	       }
	// 	});
	//
	// }
	// $(function(){
	// 	searchInfo();
	// })
	//
	//  $(".more").click(function(){
	//     $(this).closest(".conditions").siblings().toggleClass("hide");
	// });
	
	

	function is_show() {
		if ($("#bottom_price").val() > 0) {
			$("#hideid1").show();
			$("#hideid2").show();
			$("#hideid3").show();
		} else {
			$("#hideid1").hide();
			$("#hideid2").hide();
			$("#hideid3").hide();
		}
	}
	
	function postAdd(id){
		
		var queryData = new Object();
		queryData['info[id]']				= $("#product_id").val();
		queryData['info[shop_name]'] 		= $("#product_name").val();
		queryData['info[shop_price]'] 		= $("#bigding_price").html();
		queryData['info[start_price]'] 		=  $('#start_price').val(),
		queryData['info[bid_lncrement]'] 	= $('#bid_lncrement').val();
		queryData['info[starttime]'] 		= $('#starttime').datetimebox("getValue");
		queryData['info[endtime]'] 			= $('#endtime').datetimebox("getValue");
		queryData['info[order_sale_price]'] = $('#order_sale_price').val();
		queryData['info[bottom_price]'] 	= $('#bottom_price').val();
		
		$.ajax({
	        type: "POST",
	        async:true,  // 设置同步方式
	        url: "/index.php?m=Marketing&c=Bidding&a=edit&format=edit&id="+id,
	        dateType: "json",
	        data:queryData,
	        success:function(data){
	  			if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Bidding&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
	        }
	    });	
		
	}
	
	