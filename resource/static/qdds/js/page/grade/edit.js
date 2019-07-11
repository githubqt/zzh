
    	
$(function(){
	//优惠券默认值
 	select_coupan_def();
});		

function select_coupan_def() {
    $('.coupan_num').numberbox({
    	min:0
    });
    if (coupons_def) {
        coupons_def = eval('(' + coupons_def + ')');
	}
	$(".select_coupan").each(function(i,item){
		$.ajax({
	        type: "POST",
	        url: '/index.php?m=Marketing&c=Coupan&a=list&format=list',
		  	data: {
		  		info:{
		  			status:2
		  		},
		  		page:1,
		  		rows:50
		  	},
		  	dataType:"json",
		    success:function(res){
		    	if (res.code == '200') {
			        $(item).combobox({
			            data:res.rows,
			            valueField: 'id',
			            textField: 'name',
			            dataPlain : true,
			            onLoadSuccess: function(){
			                // 设置默认值
                            if (coupons_def) {
                                $(this).combobox('setValue',coupons_def[i]['id']);
                                $(this).combobox('setText',coupons_def[i]['name']);
                            } else {
                                $(this).combobox('setValue','');
                                $(this).combobox('setText','-请选择优惠券-');
							}
			            },
			        });	
				}
			}	 	    	
	    });			
	});	    

}
function select_coupan() {
    $('.coupan_num:last').numberbox({
    	min:0
    });		
	
	$.ajax({
	        type: "POST",
	        url: '/index.php?m=Marketing&c=Coupan&a=list&format=list',
		  	data: {
		  		info:{
		  			status:2
		  		},
		  		page:1,
		  		rows:50
		  	},
		  	dataType:"json",
		    success:function(res){
		    	if (res.code == '200') {
			        $('.select_coupan:last').combobox({
			            data:res.rows,
			            valueField: 'id',
			            textField: 'name',
			            dataPlain : true,
			            onLoadSuccess: function(){
			                // 设置默认值
			                $(this).combobox('setValue','');
			                $(this).combobox('setText','-请选择优惠券-');
			            },
			        });	
				}
			}	 	    	
	    });	
	    
	    
}



function printValue() {
	
	var identity = $('#identity').textbox('getValue');
	if(!identity){
	 	$.messager.alert('提示', '身份称谓不能为空');
	 	return;
	} 	
	
	var growth = $('#growth').numberbox('getValue');
	if(!growth){
	 	$.messager.alert('提示', '所需成长值不能为空');
	 	return;
	} 	

	var is_integral = $('#is_integral').is(':checked');
	var integral = $('#integral').numberbox('getValue');
	if (is_integral) {
		is_integral = 1;
		if (!integral) {
			$.messager.alert('提示', '升级送的积分不能为空');
			return;
		}
	} else {
		is_integral = 2;
		integral = 0;
	}

	//优惠券
	var is_coupons = $('#is_coupons').is(':checked');
	var coupons = [];
	if (is_coupons) {
		is_coupons = 1;
		var res = true;
 		$(".select_coupan").each(function(i,item){
 			var id = $(item).combobox('getValue');
 			var name = $(item).combobox('getText');
 			if (!id) {
 				$.messager.alert('提示', '请选择优惠券');
 				res = false;
 			}
 			var coupan = new Object();
 			coupan.id = id;
 			coupan.name = name;
 			coupons.push(coupan);
 		});	
 		
 		if (!res) {
 			return;
 		}
 		
 		$(".coupan_num").each(function(i,item){
 			var num = item.value;
 			var name = $(item).combobox('getText');
 			if (!num) {
 				$.messager.alert('提示', '请输入优惠券数量');
 				res = false;
 			} 
 			coupons[i].num = num;		   
 		});
 		if (!res) {
 			return;
 		} 		
	} else {
		is_coupons = 2;
		coupons = '';
	}
	
	var postage = $('#postage').is(':checked');
	if (postage) {
		postage = 1;
	} else {
		postage = 2;
	}
	
	var is_discount = $('#is_discount').is(':checked');
	var discount = $('#discount').numberbox('getValue');
	if (is_discount) {
		is_discount = 1;
		if (!discount) {
			$.messager.alert('提示', '会员专享折扣不能为空');
			return;
		}		
	} else {
		is_discount = 2;
		discount = 0;
	}
	
	var is_feedback = $('#is_feedback').is(':checked');
	var feedback = $('#feedback').numberbox('getValue');
	if (is_feedback) {
		is_feedback = 1;
		if (!feedback) {
			$.messager.alert('提示', '积分回馈倍率不能为空');
			return;
		}		
	} else {
		is_feedback = 2;
		feedback = 0;
	}
	
    var info = new Object();
	info['identity'] = identity;
	info['growth'] = growth;
	info['is_integral'] = is_integral;
	info['integral'] = integral;
	info['is_coupons'] = is_coupons;
	info['coupons'] = JSON.stringify( coupons );
	info['postage'] = postage;
	info['is_discount'] = is_discount;
	info['discount'] = discount;
	info['is_feedback'] = is_feedback;
	info['feedback'] = feedback;
	  console.log(info);       		
	 $.ajax({    
	     type: "POST",  
	     async:true,
	     url: location.href+"&format=edit",    
	     data: {info:info},    
	     dataType: "json",  
	     success: function(data){    
	        if(data.code == '200'){
	        	location.href="/index.php?m=Grade&c=Grade&a=list"
	        }
	        if(data.code == '500'){
	        	$.messager.alert('提示', data.msg);
	        }
	     },    
	      
	 });    
	             
}
	         
function add(){
	var addNumber = $(".select_coupan").length;
	
	if(addNumber < 5){
		var select = "<div style='margin: 2px 0 0 112px;'>"+
		"<select class='easyui-combobox select_coupan' name='info[coupan][]' style='width:130px; height:33px '>"+
			"<option value=''>-请选择优惠卷-</option>"+
		"</select>"+
		"<input class='easyui-numberbox coupan_num' type='text' name='info[num][]' data-options='min:0' style='width: 100px; height: 35px; line-height: 35px;' />张优惠卷"+
		"<a style='margin-left: 10px;' href='javascript:void(0);' onclick='removeop(this);'>删除</a>"+
		"</div>";
		$("#add").append(select);
		select_coupan();
	}
	
	if (addNumber == 4 ) {
		 $("#addNum").show();
		 $("#addCoupons").hide();
	}
}

function removeop(op){
	$(op).parent().remove();
    $("#addNum").hide();
    $("#addCoupons").show();
}
