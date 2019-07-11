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
					location.href="/index.php?m=User&c=Scorerule&a=list"
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
	
	

	
	function is_show_product_type(type) {
		if (type == 1) {
			$("#product_type_show").hide();
			$("#talk").hide();
			$("#selectproduct").hide();
			$("#overp").hide();
		} else {
			if ($('input:radio[name="info[product_type]"]:checked').val() == '1') {
				$("#selectproduct").show();
			}
			$("#product_type_show").show();
			$("#talk").show();
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
		checkbox : true
	}, {  
	    field : 'name',  
	    width : 180,  
	    title : '商品名称'  
	}, {  
	    field : 'self_code',  
	    width : 110,   
	    title : '商品编码'  
	}, {  
	    field : 'brand_name',  
	    width : 103,   
	    title : '品牌名称'  
	}, {  
	    field : 'category_name',  
	    width : 170,   
	    title : '分类名称'  
	}, {  
	    field : 'market_price',  
	    width : 75,   
	    title : '公价'  
	}, {  
	    field : 'sale_price',  
	    width : 75,   
	    title : '销售价'  
	} , {  
	    field : 'is_score_rule',  
	    width : 119,   
	    title : '是否参与其他规则'  
	} 
	] ];

	var options = {
        url: '/index.php?m=Product&c=Product&a=list&format=list',
        singleSelect:false,
	};

	//设置默认必带参数
	var default_params = {
        'info[on_status]':2,
		'info[is_score_rule]':'1'
	};

	function productSetadd() {
		var rows = $('#dg').datagrid('getSelections');
		
		if (rows.length == 0) {
			$.messager.alert('提示', '请选择后保存');
			return;
		}
		$("#overp").show();
		
		var Ahtml = '';
		$.each(rows,function(i, val){
			var temp_obj=$("tr[data-id='" + val.id + "']");
			if (temp_obj.length > 0 ) {
				
			} else {
				Ahtml += '<tr class="attribute_class" data-id="'+val.id+'" id="values_'+val.id+'">';
				Ahtml += 	'<th class="w9"></th>';
				Ahtml += 	'<th class="w9">';
				Ahtml += 		val.name+'<input type="hidden" name="product_id[]" value="'+val.id+'">';
				Ahtml += 	'</th>';
				Ahtml += 	'<th class="w9">';
				Ahtml += 		val.stock;
				Ahtml += 	'</th>';
				Ahtml += 	'<th class="w9">';
				Ahtml += 		val.is_score_rule;
				Ahtml += 	'</th>';
				Ahtml += 	'<th class="w9">';
				Ahtml += 		'<a href="javascript:void(0);" onclick=\'$("#values_'+val.id+ '").remove();setSort();\'>删除</a>';
				Ahtml += 	'</th>';
				Ahtml += '</tr>';
			}
		});
		
		$("#erpAttributeList").append(Ahtml);
		$('#product').window('close');
		setSort();
	}
	
	function setSort(){
	    var len = $('#erpAttributeList tr').length-1;
	    console.log(len);
	    for(var i=1;i<=len;i++){
	        $('#erpAttributeList tr:eq('+i+') th:first').text(i);
	    }
	}
		
	// 	function searchInfo() {
	// 		var queryData = new Object();
	// 		queryData['info[name]'] = $('#name').val();
	// 		queryData['info[id]'] = $('#id').val(),
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
	// 		queryData['info[self_code]'] = $('#self_code').val();
	// 		queryData['info[is_score_rule]'] = '1';
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
	// 			singleSelect:false,
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
	
	function is_show_product(type) {
		if (type == 1) {
			$("#selectproduct").hide();
			$("#overp").hide();
		} else {
			$("#selectproduct").show();
		}
	}
	
	
	
	