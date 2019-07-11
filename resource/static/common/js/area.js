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
                             // 增加 请选择 选项
                             if ($("#province_id").data("placeholeder")) {
                                 $("#province_id").html("<option value=''>请选择</option>");
                                 $("#city_id").html("<option value=''>请选择</option>");
                                 $("#area_id").html("<option value=''>请选择</option>");
                             }else {
                                 area_child(res.data[0].area_id,2,province,city,area);
                             }
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
		    }
	    });	
}


$(function(){
	$("body").on('change','#province_id',function(){
		area_child($(this).val(),2);
	});
	$("body").on('change','#city_id',function(){
		area_child($(this).val(),3);
	});
});


