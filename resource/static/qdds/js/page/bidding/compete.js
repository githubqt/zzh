/***
 * 首页轮播js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-19
 */
var fileds =  [ [ {
    field : 'id',
    width : 100,
    sortable:true,
    title : 'ID'
}, {
    field : 'product_name',
    width : 150,
    title : '商品名称'
} , {
    field : 'status_txt',
    width : 110,
    title : '状态'
} , {
    field : 'margin',
    width : 150,
    title : '保证金额'
}, {
        field : 'is_margin_txt',
        width : 150,
        title : '保证金状态'
}, {
    field : 'name',
    width : 150,
    title : '参拍人'
}, {
    field : 'user_txt',
    width : 160,
    title : '参拍人电话'
}, {
    field : 'created_at',
    width : 160,
    title : '交钱时间'
} ,{
    field : 'accept_name',
    width : 160,
    title : '收货人'
},{
    field : 'accept_mobile',
    width : 160,
    title : '收货人电话'
},{
    field : 'robot_txt',
    width : 100,
    title : '用户类型'
}
] ];



function searchInfo() {


	$('#dg').datagrid({
				title:'',
				width:'100%',
				height:'auto',
				nowrap: true,
				autoRowHeight: true,
				striped: true,
			    url: '/index.php?m=Marketing&c=Bidding&a=compete&format=list&id='+id,
				singleSelect:true,
				idField:'id',
				loadMsg:'数据加载中......',
				pageList: [10,20,50],
				columns: fileds,
				pagination:true,
				rownumbers:true,

			});

}
$(function(){
	searchInfo();
})

 $(".more").click(function(){
    $(this).closest(".conditions").siblings().toggleClass("hide");
});
