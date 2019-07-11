/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'id',  
    width : 60,  
    sortable:true,
    title : 'ID'  
}, {  
    field : 'name',  
    width : 250,  
    title : '网点名称'  
} , {  
    field : 'area_text',  
    width : 350,   
    title : '网点详细地址'  
} , {  
    field : 'contact',  
    width : 100,  
    title : '联系人'  
}, {  
    field : 'mobile',  
    width : 120,   
    title : '联系电话'  
}, {
	field:'operate',
	title:'操作',
	width: 220,
	align:'left', 
    formatter:function(value, row, index){  
        var str = '<input type="button" onclick="location.href=\'/index.php?m=Marketing&c=Multipoint&a=detail&id='+row.id+'\'" class="easyui-linkbutton" data-options="selected:true" value="查看" >';
        str += '<input type="button" onclick="location.href=\'/index.php?m=Marketing&c=Multipoint&a=edit&id='+row.id+'\'"  class="easyui-linkbutton" data-options="selected:true" value="编辑" >';    
        str += '<input type="button" onclick="javascript:delbrand('+row.id+');"  class="easyui-linkbutton" data-options="selected:true" value="删除" >';  
        
        return str;  
    }
}  
] ];

function delbrand(id) {
	deleteInfo("/index.php?m=Marketing&c=Multipoint&a=delete&id="+id,"/index.php?m=Marketing&c=Multipoint&a=list");
}



// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[name]'] = $('#name').val();
// 	queryData['info[contact]'] = $('#contact').val();
// 	queryData['info[mobile]'] = $('#mobile').val();
// 	queryData['info[province_id]'] = $('#province').val();
// 	queryData['info[city_id]'] = $('#city').val();
// 	queryData['info[area_id]'] = $('#area').val();
// 	queryData['info[address]'] = $('#address').val();
//
// 	$('#dg').datagrid({
// 				title:'',
// 				width:'100%',
// 				height:'auto',
// 				nowrap: true,
// 				autoRowHeight: true,
// 				striped: true,
// 			    url: '/index.php?m=Marketing&c=Multipoint&a=list&format=list',
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
