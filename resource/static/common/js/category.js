/**
 * @author huangxianguo
 */

function category_child(pid,type, one,two,three)
{
	if (pid == 'undefined') {
		pid = 0;
	}
	if (type == 'undefined') {
		type = 1;
	}
	if (one == 'undefined') {
		one = 0;
	}	
	if (two == 'undefined') {
		two = 0;
	}
	if (three == 'undefined') {
		three = 0;
	}		
	$.ajax({
	        type: "POST",
	        url: '/index.php?m=Public&c=Public&a=getCategory',
		  	data: "pid="+pid,
		  	dataType:"json",
		    success:function(res){
		    	if(res.code == 200){
		    		if(type == 1){
		    			$("#one").html("");
			    		$("#two").html("");
			    		$("#three").html("");
			    		 if(two > 0){
			    			 category_child(one,2,one,two,three);
			    		 }else{
			    			 category_child(res.data[0].id,2,one,two,three);
			    		 }
		    		}
		    		
		    		if(type == 2){
		    			$("#two").html("");
			    		$("#three").html("");
			    		if(two > 0){
			    			category_child(two,3,one,two,three);
			    		 }else{
			    			 category_child(res.data[0].id,3,one,two,three);
			    		 }
		    		}
		    		if(type == 3){
			    		$("#three").html("");
		    		}
		    		
		    		$.each(res.data, function(i,val){  
		    		   var optionHtml = "";
		    		
		    		   optionHtml = "<option value='"+val.id+"'>"+val.name+"</option>";
		    		  	    
					   if(type == 1){
						   	if(one == val.id) {
						   		 optionHtml = "<option selected='selected' value='"+val.id+"'>"+val.name+"</option>";
						   	}
						   $("#one").append(optionHtml);
					   }
					   
					   if(type == 2){
						   	if(two == val.id) {
							     optionHtml = "<option selected='selected' value='"+val.id+"'>"+val.name+"</option>";
							}
						   	$("#two").append(optionHtml);
					   }
					   
					   if(type == 3){
						   	if(three == val.id) {
							   	 optionHtml = "<option selected='selected' value='"+val.id+"'>"+val.name+"</option>";
							}
						   	$("#three").append(optionHtml);
					   }
					}); 
		    		 
		    	}
		    }
	    });	
}


$(function(){
	$("#one").change(function(){
		category_child($(this).val(),2);
	});
	$("#two").change(function(){
		category_child($(this).val(),3);
	});
});


