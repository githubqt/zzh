/***
 * 优惠券js
 * @version v0.01
 * @author lqt
 * @time 2018-05-21
 */
var fields =  [ [ {
    field : 'id',  
    width : 60,  
    sortable:true,
    title : 'ID'  
}, {  
    field : 'name',  
    width : 150,  
    title : '优惠券名称'  
}, {  
    field : 'use_type_txt',  
    width : 100,  
    title : '优惠券类型'  
}, {  
    field : 'content',  
    width : 150,   
    title : '优惠内容'  
} , {  
    field : 'give_num',  
    width : 100,  
    sortable:true,
    title : '已领取'  
} , {  
    field : 'remain_num',  
    width : 100,   
    sortable:true,
    title : '剩余' 
} , {  
    field : 'use_num',  
    width : 100,   
    sortable:true,
    title : '已使用'  
} , {  
    field : 'status_txt',  
    width : 100,   
    title : '优惠券状态'  
} , {
	field:'operate',
	title:'操作',
	width: 250,
	align:'left', 
    formatter:function(value, row, index){  
		//status 1 待审核： 查看，审核，编辑
		//status 2 进行中： 查看，推广，停止 ,编辑
		//status 3 已失效： 查看
		//status 4 已过期： 查看，停止 	
        var str = '<input type="button" onclick="location.href=\'/?m=Marketing&c=Coupan&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        if (row.status == 1) {
        	str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Coupan&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';  
        	str += '<input type="button" onclick="javascript:editStatus('+row.id+',1,\'审核\');"  class="easyui-linkbutton" data-options="selected:true" value="审核" >'; 
        }       
        if (row.status == 2) { 
        	str += '<input type="button" onclick="location.href=\'/?m=Marketing&c=Coupan&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';  
        	str += '<input type="button" onclick="javascript:promoteConpan('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="推广" >'; 
        	str += '<input type="button" onclick="javascript:editStatus('+row.id+',2,\'停止\');"  class="easyui-linkbutton" data-options="selected:true" value="停止" >'; 
        }   
        /*if (row.status == 4) { 
        	str += '<input type="button" onclick="javascript:stopConpan('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="停止" >'; 
        }  */            
        return str;  
    }
}  
] ];


function editStatus(id,type,name) {
	$.messager.confirm('温馨提示', '您确定要'+name+'吗?',function(res){
		if (res == true) {
			if (type == '1') {
				checkConpan(id);
			} else if (type == '2') {
				stopConpan(id);
			}
		}
	});
}



//推广
function promoteConpan(id) {
	$('#tuiguang').dialog({
	    title: '推广优惠券',
	    width: 600,
	    height: 400,
	    closed: false,
	    cache: false,
	    href: '/?m=Marketing&c=Coupan&a=promote&id='+id+'&is_menu=1',
	    modal: true
	});	
}
//审核
function checkConpan(id) {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Marketing&c=Coupan&a=check&id="+id,
        dateType: "json",
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=Marketing&c=Coupan&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}
//停止
function stopConpan(id) {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Marketing&c=Coupan&a=stop&id="+id,
        dateType: "json",
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=Marketing&c=Coupan&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[status]'] = $('#status').combobox('getValue');
// 	queryData['info[use_type]'] = $('#use_type').combobox('getValue');
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
// 			    url: '/index.php?m=Marketing&c=Coupan&a=list&format=list',
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
// })
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });
