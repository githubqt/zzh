/***
 * 编辑首页
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-21
 */


    $(function(){
    	if ($("#categoty_type").val() == '2') {
    		category_child(0,1,$("#c_one").val(),$("#c_two").val(),$("#c_three").val());	
    	} else {
    		category_child(0,1);	
    	}
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Indexdata&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
		
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}
	
	
	function editType() {
		var type = $("#data_type").val();
		if (type == '1') {
			$("#type_one").show();
			$("#type_two").hide();
			$("#type_three").hide();
		} else if (type == '2') {
			$("#type_one").hide();
			$("#type_two").show();
			$("#type_three").hide();
		} else if (type == '3') {
			$("#type_one").hide();
			$("#type_two").hide();
			$("#type_three").show();
		} 
	}
	
	
	
	function timeType(type) {
		if (type == 2) {
			$("#time_long").hide();
		} else {
			$("#time_long").show();
		}
	}
	
	
	
	
	
	
	
	
	
	
	