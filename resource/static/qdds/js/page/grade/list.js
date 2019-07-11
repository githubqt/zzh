/***
 * 退货订单js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-18
 */
  
 
var fields =  [ [ {
    field : 'grade_id',  
    width : 40,  
    align : 'center',
    title : 'ID'  
}, {  
	field : 'grade',  
	width : 175,  
	align : 'center',
	title : '会员等级'  
}, {  
    field : 'identity',  
    width : 175,  
    align : 'center',
    title : '身份称谓'  
}, {  
    field : 'growth',  
    width : 175,  
    align : 'center',
    title : '所需成长值'  
}, {  
    field : 'gift_txt',  
    align : 'center',
    title : '升级礼包'  
} , {  
    field : 'discount_txt',  
    align : 'center',
    title : '会员权益'  
} , {
	field:'operate',
	title:'操作',
	width: 160,
	align : 'center', 
    formatter:function(value, row, index){  
   	  var str="";
	  if(row.type =='1'){
		  str += '<input style="margin-right: 30px;" type="button" onclick="location.href=\'/?m=Grade&c=Grade&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="设置" >';
	  }
      if(row.type=='2'){
    	  str += '<input style="margin-left: -21px;" type="button" onclick="location.href=\'/?m=Grade&c=Grade&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="设置" >';
    	  str += '<input style="margin-left: 0px;" type="button" onclick="fund('+row.id+',\''+row.grade+'\')"  class="easyui-linkbutton" data-options="selected:true" value="禁用" >';  
      }
      if(row.type=='3'){
      	  str += '<input style="margin-right: 30px;" type="button" onclick="location.href=\'/?m=Grade&c=Grade&a=add&grade_id='+row.grade_id+'\'" class="easyui-linkbutton" data-options="selected:true" value="启用" >'; 
      }
      return str;  
    }
}  
] ];

		  
function refund(id,type) {
		$.ajax({
	        type: "POST",
	        async:true,  // 设置同步方式
	        url: "/?m=Grade&c=Grade&a=delete&id="+id,
	        dateType: "json",
	        data:'status='+type,
	        success:function(data){
	  			if (data.code == '200') {
					location.href="/index.php?m=Grade&c=Grade&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
	  			if (data.code == '300') {
					location.href="/index.php?m=Grade&c=Grade&&a=detail&id="+id
				} else {
					$.messager.alert('提示', data.msg);
				}
	        }
	    });	
		
}

function submitSms(id){
	$.ajax({
        type: "POST",
        async:true,  // 设置同步方式
        url: "/?m=Grade&c=Grade&a=delete&id="+id,
        dateType: "json",
        success:function(data){
  			if (data.code == '200') {
				location.href="/index.php?m=Grade&c=Grade&a=list"
			} else {
				$.messager.alert('提示', data.msg);
			}
        }
    });
	
	$('#dd').dialog('close');
}

// function searchInfo() {
//
// 	$('#dg').datagrid({
// 		title:'',
// 		width:'100%',
// 		height:'auto',
// 		nowrap: true,
// 		autoRowHeight: true,
// 		striped: true,
// 	    url: '/index.php?m=Grade&c=Grade&a=list&format=list',
// 		remoteSort: false,
// 		singleSelect:true,
// 		idField:'id',
// 		loadMsg:'数据加载中......',
// 		columns: fields,
// 		pagination:false,
// 		fitColumns:false
// 	});
//
// }
// $(function(){
// 	searchInfo();
// })
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });



function fund(id,grade) {
	$('#dd').dialog({
	    title: '消息提示！',
	    width: 500,
	    height: 280,
	    closed: false,
	    cache: false,
	    modal: true,
	    content: " <table class='kv-table'>" +
	    		"<div style='margin: 10px'>" +
	    		"<a style='margin:20px; font-family:宋体; font-size:18px'>禁用"+grade+"</a>" +
	    		"</div><hr><div style='margin: 10px; margin-top:40px'   >" +
	    		"<a style='font-size:16px;color:#8B8989; '>禁用后,原等级为"+grade+"的会员将在下次消费的时候，根据新的成长规则降级到下一级,确定禁用么？</a></div>" +
	    		"</table><div style='margin-top:35px;margin-left: 304px;'>" +
	    		"<input type='button' onclick='submitSms("+id+");' class='easyui-linkbutton' data-options='selected:true' value='确定' >" +
	    		"<a onclick='closeSms()' style='margin-left:15px' class='easyui-linkbutton' >取消</a></div>"
	});	
}



function closeSms() {
	$('#dd').dialog('close');	
}




