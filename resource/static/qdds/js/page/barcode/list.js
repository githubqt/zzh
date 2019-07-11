/***
 * 商品列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields =  [ [ {
    field : 'id',  
    width : 50,  
    title : 'ID',
    checkbox : true,   
} , {
	field:'operate',
	title:'打印数量<input id="all" style="width:50px;">同步',
	width: 140,
	align:'left', 
	CheckOnSelect:false, 
    formatter:function(value, row, index){  
        var str = '<input type="text" class="easyui-textbox print_num" id="print_num_'+row.id+'" style="width:100px;height:26px;line-height:26px;">';
		return str;  
    }     
}, {  
    field : 'self_code',  
    width : 120,  
    title : '商品编码'  
}, {  
    field : 'name',  
    width : 150,  
    title : '商品名称'  
}, {  
    field : 'brand_name',  
    width : 100,   
    title : '品牌'  
}, {  
    field : 'category_name',  
    width : 100,   
    title : '分类'  
} , {  
    field : 'market_price',  
    width : 100,   
    title : '公价'  
} , {  
    field : 'sale_price',  
    width : 100,   
    title : '销售价'  
} , {  
    field : 'channel_price',  
    width : 100,   
    title : '渠道价'  
} , {  
    field : 'stock',  
    width : 100,   
    title : '库存'  
}  , {  
    field : 'on_status_txt',  
    width : 90,   
    title : '是否上架'      
}  , {  
    field : 'created_at',  
    width : 130,   
    title : '添加时间' 
}  
] ];

var options = {
    url: '/index.php?m=Marketing&c=Barcode&a=list&format=list',
    singleSelect:false,
    onLoadSuccess:function (data) {
        $('#all').switchbutton({
            checked: false,
            onText: '是',
            offText: '否',
            onChange: function(checked){
                allselect(checked);
            }
        });
        //更新缓存page
        var options = $('#dg').datagrid("getPager").data("pagination").options;
        QDDGrid.setPageData(options.pageNumber,options.pageSize,location.href + '&format=list');
    }
};

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[self_code]'] = $('#code').val();
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
// 	queryData['info[on_status]'] = $('#on_status').val();
// 	queryData['info[admin_name]'] = $('#opera_name').val();
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue');
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Marketing&c=Barcode&a=list&format=list',
// 				remoteSort: false,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				pageList: [100,200,500],
// 				pageSize: 100,
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:true,
// 				checkOnSelect:false,
// 				selectOnCheck:false,
// 				singleSelect:false,
// 				queryParams:queryData
// 			});
// }

// $('#all').switchbutton({
//     checked: false,
//     onText: '是',
//     offText: '否',
//     onChange: function(checked){
//         allselect(checked);
//     }
// });

//全部相同 
function allselect(op){
   if(op){
   	   //获取第一个选中的
   		var rows = $('#dg').datagrid('getChecked');
   		var first_num = 1;
   		if (rows.length > 0){
   			var first_id = rows[0].id;
   			first_num = $('#print_num_'+first_id).val();
   			if (!first_num) first_num =1;
   			if (first_num <= 0) first_num =1;
   		}  	
   		$('.print_num').each(function(i,item){
   			$(item).val(first_num);
   		});
    }
}

// $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });

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

//选择打印机
function moreprint(obj){
    LODOP=getLodop(); 
    LODOP.PRINT_INIT("");
    LODOP.ADD_PRINT_RECT(0,0,0,0,0,0);

    $(obj).linkbutton('disable');  
             
    var firstPage = 0;
    // 条码样式规格60mm*40mm
	$(".print-body").each(function(){
		
        var id = $( this ) . attr( 'data-id' );
        var num = $( this ) . attr( 'data-num' );
        var self_code = $( this ) . attr( 'data-self_code' );
        var name = $( this ) . attr( 'data-name' );
        var sale_price = $( this ) . attr( 'data-sale_price' );      
        var pm_url = m_url+'details?id='+id;  
        
        for ( var i = 1; i <= num; i++ ) {
        	
            if ( firstPage != 0 ) {
                LODOP . NEWPAGEA();
            } else {
                firstPage = 1;
            }

            //字体
            LODOP . SET_PRINT_STYLE( "FontName", "微软雅黑" );
            //尺寸
            LODOP . SET_PRINT_PAGESIZE( 1, "60mm", "40mm", "" );
           
            //商品名称 
            LODOP . SET_PRINT_STYLE( "FontSize", 8 ); 
            LODOP . ADD_PRINT_TEXT( "6mm", "2mm", "33mm", "14mm", name );
           
            //销售价
            LODOP . SET_PRINT_STYLE( "FontSize", 12);
            LODOP . ADD_PRINT_TEXT( "18mm", "2mm", "33mm", "6mm", "￥" + sale_price );  
			LODOP . SET_PRINT_STYLE( "Bold", 1 );			
            
            //条码
            LODOP . SET_PRINT_STYLE( "FontSize", 7 );
            LODOP . ADD_PRINT_BARCODE( "24mm", '2mm', "33mm", "14mm", check_code(self_code), self_code );
            
            //二维码
            LODOP . SET_PRINT_STYLE( "FontSize", 8 );
            LODOP . ADD_PRINT_BARCODE( "4mm", '36mm', "30mm", "30mm", 'QRCode', pm_url );            
            
            //电话 
            LODOP . SET_PRINT_STYLE( "FontSize", 7 ); 
            LODOP . ADD_PRINT_TEXT( "26mm", "34mm", "28mm", "6mm", '电话'+phone );
            
            //公司名称 
            LODOP . SET_PRINT_STYLE( "FontSize", 7 ); 
            LODOP . ADD_PRINT_TEXT( "32mm", "34mm", "28mm", "6mm", company );                     
            
        } 
	});
	
    //LODOP.SET_PRINT_MODE("CATCH_PRINT_STATUS",true);
	if (LODOP.CVERSION) { 
	    LODOP.On_Return=function(TaskID,Value){console.info(Value);};
	    LODOP.PRINTA();
	    return;
	} else {
	    LODOP.PRINTA();
	}
    
}



function  preCode(){
	
	var rows = $('#dg').datagrid('getChecked');	
	if (rows.length <= 0){
		
		$.messager.alert('提示', '请选择要打印条码的商品');
		return;
	}  
	
    try {
        LODOP = getLodop();

        if (LODOP === undefined){
        	var $fonts = $('body').find('font[color="#ff0d00"]');
            $fonts.hide();
            $.messager.alert('提示',"CLodop云打印服务(localhost本地)未安装启动!点击右侧下载,安装后请刷新页面。");
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
				        '<div style="    position: absolute;top: 326px;left: 100px;"><input type="radio" name="type" value="1" checked id="type-1"> <label for="type-1">打印柜台条码</label></div>'+
		        		'<div id="barcode-print" style="text-align: center;width: 96%;margin: auto;">'+ 
				        '<div class="print-body" style="width:36%; height:45%;float:left; padding: 5px 10px 5px 10px; border:1px #ddd solid;position:relative;" > '+
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
					    	'<input  style="margin-left: 110px;float: left;width: 192px;margin-top: 20px;background: rgba(243, 207, 123, 1);border: 1px solid rgba(243, 207, 123, 1);color: #000;" type="button" onClick="preview();"   class="easyui-linkbutton" data-options="selected:true" value="确定"/> '+
					    	'<input  style="margin-left:30px;float:left;width: 192px;margin-top: 20px;" type="button" onClick="$(\'#preCode\').dialog(\'close\');"   class="easyui-linkbutton"  value="取消"/> '+
					    '</div>'+
			        '</div>';

        $('#preCode').dialog({
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

function preview() {
	$("#preCode").dialog('close');
	var type = $('input[name="type"]:checked').val();
	var rows = $('#dg').datagrid('getChecked');	
	if (rows.length <= 0){
		
		$.messager.alert('提示', '请选择要打印条码的商品');
		return;
	}  
	if(type == 2){
		
		var html = '<div class="row cl">'+
        '<div style="text-align: right;padding: 20px 20px 0 0;" class="no-print">'+
        	'<a href="javascript:;" onClick="custom(this);" class="easyui-linkbutton" data-options="selected:true">打印</a>'+
        '</div>' +
    '</div>'+
    '<div id="barcode-print" style="text-align: center;width: 96%;margin: auto;">';
	
	$.each(rows,function(i,item){
		var print_num = $('#print_num_'+item.id).val();
		if (!print_num) print_num =1;
		if (print_num <= 0) print_num =1;
		item.num = print_num;
	    var goods_name = item.name.substring(0,8);
	    var custom_code = item.custom_code&&item.custom_code!=null?item.custom_code:'';
	    html+= ' <div class="print-body" style="width:15%; height:45%;float:left; padding: 5px 10px 5px 10px;'+
	    '	  border:1px #ddd solid;position:relative;" '+
	   ' data-id="'+item.id+'" '+
	   ' data-num="'+item.num+'" '+
	   ' data-self_code="'+item.self_code+'" '+
	   ' data-name="'+goods_name+'" '+
	  '  data-sale_price="'+item.gold_sale_price+'"'+
	  '  data-custom_code="'+custom_code+'">'+
	  ' <span class="icon-clear del_img" onclick="del_btn(this)">&nbsp;&nbsp;</span>'+
	  ' <div class="print-table">'+
		  '<div>'+
			  '<p id="rot90" style="margin-left: 84px;margin-right: 123px;font-size: 14px;width: 100px;margin-top: 114px;">'+ goods_name +'</p> '+
			  '<p id="rot90" style="margin-left: 39px;margin-right: -47px;font-size: 14px;margin-top: 13px;">¥'+ item.gold_sale_price +'</p> '+
			  '<p id="rot90" style="margin-left: -47px;font-size: 9px;height: 164px;width: 114px;margin-top: -164px;">'+company+'</p> '+
			  '<img  style="width:67px;height:67px;float: right;margin-top: -148px;margin-right: -12px;" src="/index.php?c=Public&a=qcode&content='+m_url+'details?id='+item.id+'">'+
		  '</div>'+
		  '<div>'+ 
		  	'<div id="rot270" style="margin-left: -148px;margin-top: -192px;">'+ custom_code +'</div> '+
		  	'<img  id="rot270"  style="margin-top: 69px;width:100px;height:50px;margin-left: -20px;" src="/index.php?c=Public&a=barcode&content='+item.self_code+'">'+ 
		    '<p id="rot90" style="margin-top: 32px;margin-left: -25px;"></p>'+
		  '</div>'+
	  '</div>'+
	  '<div style="border-top:1px #ddd solid;margin-top: 62px;background:#fff;padding-top: 8px;text-align:center;height: 50px;">打印数量<span style="font-size:18px; color:#ff0000;">'+item.num+'</span>张</div></div>';	
	});
	 html+= '</div>';
	
	$('#preview').dialog({
	    title: '打印预览',
	    width: 1000,
	    height: 500,
	    closed: false,
	    cache: false,
	    modal: true,
	    content: html
	});
		
		
	} else {
		var html = '<div class="row cl">'+
	        '<div style="text-align: right;padding: 20px 20px 0 0;" class="no-print">'+
	        	'<a href="javascript:;" onClick="moreprint(this);" class="easyui-linkbutton" data-options="selected:true">打印</a>'+
	        '</div>' +
	    '</div>'+
	    '<div id="barcode-print" style="text-align: center;width: 96%;margin: auto;">';
		
		$.each(rows,function(i,item){
			var print_num = $('#print_num_'+item.id).val();
			if (!print_num) print_num =1;
			if (print_num <= 0) print_num =1;
			item.num = print_num;
		    var goods_name = item.name.substring(0,30);
		    html+= '<div class="print-body" id="div1" style="width:28%;float:left; padding: 5px 10px 5px 10px; border:1px #ddd solid;position:relative;" '+ 
			    'data-id="'+item.id+'" '+
			    'data-num="'+item.num+'" '+
			    'data-self_code="'+item.self_code+'" '+
			    'data-name="'+goods_name+'" '+
			    'data-sale_price="'+item.gold_sale_price+'">'+
		        '<span class="icon-clear del_img" onclick="del_btn(this)">&nbsp;&nbsp;</span>'+
		        '<div class="print-table"><table cellpadding="0" cellspacing="0" class="maintable" style="">'+
		            '<tbody>'+
		                '<tr>'+
		                    '<td style="font-size:15px;width:60%;"><b>'+ goods_name +'</b></td>'+
		                    '<td rowspan="2" style="width:40%;">'+
		                        '<img width="100" height="100" src="/index.php?c=Public&a=qcode&content='+m_url+'details?id='+item.id+'">'+
		                    '</td>'+
		                '</tr>'+
		                '<tr>'+
		                    '<td style="font-size:20px;"><b>¥'+ item.gold_sale_price +'</b></td>'+
		                '</tr>'+
		                '<tr>'+
		                    '<td rowspan="2">'+
		                        '<img width="100" height="50" src="/index.php?c=Public&a=barcode&content='+item.self_code+'">'+
		                    '</td>'+	                    
		                    '<td style="font-size:12px;">电话'+phone+'</td>'+
		                '</tr>'+
		                '<tr>'+
		                    '<td style="font-size:12px;"><b>'+company+'</b></td>'+
		                '</tr>'+	                
		            '</tbody>'+
		    '</table></div>'+
		   '<div style="border-top:1px #ddd solid;padding-bottom:6px;margin-top:6px;background:#fff;padding-top:6px;text-align:center;">打印数量'+
		   '<span style="font-size:18px; color:#ff0000;">'+item.num+'</span>张</div>'+
		'</div>';
		});
	    html+='</div>';	
		
		$('#preview').dialog({
		    title: '打印预览',
		    width: 1000,
		    height: 500,
		    closed: false,
		    cache: false,
		    modal: true,
		    content: html
		});
		
		
		
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
	    //LODOP.  PRINT_DESIGN();
        return;
    } else {
        LODOP.PRINTA();
//		 LODOP.  PRINT_DESIGN();
    }
}

//删除小票
function del_btn(e){
   $(e).parents('.print-body').remove();    
}   

