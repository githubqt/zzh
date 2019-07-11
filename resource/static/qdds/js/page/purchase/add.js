var fields = [[
    {
        field: 'id',
        width: 120,
        checkbox: true,
        sortable: true,
        title: 'ID'
    }, {
        field: 'self_code',
        width: 150,
        title: '商品编号'
    }, {
        field: 'name',
        width: 200,
        title: '商品名称'
    }, {
        field: 'brand_name',
        width: 100,
        title: '品牌'
    }, {
        field: 'category_name',
        width: 180,
        title: '分类'
    }, {
        field: 'market_price',
        width: 100,
        sortable: true,
        title: '公价'
    }, {
        field: 'channel_price',
        width: 100,
        sortable: true,
        title: '供应价'
    }, {
        field: 'stock',
        width: 100,
        sortable: true,
        title: '库存'
    }, {
        field: 'is_return_text',
        width: 100,
        sortable: true,
        title: '是否支持退换货'
    }, {
        field: 'operate',
        title: '操作',
        align: 'center',
        formatter: function (value, row, index) {
            return showOperatorPurchase(value, row, index);
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
function showOperatorPurchase(value, row, index) {

    var operator = [];
    operator.push({'action': 'detail', 'name': '查看'});
    return QDDGrid.renderOperator(operator, row, index);
}

//
var options = {
    url: '/index.php?m=Purchase&c=Purchase&a=add&format=select_product&from=purchase',
    selectOnCheck: true,
    singleSelect: false
};

var handles = {
    // 这里是主订单操作
    detail: function (row) {
        var d = {
            "format":'detail',
            "product_id":row.id
        };
        $.post(location.href,d,function (response) {
            if (response.code === 500){
                return false;
            }
            var data = {
                data: response.data,
                host_file: HOST_FILE,
            };
            $("#purchase_add_detail_dialog").html(template('purchase_add_detail', data));
            $("#purchase_add_detail_dialog").window('open');
        },'json');

    }
};


$(function () {
    //显示选择商品弹窗
    $("#show_purchase_add_select_dialog").on('click', function () {
        $("#purchase_add_select_dialog").window('open');
        $(".reset").trigger('click');
    });
    //关闭选择商品弹窗
    $("#close_purchase_add_select_dialog").on('click', function () {
        $("#purchase_add_select_dialog").window('close');
    });

    /**
     * 提交表单
     */
    $('#ff2').form({
        onSubmit: function () {

            var isValid = $(this).form('validate');
            if (!isValid) {
                $.messager.progress('close');
                return false;
            }

            var products = Product.form();
            if (products.length === 0) {
                $.messager.alert('提示', '请选择商品！');
                return false;
            }

            if (!Product.validNums()) {
                $.messager.alert('提示', Product.err);
                return false;
            }

            $("#hdn-product").val(JSON.stringify(Product.form()));
            $("input[type='submit']").attr('disabled',true);
            return true;
        },
        success: function (data) {
            var data = JSON.parse(data);
            if (data.code == '200') {
                location.href = "?m=Purchase&c=Purchase&a=list";
            } else {
                $.messager.alert('提示', data.msg);
                $("input[type='submit']").attr('disabled',false);
            }
        }
    });


    //保存
    $("#save").on('click', function () {
        var selected = $("#dg").datagrid("getChecked");//获取的是数组，多行数据

        if (selected.length == 0) {
            $.messager.alert('提示', '请选择商品！');
            return;
        }
        selected.forEach(function (item) {
            Product.add(item);
        });
        showSelectedList();
    });

    $tableBody = $("#tbody");
    //更新数量
    $tableBody.on('change', 'input.update-item', function () {
        var index = $(this).data('index-id');
        var id = $(this).data('item-id');
        var val = $(this).val() || 0;

        Product.update(index, val);
        //更新小计
        var price = Product.rowPrice(index);
        $("span[data-item-id='" + id + "']").html(price.toFixed(2));
        //更新总额
        Product.totalPrice();
        //更新总数
        Product.totalAmount();
        //更新验证
        Product.validNum(id);
    });

    //删除
    $tableBody.on('click', 'input.delete-item', function () {
        var index = $(this).data('index-id');
        Product.delete(index);
        showSelectedList();
        //更新总额
        Product.totalPrice();
        //更新总数
        Product.totalAmount();
    });

});

/**
 * 显示已选商品
 */
function showSelectedList() {
    var data = {
        total: '20000',
        list: Product.data,
        total_amount: Product.totalAmount(),
        total_price: Product.totalPrice(),
        host_file: HOST_FILE,
    };
    var html = template('purchase_add_selected', data);
    $("#tbody").html(html);
    $("#purchase_add_select_dialog").window('close');
}

/**
 * 已选商品
 * @type {{data: Array, add: Product.add, delete: Product.delete, update: Product.update, exist: (function(*): boolean), totalAmount: (function(): number), totalPrice: (function(): number), rowPrice: (function(*): number), form: (function(): Array), validNums: (function(): boolean), validNum: Product.validNum}}
 */
var Product = {
    /**
     * 保存已选商品
     */
    data: [],
    err:'',
    /**
     * 添加商品
     * @param item
     * @returns {boolean}
     */
    add: function (item) {
        if (this.exist(item)) {
            return false;
        }
        item.num = 0;
        this.data.push(item);
    },
    /**
     * 删除商品
     * @param index
     */
    delete: function (index) {
        var newData = [];
        delete this.data[index];
        this.data.forEach(function (item) {
            newData.push(item);
        });
        this.data = newData;
    },
    /**
     * 更新数量值
     * @param index
     * @param val
     */
    update: function (index, val) {
        this.data[index].num = val;
    },
    /**
     * 判断商品是否已添加
     * @param val
     * @returns {boolean}
     */
    exist: function (val) {
        var flag = false;
        this.data.forEach(function (item) {
            if (item.id === val.id) {
                flag = true;
                return;
            }
        });
        return flag;
    },
    /**
     * 计算商品数量总额
     * @returns {number}
     */
    totalAmount: function () {
        var t = 0
        this.data.forEach(function (item) {
            t += parseInt(item.num);
        });
        $(".total_amount").html(t);
        return t;
    },
    /**
     * 计算商品价格总额
     * @returns {number}
     */
    totalPrice: function () {
        var t = 0;
        this.data.forEach(function (item) {
            t += parseInt(item.num) * parseFloat(item.channel_price);
        });
        $(".total_price").html(t.toFixed(2));
        return t;
    },
    /**
     * 计算单个商品价格小计
     * @param index
     * @returns {number}
     */
    rowPrice: function (index) {
        return this.data[index].num * this.data[index].channel_price;
    },
    /**
     * 提交的form数据
     * @returns {Array}
     */
    form: function () {
        var form = [];
        this.data.forEach(function (item) {
            form.push({
                id: item.id,
                num: item.num
            });
        });
        return form;
    },
    /**
     * 验证所选商品数量
     * @returns {boolean}
     */
    validNums: function () {
        try{
            this.data.forEach(function (item) {
                var stock = parseInt($("#stock_"+item.id).html());
                var $input = $("input[data-input='" + item.id + "']");
                if (item.num >= 1) {
                    if (item.num > stock){
                        $input.addClass('textbox-invalid').focus();
                        throw new Error('数量不能超过库存数');
                    }else{
                        $input.removeClass('textbox-invalid');
                    }
                } else {
                    $input.addClass('textbox-invalid').focus();
                    throw new Error('请输入数量');
                }
            });
            return true;
        }catch (e) {
            this.err = e.message;
            return false;
        }
    },
    /**
     * 验证单个商品数量
     * @param id
     * @returns {boolean}
     */
    validNum: function (id) {
        var $input = $("input[data-input='" + id + "']");
        var stock = $input.data('stock');
        if ($input.val() >= 1) {
            if ($input.val() > stock){
                $.messager.alert('提示', '数量不能超过库存数');
                $input.addClass('textbox-invalid').focus();
                return false;
            }else{
                $input.removeClass('textbox-invalid');
                return false;
            }
        } else {
            $input.addClass('textbox-invalid').focus();
            return false;
        }
    },
};

