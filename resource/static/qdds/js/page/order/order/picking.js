
function pickingOrder(id) {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Order&c=Order&a=picking&format=picking&id="+id,
        dateType: "json",
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=Order&c=Order&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}

