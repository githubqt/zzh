/***
 * 商品列表js
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
var fields =  [ [ {
    field : 'id',
    width : 50,
    title : 'ID'
}, {
    field : 'order_no',
    width : 180,
    title : '主单编号'
}, {
    field : 'child_order_no',
    width : 180,
    title : '子单编号'
}, {
    field : 'child_status_txt',
    width : 100,
    title : '订单状态'
}, {
    field : 'delivery_type_txt',
    width : 100,
    title : '发货类型'
}, {
    field : 'is_channel_txt',
    width : 100,
    title : '订单类型'
}, {
    field : 'child_order_actual_amount',
    width : 100,
    title : '订单金额'
} , {
    field : 'user_name',
    width : 100,
    title : '会员姓名'
} , {
    field : 'user_mobile',
    width : 100,
    title : '会员手机'
} , {
    field : 'created_at',
    width : 140,
    title : '下单时间'
} , {
    field : 'delivery_time',
    width : 140,
    title : '发货时间'
} , {
    field : 'express_no',
    width : 140,
    title : '快递单号'
}  , {
	field:'operate',
	title:'操作',
	width: 240,
	align:'left',
    formatter:function(value, row, index){
		// 查看   所有
        var str = '<input type="button" onclick="location.href=\'/?m=Order&c=Order&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        // 审核   10，21
        if ($.inArray(row.child_status,['10','21'])!=-1) {
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Order&a=check&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="审核" >';
        }
        // 取消	10，11，20
        if ($.inArray(row.child_status,['10','11','20'])!=-1) {
        	str += '<input type="button" onclick="javascript:editStatus('+row.id+',\'取消\');"  class="easyui-linkbutton" data-options="selected:true" value="取消" >';
        }
        // 邀请拼团 22
        if ($.inArray(row.child_status,['22'])!=-1) {
        	str += '<input type="button" onclick="javascript:share('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="邀请拼团" >';
        }
        // 修改	10，11，20，21，30
        // 门店自提订单不修改  delivery_type
        if ($.inArray(row.child_status,['10','11','20','21','30'])!=-1 && row.delivery_type == 0) {
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Order&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="修改" >';
        }
        // 拣货	11，30
		// 供应订单在供应采购单入库完成才能拣货
        if ($.inArray(row.child_status,['11','30'])!=-1) {
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Order&a=picking&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="拣货" >';
        }
        // 发货	40
        // 门店自提订单不发货  delivery_type
        if ($.inArray(row.child_status,['40'])!=-1 && row.delivery_type == 0) {
        	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Order&a=fire&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="发货" >';
        }

        // 待收货 50
        // 门店自提订单可以查看取货码
        if ($.inArray(row.child_status,['50'])!=-1 && row.delivery_type == 1) {
        	str += '<input type="button" onclick="javascript:code('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="自提码" >';
        	str += '<input type="button" onclick="javascript:delivery('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="商家自提" >';
        }

        // 售后	60
        // 已申请的不可申请
        if ($.inArray(row.child_status,['60'])!=-1 && row.is_after_sales == 1 && row.chan_return == 2 && row.discount_type !== '3') {
            	str += '<input type="button" onclick="location.href=\'/?m=Order&c=Order&a=applyReturn&id=' + row.id + '\'"  class="easyui-linkbutton" data-options="selected:true" value="售后" >';
        }

        return str;
    }
}
] ];


function editStatus(id,name) {
	$.messager.confirm('温馨提示', '您确定要'+name+'吗?',function(res){
		if (res == true) {
			cancleOrder(id);
		}
	})
}

function code(id) {
	$('#delivery').dialog({
	    title: '自提码',
        top: 5,
	    width: 400,
	    height: 400,
	    closed: false,
	    cache: false,
	    href: '/?m=Order&c=Order&a=code&id='+id+'&is_menu=1',
	    modal: true
	});	
}

function delivery(id) {
	$('#delivery').dialog({
	    title: '商家自提',
        top: 5,
	    width: 400,
	    height: 400,
	    closed: false,
	    cache: false,
	    href: '/?m=Order&c=Order&a=delivery&id='+id+'&is_menu=1',
	    modal: true
	});	
}

function share(id) {
	$('#delivery').dialog({
	    title: '邀请拼团',
        top: 5,
	    width: 300,
	    height: 400,
	    closed: false,
	    cache: false,
	    href: '/?m=Order&c=Order&a=share&id='+id+'&is_menu=1',
	    modal: true
	});	
}

function cancleOrder(id) {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Order&c=Order&a=cancle&id="+id,
        dateType: "json",
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=Order&c=Order&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[order_no]'] = $('#order_no').val();
// 	queryData['info[child_order_no]'] = $('#child_order_no').val();
// 	queryData['info[user_name]'] = $('#user_name').val();
// 	queryData['info[user_mobile]'] = $('#user_mobile').val();
// 	queryData['info[child_status]'] = $('#child_status').combobox('getValue');
// 	queryData['info[express_no]'] = $('#express_no').val();
// 	queryData['info[created_start_time]'] = $('#created_start_time').datebox('getValue');
// 	queryData['info[created_end_time]'] = $('#created_end_time').datebox('getValue');
// 	queryData['info[delivery_start_time]'] = $('#delivery_start_time').datebox('getValue');
// 	queryData['info[delivery_end_time]'] = $('#delivery_end_time').datebox('getValue');
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Order&c=Order&a=list&format=list',
// 				remoteSort: false,
// 				singleSelect:true,
// 				idField:'id',
// 				loadMsg:'数据加载中......',
// 				pageList: [10,20,50],
// 				columns: fields,
// 				pagination:true,
// 				rownumbers:true,
// 				queryParams:queryData
// 			});
//
// }
// $(function(){
// 	searchInfo();
// });
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });
