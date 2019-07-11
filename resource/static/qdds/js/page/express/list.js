/***
 * 快递查询js
 * @version v0.01
 * @time 2018-05-09
 */
var fields =  [ [ {
    field : 'id',
    width : 120,
    title : 'ID'
}, {
    field : 'content',
    width : 290,
    title : '查询内容'
}, {
    field : 'express_no',
    width : 240,
    title : '查询单号'
}, {
    field : 'express_name',
    width : 120,
    title : '快递公司'
}, {
    field : 'source_txt',
    width : 120,
    title : '查询来源'
} , {
    field : 'query_time',
    width : 240,
    title : '查询时间'
} , {
    field : 'inquirer',
    width : 200,
    title : '查询人'
}
] ];

// function searchInfo() {
//
// 	var queryData = new Object();
// 	queryData['info[status]'] = $('#status').combobox('getValue');
// 	queryData['info[inquirer]'] = $('#inquirer').val();
// 	queryData['info[express_name]'] = $('#express_name').val();
// 	queryData['info[express_no]'] = $('#express_no').val();
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
// 			    url: '/index.php?m=Marketing&c=Express&a=list&format=list',
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
//
//
//
//  $(".more").click(function(){
//     $(this).closest(".conditions").siblings().toggleClass("hide");
// });
