/***
 * 管理员登陆js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-05-04
 */

document.onkeydown = function(e){
    if(e.keyCode == 13){
        login();
    }
};

//点击切换
function click_code(){
	
	$("#getcode_char").attr("src",'/index.php?m=Auth&c=Login&a=code&t=' + Math.random());
};


 
$(function(){
	
	//登录页跳出iframe
    if(window.frameElement){
        if ($(".page-iframe").context.location.search == '?m=Auth&c=login&a=login') {
            window.top.location='/index.php?m=Auth&c=login&a=login';
        }
    }

	//判断是否存在过用户
	var storage = window.localStorage;
	if("yes" == storage["isstorename"]){
	    $("#remb_me").attr("checked", true);
	    $("#login_name").val(storage["loginname"]);
	} 
})

function hide(){
	$('#layer').hide()
}

function login(){
	var form = $('#loginform');
	var code = $('#sp_number').val();
    var name = $('#login_name').val();
    var password = $('#password').val();
    var code_char = $('#code_char').val();
    
    if(code == null || code == ""){
        $("#layer").show();
        $("#msg_info").html("请输入商户号！");
        return;
    }
    
    if(name == null || name == ""){
        $("#layer").show();
        $("#msg_info").html("请输入账号！");
        return;
    }

    if(!password) {
        $("#layer").show();
        $("#msg_info").html("密码输入错误！");
        return;
    }
    // if(password.length < 6  ) {
    //     $("#layer").show();
    //     $("#msg_info").html("密码应不小于六位！");
    //     return;
    // }

    if(!code_char) {
        $("#layer").show();
        $("#msg_info").html("请输入验证码！");
        return;
    }    
    
    
    var loginData = {
    	code: code,
        name: name,
        password: password,
        code_char:code_char
    };
	
	
	$.ajax({
        //提交数据的类型 POST GET
        type:"POST",
        //提交的网址
        url:"/index.php?m=Auth&c=Login&a=login&format=login",
        //提交的数据
        data:loginData,
        //返回数据的格式
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
        //在请求之前调用的函数
       // beforeSend:function(){$("#msg").html("logining");},
        //成功返回之后调用的函数             
        success:function(data){
        	
            if (data.code == 200) {
                location.href = "/index.php?m=Index&c=Index&a=index";
            } else {
            	$("#layer").show();
                $("#msg_info").html(data.msg);
                click_code();
            }
       		         
        }      
     });
	
	//判断是否保存用户名
	var storage = window.localStorage;
	if($("#remb_me").is(':checked')){
	     //存储到loaclStage
	     storage["loginname"] = $("#login_name").val();
	     storage["isstorename"] =  "yes"; 
	 }else{
	     storage["loginname"] = "";
	     storage["isstorename"] =  "no"; 
	}
	
	//判断是否存在过用户
	var storage = window.localStorage;
	if("yes" == storage["isstorename"]){
	    $("#remb_me").attr("checked", true);
	    $("#login_name").val(storage["loginname"]);
	} 
}    