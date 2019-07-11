/***
 * 会员修改密码
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-19
 */


    $(function(){
    	
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=User&c=User&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
		
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}