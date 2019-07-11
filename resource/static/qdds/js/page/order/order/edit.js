/***
 * 添加订单
 * @version v0.01
 * @author lqt
 * @time 2018-05-24
 */

    $(function(){ 			 
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Order&c=Order&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
    });	
