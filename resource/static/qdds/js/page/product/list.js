/***
 * 商品列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields = [[{
    field: 'id',
    width: 80,
    sortable: true,
    title: 'ID'
}, {
    field: 'self_code',
    width: 130,
    title: '商品编号'
}, {
    field: 'custom_code',
    width: 130,
    title: '自定义码'
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
    width: 100,
    title: '分类'
}, {
    field: 'market_price',
    sortable: true,
    width: 100,
    title: '公价'
}, {
    field: 'sale_price',
    sortable: true,
    width: 100,
    title: '销售价'
}, {
    field: 'channel_price',
    sortable: true,
    width: 100,
    title: '渠道价'
}, {
    field: 'purchase_price',
    sortable: true,
    width: 100,
    title: '采购价'
}, {
    field: 'stock',
    sortable: true,
    width: 100,
    title: '库存'
}, {
    field: 'on_status_txt',
    width: 100,
    title: '商城状态'
}, {
    field: 'channel_status_txt',
    width: 100,
    title: '渠道状态'
}, {
    field: 'is_supplement_info_txt',
    width: 90,
    title: '是否补全信息'
}, {
    field: 'appraisal_status_txt',
    width: 110,
    title: '是否含有鉴定证书'
}, {
    field: 'created_at',
    sortable: true,
    width: 130,
    title: '添加时间'
}, {
    field: 'operate',
    title: '操作',
    width: 550,
    align: 'left',
    formatter: function (value, row, index) {
        var str = '<input type="button" onclick="location.href=\'/?m=Product&c=Product&a=detail&id=' + row.id + '\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';

        str += '<input type="button" onclick="location.href=\'/?m=Product&c=Product&a=edit&id=' + row.id + '\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';
        if (row.on_status == 1) {
            str += '<input type="button" onclick="location.href=\'/?m=Product&c=Product&a=onstatus&channel=1&id=' + row.id + '\'"  class="easyui-linkbutton" data-options="selected:true" value="商城上架" >';
        }
        if (row.channel_status == 1 && row.is_purchase != 3) {
            str += '<input type="button" onclick="location.href=\'/?m=Product&c=Product&a=onstatus&channel=2&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="渠道上架" >';
        }

        if (row.is_purchase != 3) {
        	str += '<input type="button" onclick="location.href=\'/?m=Product&c=Product&a=stock&id=' + row.id + '\'"  class="easyui-linkbutton" data-options="selected:true" value="调库存" >';
        }
        
        if (row.is_ok_appraisal == '1') {
	        if (row.appraisal_status == '2') {
	        	str += '<input type="button" onclick="javascript:editStatus(' + row.id + ',\'取消鉴定证书\',\'1\');"  class="easyui-linkbutton" data-options="selected:true" value="取消鉴定证书" >';
	        } else {
	        	str += '<input type="button" onclick="javascript:editStatus(' + row.id + ',\'设置鉴定证书\',\'2\');"  class="easyui-linkbutton" data-options="selected:true" value="设置鉴定证书" >';
	        }
        }
        
        if (row.on_status == 1) {
            str += '<input type="button" onclick="javascript:delproduct(' + row.id + ');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';
        }
        return str;
    }
}
]];


function editStatus(id,name,type) {
	$.messager.confirm('温馨提示', '您确定要'+name+'吗?',function(res){
		if (res == true) {
			AppraisalEdit(id,type);
		}
	})
}


function AppraisalEdit(id,type) {
	$.ajax({
        type: "POST",
        async:true,
        url: location.href+'&format=add_appraisal&status='+type,
        dateType: "json",
        data: 'product_id='+id, 
        success:function(data){
        	$.messager.alert('提示', data.msg);
            if (data.code == '200') {
            	$('.query').trigger('click');
            }
        }
    });
}




function delproduct(id) {
    deleteInfo("/?m=Product&c=Product&a=delete&id=" + id, "/index.php?m=Product&c=Product&a=list");
}

// function getQueryData() {
//     var queryData = new Object();
//     queryData['info[name]'] = $('#name').val();
//     queryData['info[self_code]'] = $('#code').val(),
//     queryData['info[custom_code]'] = $('#custom_code').val();
//     queryData['info[is_supplement_info]'] = $('#is_supplement_info').val(),
//     queryData['info[appraisal_status]'] = $('#appraisal_status').val();
// 	var $input = $('input[name="info[brand_id][]"]');
//     if ($input.length >1){
//         var inputVal = [];
//         $input.each(function () {
//             if ($(this).val()) {
//                 inputVal.push($(this).val()) ;
//             }
//         });
//         queryData['info[brand_id][]'] = inputVal;
//     }else{
//     	queryData['info[brand_id][]'] = $input.val();
//     }
//     queryData['info[on_status]'] = $('#on_status').val();
//     queryData['info[channel_status]'] = $('#channel_status').val();
//     queryData['info[admin_name]'] = $('#opera_name').val();
//     queryData['info[start_time]'] = $('#start_time').datebox('getValue');
//     queryData['info[end_time]'] = $('#end_time').datebox('getValue');
//     queryData['info[multi_point_id]'] = $('#multi_point_id').val();
//     var $input = $('input[name="info[category_id][]"]');
//     if ($input.length >1){
//         var inputVal = [];
//         $input.each(function () {
//             if ($(this).val()) {
//                 inputVal.push($(this).val()) ;
//             }
//         });
//         queryData['info[category_id][]'] = inputVal;
//     }else{
//     	queryData['info[category_id][]'] = $input.val();
//     }
//     return queryData;
// }
//
// function searchInfo() {
//     $('#dg').datagrid({
//         title: '',
//         width: '100%',
//         height: 'auto',
//         nowrap: true,
//         autoRowHeight: true,
//         striped: true,
//         url: location.href + "&format=list",
//         singleSelect: true,
//         idField: 'id',
//         loadMsg: '数据加载中......',
//         pageList: [10, 20, 50],
//         columns: fields,
//         pagination: true,
//         rownumbers: true,
// 		checkOnSelect:false,
//         queryParams: getQueryData()
//     });
//
// }
//
// $(function () {
//     searchInfo();
// })
//
// $(".more").click(function () {
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });


//下载
var progressTimer = '';
var value = 1;
var filename = '1';

function download_data() {
    if (filename == '1') {
        filename = new Date().getTime();
    }
    $('#dialog_html').dialog({
        title: '导出',
        width: 400,
        height: 200,
        top: 100,
        closed: false,
        cache: false,
        content: '<div style="margin:20px">' +
            '<div style="margin:20px;text-align: center;">文件生成中</div>' +
            '<div id="progressbar" style="margin:20px"></div>' +
            '<div class="opt-buttons" style="margin:20px;text-align: center;">' +
            '<a href="' + location.href + '&format=download&filename=' + filename + '" class="easyui-linkbutton" data-options="selected:true" disabled id="download_btn">下载</a>' +
            '<a href="javascript:;" onclick="$(\'#dialog_html\').dialog(\'close\');" class="easyui-linkbutton" style="margin-left:10px;">取消</a>' +
            '</div>' +
            '</div>',
        modal: true
    });
    $('#progressbar').progressbar({
        value: value
    });

    if (value == 1) {
        var form_id = $('.query').parents("form").attr("id");
        if (!form_id) form_id = $('.query').parents(".easyui-panel").attr("id");
        var data = QDDGrid.getFormParams(0,form_id,location.href + '&format=list');
        data['filename'] = filename;
        $.ajax({
            url: location.href + '&format=posdownload',
            type: 'post',
            data: data,
            success: function (res) {
            }
        });

        progressTimer = setInterval(function () {
            getProgress();
        }, 1000);
    }
}

function getProgress() {
    value = $('#progressbar').progressbar('getValue');
    if (value < 100) {
        $.ajax({
            url: location.href + '&format=progress&filename=' + filename,
            type: 'get',
            success: function (res) {
                if (res.code == '200') {
                    if (!res.done) {
                        value += Math.floor(Math.random() * 10);
                        if (value >= 100) {
                            value = 99;
                        }
                    } else {
                        value = 100;
                    }
                    $('#progressbar').progressbar('setValue', value);
                }
            }
        })
    } else {
        $('#download_btn').linkbutton('enable');
        clearInterval(progressTimer);
        value = 1;
        filename = '1';
    }
}
