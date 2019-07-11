/**
 * 公用数据编辑操作
 */

$(function () {
    /**
     * 提交表单
     */
    $('#ff').form({
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
            var data = JSON.parse(data);
            if (data.code == '200') {
               location.href = getListUrl();
            } else {
                $.messager.alert('提示', data.msg);
                $("input[type='submit']").attr('disabled',false);
            }
        }
    });
    /**
     * 商品分类
     * @type {jQuery}
     */
    var parent_id = 0;
    var selected_category_id = $('#selected_category_id').val();
    var self = $('#category_id').combotree(
        {
            url: '/?c=Public&a=category',
            lines: true,
            animate: true,
            editable: true,
            loadFilter: function (data) {
                if (data.success == false) {
                    $.messager.alert('错误', data.msg, 'error');
                } else {
                    return data;
                }
            },
            required: true,
            missingMessage: "请选择分类",
            onlyLeafCheck: true,
            onLoadError: function (jqXHR, textStatus, errorThrown) {
                $.messager.alert('错误', errorThrown, 'error');
            },
            onBeforeSelect: function (node) {
                if (node.parent_id == node.root_id) {
                    $('#category_id').combotree('tree').tree('toggle',node.target);
                    $('#category_id').combotree('tree').combo('showPanel');
                    return false;
                }
            },
            onLoadSuccess:function(node, data) {
                if (selected_category_id) {
                    $('#category_id').combotree('setValues', selected_category_id);
                    var tree = $('#category_id').combotree('tree');
                    var selected = tree.tree('getSelected');
                    //var parent = tree.tree('getParent', selected.target);
                    if (selected && selected.parent_id == 797) {
                        if ($("#channel_is_up").is(":checked")) {
                            $('#channel_price').numberbox('disable');
                            $('#channel_up_price').numberbox({required: true});
                        }
                        $('.is_up').show();
                    } else {
                        $('.is_up').hide();
                        $('#channel_price').numberbox('enable');
                    }
                    parent_id = selected?selected.parent_id:0;
                }
            },
            onSelect:function (node) {
                //判断是否是黄金
                //var tree = $('#category_id').combotree('tree');
                //var parent = tree.tree('getParent', node.target);
                if (node.parent_id == 797) {
                    if (parent_id != 797) {
                        $('#channel_is_up').attr('checked',false);
                        $('#channel_price').numberbox('enable');
                        $('#channel_up_price').numberbox({required: false});
                        $('.is_up').show();
                    }
                } else {
                    $('.is_up').hide();
                    $('#channel_price').numberbox('enable');
                }
                parent_id = node.parent_id;
            }
        });

    $('#channel_is_up').change(function () {
        if (this.checked) {
            $('#channel_price').numberbox('disable');
            $('#channel_up_price').numberbox({required: true});
        } else {
            $('#channel_price').numberbox('enable');
            $('#channel_up_price').numberbox({required: false});
        }
    });
});


if (typeof clearForm !== 'function') {
    function clearForm() {
        $('#ff').form('clear');
    }
}

if (typeof clearAttrForm !== 'function') {
    function clearAttrForm() {
        $('#attr').form('clear');
    }
}

function getListUrl() {
    var url = location.search;
    var params = url.split('&');
    var params_a = params[2].split('=');
    var uri = url.replace(params_a[1], 'list');
    if (params.length>3){
        return uri.substr(0, uri.lastIndexOf('&'));
    }
    return uri;
}

