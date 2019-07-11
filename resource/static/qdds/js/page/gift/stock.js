/***
 * 修改商品库存
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */


    $(function(){

    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Gift&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}
	
	