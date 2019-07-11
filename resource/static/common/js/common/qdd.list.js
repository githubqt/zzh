/**
 * 公用数据表格操作
 */
$(function () {

    if (typeof fields === "undefined") {
        throw ReferenceError(' 未定义 fields 变量，请定义数据网格（datagrid）的列（column）对象 ');
    }
    /**
     * 定义数据网格（DataGrid）属性
     * @type {{title: string, width: string, height: string, nowrap: boolean, autoRowHeight: boolean, striped: boolean, url: string, remoteSort: boolean, singleSelect: boolean, idField: string, loadMsg: string, pageList: number[], columns: *, pagination: boolean, rownumbers: boolean}}
     */

    var settings = {
        title: '',
        width: '100%',
        height: 'auto',
        nowrap: true,
        autoRowHeight: true,
        striped: true,
        url: location.href + '&format=list',
        remoteSort: true,
        singleSelect: true,
        idField: 'id',
        loadMsg: '数据加载中......',
        pageList: [10, 20, 50],
        columns: fields,
        pagination: true,
        rownumbers: true
    };
    /**
     * 获取列表表格对象
     * @type {jQuery|HTMLElement}
     */
    var $dg = $('#dg');

    // settings.onBeforeLoad = function (param) {
    //     console.log(param);
    // };

    settings.onLoadSuccess = function (data) {
        //更新缓存page
        var options = $dg.datagrid("getPager").data("pagination").options;
        QDDGrid.setPageData(options.pageNumber,options.pageSize,settings.url);
    };

    /**
     * 合并options到settings
     */

    if (typeof options === 'object') {
        $.extend(settings, options);
    }

    /**
     * 初始化列表表格
     */
    function init_params(p){
        var form_id = $('.query').parents("form").attr("id");
        if (!form_id) form_id = $('.query').parents(".easyui-panel").attr("id");
        var params = QDDGrid.getFormParams(p,form_id,settings.url);

        //合并默认必带参数
        if (typeof default_params === 'object') {
            $.extend(params, default_params);
        }
        settings.queryParams = params;
        settings.pageNumber = params.page;
        settings.pageSize = params.rows;
        settings.page = params.page;
        settings.rows = params.rows;
    }

    init_params(0);
    $dg.datagrid(settings);
    /**
     * 查询
     */
    $(".query").click(function () {
        try {
            init_params(1);
            QDDGrid.log('查询时options:', settings);
            $dg.datagrid(settings);
        } catch (e) {
            QDDGrid.error(e);
        }
    });
    /**
     * 重置
     */
    $(".reset").click(function () {
        try {
            var form_id = $('.query').parents("form").attr("id");
            if (!form_id) form_id = $('.query').parents(".easyui-panel").attr("id");
            QDDGrid.clearForm(form_id);
            init_params(1);
            QDDGrid.log('重置时options:', settings);
            $dg.datagrid(settings);
        } catch (e) {
            QDDGrid.error(e);
        }
    });

    /**
     * 展开收起更多
     */
    $(".more").click(function () {
        $(this).closest(".conditions").siblings().toggleClass("hide");
    });

    /**
     * 监听操作按钮点击事件
     */
    $('body').on('click', 'input.easyui-linkbutton[data-action]', function () {
        var action = $(this).data('action');
        var rowIndex = $(this).data('row-index');
        var rows = $('#dg').datagrid('getRows');
        var row = rows[rowIndex];
        QDDGrid.log(action + '操作:', '当前行数据：', row);
        if (typeof handles === 'object') {
            if (typeof handles[action] === 'function'){
                return handles[action](row);
            }else if (typeof handles[action] === 'string') {
                return QDDGrid.redirect(handles[action]);
            }
        }

        var url = location.href.replace('list', action) + '&id=' + row.id;
        switch (action) {
            case 'delete': //删除操作
                QDDGrid.delete(url);
                break;
            case 'detail'://查看详情
            case 'edit'://编辑
            case 'onstatus'://上架
            case 'stock'://调库存
            default:
                QDDGrid.redirect(url);
                break;
        }

    });
});