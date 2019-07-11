/***
 * 添加角色js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-08
 */

    $(function(){

    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Auth&c=Role&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    });
    
    $(function(){
    	$(".permission-list dt input:checkbox").click(function(){
    		$(this).closest("dl").find("dd input:checkbox").prop("checked",$(this).prop("checked"));
    	});
    	
    	$(".permission-list2 dt input:checkbox").click(function(){	
    		var l23=$(this).parents(".permission-list").find("dt").find("input:checked").length;		
    		if($(this).prop("checked")){
    			$(this).parents(".permission-list").find("dt").first().find("input:checkbox").prop("checked",true);
    		}else{
    			if(l23==1){
    				$(this).parents(".permission-list").find("dt").first().find("input:checkbox").prop("checked",false);
    			}
    		}		
    		
    	});	
    	
    	$(".permission-list2 dd input:checkbox").click(function(){
    		var l =$(this).parent().parent().find("input:checked").length;
    		var l2=$(this).parents(".permission-list").find(".permission-list2 dd").find("input:checked").length;
    		if($(this).prop("checked")){
    			$(this).closest("dl").find("dt input:checkbox").prop("checked",true);
    			$(this).parents(".permission-list").find("dt").first().find("input:checkbox").prop("checked",true);
    		}
    		else{
    			if(l==0){
    				$(this).closest("dl").find("dt input:checkbox").prop("checked",false);
    			}
    			if(l2==0){
    				$(this).parents(".permission-list").find("dt").first().find("input:checkbox").prop("checked",false);
    			}
    		}	
    	});
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}