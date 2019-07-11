/***
 * 添加退货js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
$(function () {

    /**
     * 减
     */
    $("#ff2").on('click', '.return-minus', function () {
        // 退货商品编号
        var ppId = $(this).data('purchase-product-id');
        // 退货输入框对象
        var $returnNum = $("#return_num_" + ppId);
        // 退货价格显示对象
        var $returnPrice = $("#return_price_" + ppId);
        // 商品采购价格
        var price = $returnNum.data('price');
        // 可退货数量
        var num = $returnNum.data('num');
        var retNum = parseInt($returnNum.val());
        if (isNaN(retNum)) {
            $.messager.alert('提示', '输入格式错误,请输入数字');
            return false;
        }

        if (retNum < 1) {
            $.messager.alert('提示', '请输入有效的退货数量');
            return false;
        }
        $returnNum.val(--retNum);
        $returnPrice.html((retNum * price).toFixed(2));
    });

    /**
     * 加
     */
    $("#ff2").on('click', '.return-plus', function () {
        // 退货商品编号
        var ppId = $(this).data('purchase-product-id');
        // 退货输入框对象
        var $returnNum = $("#return_num_" + ppId);
        // 退货价格显示对象
        var $returnPrice = $("#return_price_" + ppId);
        // 商品采购价格
        var price = $returnNum.data('price');
        // 可退货数量
        var num = $returnNum.data('num');
        // 退货数
        var retNum = parseInt($returnNum.val());
        if (isNaN(retNum)) {
            $.messager.alert('提示', '输入格式错误,请输入数字');
            return false;
        }

        if (retNum >= num) {
            $.messager.alert('提示', '该商品最多退货 ' + num + ' 件');
            return false;
        }
        $returnNum.val(++retNum);
        $returnPrice.html((retNum * price).toFixed(2));
    });
    /**
     * 输入
     */
    $("#ff2").on('input', '.return_num', function () {
        // 退货商品编号
        var ppId = $(this).data('purchase-product-id');
        // 退货输入框对象
        var $returnNum = $("#return_num_" + ppId);
        // 退货价格显示对象
        var $returnPrice = $("#return_price_" + ppId);
        // 商品采购价格
        var price = $returnNum.data('price');
        // 可退货数量
        var num = $returnNum.data('num');
        // 退货数
        var retNum = parseInt($returnNum.val());
        if (isNaN(retNum)) {
            $.messager.alert('提示', '输入格式错误,请输入数字');
            return false;
        }

        if (retNum <= 0) {
            $.messager.alert('提示', '请输入有效的退货数量');
            $returnNum.val(1);
            return false;
        }

        if (retNum > num) {
            $.messager.alert('提示', '该商品最多退货 ' + num + ' 件');
            $returnNum.val(num);
            return false;
        }

        $returnPrice.html((retNum * price).toFixed(2));
    });


    $('#ff2').form({
        onSubmit: function(){
            var isValid = $(this).form('validate');
            if (!isValid) {
                $.messager.progress('close');
                return false;
            }
            $("input[type='submit']").attr('disabled',true);
            return true;
        },
        success: function (data) {
            try {
                var data = JSON.parse(data);
                if (data.code == '200') {
                    location.href = "/index.php?m=SalesReturn&c=SalesReturn&a=list"
                } else {
                    $.messager.alert('提示', data.msg);
                    $("input[type='submit']").attr('disabled',false);
                }
            }catch (e) {
                $("input[type='submit']").attr('disabled',false);
            }
        }
    });

});


function clearForm() {
    $('#ff').form('clear');
}
		
		
		
		