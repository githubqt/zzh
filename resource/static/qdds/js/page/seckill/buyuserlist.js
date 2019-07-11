/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'user_id',  
    width : 50,  
    title : '会员ID'  
}, {  
    field : 'user_name',  
    width : 100,  
    title : '会员姓名'  
} , {  
    field : 'sex_name',  
    width : 100,  
    title : '会员性别'  
} , {  
    field : 'user_mobile',  
    width : 100,   
    title : '会员手机'  
} , {  
    field : 'created_at',  
    width : 140,   
    title : '购买时间'   
}
] ];

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[user_name]'] = $('#user_name').val();
// 	queryData['info[user_mobile]'] = $('#user_mobile').val();
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
// 			    url: location.href+'&format=list',
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
