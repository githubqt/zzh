/***
 * 添加黑名单
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */


    $(function(){

    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Blacklist&c=Blacklist&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}