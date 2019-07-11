/***
 * 处理
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-24
 */


    function onstatus(on_status) {
		$("#status").val(on_status);
		
		var isValid = $("#ff").form('validate');
		if ( isValid == false) {
			return isValid;
		}
	
		
		$.ajax({
             url : $('#ff').attr("action"),
             type : "post",
             dataType : "jsonp",
             jsonp : "jsonpcallback",
             data : $('#ff').serialize(),
             success : function(data){
             	
                if (data.code == '200') {
					location.href="/index.php?m=Proposal&c=Proposal&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
             }
        });
	}
    

	
	
	