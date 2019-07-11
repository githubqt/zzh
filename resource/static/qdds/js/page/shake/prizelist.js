/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fields =  [ [ {
    field : 'id',  
    width : 50,  
    title : 'ID'  
}, {  
    field : 'user_name',  
    width : 100,  
    title : '会员姓名'  
} , {  
    field : 'user_mobile',  
    width : 100,   
    title : '会员手机'  
} , {  
    field : 'level_txt',  
    width : 100,  
    title : '奖品等级'  
}, {  
    field : 'prize_type_txt',  
    width : 100,   
    title : '奖品类型'  
}, {  
    field : 'note',   
    title : '奖品内容'  
}, {  
    field : 'status_txt',  
    width : 100,   
    title : '领取状态'  
}, {  
    field : 'is_prize_txt',  
    width : 100,   
    title : '是否中奖'     
}  , {  
    field : 'created_at',  
    width : 140,   
    title : '摇奖时间'   
} , {  
    field : 'prize_at',  
    width : 140,   
    title : '领奖时间'  
}
] ];

// function searchInfo() {
// 	var queryData = new Object();
// 	queryData['info[user_name]'] = $('#user_name').val();
// 	queryData['info[user_mobile]'] = $('#user_mobile').val();
// 	queryData['info[level]'] = $('#level').val();
// 	queryData['info[prize_type]'] = $('#prize_type').val();
// 	queryData['info[status]'] = $('#status').val();
// 	queryData['info[is_prize]'] = $('#is_prize').val();
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
