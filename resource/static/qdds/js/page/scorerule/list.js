/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'receive_type_name',  
    width : 270,  
    title : '奖励条件'  
}, {  
    field : 'score_num',  
    width : 250,  
    title : '奖励积分'  
} , {  
    field : 'updated_at',  
    width : 210,   
    title : '规则更新时间'  
} , {  
    field : 'have_score_num',  
    width : 180,  
    title : '已奖励总积分'  
}, {
	field:'operate',
	title:'操作',
	width: 166,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/?m=User&c=Scorerule&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';
        
        str += '<input type="button" onclick="javascript:delproduct('+row.id+');" class="easyui-linkbutton" data-options="selected:true" value="删除" >';   
        
        return str;  
    }
}  
] ];

function delproduct(id) {
	deleteInfo("/?m=User&c=Scorerule&a=delete&id="+id,"/index.php?m=User&c=Scorerule&a=list");
}

// function searchInfo() {
// 	var queryData = new Object();
// 	/*queryData['info[name]'] = $('#title_name').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[product_name]'] = $('#product_name').val();
// 	queryData['info[start_time]'] = $('#start_time').datebox('getValue');
// 	queryData['info[end_time]'] = $('#end_time').datebox('getValue'); */
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=User&c=Scorerule&a=list&format=list',
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
$(function(){
	//searchInfo();
	is_readonly();
});

//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });



function time_hide(type){
	if (type == 1) {
		$("#time_select").hide();
	} else {
		$("#time_select").show();
	}
}



function score_time_add () {
	var data = new Object();
	data.score_rule_type = $('input:radio[name="score_rule_type"]:checked').val();
	data.score_rule_time = $('#end_time').datebox('getValue');
	data.score_rule_year = $("#rule_time").val();
	
	
	
	$.ajax({
	     url : '/index.php?m=User&c=Scorerule&a=list&format=editsupplier',
	     type : "post",
	     dataType : "jsonp",
	     jsonp : "jsonpcallback",
	     data : data,
	     success : function(data){
	     	
	        if (data.code == '200') {
				$('#score_time').window('close');
				location.href="/index.php?m=User&c=Scorerule&a=list";
			} else {
				$.messager.alert('提示', data.msg);
			}
	     }
	});
}


function score_top_add () {
	var data = new Object();
	data.score_top = '1';
	if ($('#score_top_type').is(':checked')) {
		data.score_top = '2';
	}
	data.score_top_num = $("#score_top_num").val();
	
	$.ajax({
	     url : '/index.php?m=User&c=Scorerule&a=list&format=editscore',
	     type : "post",
	     dataType : "jsonp",
	     jsonp : "jsonpcallback",
	     data : data,
	     success : function(data){
	     	
	        if (data.code == '200') {
				$('#score_time').window('close');
				location.href="/index.php?m=User&c=Scorerule&a=list";
			} else {
				$.messager.alert('提示', data.msg);
			}
	     }
	});
}

function is_readonly() {
	if ($('#score_top_type').is(':checked')) {
		$("#score_top_num").textbox('textbox').attr("disabled",false);
	} else {
		$("#score_top_num").textbox('textbox').attr("disabled",true);
	}
	
}




