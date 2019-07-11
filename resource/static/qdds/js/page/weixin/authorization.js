/***
 * 微信设置js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-20
 */
function layerwin() {
	
	 $.messager.defaults = { ok: "已成功授权", cancel: "授权失败 重试" };
	 $('.window-mask').show();
	$.messager.confirm({
	    width: 350,
	    height: 180, 
	    top:200,
	    title: '操作提示',
	    msg: '请在新窗口中完成微信公众号授权',
	    fn: function (data) {
	    	if(data) {
	    		$('.window-mask').hide();
	    		location.reload();
	    	}else {
	    		layerwin();
	    		window.open("/index.php?m=Weixin&c=Weixin&a=threeAuth"); 
	    	}
	    }
	});
}

