
	$('.easyui-tabs1').tabs({
      	tabHeight:  -1,
      	selected:1   
    });
    $('.easyui-tabs1').tabs('disableTab', 2);	
    $('.easyui-tabs1').tabs('disableTab', 3);	    
    $(window).resize(function(){
    	$('.easyui-tabs1').tabs("resize");
    }).resize();
    
  	//去支付
    function recharge (){   
    	if (!$("#read").is(":checked")) {
    		$.messager.alert('提示', '请阅读并同意《短信充值协议》');
    		return;
    	}	
    	$.ajax({
             url : location.href+'&format=add',
             type : "post",
             dataType : "json",
             success : function(data){
                if (data.code == '200') {
    				location.href = '/index.php?m=Marketing&c=Pushmsg&a=pay&id='+data.id
    			} else {
    				$.messager.alert('提示', data.msg);
    			}
             }
        });    	
 	};
