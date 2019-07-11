var mainPlatform = {

	init: function(){
		this.getAuth();
		this.bindEvent();
		
	},
	
	getAuth: function(){
		$.ajax({
	        type:"POST",
	        url:"/index.php?m=Index&c=Index&a=index&format=getAuth",
	        datatype: "json",
	        success:function(data){
	            if (data.code == 200) {
	            	mainPlatform.render(data.menu);
	            } else {
	            	mainPlatform.render([]);
	            }       
	        }      
	     });
	},		

	bindEvent: function(){
		
		//左侧菜单展开
        $(document).on('click', '.sider-nav li', function() {
            $('.sider-nav li').removeClass('current');
            $(this).addClass('current');
            $('iframe').attr('src', $(this).data('src'));
        });

        //左侧菜单收起
        $(document).on('click', '.toggle-icon', function() {
            $(this).closest("#pf-bd").toggleClass("toggle");
            setTimeout(function(){
            	$(window).resize();
            },300);
        });		
		
		//左侧菜单点击事件
        $(document).on('click', '.sider-nav-s li a', function() {
        	
            $('.sider-nav-s li').removeClass('active');
            $(this).parent().addClass('active');
            
            mainPlatform.addTab($(this).attr('title'),$(this).attr('data-src'));	       	
        });
	},

	render: function(menu){
		//组装产品
		var current,
			html = ['<h2 class="pf-model-name">'+
						'<span class="iconfont">&#xe64a;</span>'+
						'<span class="pf-name">'+ menu.title +'</span>'+
						'<span class="toggle-icon"></span>'+
					'</h2>'+
					'<ul class="sider-nav">'];
		//组装一级菜单
		$.each(menu.menu,function(topk,topv){
			var currenthtml = '';
			if(topv.isCurrent){
				currenthtml = ' class="current"';
			}
			html.push('<li '+currenthtml+' title="'+ topv.title +'">'+
							'<a href="javascript:;">'+
								'<span class="iconfont sider-nav-icon icon Hui-iconfont">'+ topv.icon +'</span>'+
								'<span class="sider-nav-title">'+ topv.title +'</span>'+
								'<i class="iconfont">&#xe642;</i>'+
							'</a>'+
							'<ul class="sider-nav-s">');
			//组装二级菜单
			if (topv.childs) {
				
				$.each(topv.childs,function(childk,childv){
					if(childv.isCurrent){
						current = childv;
						html.push('<li class="active"><a href="javascript:;" title="'+ childv.title +'" data-src="'+ childv.href +'">'+ childv.title +'</a></li>');	
					} else {
						html.push('<li><a href="javascript:;" title="'+ childv.title +'" data-src="'+ childv.href +'">'+ childv.title +'</a></li>');	
					}	
				});	
			
			}
		
											
			html.push('</ul></li>');				
		});
		html.push('</ul>');
		
		$('#pf-sider').html(html.join(''));
		//加载默认选中
		if (current) {
			mainPlatform.addTab(current.title,current.href);				
		}
	},
	
	addTab: function(title, url){
		if ($('#tt').tabs('exists', title)){
			$('#tt').tabs('select', title);
		} else {
			var content = '<iframe scrolling="auto" frameborder="0"  src="'+url+'" style="width:100%;height:100%;"></iframe>';
			$('#tt').tabs('add',{
				title:title,
				content:content,
				closable:true
			});
		}
	}	

};

function showWin() {
	$('#win').window('open');
}
function passw() {
	$('#passw').window('open');
}
function setadd(){
    var password = $('#password').val();
    var repassword = $('#repassword').val();
    
    var loginData = {
        info:{
    		password: password,
    		repassword: repassword
        }
    };
	
	
	$.ajax({
        //提交数据的类型 POST GET
        type:"POST",
        //提交的网址
        url:"/index.php?m=Supplier&c=Admin&a=editPassword&format=edit&id="+$("#hide_user_id").val(),
        //提交的数据
        data:loginData,
        //返回数据的格式
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
                 
        success:function(data){
        	
            if (data.code == 200) {
                location.href = "/index.php?m=Auth&c=login&a=login";
            } else {
            	$.messager.alert('提示', data.msg);
            }
       		         
        }      
     });
}    


function shopSetadd(){
    var password = $('#password').val();
    var repassword = $('#repassword').val();
    
    var loginData = {
        info:{
    		password: password,
    		repassword: repassword
        }
    };
	
	
	$.ajax({
        //提交数据的类型 POST GET
        type:"POST",
        //提交的网址
        url:"/index.php?m=Auth&c=Admin&a=editPassword&format=edit&id="+$("#hide_user_id").val(),
        //提交的数据
        data:loginData,
        //返回数据的格式
        datatype: "json",//"xml", "html", "script", "json", "jsonp", "text".
                 
        success:function(data){
        	
            if (data.code == 200) {
                location.href = "/index.php?m=Auth&c=login&a=login";
            } else {
            	$.messager.alert('提示', data.msg);
            }
       		         
        }      
     });
}    

mainPlatform.init();