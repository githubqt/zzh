/***
 * 编辑角色js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-08
 */

    $(function(){

    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Auth&c=Admin&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}