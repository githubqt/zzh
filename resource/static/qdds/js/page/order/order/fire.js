
function fireOrder(id) {

    $.messager.confirm('提示', '确定发货?', function (r) {
        if (r) {
        	$('input[type="button"]').prop('disabled',true);
            $.ajax({
                type: "POST",
                async:true,  // 设置同步方式
                url: "/?m=Order&c=Order&a=fire&format=fire&id="+id,
                data:{
                    express_id:$('#express_id').combobox('getValue'),
                    express_no:$('#express_no').val(),
                    express_name:$('#express_id').combobox('getText')
                },
                dateType: "json",
                success:function(data){
                    if (data.code == '200') {
                        location.href="/index.php?m=Order&c=Order&a=list"
                    } else {
                        $.messager.alert('提示', data.msg);
                        $('input[type="button"]').prop('disabled',false);
                    }
                }
            });
        }
    });

 
} 