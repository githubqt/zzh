
	$('.easyui-tabs1').tabs({
      	tabHeight:  -1,
      	selected:2   
    });
    $('.easyui-tabs1').tabs('disableTab', 0);	
    $('.easyui-tabs1').tabs('disableTab', 1);	    
    $('.easyui-tabs1').tabs('disableTab', 3);	    
    $(window).resize(function(){
    	$('.easyui-tabs1').tabs("resize");
    }).resize();      
    
  	//支付二维码
    function payType(type){
		if (type == 1) {
			$("#tips").html('请用微信扫码付款'); 
		} else {
			$("#tips").html('请用支付宝扫码付款'); 	
		}    	
		$("#erweima").attr("src",location.href+'&format=pay&type='+type);   
 	};
 	
  	//确认支付
    function submitOrder(){  	
    	$.ajax({
             url : location.href+'&format=submit',
             type : "post",
             dataType : "json",
             success : function(data){
                if (data.code == '200') {
    				location.href = '/index.php?m=Marketing&c=Pushmsg&a=recharge'
    			} else {
    				$.messager.alert('提示', data.msg);
    			}
             }
        });    	
 	};
 
 
  