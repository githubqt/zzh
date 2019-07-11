/***
 * 商品列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields = [[{
    field: 'id',
    width: 70,
    sortable: true,
    title: 'ID'
}, {
    field: 'self_code',
    width: 200,
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
    width: 100,
    sortable: true,
    title: '公价'
}, {
    field: 'sale_price',
    width: 100,
    sortable: true,
    title: '销售价'
}, {
    field: 'channel_price',
    width: 100,
    sortable: true,
    title: '渠道价'
}, {
    field: 'stock',
    width: 70,
    sortable: true,
    title: '库存'
}, {
    field: 'on_status_txt',
    width: 90,
    title: '是否上架'
}, {
    field: 'admin_name',
    width: 130,
    sortable: true,
    title: '操作人'
}, {
    field: 'created_at',
    width: 130,
    sortable: true,
    title: '添加时间'
}, {
    field: 'operate',
    title: '操作',
    width: 195,
    align: 'center',
    formatter: function (value, row, index) {

        var str = '<input type="button" onclick="javascript:unstatus(' + row.id + ');"  class="easyui-linkbutton" data-options="selected:true" value="下架" >';
        str += '<input type="button" onclick="javascript:promoteProductnow(' + row.id + ');"  class="easyui-linkbutton" data-options="selected:true" value="推广" >';
        str += "<input type='button'  onclick='javascript:selectCode(" + row.id + ");' class='easyui-linkbutton' data-options='selected:true' value='打印' >";
        return str;
    }
}
]];


function promoteProductnow(id) {

    $('#tuiguang').dialog({
        title: '推广',
        top: 5,
        width: 400,
        height: 600,
        closed: false,
        cache: false,
        href: '/?m=Product&c=Productnow&a=promote&id=' + id+'&is_menu=1',
        modal: true
    });
}


function unstatus(id) {
    $.messager.confirm('温馨提示', '您确定要下架该商品吗?', function (res) {
        if (res == true) {
            $.ajax({
                type: "POST",
                async: true,  // 设置同步方式
                url: "/?m=Product&c=Productnow&a=unstatus&channel=1&id=" + id,
                dateType: "json",
                success: function (data) {
                    if (data.code == '200') {
                        location.href = "/index.php?m=Product&c=Productnow&a=list"
                    } else {
                        $.messager.alert('提示', data.msg);
                    }
                }
            });
        }
    });
}

// function searchInfo() {
//     var queryData = new Object();
//     queryData['info[name]'] = $('#name').val();
//     queryData['info[self_code]'] = $('#code').val(),
//     queryData['info[custom_code]'] = $('#custom_code').val();
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
//     queryData['info[admin_name]'] = $('#opera_name').val();
//     queryData['info[start_time]'] = $('#start_time').datebox('getValue');
//     queryData['info[end_time]'] = $('#end_time').datebox('getValue');
//
//
//     $('#dg').datagrid({
//         title: '',
//         width: '100%',
//         height: 'auto',
//         nowrap: true,
//         autoRowHeight: true,
//         striped: true,
//         url: '/index.php?m=Product&c=Productnow&a=list&format=list',
//         singleSelect: true,
//         idField: 'id',
//         loadMsg: '数据加载中......',
//         pageList: [10, 20, 50],
//         columns: fields,
//         pagination: true,
//         rownumbers: true,
//         queryParams: queryData
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


function preCode() {

    $("#selectCode").dialog('close');

    var id = $('#code_id').val();
    var type = $('#type').val();

    var html = '<div style="margin-left: 60px;margin-top: 40px;"><div style="margin: 10px auto 10px auto;"><input calss="easyui_textbox"   type="hidden" id="id" value="' + id + '" /><input calss="easyui_textbox"   type="hidden" id="type" value="' + type + '" /><a  style="font-size:16px ; color:red;">请输入需要打印的数量！</a></div><input class="easyui-numberbox"  data-options="required: true" type="text" id="num"    style="width:120px; height: 30px;"> 张<input  style="margin-left:10px" type="button"  onclick="javascript:printCode(\'' + id + '\');"  class="easyui-linkbutton" data-options="selected:true" value="确定"/> </div>';

    $('#all').dialog({
        title: '打印数量选择',
        width: 300,
        height: 200,
        closed: false,
        cache: false,
        modal: true,
        content: html
    });

}


function printCode() {

    var num = $("#num").val();

    if (num.length == 0 && num.length < 1) {
        return;
    }

    var id = $('#id').val();
    var type = $('input[name="type"]:checked').val();

    $("#all").dialog('close');


    if (type == 2) {

        var rows = $('#dg').datagrid('getChecked');

        var html = '<div class="row cl">' +
            '<div style="text-align: right;padding: 20px 20px 0 0;" class="no-print">' +
            '<a href="javascript:;" onClick="custom(this);" class="easyui-linkbutton" style="margin-right: 190px;" data-options="selected:true">打印</a>' +
            '</div>' +
            '</div>' +
            '<div id="barcode-print" style="text-align: center;width: 100%;margin: auto;">';

        $.each(rows, function (i, item) {
            var print_num = num;
            if (!print_num) print_num = 1;
            if (print_num <= 0) print_num = 1;
            item.num = print_num;
            var goods_name = item.name.substring(0, 8);
    	    var custom_code = item.custom_code&&item.custom_code!=null?item.custom_code:'';
            html += ' <div class="print-body" style="width:35%; height:60%;margin-left: 155px; margin-top: 9px;padding: 50px  10px 5px 10px;' +
                '	  border:1px #ddd solid;position:relative;" ' +
                ' data-id="' + item.id + '" ' +
                ' data-num="' + item.num + '" ' +
                ' data-self_code="' + item.self_code + '" ' +
                ' data-name="' + goods_name + '" ' +
                '  data-sale_price="' + item.gold_sale_price + '"' +
          	  	'  data-custom_code="'+custom_code+'">'+
                ' <div class="print-table">' +
                ' <div>' +
                '<p id="rot90" style="margin-left: 113px;margin-right: 123px;font-size: 14px;width: 100px;margin-top: 26px;">'+ goods_name +'</p> '+
  			  '<p id="rot90" style="margin-left: 54px;margin-right: -45px;font-size: 14px;margin-top: 8px;">¥'+ item.gold_sale_price +'</p> '+
  			  '<p id="rot90" style="margin-left: -23px;font-size: 9px;height: 164px;width: 114px;margin-top: -126px;">'+company+'</p> '+
  			  '<img  style="width:67px;height:67px;float: right;margin-top: -145px;margin-right: -6px;" src="/index.php?c=Public&a=qcode&content='+m_url+'details?id='+item.id+'">'+
                ' </div>' +
	      		  '<div>'+ 
		  		  	'<div id="rot270" style="margin-left: -165px;margin-top: -88px;">'+ custom_code +'</div> '+
		  		  	'<img  id="rot270"  style="margin-top: -33px;width:100px;height:50px;margin-left: -100px;" src="/index.php?c=Public&a=barcode&content='+item.self_code+'">'+ 
		  		    '<p id="rot90" style="margin-top: 32px;margin-left: -25px;"></p>'+
		  		  '</div>'+
		  		'</div>'+
                ' <div style="border-top:1px #ddd solid;margin-top: 111px;background:#fff;padding-top:6px;text-align:center;">打印数量<span style="font-size:18px; color:#ff0000;">' + item.num + '</span>张</div></div>';
        });
        html += '</div>';

        $('#previewCode').dialog({
            title: '打印预览',
            width: 500,
            height: 350,
            closed: false,
            cache: false,
            modal: true,
            content: html,

        });


    } else if (type == 1) {


        $('#preview').dialog({
            title: '打印预览',
            width: 500,
            height: 300,
            closed: false,
            cache: false,
            href: '/?m=Product&c=Productnow&a=print&id=' + id + '&num=' + num+'&is_menu=1',
            modal: true,

        });
    }
}


function selectCode(id) {

    try {
        LODOP = getLodop();

        if (LODOP === undefined){
            throw '打印控件未安装';
        }

        var html = '<div style="margin-left: 15px;margin-top: 20px;">'+
			        	'<div style="margin: 10px auto 10px auto;">'+
			        		'<a  style="font-size:16px ; color:red;">请选择打印商品标签类型！</a>'+
				        '</div>'+

		        		'<div class="print-body" id="div1" style="width:46%;margin-top: 60px;float:left; padding: 5px 10px 5px 10px; border:1px #ddd solid;position:relative;" >'+
					        '<div class="print-table">'+
						        '<table cellpadding="0" cellspacing="0" class="maintable" style="">'+
							        '<tbody>'+
								        '<tr>'+
									        '<td style="font-size:15px;width:60%;"><b>商品名称</b></td>'+
									        '<td rowspan="2" style="width:40%;"><img width="100" height="100" src="/index.php?c=Public&amp;a=qcode&amp;content=http://test.testm.qudiandang.com/details?id=122"></td>'+
									    '</tr>'+
									    '<tr>'+
									        '<td style="font-size:20px;"><b>¥998.00</b></td>'+
									    '</tr>'+
									    '<tr>'+
									        '<td rowspan="2"><img width="100" height="50" src="/index.php?c=Public&amp;a=barcode&amp;content=10001809300054"></td>'+
									        '<td style="font-size:12px;">电话010-85769551</td>'+
									    '</tr>'+
									    '<tr>'+
									        '<td style="font-size:12px;"><b>北京扎呵呵科技有限公司</b></td>'+
								        '</tr>'+
							        '</tbody>'+
						        '</table>'+
					        '</div>'+
				        '</div>'+
				        '<div style="position: absolute;top: 326px;left: 100px;"><input type="radio" name="type" value="1" checked id="type-1"> <label for="type-1">打印柜台条码</label></div>'+
		        		'<div id="barcode-print" style="text-align: center;width: 96%;margin: auto;">'+ 
				        '<div class="print-body" style="margin-left: 15px;width:36%; height:45%;float:left; padding: 5px 10px 5px 10px; border:1px #ddd solid;position:relative;" > '+
					        '<div class="print-table">'+
						        '<div >  '+
							        '<p id="rot90" style="width: 77px;font-size: 16px;position: absolute;left: 161px;top: 111px;">商品名称</p> '+
							        '<p id="rot90" style="font-size: 16px;position: absolute;left: 140px;top: 103px;">¥998.00</p> '+
							        '<p id="rot90" style="font-size: 11px;position: absolute;left: 71px;top: 78px;">北京扎呵呵科技有限公司</p> '+
							        '<img style="width:71px;height:71px;float: right;left: 149px;position: absolute;top: 8px;" src="/index.php?c=Public&amp;a=qcode&amp;content=10001809300054">'+
							    '</div>'+
							    '<div style="border-left: 2px solid;height: 100%;position: absolute;left: 111px;top: 0px;"></div>'+ 
						        '<div style="width:50px"> '+
						        	'<p id="rot270" style="margin-top: 46px;width: 134px;height: 68px;margin-left: -38px;">8465168456486</p>'+
					        		'<img id="rot270" style="margin-top: -48px;width: 170px;height: 68px;margin-left: -35px;" src="/index.php?c=Public&amp;a=barcode&amp;content=10001809300054"> '+
					        		'<p id="rot90" style="margin-top: 32px;margin-left: -25px;"></p>'+
						        '</div> '+
						    '</div> '+
					    '</div>'+
					    '<div style="position: absolute;border: 1px solid #ccc;width: 45px;height: 11px;left: 543px;top: 189px;border-top-right-radius: 5px;border-bottom-right-radius: 5px;"></div>'+
				        '<div style="position: absolute;top: 326px;left: 380px;"><input type="radio" name="type" value="2" id="type-2"> <label for="type-2">打印标签条码</label></div>'+
					    '<div style="width: 598px;height: 90px;border-top: 1px solid #ccc;position: absolute;top: 404px;margin-left: -27px;">'+
					    	'<input calss="easyui_textbox"   type="hidden" id="code_id" value="' + id + '" />'+
					    	'<input  style="margin-left: 110px;float: left;width: 192px;margin-top: 20px;background: rgba(243, 207, 123, 1);border: 1px solid rgba(243, 207, 123, 1);color: #000;" type="button" onClick="preCode();"   class="easyui-linkbutton" data-options="selected:true" value="确定"/> '+
					    	'<input  style="margin-left:30px;float:left;width: 192px;margin-top: 20px;" type="button" onClick="$(\'#selectCode\').dialog(\'close\');"   class="easyui-linkbutton"  value="取消"/> '+
					    '</div>'+
			        '</div>';
        $('#selectCode').dialog({
            title: '选择打印类型',
            width: 600,
            height: 500,
            closed: false,
            cache: false,
            modal: true,
            content: html
        });

    } catch (e) {
        console.error(e);
    }


}






//选择打印机
function custom(obj) {
    LODOP = getLodop();
    LODOP.PRINT_INIT("");
    LODOP.ADD_PRINT_RECT("0pt","0pt","0pt","0pt",0,0);
    LODOP.ADD_PRINT_HTML(10, 15, "100%", "100%");
    $(obj).linkbutton('disable');

    var firstPage = 0;

    $(".print-body").each(function () {

        var id = $(this).attr('data-id');
        var num = $(this).attr('data-num');
        var self_code = $(this).attr('data-self_code');
        var name = $(this).attr('data-name');
        var sale_price = $(this).attr('data-sale_price');
        var pm_url = m_url + 'details?id=' + id;
        var custom_code = $(this).attr('data-custom_code');

        for (var i = 1; i <= num; i++) {

            if (firstPage != 0) {
                LODOP.NEWPAGEA();
            } else {
                firstPage = 1;
            }

            //字体
            LODOP.SET_PRINT_STYLE("FontName", "微软雅黑");
            //尺寸
            LODOP.SET_PRINT_PAGESIZE(2, "80mm", "37.5mm", "");

            //商品名称 
            LODOP.SET_PRINT_STYLE("FontSize", 7);
            LODOP.ADD_PRINT_TEXT("34.32mm","22.73mm","25mm","5.77mm", name);
    		LODOP.SET_PRINT_STYLEA(0,"Angle",180);
    		
            //销售价
            LODOP.SET_PRINT_STYLE("FontSize", 6);
            LODOP.ADD_PRINT_TEXT("29.71mm","22.73mm","30mm","4.71mm", "￥" + sale_price);
            LODOP.SET_PRINT_STYLE("Bold", 1);
    		LODOP.SET_PRINT_STYLEA(0,"Angle",180);
    		
    		//自定义码
            LODOP.SET_PRINT_STYLE("FontSize", 6);
            LODOP.ADD_PRINT_TEXT("2.3mm","14.47mm","29.42mm","5.27mm", custom_code);
            
            //条码
            LODOP.SET_PRINT_STYLE("FontSize", 6);
            LODOP.ADD_PRINT_BARCODE("4.31mm","8.94mm","25mm","12.01mm", check_code(self_code), self_code);

            //二维码
            LODOP.SET_PRINT_STYLE("FontSize", 8);
            LODOP.ADD_PRINT_BARCODE("23.92mm","22.73mm","12.59mm","11.54mm", 'QRCode', pm_url);
    		LODOP.SET_PRINT_STYLEA(0,"Angle",180);
    		
            //公司名称 
            LODOP.SET_PRINT_STYLE("FontSize", 5);
            LODOP.ADD_PRINT_TEXT("24.87mm","32.7mm","40.01mm","4.89mm", company);
    		LODOP.SET_PRINT_STYLEA(0,"Angle",180);


        }
    });

    //LODOP.SET_PRINT_MODE("CATCH_PRINT_STATUS",true);
    if (LODOP.CVERSION) {
        LODOP.On_Return = function (TaskID, Value) {
            console.info(Value);
        };
         LODOP.PRINTA();
         //   LODOP.  PRINT_DESIGN();
  //      return;
    } else {
        LODOP.PRINTA();
//		 LODOP.  PRINT_DESIGN();
    }
}


//条码检测                
function check_code(a) {
    var b = "Code93";
    a = a + "";
    if (a.length === 18) {
        b = "EAN128C";
    }
    if (a.length === 17) {
        b = "Code93";
    }
    if (a.length === 16) {
        b = "128Auto";
    }
    if (a.length === 15) {
        b = "Code93";
    }
    if (a.length === 13 || a.length === 14) {
        b = "128A";
    }
    return b;
}


