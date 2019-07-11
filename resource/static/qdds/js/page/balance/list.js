var fields = [[{
    field: 'id',
    width: 100,
    sortable: true,
    title: 'ID'
}, {
    field: 'balance_no',
    width: 150,
    title: '结算单号'
}, {
    field: 'original_amount',
    width: 100,
    sortable: true,
    title: '原始结算金额'
}, {
    field: 'actual_amount',
    width: 100,
    sortable: true,
    title: '实际结算金额'
}, {
    field: 'balance_type_txt',
    width: 100,
    title: '结算类型'
}, {
    field: 'status_txt',
    width: 100,
    title: '结算状态'
}, {
    field: 'note',
    width: 150,
    title: '备注'
}, {
    field: 'created_at',
    width: 140,
    title: '添加时间'
}, {
    field: 'admin_name',
    width: 100,
    title: '操作人'
}, {
    field: 'operate',
    title: '操作',
    width: 200,
    formatter: function (value, row, index) {
        return showOperator(value, row, index);
    }
}
]];

/**
 * 显示操作项
 * @param value 字段的值。
 * @param row 行的记录数据。
 * @param index 行的索引。
 * @returns {string}
 */
function showOperator(value, row, index) {
    var operator = [];
    operator.push({action: 'detail&rows=2000', name: '查看'});
    if (row.status === '2') {
        operator.push({action: 'check&rows=2000', name: '确认'});
    } else if (row.status === '4') {
        operator.push({action: 'finance&rows=2000', name: '收款'});
    }
    return QDDGrid.renderOperator(operator, row, index);
}

/**
 * 此处扩展默认的处理动作
 * @type {{action: function | string}}
 */
var handles = {
    delete:function(row){
        var url = location.href.replace('list', 'delete') + '&id=' + row.id;
        $.messager.confirm('温馨提示', '您确定要取消吗?',function(res){
            if (res === true) {
                $.ajax({
                    type: "POST",
                    async:true,
                    url: url,
                    dateType: "json",
                    success:function(data){
                        if (data.code === '200') {
                            $(".reset").click();
                        } else {
                            $.messager.alert('提示', data.msg);
                        }
                    }
                });
            }
        });
    }
};