/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'id',
    width : 50,
    title : '编号'
} , {
    field : 'brand_name',
    width : 150,
    title : '品牌'
}, {
    field : 'category_name',
    width : 150,
    title : '分类'
} , {
    field : 'appraisal_material',
    width : 150,
    title : '材质'
}  , {
    field : 'use_time_note',
    width : 150,
    title : '使用时间'
}  , {
    field : 'flaw_txt',
    width : 150,
    title : '瑕疵'
}  , {
    field : 'enclosure_txt',
    width : 150,
    title : '附件'
}  , {
    field : 'appraisal_size',
    width : 150,
    title : '尺寸'
}  , {
    field : 'status_txt',
    width : 100,
    title : '状态'
}, {
    field : 'option_admin_name',
    width : 150,
    title : '添加人'
}, {
    field : 'created_at',
    width : 150,
    title : '添加时间'
} , {
    field:'operate',
    title:'操作',
    width: 380,
    align:'left',
    formatter:function(value, row, index){
        var str = '<input type="button" onclick="location.href=\'/?m=Appraisal&c=Appraisal&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';

        if (row.appraisal_status == '10') {
        	str += '<input type="button" onclick="unstatus('+row.id+')"  class="easyui-linkbutton" data-options="selected:true" value="取消开具证书" >';	
        }
        
        if (row.appraisal_status == '10' || row.appraisal_status == '15') {
            str += '<input type="button" onclick="$(\'#examine\').window(\'open\');$(\'#examine_id\').val('+row.id+')"  class="easyui-linkbutton" data-options="selected:true" value="付款" >';
        }
        
        if (row.appraisal_status == '30') {
        	str += '<input type="button" onclick="location.href=\'/?m=Appraisal&c=Appraisal&a=edit&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="补全资料" >';
        }
        

        if (row.appraisal_status == '50') {
        	str += '<input type="button" onclick="receiving('+row.id+')"  class="easyui-linkbutton" data-options="selected:true" value="收货" >';	
        }

        if (row.appraisal_status != '70') {
        	str += '<input type="button" onclick="location.href=\'/?m=Appraisal&c=Appraisal&a=addproduct&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="补全商品信息" >';	
        }
        
        return str;
    }
}
] ];


function unstatus(id) {
    $.messager.confirm('温馨提示', '您确定取消吗?', function (res) {
        if (res == true) {
            $.ajax({
                type: "POST",
                async: true,  // 设置同步方式
                url: "/?m=Appraisal&c=Appraisal&a=detail&format=cancel&id=" + id,
                dateType: "json",
                success: function (data) {
                    if (data.code == '200') {
                        location.href = "/index.php?m=Appraisal&c=Appraisal&a=list"
                    } else {
                        $.messager.alert('提示', data.msg);
                    }
                }
            });
        }
    });
}

function receiving(id) {
    $.messager.confirm('温馨提示', '您确定要收货吗?', function (res) {
        if (res == true) {
            $.ajax({
                type: "POST",
                async: true,  // 设置同步方式
                url: "/?m=Appraisal&c=Appraisal&a=detail&format=receiving&id=" + id,
                dateType: "json",
                success: function (data) {
                    if (data.code == '200') {
                        location.href = "/index.php?m=Appraisal&c=Appraisal&a=list"
                    } else {
                        $.messager.alert('提示', data.msg);
                    }
                }
            });
        }
    });
}

//
// function searchInfo() {
//     var queryData = new Object();
//     queryData['info[self_code]'] = $('#self_code').val();
//     queryData['info[product_name]'] = $('#product_name').val();
//     queryData['info[appraisal_status]'] = $('#appraisal_status').val();
//     queryData['info[option_admin_name]'] = $('#option_admin_name').val();
//     queryData['info[start_time]'] = $('#start_time').datebox('getValue');
//     queryData['info[end_time]'] = $('#end_time').datebox('getValue');
//
//     var $input = $('input[name="info[brand_id][]"]');
//     if ($input.length >1){
//         var inputVal = [];
//         $input.each(function () {
//             if ($(this).val() && !isNaN($(this).val())) {
//                 inputVal.push($(this).val()) ;
//             }
//         });
//         queryData['info[brand_id][]'] = inputVal;
//     }else{
//         queryData['info[brand_id][]'] = $input.val();
//     }
//
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
//         queryData['info[category_id][]'] = $input.val();
//     }
//
//     $('#dg').datagrid({
//         title:'',
//         width:'100%',
//         height:'auto',
//         nowrap: true,
//         autoRowHeight: true,
//         striped: true,
//         url: '/index.php?m=Appraisal&c=Appraisal&a=list&format=list',
//         remoteSort: true,
//         singleSelect:true,
//         idField:'id',
//         loadMsg:'数据加载中......',
//         pageList: [10,20,50],
//         columns: fields,
//         pagination:true,
//         rownumbers:true,
//         queryParams:queryData
//     });
//
// }
// $(function(){
//     searchInfo();
// })
//
// $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });



$(function(){

    $('#ff2').form({
        success:function(data){
            var data = JSON.parse(data);
            if (data.code == '200') {
                location.href="/index.php?m=Appraisal&c=Appraisal&a=list"
            } else {
                $.messager.alert('提示', data.msg);
            }
        }
    });
});













