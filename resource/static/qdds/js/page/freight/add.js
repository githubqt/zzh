

    $(function(){ 
    	//area_child();
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Freight&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}	
	

/*function area_child()
{	
	$.ajax({
        type: "POST",
        url: '/index.php?m=Marketing&c=Freight&a=add&&format=per',
	  	dataType:"json",
	    success:function(res){
	    	if(res.code == 200){
	    		$.each(res.data, function(i,val){  
	    			var optionHtml = "<option value='"+val.area_id+"'>"+val.area_name+"</option>";
					$("#province").append(optionHtml);				 
				}); 		    		 
	    	}
	    }
    });	
}*/



	
	

