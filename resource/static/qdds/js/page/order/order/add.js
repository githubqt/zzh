/***
 * 添加订单
 * @version v0.01
 * @author lqt
 * @time 2018-05-24
 */

    $(function(){
    	
		area_child(0,1);	 

    	$('#ff').form({
            onSubmit: function () {
                $("input[type='submit']").attr('disabled',true);
                return true;
            },
			success:function(data){
				var data = JSON.parse(data);
                $("input[type='submit']").attr('disabled',false);
				if (data.code == '200') {
					location.href="/index.php?m=Order&c=Order&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
    });
	//会员弹框	
	function clearUserForm(){
		$('#user_ff').form('clear');
		searchUser();
	}
	
	var userFields =  [ [ {
		field:'operate',
		title:'选择',
		width: 60,
		align:'left', 
	    formatter:function(value, row, index){  
	         var str = '<input type="radio" name="select_user" value="'+row.id+'" data-name="'+row.name+'" data-mobile="'+row.mobile+'" />';
	         return str;  
		}
	}, {		  
	    field : 'id',  
	    width : 150, 
	    checkout : true, 
	    title : 'ID'  
	}, {  
	    field : 'name',  
	    width : 415,  
	    title : '会员名称'  
	}, {  
	    field : 'mobile',  
	    width : 413,   
	    title : '会员手机'  
	} 
	] ];


		
	function searchUser() {
		var queryData = new Object();
		queryData['info[user_name]'] = $('#search_user_name').val();
		queryData['info[user_mobile]'] = $('#search_user_mobile').val();
        queryData['info[mobile_is_not_null]'] = 1;//过滤没有绑定手机号的会员

        $('#user_dg').datagrid({
			title:'',
			width:'100%',
			height:'auto',
			nowrap: true,
			autoRowHeight: true,
			striped: true,
		    url: '/index.php?m=User&c=User&a=list&format=list',
			remoteSort: false,
			singleSelect:true,
			idField:'id',
			loadMsg:'数据加载中......',  
			pageList: [10,20,50],		
			columns: userFields,
			pagination:true,
			rownumbers:true,
			queryParams:queryData
		});
			
	}
	
	function userSelect() {
		var user = $("input[name='select_user']:checked");
		if (user.length == 0) {
			$.messager.alert('提示', '请选择会员！');
			return;
		}
		var user_id = user.val();
		var user_name = user.attr('data-name');
		var user_mobile = user.attr('data-mobile');
        user_name = user_name === 'null'?user_mobile:user_name;
		$('#user_name').html('已选择会员：'+user_name).show();
		$('#user_id').val(user_id);

		$('#accept_name').textbox('setValue',user_name);
		$('#accept_mobile').numberbox('setValue',user_mobile);

		$('#user_dialog').window('close');
	}
	
	//商品弹框
	function clearProductForm(){
		$('#product_ff').form('clear');
		searchProduct();
	}	
	
	$(".more").click(function(){
	    $(this).closest(".conditions").siblings().toggleClass("hide");
	});		
	
	var product_fields =  [ [
	{ 
		field:'operate',
		title:'选择',
		width: 60,
		checkbox: true
	},			
	{  
	    field : 'id',  
	    width : 60, 
	    title : 'ID'  
	}, {  
	    field : 'name',  
	    width : 200,  
	    title : '商品名称'  
	}, {  
	    field : 'self_code',  
	    width : 130,   
	    title : '商品编码'  
	} , {  
	    field : 'brand_name',  
	    width : 130,   
	    title : '品牌名称'  
	}  , {  
	    field : 'category_name',  
	    width : 200,   
	    title : '分类名称'  
	}  , {  
	    field : 'sale_price',  
	    width : 100,   
	    title : '商品价格'
	}  , {
		field : 'stock',
		width : 100,
		title : '库存'
	}  , {
	    field : 'product_type_txt',
	    width : 100,   
	    title : '商品类型'
	}  , {
		field : 'is_return_txt',
		width : 100,
		title : '是否支持退货'
	}
	] ];
	
	function searchProduct() {
		var queryData = new Object();
		queryData['info[name]'] = $('#product_name').val();
		queryData['info[id]'] = $('#product_id').val();
		queryData['info[on_status]'] = 2;
		var $input = $('input[name="info[brand_id][]"]');
	    if ($input.length >1){
	        var inputVal = [];
	        $input.each(function () {
	            if ($(this).val()) {
	                inputVal.push($(this).val()) ;
	            }
	        });
	        queryData['info[brand_id][]'] = inputVal;
	    }else{
	    	queryData['info[brand_id][]'] = $input.val();
	    }
		var $input = $('input[name="info[category_id][]"]');
        if ($input.length >1){
            var inputVal = [];
            $input.each(function () {
                if ($(this).val()) {
                    inputVal.push($(this).val()) ;
                }
            });
            queryData['info[category_id][]'] = inputVal;
        }else{
        	queryData['info[category_id][]'] = $input.val();
        }
		queryData['info[self_code]'] = $('#self_code').val();
		
		$('#product_dg').datagrid({
			title:'',
			width:'100%',
			height:'auto',
			nowrap: true,
			autoRowHeight: true,
			striped: true,
		    url: '/index.php?m=Product&c=Product&a=list&format=addOrder',
			remoteSort: false,
			idField:'id',
			loadMsg:'数据加载中......',  
			pageList: [10,20,50],		
			columns: product_fields,
			pagination:true,
			singleSelect:false,
			rownumbers:true,
			queryParams:queryData,
			onLoadSuccess: function(data){   
	          var panel = $(this).datagrid('getPanel');   
	          var tr = panel.find('div.datagrid-body tr');   
	          tr.each(function(){   
	              var td = $(this).children('td[field="userNo"]');   
	              td.children("div").css({   
	                  "height": "50px" 
	              });   
	          });   
	       }
		});
	}	

	function productSelect() {
		var selectrow = $("#product_dg").datagrid("getChecked");

		if (selectrow.length == 0) {
			$.messager.alert('提示', '请选择商品！');
			return;
		}
		
		$.each(selectrow,function(index,item){
			if (item.stock > 0 && $('#product_'+item.id).length == 0) {
				var productHtml = ''+
					'<tr class="product" id="product_'+item.id+'" data-id="'+item.id+'">'+
						'<td>'+item.id+'</td>'+
						'<td>'+item.name+'</td>'+
						'<td id="sale_price_'+item.id+'">'+item.sale_price+'</td>'+
						'<td><input id="num_'+item.id+'" name="info[product]['+item.id+']" value="1" onkeyup="this.value=this.value.replace(/[^0-9]/gi,\'\');this.value=this.value>'+item.stock+'?'+item.stock+':this.value;changNum(this,'+item.id+');"></td>'+
						'<td id="total_price_'+item.id+'">'+item.sale_price+'</td>'+
						'<td><input type="button" class="easyui-linkbutton" onclick="del('+item.id+');" data-options="selected:false" value="删除"></th>'+
					'</tr>';			
				$("#product").append(productHtml);		
			}
		});
        total();
		$('#product_dialog').window('close');
	}

	function del(id){
        $('#product_'+id).remove();
        total();
        var rowIndex = $('#product_dg').datagrid('getRowIndex',id);
        $("#product_dg").datagrid('deleteRow',rowIndex);//根据索引删除对应的行。
	}
	
	function changNum(obj,id){
		var num = $(obj).val();
		num = Number(num);
		var sale_price = $('#sale_price_'+id).text();
		sale_price = Number(sale_price);		
		$("#total_price_"+id).html(parseFloat(num*sale_price).toFixed(2));	
		total();
	}
	function total(){
		var total = $(".product").length;
		var total_num = 0 ;
		var total_price = 0;
		$(".product").each(function(index,item){		
			var id = $(item).attr('data-id');	
			var num = $('#num_'+id).val();	
			num = Number(num);						
			var sale_price = $('#sale_price_'+id).text();	
			sale_price = Number(sale_price);	
			var reach_price = Number(num*sale_price);			
			total_num = total_num + num;
			total_price = total_price + reach_price;
		});
		
		$('#total').html(total);
		$("#total_num").html(total_num);
		$("#total_price").html(parseFloat(total_price).toFixed(2));
	    
	    
	}

	$('input[type="radio"][name="info[delivery_type]"]').change(function() {
		if (this.value == '0') {
			$('.address_tr').show();
		}
		else if (this.value == '1') {
            $('.address_tr').hide();
		}
	});
