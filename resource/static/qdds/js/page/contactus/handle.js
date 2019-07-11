/***
 * 处理
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-24
 */


function onstatus(on_status) {
    $("#status").val(on_status);

    var $frm = $("#ff");

    var isValid = $frm.form('validate');
    if (isValid == false) {
        return isValid;
    }

    $frm.find('input[type="button"]').attr('disabled',true);

    $.ajax({
        url: $frm.attr("action"),
        type: "post",
        dataType: "jsonp",
        jsonp: "jsonpcallback",
        data: $frm.serialize(),
        success: function (data) {

            if (data.code == '200') {
                location.href = "/index.php?m=Contactus&c=Contactus&a=list"
            } else {
                $.messager.alert('提示', data.msg);
                $frm.find('input[type="button"]').attr('disabled',false);
            }
        },
        error:function () {
            $frm.find('input[type="button"]').attr('disabled',false);
        }
    });
}
    

	
	
	