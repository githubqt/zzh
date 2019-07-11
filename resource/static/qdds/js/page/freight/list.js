/***
 * 运费js
 * @version v0.01
 * @author lqt
 * @time 2018-05-21
 */
var fields =  [ [ {
    field : 'province_name',  
    width : 350,
    title : '省份'  
}, {  
    field : 'freight',
    width : 350,  
    title : '运费'   
} , {
	field:'operate',
	width : 400,
	title :'操作',
	align :'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="javascript:editArea('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';  
        str += '<input type="button" onclick="javascript:delArea('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';             
        return str;  
    }
}  
] ];
//保存
function setFreight() {	
    var p = $("input[name='freight_type']:checked").val();
    var f = $("input[name='freight']").val();
	$.ajax({
        type: "POST",
        async:true,
        url: "/?m=Marketing&c=Freight&a=list&format=set",
        dateType: "json",
        data: {freight_type:p,freight:f},
        success:function(data){
        	$.messager.alert('提示', data.msg);
			if (data.code == '200') {
				location.href="/index.php?m=Marketing&c=Freight&a=list"
			}        	
        }
    });	
}
//编辑
function editArea(id) {
	$('#diag').dialog({
	    title: '编辑运费',
	    width: 500,
	    height: 300,
	    closed: false,
	    cache: false,
	    href: '/?m=Marketing&c=Freight&a=edit&id='+id+'&is_menu=1',
	    modal: true
	});	
}
//添加
function addArea() {
	$('#diag').dialog({
	    title: '添加运费',
	    width: 500,
	    height: 300,
	    closed: false,
	    cache: false,
	    href: '/?m=Marketing&c=Freight&a=add&is_menu=1',
	    modal: true
	});	
}
//删除
function delArea(id) {
	deleteInfo("/?m=Marketing&c=Freight&a=delete&id="+id,"/index.php?m=Marketing&c=Freight&a=list");
}

// function searchInfo() {
// 	$('#dg').datagrid({
// 		title:'',
// 		width:'100%',
// 		height:'auto',
// 		nowrap: true,
// 		autoRowHeight: true,
// 		striped: true,
// 	    url: '/index.php?m=Marketing&c=Freight&a=list&format=list',
// 		remoteSort: false,
// 		singleSelect:true,
// 		idField:'id',
// 		loadMsg:'数据加载中......',
// 		pageList: [10,20,50],
// 		columns: fields,
// 		pagination:true,
// 		rownumbers:true
// 	});
//
// }
// $(function(){
// 	searchInfo();
// })
