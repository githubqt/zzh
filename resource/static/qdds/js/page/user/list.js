/***
 * 退货订单js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-18
 */
var fields =  [ [ {
    field : 'id',  
    width : 60,  
    title : 'ID'  
}, {  
    field : 'name',  
    width : 100,  
    title : '姓名'  
}, {  
    field : 'sex_name',  
    width : 60,  
    title : '性别'  
}, {  
    field : 'identity_txt',  
    width : 100,   
    title : '会员等级' , 
    align :'content'
} ,{  
    field : 'mobile',  
    width : 102,   
    title : '手机号'  
} , {  
    field : 'email',  
    width : 140,   
    title : '邮箱'  
} ,{  
    field : 'status_name',  
    width : 100,   
    title : '状态'  
} , {  
    field : 'first_pay_at',  
    width : 140,   
    title : '首次消费时间'  
} , {  
    field : 'created_at',  
    width : 150,   
    title : '注册时间'  
} , {
	field:'operate',
	title:'操作',
	width: 315,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=User&c=User&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        str += '<input type="button" onclick="location.href=\'/?m=User&c=User&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >'; 
        
        str += '<input type="button" onclick="location.href=\'/?m=User&c=User&a=editPassword&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="修改密码" >';
        if (row.status == '2') {
        	str += '<input type="button" onclick="editStatus('+row.id+',1,\'禁用\')"  class="easyui-linkbutton" data-options="selected:true" value="禁用" >';  
        } else {
        	str += '<input type="button" onclick="editStatus('+row.id+',2,\'启用\')"  class="easyui-linkbutton" data-options="selected:true" value="启用" >';     
        }
        str += '<input type="button" onclick="javascript:promoteUser('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="推广" >';
         return str;  
    }
}  
] ];

function editStatus(id,type,name) {
	$.messager.confirm('温馨提示', '您确定要'+name+'吗?',function(res){
		if (res == true) {
			refund(id,type)
		}
	});
}


function refund(id,type) {
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=User&c=User&a=delete&id="+id,
        dateType: "json",
        data:'status='+type,
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=User&c=User&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });	
}



//推广
function promoteUser(id) {
	$('#tuiguang').dialog({
	    title: '推广会员',
	    width: 600,
	    height: 400,
	    top:0,
	    closed: false,
	    cache: false,
	    href: '/?m=User&c=User&a=promote&id='+id+'&is_menu=1',
	    modal: true
	});	
}


//
// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[user_mobile]'] = $('#mobile').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[user_name]'] = $('#user_name').val();
// 	queryData['info[email]'] = $('#email').val(),
// 	queryData['info[sex]'] = $('#sex').val();
// 	queryData['info[id]'] = $('#ID').val();
// 	queryData['info[first_pay_start_time]'] = $('#first_pay_start_time').datebox('getValue');
// 	queryData['info[first_pay_end_time]'] = $('#first_pay_end_time').datebox('getValue');
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue');
// 	queryData['info[grade_id]'] = $('#grade_id').val();
//
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=User&c=User&a=list&format=list',
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
// })
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });
