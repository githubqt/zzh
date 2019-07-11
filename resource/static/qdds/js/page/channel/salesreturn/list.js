var fields = [[{
    field: 'order_no',
    width: 150,
    sortable: true,
    title: '采购单号',
}, {
    field: 'child_order_no',
    width: 150,
    title: '子单编号',
}, {
    field: 'return_no',
    width: 150,
    title: '退货单号'
}, {
    field: 'order_created_at',
    width: 150,
    title: '下单时间'
}, {
    field: 'created_at',
    width: 160,
    title: '退货时间'
}, {
    field: 'order_status_text',
    width: 80,
    title: '订单状态'
}, {
    field: 'return_status_text',
    width: 80,
    title: '退货状态'
}, {
    field: 'purchase_status_text',
    width: 80,
    title: '采购单类型'
}, {
    field: 'return_number',
    width: 50,
    title: '数量'
}, {
    field: 'name',
    width: 80,
    title: '收货人'
}, {
    field: 'mobile',
    width: 100,
    title: '联系方式'
}, {
    field: 'express_name',
    width: 80,
    title: '物流公司'
}, {
    field: 'express_no',
    width: 150,
    title: '物流单号'
}, {
    field: 'admin_name',
    width: 80,
    sortable: true,
    title: '操作人'
}, {
    field: 'return_money',
    width: 80,
    sortable: true,
    title: '退款金额'
}, {
    field: 'operate',
    title: '操作',
    align: 'center',
    formatter: function (value, row, index) {
        return showOperator(value, row, index);
    }
}]
];

/**
 * 显示操作项
 * @param value 字段的值。
 * @param row 行的记录数据。
 * @param index 行的索引。
 * @returns {string}
 */
function showOperator(value, row, index) {
    var order_status = parseInt(row.order_status);
    var operator = [];
    operator.push({'action': 'detail', 'name': '查看'});
    switch (order_status) {
        case return_order.status_41:
            if (row.return_to === 'supplier') {
                operator.push({'action': 'receipt', 'name': '确认收货'});
                operator.push({'action': 'reject', 'name': '拒绝收货'});
            }
            break;
        case return_order.status_63:
            operator.push({'action': 'express', 'name': '填写物流'});
            break;
    }
    return QDDGrid.renderOperator(operator, row, index);
}

/**
 * 此处扩展默认的网格属性
 * @type {{action: function | string}}
 */
var options = {};

/**
 * 此处扩展默认的处理动作
 * @type {{action: function | string}}
 */
var handles = {
    // 这里是主订单操作
    detail: function (row) {
        QDDGrid.redirect("?m=Channel&c=SalesReturn&a=detail&id=" + row.id);
    },
    /**
     * 确认收货
     * @param row
     */
    receipt: function (row) {
        handleConfirm("?m=Channel&c=SalesReturn&a=list&format=receipt&id=" + row.id, '收货');
    },
    /**
     * 拒绝收货
     * @param row
     */
    reject: function (row) {
        $.messager.confirm({
            title: '提示',
            msg: '确定拒收吗？',
            fn: function (res) {
                if (res) {
                    var $obj = $("#return-receipt-reject-dialog");
                    $obj.window('open');
                    $obj.find("#id").val(row.id);
                }
            },
            ok: '确认拒收',
            cancel: '取消'
        });
    },
    /**
     * 填写物流
     * @param row
     */
    express: function (row) {
        var $obj = $("#return-express-dialog");
        $obj.window('open');
        $obj.find("#id").val(row.id);
        //姓名
        $obj.find('input[name="fd[name]"]').val(row.name);
        //手机
        $obj.find('input[name="fd[mobile]"]').val(row.mobile);
        $("#psa2").html('');
        $("#psa1").html(template('psa-tpl', {})).find('select').attr('disabled', true);
        //省市区
        area_child(0, 1, row.province_id, row.city_id);
        //地址
        $obj.find('input[name="fd[address]"]').val(row.address);
        //修改编辑弹框类型
        $obj.find('input[name="format"]').val('express');
    }
};

