/**
 * 公共js
 * huangxianguo
 */

function formStart(url) {
	var isValid = $("#ff").form('validate');
	if ( isValid == false) {
		return isValid;
	}
	
	$.ajax({
         url : $('#ff').attr("action"),
         type : "post",
         dataType : "jsonp",
         jsonp : "jsonpcallback",
         data : $('#ff').serialize(),
         success : function(data){
            if (data.code == '200') {
				location.href = url
			} else {
				$.messager.alert('提示', data.msg);
			}
         }
    });
}

  $(function() {
	  //全局图片懒加载
	  $("img.lazy").lazyload({
		  placeholder : HOST_STATIC+"/common/images/common.png", //用图片提前占位
		    // placeholder,值为某一图片路径.此图片用来占据将要加载的图片的位置,待图片加载时,占位图则会隐藏
		  effect: "fadeIn", // 载入使用何种效果
		    // effect(特效),值有show(直接显示),fadeIn(淡入),slideDown(下拉)等,常用fadeIn
		  //threshold: 200, // 提前开始加载
		    // threshold,值为数字,代表页面高度.如设置为200,表示滚动条在离目标位置还有200的高度时就开始加载图片,可以做到不让用户察觉
		  //event: 'click',  // 事件触发时才加载
		    // event,值有click(点击),mouseover(鼠标划过),sporty(运动的),foobar(…).可以实现鼠标莫过或点击图片才开始加载,后两个值未测试…
		  //container: $("#container"),  // 对某容器中的图片实现效果
		    // container,值为某容器.lazyload默认在拉动浏览器滚动条时生效,这个参数可以让你在拉动某DIV的滚动条时依次加载其中的图片
		  //failurelimit : 10 // 图片排序混乱时
		     // failurelimit,值为数字.lazyload默认在找到第一张不在可见区域里的图片时则不再继续加载,但当HTML容器混乱的时候可能出现可见区域内图片并没加载出来的情况,failurelimit意在加载N张可见区域外的图片,以避免出现这个问题.
	  });

	$.extend($.fn.textbox.defaults.rules, {
        numDash : {
            validator : function(value) {
                return /^[0-9]*([-]|[0-9])*$/.test(value);
            },
            message : "请输入数字或 - "
        },
        engnumber : {
	        validator : function(value, param) {  
	            return /^[0-9A-Za-z]*$/.test(value);  
	        },  
	        message : "请输入英文或数字"  
	    },  
	    english : {  
	        validator : function(value, param) {  
	            return /^[A-Za-z]*$/.test(value);  
	        },  
	        message : "请输入英文"  
	    },
	    centenglish : {  
	        validator : function(value, param) {  
	            return /^[\s+A-Za-z]*$/.test(value);  
	        },  
	        message : "请输入英文"  
	    },

	    number : {  
	        validator : function(value, param) {  
	            return /^[0-9]*$/.test(value);  
	        },  
	        message : "请输入数字"  
	    },  
	    phone : {  
	        validator : function(value, param) {  
	            return /^1[3456789]\d{9}$/.test(value);  
	        },  
	        message : "请输入正确的手机号"  
	    },
	    mobile : {  
	        validator : function(value, param) {  
	            return  /^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/.test(value);  
	        },  
	        message : "请输入正确的座机号"  
	    },
	    chinese : {  
	        validator : function(value, param) {  
	            var reg = /^[\u4e00-\u9fa5]+$/i;  
	            return reg.test(value);  
	        },  
	        message : "请输入中文"  
	    },  
	    checkLength: {  
	        validator: function(value, param){  
	            return param[0] >= get_length(value);  
	        },  
	        message: '请输入最大{0}位字符'  
	    },  
	    specialCharacter: {  
	        validator: function(value, param){  
	            var reg = new RegExp("[`~!@#$^&*()=|{}':;'\\[\\]<>~！@#￥……&*（）——|{}【】‘；：”“'、？]");  
	            return !reg.test(value);  
	        },  
	        message: '不允许输入特殊字符'  
	    } ,
	    Card: {
	    	validator: function isCardNo(value, param) {  
	       // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X  
	       var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;  
           return reg.test(value);
	    },
	    	message: '请输入正确得身份证号'  
	    },
        maxDate: {
          validator: function(value, param){
              var d1 = $(param[0]).datetimebox('getValue');  //获取结束时间
			  if ( !d1 || value <= d1 ) {
			  	  return true;
			  }
              return false;  //有效范围小于结束时间的日期
          },
          message: '开始时间不能早于结束时间!'
      	},
        minDate: {
            validator: function(value, param){
                var d1 = $(param[0]).datetimebox('getValue');  //获取开始时间
                if ( !d1 || value >= d1 ) {
                    return true;
                }
                return false;
            },
            message: '结束时间不能早于开始时间!'
        }
	});
});
  
  
  
  function toggleFullScreen() {
	if (!document.fullscreenElement && // alternative standard method
		!document.mozFullScreenElement && !document.webkitFullscreenElement) {// current working methods
		if (document.documentElement.requestFullscreen) {
			document.documentElement.requestFullscreen();
		} else if (document.documentElement.mozRequestFullScreen) {
			document.documentElement.mozRequestFullScreen();
		} else if (document.documentElement.webkitRequestFullscreen) {
			document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
		}
	} else {
		if (document.cancelFullScreen) {
			document.cancelFullScreen();
		} else if (document.mozCancelFullScreen) {
			document.mozCancelFullScreen();
		} else if (document.webkitCancelFullScreen) {
			document.webkitCancelFullScreen();
		}
	}
}
  
  function deleteInfo(url,return_url) {
		$.messager.confirm('温馨提示', '您确定要删除吗?',function(res){
			if (res == true) {
				$.ajax({
			        type: "POST",
			        async:true,  // 设置同步方式
			        url: url,
			        dateType: "json",
			        success:function(data){
			  			if (data.code == '200') {
							location.href=return_url
						} else {
							$.messager.alert('提示', data.msg);
						}
			        }
			    });	
			}
		})
	}

function handleConfirm(url,msg) {

    $.messager.confirm('温馨提示', '确定'+ msg + '?',function(res){
        if (res == true) {
            $.ajax({
                type: "POST",
                async:true,  // 设置同步方式
                url: url,
                dateType: "json",
                success:function(data){
                    if (data.code == '200') {
                        location.reload();
                    } else {
                        $.messager.alert('提示', data.msg);
                    }
                }
            });
        }
    })
}



/**************************************时间格式化处理************************************/
function dateFtt(fmt,date)
{ //author: meizz
    var o = {
        "M+" : date.getMonth()+1,                 //月份
        "d+" : date.getDate(),                    //日
        "h+" : date.getHours(),                   //小时
        "m+" : date.getMinutes(),                 //分
        "s+" : date.getSeconds(),                 //秒
        "q+" : Math.floor((date.getMonth()+3)/3), //季度
        "S"  : date.getMilliseconds()             //毫秒
    };
    if(/(y+)/.test(fmt))
        fmt=fmt.replace(RegExp.$1, (date.getFullYear()+"").substr(4 - RegExp.$1.length));
    for(var k in o)
        if(new RegExp("("+ k +")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
    return fmt;
}

/**************************************自定义本地字典对象************************************/
function Dictionary(){
    this.data = window.sessionStorage;

    //该方法接受一个键名(k)和值(v)作为参数，将键值对添加到存储中；如果键名存在，则更新其对应的值
    this.put = function(k,v){
        this.data.setItem(k,v);
    };

    //返回键名(k)对应的值(v)。若没有返回null
    this.get = function(k){
        return this.data.getItem(k);
    };

    //将指定的键名(k)从缓存对象中移除
    this.remove = function(k){
        return this.data.removeItem(k);
    };

    //返回当前缓存对象的第i序号的k名称。若没有返回null
    this.key = function(i){
        return this.data.key(i);
    };

    //清除缓存所有的项
    this.clear = function(){
        return this.data.clear();
    };

    //返回一个整数，表示存储在缓存对象中的数据项(键值对)数量
    this.length = function(){
        return this.data.length;
    };

}
/**
 * 使用 例子
 * var d = new Dictionary();
 * d.put("CN", "China");
 * d.put("US", "America");
 * document.write(d.get("CN"));
 * */

/**
 * 获取列表查询参数
 */
var Params = {
    /**
     * 保存已选
     */
    Params: new Dictionary(),
    err:'',
    /**
     * 添加参数
     * @param k
     * @param v
     * @returns {boolean}
     */
    set: function (k,v) {
        this.Params.put(k,v);
    },
    /**
     * 获取参数
     * @param k
     * @returns {params}
     */
    get: function (k) {
        return this.Params.get(k);
    },
    /**
     * 删除
     * @param k
     */
    delete: function (k) {
        this.Params.remove(k);
    },
    /**
     * 清除所有
     * @returns {boolean}
     */
    clear: function () {
        return this.Params.clear();
    }
};
