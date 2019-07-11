/***
 * 添加订单
 * @version v0.01
 * @author lqt
 * @time 2018-05-24
 */

    $(function(){
        $('#ff').form({
            onSubmit: function () {
                $("input[type='submit']").attr('disabled',true);
                return true;
            },
            success:function(data){
                var data = JSON.parse(data);
                $("input[type='submit']").attr('disabled',false);
                if (data.code == '200') {
                    location.href="/index.php?m=Order&c=Order&a=list"
                } else {
                    $.messager.alert('提示', data.msg);
                }
            }
        });
		
    });	
