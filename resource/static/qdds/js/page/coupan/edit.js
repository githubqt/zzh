
    $(function(){ 
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Coupan&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}	
	
    $('#start_time').datetimebox({
	    stopFirstChangeEvent: false,
	    onChange: function() {
	        var options = $(this).datetimebox('options');
	        if(options.stopFirstChangeEvent) {
	            options.stopFirstChangeEvent = false;
	            return;
	        }
	        note();
	    }
	});  
	  	  		
    $('#end_time').datetimebox({
	    stopFirstChangeEvent: false,
	    onChange: function() {
	        var options = $(this).datetimebox('options');
	        if(options.stopFirstChangeEvent) {
	            options.stopFirstChangeEvent = false;
	            return;
	        }
	        note();
	    }
	});    	  		
	
	function note(){
		var noteHtml = '';
		//名称
		var name = $('#name').val();
		name = name?name:'';
		noteHtml += name + '\r';
		//使用时间
		var time_type = $("input[name='info[time_type]']:checked").val(); 
		time_type = time_type?time_type:1;
		if (time_type == 1) {//时间段
			var start_time = $("#start_time").datebox('getValue');
			start_time = start_time?start_time:'';
			var end_time = $("#end_time").datebox('getValue');
			end_time = end_time?end_time:'';	
			noteHtml += '使用时间：'+ start_time + '至' + end_time + '\r';		
		} else if (time_type == 2) {//不限
			noteHtml += '使用时间：不限\r';
		}
		//优惠内容
		//适用商品
		var content = '';
		var use_type = $('#use_type').val();
		use_type = use_type?use_type:1;
		if (use_type == 1) {//全部商品
			content += '全部商品 ';
		} else if (use_type == 2) {//部分商品
			content += '部分商品 ';
		}
		//使用门槛
		var sill_type = $("input[name='info[sill_type]']:checked").val(); 
		sill_type = sill_type?sill_type:1;
		if (sill_type == 1) {//无使用门槛
			content += '无使用门槛 ';
		} else if (sill_type == 2) {//满N元可用
			var sill_price = $("#sill_price").numberbox('getValue');
			sill_price = sill_price?sill_price:0;
			sill_price = Number(sill_price).toFixed(2);
			content += '满'+sill_price+'元 ';
		}	
		//优惠内容
		var pre_type = $("input[name='info[pre_type]']:checked").val(); 
		pre_type = pre_type?pre_type:1;
		if (pre_type == 1) {//减免
			var pre_value_1 = $("#pre_value_1").numberbox('getValue');
			pre_value_1 = pre_value_1?pre_value_1:0;
			pre_value_1 = Number(pre_value_1).toFixed(2);
			content += '减免'+pre_value_1+'元 ';
		} else if (pre_type == 2) {//打折
			var pre_value_2 = $("#pre_value_2").numberbox('getValue');
			pre_value_2 = pre_value_2?pre_value_2:0;
			pre_value_2 = Number(pre_value_2).toFixed(2);
			content += '打'+pre_value_2+'折 ';
		}		
		noteHtml += '优惠内容：'+content+'\r';
		//每人限领次数
		var give_type = $("input[name='info[give_type]']:checked").val(); 
		give_type = give_type?give_type:1;
		if (give_type == 1) {//不限
			noteHtml += '每人限领次数：不限\r';
		} else if (give_type == 2) {//限制
			var give_value = $("#give_value").numberbox('getValue');
			give_value = give_value?give_value:0;
			noteHtml += '每人限领次数：'+give_value+'次\r';
		}		
		//其他限制：仅原价购买商品时可用券
		var is_more = $("#is_more").is(":checked");
		if (is_more) {
			noteHtml += '其他限制：仅原价购买商品时可用券';
		}
		$('#noteHtml').val(noteHtml);
	}
	
//商品弹框
	
	// function clearAttrForm(){
	// 	$('#attr').form('clear');
	// 	searchInfo();
	// }
	
	var fields =  [ [
	{ 
		field:'operate',
		title:'选择',
		width: 60,
		align:'left', 
	    formatter:function(value, row, index){  
	         var str = '<input type="checkbox" onclick="selectProduct(this);" name="product_id" value="'+row.id+'" data-name="'+row.name+'" data-sale_price="'+row.sale_price+'" />';
	         return str;  
		}
	},			
	{  
	    field : 'id',  
	    width : 60, 
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
		field : 'product_type_txt',
		width : 133,
		title : '商品类型'
	}
	] ];
	
	function selectProduct(obj) {
		var id = $(obj).val();
		var name = $(obj).data("name");
		var sale_price = $(obj).data("sale_price");
		var is_checked = $(obj).is(":checked");
		if (is_checked) {
			if ($("#addProduct_"+id).length == 0) {
				var html = ''+
				'<tr id="addProduct_'+id+'">'+
					'<td class="kv-content"><input hidden value="'+id+'" class="add_product">'+id+'</td>'+
					'<td class="kv-content">'+name+'</td>'+				    				
					'<td class="kv-content">'+sale_price+'</td>'+				    				
					'<td class="kv-content"><input type="button" class="easyui-linkbutton" onclick="$(\'#addProduct_'+id+'\').remove();use_product();" data-options="selected:false" value="删除" ></td>'+				    				
				'</tr>';			
				$('#addProduct').append(html);					
			}	
		} else {
			$("#addProduct_"+id).remove();
		}
		use_product();
	}	
	
	function use_product() {
        var v = '';
        $(".add_product").each(function () {
            v += $(this).val() + ",";
        });	
        if (v.length > 0) v = v.substring(0, v.length - 1);
        $('#use_product_ids').val(v);	  
	}

    var options = {
        url: '/index.php?m=Product&c=Product&a=list&format=addOrder',
    };

    var default_params = {
        'info[on_status]':2
    };

	// 	function searchInfo() {
	// 		var queryData = new Object();
	// 		queryData['info[name]'] = $('#product_name').val();
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
	// 		queryData['info[self_code]'] = $('#self_code').val();
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
	//
	//  $(".more").click(function(){
	//     $(this).closest(".conditions").siblings().toggleClass("hide");
	// });
	
