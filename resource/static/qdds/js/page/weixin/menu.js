/***
 * 自定义菜单js
 * @version v0.01
 * @author huangxianguo
 * @time 2018-07-20
 */

$(function(){
	if ($("#reload_value").val()) {
		if ($("#reload_value").val() == '2') {
			$.messager.alert('提示', '保存成功');
		} else {
			$.messager.alert('提示', '发布成功');
		}
	}
	showTwoMenu(0)
	$('#ff').form({
		success:function(data){
			var data = JSON.parse(data);
			if (data.code == '200') {
				location.href="/index.php?m=Weixin&c=Weixin&a=Menu&reload="+$("#is_add").val();
			} else {
				$.messager.alert('提示', data.msg);
			}
		}
	});
	
	
});

function minilink() {
	var menu_id = $("#wei_menu_id").val();
	var two_menu_now_id = $("#select_menu_now_"+menu_id).val();
	var miniid = $("#miniid").val();
	var minipage = $("#minipage").val();
	var sparelink = $("#sparelink").val();
	if (!miniid) {
		$.messager.alert('提示', '请输入小程序 appid');
		return;
	}
	if (!minipage) {
		$.messager.alert('提示', '请输入小程序页面路径');
		return;
	}
	if (!sparelink) {
		$.messager.alert('提示', '请输入备用网页');
		return;
	}
	
	var Expression=/^[0-9A-Za-z_]*$/;
	var objExp=new RegExp(Expression);
	if(objExp.test(miniid)==false){
		$.messager.alert('提示', 'appid格式不正确');
		return;
	}

	/* var Expression=/^[0-9A-Za-z\/=?]*$/;
	var objExp=new RegExp(Expression);
	if(objExp.test(minipage)==false){
		$.messager.alert('提示', '小程序页面路径不正确');
		return;
	}
	
	var Expression=/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
	var objExp=new RegExp(Expression);
	if(objExp.test(sparelink)==false){
		$.messager.alert('提示', '请输入正确格式的备用网页');
		return;
	} */
	
	//该栏目是否已存在，已存在删除
	if ($(".link-to-"+menu_id+'-'+two_menu_now_id).html()) {
		$(".link-to-"+menu_id+'-'+two_menu_now_id).remove();
	}
	
	//添加新的栏目
	if (two_menu_now_id == '0') {
		$("#info_type_"+menu_id).val(2);
		var Ahtml = '<div class="link-to js-link-to link-to-'+menu_id+'-'+two_menu_now_id+'">'+
    					'<div class="minic"><input type="hidden" name="info['+menu_id+'][app_id]" value="'+miniid+'">appid：'+miniid+'</div>'+
    					'<div class="minic"><input type="hidden" name="info['+menu_id+'][url]" value="'+minipage+'">页面路径：'+minipage+'</div>'+
    					'<div class="minic"><input type="hidden" name="info['+menu_id+'][spare_url]" value="'+sparelink+'">备用网页：'+sparelink+'</div>'+
    				'</div>';
	} else {
		$("#info_two_type_"+menu_id+'_'+two_menu_now_id).val(2);
		var Ahtml = '<div class="link-to js-link-to link-to-'+menu_id+'-'+two_menu_now_id+'">'+
						'<div class="minic"><input type="hidden" name="info['+menu_id+'][two]['+two_menu_now_id+'][app_id]" value="'+miniid+'">appid：'+miniid+'</div>'+
						'<div class="minic"><input type="hidden" name="info['+menu_id+'][two]['+two_menu_now_id+'][url]" value="'+minipage+'">页面路径：'+minipage+'</div>'+
						'<div class="minic"><input type="hidden" name="info['+menu_id+'][two]['+two_menu_now_id+'][spare_url]" value="'+sparelink+'">备用网页：'+sparelink+'</div>'+
					'</div>';
	}
	$(".content-"+menu_id).append(Ahtml);
	$('#minilink').window('close');
	/* $("#miniid").val("");
	$("#minipage").val("");
	$("#sparelink").val(""); */
}

function weichatlink() {
	var menu_id = $("#wei_menu_id").val();
	var two_menu_now_id = $("#select_menu_now_"+menu_id).val();
	var link = $("#wei_link").val();
	if (!link) {
		$.messager.alert('提示', '请输入链接');
		return;
	}
	/* var Expression=/http:\/\/[A-Za-z0-9\.-]\.[A-Za-z]/;
	var objExp=new RegExp(Expression);
	if(objExp.test(sparelink)==false){
		$.messager.alert('提示', '请输入正确格式的链接');
		return;
	} */
	//该栏目是否已存在，已存在删除
	if ($(".link-to-"+menu_id+'-'+two_menu_now_id).html()) {
		$(".link-to-"+menu_id+'-'+two_menu_now_id).remove();
	}

	//添加新的栏目
	if (two_menu_now_id == '0') {
		$("#info_type_"+menu_id).val(1);
		var Ahtml = '<div class="link-to js-link-to link-to-'+menu_id+'-'+two_menu_now_id+'"><input type="hidden" name="info['+menu_id+'][url]" value="'+link+'">'+link+'</div>';
	} else {
		$("#info_two_type_"+menu_id+'_'+two_menu_now_id).val(1);
		var Ahtml = '<div class="link-to js-link-to link-to-'+menu_id+'-'+two_menu_now_id+'"><input type="hidden" name="info['+menu_id+'][two]['+two_menu_now_id+'][url]" value="'+link+'">'+link+'</div>';
	}
	$(".content-"+menu_id).append(Ahtml);
	$('#weilink').window('close');
	/* $("#wei_link").val(""); */
}

function setSort(menu_id,two_menu_id){
    var len = $(".second_length_"+menu_id).length;
    
    for(var i=0;i<=len;i++){
        $('.second_length_'+menu_id+':eq('+i+') div span:first span:eq(0)').text(i+1);
    }
    $("#two_menu_num_"+menu_id).val()

}

function giveIdToopen(menu_id,two_menu_id) {
	$("#open_menu_id").val(menu_id);
	$("#open_two_menu_id").val(two_menu_id);
}

function addMenu() {
	
	var menu_id = $("#open_menu_id").val();
	var two_menu_id = $("#open_two_menu_id").val();
	var name = $("#menu_name").val();

	if (!name) {
		$.messager.alert('提示', '请输入名称');
		return;
	}
	
	if (two_menu_id == '0') {
		var Expression=/^([\u4e00-\u9fa5].{0,3}|[a-zA-Z].{0,7})$/;
		var objExp=new RegExp(Expression);
		if(objExp.test(name)==false){
			$.messager.alert('提示', '一级菜单项标题不能超过4个汉字或8个字母');
			return;
		}
		$(".one_title_"+menu_id+"_0").html(name);
		$(".mainmenu_txt_"+menu_id+"_0").html(name);
		$("#info_name_"+menu_id).val(name);
	} else {
		var Expression=/^([\u4e00-\u9fa5].{0,7}|[a-zA-Z].{0,15})$/;
		var objExp=new RegExp(Expression);
		if(objExp.test(name)==false){
			$.messager.alert('提示', '二级菜单项标题不能超过8个汉字或16个字母');
			return;
		}
		$(".two_menu_title_"+menu_id+"_"+two_menu_id).html(name);
		$(".two_menu_"+menu_id+"_"+two_menu_id).html(name);
		$("#info_two_name_"+menu_id+"_"+two_menu_id).val(name);
	}

	
	$("#open_menu_id").val("");
	$("#open_two_menu_id").val("");
	$("#menu_name").val("");
	$('#passw').window('close');
}

$("body").mousedown(function(){
	$('.submenu').hide();
	$('.ui-popover').hide();
});

function deleteMenu(menu_id,two_menu_id) {
	$.messager.confirm({
	    width: 350,
	    height: 180, 
	    top:200,
	    title: '温馨提示',
	    msg: '您确定要删除吗?',
	    fn: function (data) {
	    	if (data == true) {
        		if (two_menu_id == '0') {
        			$("#menu_num_"+menu_id).remove();
        			$("#one_"+menu_id).remove();
        			$(".shu_"+menu_id).remove();
        			var menu_num = $('.menu_num').length;
        			var width = 268/(parseInt(menu_num));
        			$('.one').css('width',width)
        			if (menu_num == '3') {
                		$('.add-first-nav').hide();
                	} else {
                		$('.add-first-nav').show();
                	}
        		} else {
        			var second_length = $(".second_length_"+menu_id).length?$(".second_length_"+menu_id).length:0;
        			var num = parseInt(second_length)-parseInt(1);
        			
					if ($(".second_length_"+menu_id).length == '1') {
						$(".two_menu_title_name").remove();
	        			$('.content-'+menu_id).css('min-height','80');
					} else {
						
	        			$('.content-'+menu_id).css('min-height',parseInt(80)+parseInt(num)*parseInt(43));
					}
            		
        			$(".second_title_name_"+menu_id+"_"+two_menu_id).remove();

        			if (second_length == '5') {
						$(".add-second-nav-"+menu_id).show();
            		}
            		
        			$('#two_menu_'+menu_id+' #two_menu_num_foot_'+two_menu_id).remove();
        			$('#two_menu_'+menu_id+' #two_menu_line_num_'+two_menu_id).remove();
					if ($(".second_length_"+menu_id).length == '0') {
						shownow(menu_id,0);
					} else {
						shownow(menu_id,parseInt(two_menu_id)-parseInt(1));
					}
        			var second_length = $(".second_length_"+menu_id).length?$(".second_length_"+menu_id).length:0;
        			if (second_length == '0') {
						$("#submenu_"+menu_id).remove();
            		}
        			$('.link-to-'+menu_id+'-'+two_menu_id).remove();
        		}
        		setSort(menu_id,two_menu_id);
    		}
	    }
	});

}

function showTwoMenu(menu_id) {
	$('.submenu').hide();
	
	var one_menu_length = $('.one').length;
	
	if (one_menu_length == '1') {
		$('.nav-menu .one:first div').css('left','110px');
    	$('.arrow').css('left','62px');
	} else if (one_menu_length == '2') {
		$('.nav-menu .one:first div').css('left','42px');
		$(".nav-menu:nth-child(3)").css('left','42px');
		$(".nav-menu:nth-child(5)").css('left','180px');
    	$('.arrow').css('left','62px');
    	$('.js-submneu-a').css('min-width','118px');
	} else if (one_menu_length == '3') {
		$('.nav-menu .one:first div').css('left','42px');
		$(".nav-menu:nth-child(3)").css('left','43.5px');
		$(".nav-menu:nth-child(5)").css('left','134.5px');
		$(".nav-menu:nth-child(7)").css('left','225.5px');
    	$('.arrow').css('left','39.5px');
    	$('.js-submneu-a').css('min-width','73px');
	}
	
	$('#submenu_'+menu_id).show();
	
}

function addcha(id) {
	$('#menu_num_'+id).addClass('colorf8f8f8');
	$('#cha-'+id).show();
}

function deletecha(id) {
	$('#menu_num_'+id).removeClass('colorf8f8f8');
	$('#cha-'+id).hide();
}

function shownow(menu_id,content_id) {

	$('.edit_'+menu_id+'_0').css('color','rgb(204, 204, 204)');
	$('.del_'+menu_id+'_0').css('color','rgb(204, 204, 204)');
	$('.second_title_'+menu_id).addClass('pr7');
	
	$('.second_title_'+menu_id+'_'+content_id).removeClass('pr7');
	$('.edit_'+menu_id+'_'+content_id).css('color','#38f');
	$('.del_'+menu_id+'_'+content_id).css('color','#38f');
	var second_length = $(".second_length_"+menu_id).length;

	if (content_id == '0') {
		$('.second_title_'+menu_id).removeClass('pr7');
		$('.edit_'+menu_id+'_0').css('color','#38f');
		$('.del_'+menu_id+'_0').css('color','#38f');
		
	}
	$("#select_menu_now_"+menu_id).val(content_id);
	var length = $("#two_menu_num_"+menu_id).val();
	if (second_length >= '1') {
		for(var i=length;i>'0';i--) {
			if (content_id != i) {
				$('.edit_'+menu_id+'_'+i).css('color','rgb(204, 204, 204)');
				$('.del_'+menu_id+'_'+i).css('color','rgb(204, 204, 204)');
				$('.second_title_'+menu_id+'_'+i).addClass('pr7');
			} else {
				$("#select_menu_now_"+menu_id).val(i);
			}
		}
	}
	$('.content-'+menu_id+' .js-link-to').hide();
	//右侧内容
	if (content_id == '0' && $(".second_length_"+menu_id).length > '0') {
		$('.link-to-'+menu_id+'--1').show();
		$(".change-link-"+menu_id).hide();
	} else {
		$(".change-link-"+menu_id).show();
		$('.link-to-'+menu_id+'-'+content_id).show();
	}

}

function addSecond(num_id){

	$('.edit_'+num_id+'_0').css('color','rgb(204, 204, 204)');
	$('.del_'+num_id+'_0').css('color','rgb(204, 204, 204)');
	$('.second_title_'+num_id).addClass('pr7');
	
	var second_length = $(".second_length_"+num_id).length?$(".second_length_"+num_id).length:0;
	if (second_length >= 4) {
		$('.add-second-nav-'+num_id).hide();
	}
	var num = parseInt(second_length)+parseInt(1);
	
	$('.second_title_'+num_id+'_'+second_length).addClass('pr7');
	$('.content-'+num_id).css('min-height',parseInt(80)+parseInt(num)*parseInt(43));

	if (second_length >= '1') {
		for(var i=second_length;i>'0';i--) {
			if (num != i) {
				$('.edit_'+num_id+'_'+i).css('color','rgb(204, 204, 204)');
				$('.del_'+num_id+'_'+i).css('color','rgb(204, 204, 204)');
				$('.second_title_'+num_id+'_'+i).addClass('pr7');
			}
		}
	}
	
	var Ahtml = '';
	if (num == '1') {
		//$('.weixin_one_menu').addClass('pr7');
		Ahtml += '<div class="one_menu two_menu_title_name" style="margin-bottom: 0px;">'+
				'二级页面：'+
		'</div>';
	}

	var length_lang = $("#two_menu_num_"+num_id).val();
	
	Ahtml += '<div class="one_title_menu  second_length_'+num_id+' second_title_name_'+num_id+'_'+length_lang+'"" onclick="shownow('+num_id+','+length_lang+')">'+
        		'<div class="weixin_one_menu_two second_title_'+num_id+'_'+length_lang+'">'+
        			'<span class="h5" ><span>'+num+'</span>.'+
        			'<input type="hidden" id="info_two_name_'+num_id+'_'+length_lang+'" name="info['+num_id+'][two]['+length_lang+'][name]" value="标题">'+
        			'<input type="hidden" id="info_two_type_'+num_id+'_'+length_lang+'" name="info['+num_id+'][two]['+length_lang+'][type]" value="">'+
        			'<span class="overhide two_menu_title_'+num_id+'_'+length_lang+'">标题</span></span>'+
        			'<span class="opts">'+
                        '<a href="javascript:void(0);" style="color:rgb(51, 136, 255)" onclick="$(\'#passw\').window(\'open\');giveIdToopen('+num_id+','+length_lang+')" class="js-edit-first edit_'+num_id+'_'+length_lang+'">编辑</a> - '+
                        '<a href="javascript:void(0);" style="color:rgb(51, 136, 255)" onclick="deleteMenu('+num_id+','+length_lang+')" class="js-edit-first del_'+num_id+'_'+length_lang+'">删除</a>'+
                    '</span>'+
        		'</div>'+
        	'</div>';
	$("#second_"+num_id).append(Ahtml);
	
	//二级菜单列表
	if (num == '1') { 
    	var menu_two = '<div class="submenu js-submenu" id="submenu_'+num_id+'">'+
                           '<span class="arrow before-arrow"></span>'+
                           '<span class="arrow after-arrow"></span>'+
                           '<ul id="two_menu_'+num_id+'">'+
                               '<li id="two_menu_num_foot_'+length_lang+'">'+
                                   '<a class="js-submneu-a two_menu_'+num_id+'_'+length_lang+'" href="javascript:void(0);">'+
                                        	'标题'+
                                   '</a>'+
                               '</li>'+
                           '</ul>'+
                   	   '</div>';
 	   $('#one_'+num_id).append(menu_two);
	} else {
        var two_menu = '<li class="line-divide"  id="two_menu_line_num_'+length_lang+'"></li>'+
        '<li id="two_menu_num_foot_'+length_lang+'">'+
            '<a class="js-submneu-a two_menu_'+num_id+'_'+length_lang+'" href="javascript:void(0);">'+
                 	'标题'+
            '</a>'+
        '</li>';
        $('#two_menu_'+num_id).append(two_menu);
	}
	$('.submenu').hide();
	$("#two_menu_num_"+num_id).val(parseInt(length_lang)+parseInt(1));
	shownow(num_id,length_lang);
	
}

function delmenu(type) {
	$("#menu_"+type).remove();
	$('.add-first-nav').show();
	$('.shu_'+type).remove();
	$('.one_'+type).remove();
	
	var menu_num = $('.menu_num').length;
	if (menu_num <= '1') {
    	var width = 268;
	} else {
		var width = 268/(parseInt(menu_num));
	}
	
	$('.one').css('width',width)
}

function addOneMenu() {
	var menu_num = $('.menu_num').length;
	if (menu_num >= '2') {
		$('.add-first-nav').hide();
	} else {
		$('.add-first-nav').show();
	}

	
	var num_num = parseInt($("#num_num").val())+parseInt(1);
	$('#num_num').val(num_num);
	var Ahtml = '<div class=" menu_num" id="menu_num_'+num_num+'" data-id="'+num_num+'" onmouseover="addcha('+num_num+')"  onmouseout="deletecha('+num_num+')" style="border:1px solid #cccdcc;border-radius: 5px;display: table;margin-bottom: 15px;">'+
					'<input type="hidden" id="two_menu_num_'+num_num+'" value="1">'+
                    '<input type="hidden" id="select_menu_now_'+num_num+'" value="0">'+
                    '<input type="hidden" id="info_type_'+num_num+'" name="info['+num_num+'][type]" value=""/>'+
					'<div style="position: relative;">'+
        				'<div class="close-modal js-del-first " id="cha-'+num_num+'" onclick="deleteMenu('+num_num+',0)" style="display: none;">×</div>'+
                        '<div class="" style="float: left">'+
                        	'<div id="second_'+num_num+'">'+
                            	'<div class="one_menu ">'+
                            		'一级页面：'+
                            	'</div>'+
                            	'<div class="one_title_menu second_title" onclick="shownow('+num_num+',0)">'+
                            		'<div class="weixin_one_menu second_title_'+num_num+'">'+
                            			'<input type="hidden" id="info_name_'+num_num+'" name="info['+num_num+'][name]" value="标题">'+
                            			'<span class="h5 one_title_'+num_num+'_0">标题</span>'+
                            			'<span class="opts">'+
                                            '<a href="javascript:void(0);" style="color: #38f;" onclick="$(\'#passw\').window(\'open\');giveIdToopen('+num_num+',0)" class="js-edit-first edit_'+num_num+'_0">编辑</a>'+
                                        '</span>'+
                            		'</div>'+
                            	'</div>'+
                        	'</div>'+
                        	'<div class="add-second-nav-'+num_num+' one_menu" style="margin-left: 10px;margin-top: 30px;">'+
                                '<a href="javascript:void(0);" onclick="addSecond('+num_num+')" class="js-add-second">+ 添加二级菜单</a>'+
                            	'<input type="hidden" value="0" id="second_num">'+
                            '</div>'+
                        '</div>'+
                    	'<div class=""  style="margin-top:15px;margin-left: 189px;">'+
                    		'<div class="menu-content content-'+num_num+'" style="min-height:80px;margin-right: 15px;">'+
                        		'<div class="link-to js-link-to link-to-'+num_num+'--1 hide">'+
                                    '<span class="died-link-to">使用二级菜单后主回复已失效。</span>'+
                                '</div>'+
                            '</div>'+
                            '<div class="select-link js-select-link change-link-'+num_num+'" style="margin-bottom: 10px;">'+
                                '<span class="change-txt">回复内容：</span>'+
                                '<span class="main-link">'+
                                    '<a class="js-modal-txt" data-type="txt" onclick="$(\'#wei_menu_id\').val('+num_num+');$(\'#weilink\').window(\'open\')" href="javascript:void(0);">微链接</a> - '+
                                    '<a class="js-modal-thrid-app" data-type="weapp" onclick="$(\'#wei_menu_id\').val('+num_num+');$(\'#minilink\').window(\'open\')" href="javascript:void(0);">小程序</a>'+
                                '</span>'+
                                '<div class="editor-image js-editor-image"></div>'+
                                '<div class="hide editor-place js-editor-place"></div>'+
                            '</div>'+
                    	'</div>'+
                	'</div>'+
                '</div>';
    $("#js_nav").append(Ahtml);

	var menu = '';
	if (menu_num != '0') {
		menu += '<div class="divide shu_'+num_num+'">&nbsp;</div>';
	}
	menu += '<div class="one one_'+num_num+'" id="one_'+num_num+'" data-id="'+num_num+'">'+
                    '<a class="mainmenu js-mainmenu" href="javascript:void(0);" onclick="showTwoMenu('+num_num+')" data-type="text">'+
                        '<span class="mainmenu-txt mainmenu_txt_'+num_num+'_0">标题</span>'+
                    '</a>'+
                '</div>';
    $('.nav-menu').append(menu);
    
    var width = 268/(parseInt(menu_num)+parseInt(1));
	$('.one').css('width',width)
	$('.submenu').hide();
}

	function atoff() {
		if ($("#onoff").val() == '1') {
			$("#onoffbutton").removeClass('ui-switcher-off');
		  	$("#onoffbutton").addClass("ui-switcher-on"); 
		  	$("#onoff").val("2");
		} else {
			$("#onoffbutton").removeClass('ui-switcher-on');
		  	$("#onoffbutton").addClass("ui-switcher-off"); 
		  	$("#onoff").val("1");
		}
	}