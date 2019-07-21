/***
 * 设备列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields = [[{
    field: 'name',
    width: 200,
    title: '设备名称'
}, {
    field: 'self_code',
    width: 200,
    title: '设备编码'
}, {
    field: 'custom_code',
    width: 200,
    title: '自定义码'
}, {
    field: 'parent_name',
    width: 200,
    title: '父级设备'
}, {
    field: 'created_at',
    width: 200,
    title: '添加时间'
}, {
    field: 'operate',
    title: '操作',
    width: 150,
    align: 'left',
    formatter: function (value, row, index) {
        var str =  '<input type="button" onclick="javascript:promoteProductnow(' + row.id + ');"  class="easyui-linkbutton" data-options="selected:true" value="二维码" >';
        str +=  '<input type="button" onclick="location.href=\'/?m=Machine&c=Machine&a=addlog&id=' + row.id + '\'" class="easyui-linkbutton" data-options="selected:true" value="维护" >';
        return str;
    }
}
]];

function promoteProductnow(id) {
    $('#tuiguang').dialog({
        title: '二维码',
        top: 5,
        width: 400,
        height: 400,
        closed: false,
        cache: false,
        href: '/?m=Machine&c=Machine&a=erweima&id=' + id+'&is_menu=1',
        modal: true
    });
}