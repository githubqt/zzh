


var QDDGrid = {};
/**
 * 获取列表查询参数
 * @param p 是否更新缓存数据
 * @param f 表单id
 * @param u 搜索的url
 */
QDDGrid.getFormParams = function (p,f,u) {
    f = f?"#"+f:"";
    var new_params = {};
    if (p == 1) {
        new_params.page = 1;
        new_params.rows = 10;
        //自定义初始值
        var nodes = $('#cf').tree('getSelected');
        if (nodes) {
            new_params['info[parent]'] = nodes.id;
        }
        $(f+" :input[name]").each(function () {
            //获取表单数据
            var inputName = $(this).attr('name');
            var $input = $(f+' input[name="'+inputName+'"]');
            var new_params_value = '';
            if ($input.length >1){
                var inputVal = [];
                $input.each(function () {
                    if ($(this).val()) {
                        inputVal.push($(this).val()) ;
                    }
                });
                new_params_value = inputVal;
            }else{
                new_params_value = $(this).val();
            }
            if (inputName.indexOf('info[') == -1) {
                inputName = 'info['+inputName+']';
            }
            new_params[inputName] = new_params_value;
        });
        //更新本地缓存
        Params.set(u,JSON.stringify(new_params));
    } else {
        //获取本地缓存
        if (Params.get(u)) {
            new_params = JSON.parse(Params.get(u ));
            QDDGrid.setFormParams(f,new_params);
        }
        new_params.page = new_params.page?new_params.page:1;
        new_params.rows = new_params.rows?new_params.rows:10;
    }
    return new_params;

};
/**
 * 设置列表查询参数
 * @param f form_id
 * @param new_params params
 */
QDDGrid.setFormParams = function (f,new_params) {
    jQuery.each(new_params, function(id, val) {
        if (id.indexOf('info[') !== -1) {
            id = id.replace("info[", "");
            if (id.indexOf('[]') == -1) {
                id = id.replace("]", "");
            } else {
                id = id.replace("][]", "");
            }
            if ($(f + ' #' + id)) {
                if ($(f + ' #' + id).attr('class').indexOf('easyui-combobox') !== -1) {
                    $(f + ' #' + id).combobox('setValue', val);//单选框赋值
                } else if ($(f + ' #' + id).attr('class').indexOf('easyui-combotree ') !== -1) {
                    $(f + ' #' + id).combotree('setValues', val);//树形框赋值
                } else {
                    $(f + ' #' + id).textbox('setValue', val);//输入框，数字框，时间框赋值
                }
            }
        }
    });
};
/**
 * 设置列表页码参数
 * @param p page
 * @param r rows
 * @param u 搜索的url
 */
QDDGrid.setPageData = function (p,r,u) {
    //获取本地缓存
    var params = {};
    if (Params.get(u)) {
        params = JSON.parse(Params.get(u));
    }
    params.page = p;
    params.rows = r;
    Params.set(u,JSON.stringify(params));
    return params;
};
/**
 * 渲染操作项
 * @param operator
 * @param row 行的记录数据
 * @param index 行的索引
 * @returns {string}
 */
QDDGrid.renderOperator = function (operator, row, index) {
    var button = '';
    for (var i = 0; i < operator.length; i++) {
        button += '<input type="button" class="easyui-linkbutton" data-options="selected:true" ' +
            'value="' + operator[i].name + '"' +
            'data-action="' + operator[i].action + '" ' +
            'data-row-index="' + index +
            '" >';
    }
    return button;
};
/**
 * 清空清除表单数据
 */
QDDGrid.clearForm = function (f) {
    f=f?f:"ff";
    $("#"+f).form('clear');
};

/**
 * 重定向跳转
 * @param url
 */
QDDGrid.redirect = function (url) {
    QDDGrid.log(url);
    window.location.href = url;
};

/**
 * 删除操作
 * @param url
 */
QDDGrid.delete = function (url, redirectUrl) {
    if (typeof redirectUrl === 'string') {
        deleteInfo(url, redirectUrl);
    } else {
        deleteInfo(url, url.replace('delete', 'list'));
    }
};
/**
 * 普通提示
 */
QDDGrid.log = function () {
    for (var i = 0; i < arguments.length; i++) {
        console.log(arguments[i]);
    }
};
/**
 * 错误提示
 */
QDDGrid.error = function () {
    for (var i = 0; i < arguments.length; i++) {
        console.error(arguments[i]);
    }
};