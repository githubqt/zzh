/***
 * 会员
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-18
 */


    $(function(){
    	category_child(0,1);	 
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
	
	
	
	
	
	
	
	
	
	
	