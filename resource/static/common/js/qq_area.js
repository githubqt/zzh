/**
 * @author zhaoyu
 */

function area_child(pid,type, province,city,area)
{
	if (pid == 'undefined') {
		pid = 0;
	}
	if (type == 'undefined') {
		type = 1;
	}
	if (province == 'undefined') {
		province = 0;
	}	
	if (city == 'undefined') {
		city = 0;
	}
	if (area == 'undefined') {
		area = 0;
	}		
	$.ajax({
        type: "POST",
        url: '/index.php?m=Public&c=Public&a=area',
	  	data: "pid="+pid,
	  	dataType:"json",
	    success:function(res){
	    	if(res.code == 200){
	    		if(type == 1){
	    			$("#province_id").html("");
		    		$("#city_id").html("");
		    		$("#area_id").html("");
		    		 if(city > 0){
		    		    area_child(province,2,province,city,area);
		    		 }else{
		    		 	area_child(res.data[0].area_id,2,province,city,area);
		    		 }
	    		}
	    		
	    		if(type == 2){
	    			$("#city_id").html("");
		    		$("#area_id").html("");
		    		if(city > 0){ 
		    		    area_child(city,3,province,city,area);
		    		 }else{
		    		 	area_child(res.data[0].area_id,3,province,city,area);
		    		 }
	    		}
	    		if(type == 3){
		    		$("#area_id").html("");
	    		}
	    		
	    		$.each(res.data, function(i,val){  
	    		   var optionHtml = "";
	    		
	    		   optionHtml = "<option value='"+val.area_id+"'>"+val.area_name+"</option>";
	    		  	    
				   if(type == 1){
					   	if(province == val.area_id) {
					   		 optionHtml = "<option selected='selected' value='"+val.area_id+"'>"+val.area_name+"</option>";
					   	}
					   $("#province_id").append(optionHtml);
				   }
				   
				   if(type == 2){
					   	if(city == val.area_id) {
						     optionHtml = "<option selected='selected' value='"+val.area_id+"'>"+val.area_name+"</option>";
						}
					   	$("#city_id").append(optionHtml);
				   }
				   
				   if(type == 3){
					   	if(area == val.area_id) {
						   	 optionHtml = "<option selected='selected' value='"+val.area_id+"'>"+val.area_name+"</option>";
						}
					   	$("#area_id").append(optionHtml);
				   }
				}); 
	    		 
	    	}
	    	searchKeyword2();
	    }
    });	
}


function area_child_name(p_name,type,province,city,area,id='')
{
	
	
	var parent_id = '';
	/*if (p_name == '北京市' || p_name == '上海市' || p_name == '天津市' || p_name == '重庆市') {
		p_name = p_name.replace('市', '');
	} else {
		p_name = p_name.replace('省', '');
	}*/
	
	if (province == '北京市' || province == '上海市' || province == '天津市' || province == '重庆市') {
		province = province.replace('市', '');
	} else {
		province = province.replace('省', '');
		province = province.replace('特别行政区', '');
		province = province.replace('回族自治区', '');
		province = province.replace('维吾尔自治区', '');
		province = province.replace('自治区', '');
	}
	
	if (city == '北京市' || city == '上海市' || city == '天津市' || city == '重庆市') {
		city = city.replace('市', '');
	} else {
		city = city.replace('特别行政区', '');
		city = city.replace('蒙古', '');
		city = city.replace('自治', '');
	}
	
	if (type == '1') {
		p_name = '';
		parent_id = '0';
	}console.log(type)
	
	if (type == '2') {
		parent_id = '';
		if (p_name == '北京') {
			parent_id = '1';
		}
		if (p_name == '上海') {
			parent_id = '2';
		}
		if (p_name == '天津') {
			parent_id = '3';
		}
		if (p_name == '重庆') {
			parent_id = '4';
		}
		
	}

	
	$.ajax({
        type: "POST",
        url: '/index.php?m=Public&c=Public&a=areaname',
	  	data: "pname="+p_name+'&pid='+parent_id,
	  	dataType:"json",
	    success:function(res){
	    	if(res.code == 200){
	    		if(type == 1){
	    			$("#province_id").html("");
		    		$("#city_id").html("");
		    		$("#area_id").html("");
		    		 
		    			 area_child_name(province,2,province,city,area);
		    		 
	    		}
	    		
	    		if(type == 2){
	    			$("#city_id").html("");
		    		$("#area_id").html("");
		    		
		    			area_child_name(city,3,province,city,area);
		    		 
	    		}
	    		if(type == 3){
		    		$("#area_id").html("");
	    		}
	    		
	    		$.each(res.data, function(i,val){  
	    		   var optionHtml = "";
	    		
	    		   optionHtml = "<option value='"+val.area_id+"'>"+val.area_name+"</option>";
	    		  	    
				   if(type == 1){
					   	if(province == val.area_name) {
					   		 optionHtml = "<option selected='selected' value='"+val.area_id+"'>"+val.area_name+"</option>";
					   	}
					   $("#province_id").append(optionHtml);
				   }
				   
				   if(type == 2){
					   	if(city == val.area_name) {
						     optionHtml = "<option selected='selected' value='"+val.area_id+"'>"+val.area_name+"</option>";
						}
					   	$("#city_id").append(optionHtml);
				   }
				   
				   if(type == 3){
					   	if(area == val.area_name) {
						   	 optionHtml = "<option selected='selected' value='"+val.area_id+"'>"+val.area_name+"</option>";
						}
					   	$("#area_id").append(optionHtml);
				   }
				}); 
	    		 
	    	}
	    	
	    }
    });	
}


$(function(){
	$("#province_id").change(function(){
		area_child($(this).val(),2);
	});
	$("#city_id").change(function(){
		area_child($(this).val(),3);
	});
});


