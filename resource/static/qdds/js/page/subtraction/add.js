/***
 * 添加秒杀
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-21
 */


    $(function(){
    	
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Seckill&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
		
    });
    
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
	
	
	
	
	
	// function clearAttrForm(){
	// 	$('#attr').form('clear');
	// 	searchInfo();
	// }
	
	
	
	var fields =  [ [
	 { 
		field:'operate',
		title:'选择',
		width: 40,
		align:'left', 
	    formatter:function(value, row, index){  
	        var str = '<input type="radio" style="width: 16px;height: 16px" name="status" value="'+row.id+'" data-name="'+row.name+'" />';
	        
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
	    width : 103,   
	    title : '品牌名称'  
	}  , {  
	    field : 'category_name',  
	    width : 180,   
	    title : '分类名称'  
	}  , {  
	    field : 'market_price',  
	    width : 83,   
	    title : '公价'  
	}   , {  
	    field : 'sale_price',  
	    width : 83,   
	    title : '销售价'  
	} 
	] ];

	function productSetadd() {
		var id = $("input[name='status']:checked").val();
		var name = $("input[name='status']:checked").data("name");
		
		if (!id) {
			$.messager.alert('提示', '请选择后保存');
			return;
		}
		
		$("#show").show();
		$("#product_id").val(id);
		$("#product_name").val(name);
		$("#pn").html(name);
		$('#product').window('close');
	}

var options = {
    url: '/index.php?m=Product&c=Product&a=list&format=addOrder',
    singleSelect:false,
};

//设置默认必带参数
var default_params = {
    'info[on_status]':2,
    'info[not_in]':'1'
};
		
	// 	function searchInfo() {
	// 		var queryData = new Object();
	// 		queryData['info[name]'] = $('#name').val();
	// 		queryData['info[id]'] = $('#id').val(),
	// 		queryData['info[on_status]'] = 2,
	// 		queryData['info[brand_name]'] = $('#brand_name').val();
	// 		queryData['info[category_name]'] = $('#category_name').val();
	// 		queryData['info[self_code]'] = $('#self_code').val();
	// 		queryData['info[not_in]'] = '1';
	//
	//
	// 		$('#dg').datagrid({
	// 			title:'',
	// 			width:'100%',
	// 			height:'auto',
	// 			nowrap: true,
	// 			autoRowHeight: true,
	// 			striped: true,
	// 		    url: '/index.php?m=Product&c=Product&a=list&format=list',
	// 			remoteSort: false,
	// 			singleSelect:true,
	// 			idField:'id',
	// 			loadMsg:'数据加载中......',
	// 			pageList: [10,20,50],
	// 			columns: fields,
	// 		    //singleSelect:false,
	// 			pagination:true,
	// 			rownumbers:true,
	// 			queryParams:queryData,
	// 			onLoadSuccess: function(data){
	// 	          var panel = $(this).datagrid('getPanel');
	// 	          var tr = panel.find('div.datagrid-body tr');
	// 	          tr.each(function(){
	// 	              var td = $(this).children('td[field="userNo"]');
	// 	              td.children("div").css({
	// 	                  //"text-align": "right"
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
	
	
	
	
	
	