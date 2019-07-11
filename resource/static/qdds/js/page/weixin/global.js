
/*
var web_domain='http://www.itokit.com';
var static_domain='http://weixin.itokit.com';
var edit_options = {
	autoHeightEnabled:false,
	catchRemoteImageEnable:false,
	imagePopup:false,
	toolbars:[['fullscreen','fontfamily', 'fontsize', 'forecolor', 'backcolor', 
		'|', 'bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 
		'|', 'indent', '|', 'justifyleft', 'justifycenter', 'justifyright',
		'|', "undo","redo", 
		'|', 'link', 'unlink', '|', 'insertimage', '|', 'insertvideo', 'removeformat', '|',
		'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', 'charts']]
};*/

var global_obj={
	file_upload:function(file_input_obj, filepath_input_obj, img_detail_obj, size){
		
		
		alert("sdfsdfsdfsdf");return false;
		
		
		var multi=(typeof(arguments[4])=='undefined')?false:arguments[4];
		var queueSizeLimit=(typeof(arguments[5])=='undefined')?5:arguments[5];
		var callback=arguments[6];
		var fileExt=(typeof(arguments[7])=='undefined')?'*.jpg;*.png;*.gif;*.jpeg;*.bmp':arguments[7];
		var fileSize=(typeof(arguments[8])=='undefined')?300:arguments[8];
		file_input_obj.omFileUpload({
			action:'./',
			actionData:{
				do_action:'action.file_upload',
				size:size
			},
			fileExt:fileExt,
			fileDesc:'Files',
			sizeLimit:fileSize*1024,
			onError:function(ID, fileObj, errorObj, event){
				if(errorObj.type=='File Size'){
					alert('上传的文件大小不能超过'+fileSize+'KB！');
				}
			},
			autoUpload:true,
			multi:multi,
			queueSizeLimit:queueSizeLimit,
			swf:'/Public/swf/fileupload.swf?r='+Math.random(),
			method:'post',
			onComplete:function(ID, fileObj, response, data, event){
				var jsonData=eval('('+response+')');
				//console.log(jsonData);
				if(jsonData.status==1){
					if($.isFunction(callback)){
                        if((window.location.href.indexOf('member/myfiles')>-1) || (window.location.href.indexOf('column/d/article_edit')>-1)){
							callback(jsonData);
                        }else{
                            callback(jsonData.imgpath);
                        }
					}else{
						filepath_input_obj.val(jsonData.imgpath);
						img_detail_obj.html(global_obj.img_link(jsonData.imgpath));
					}
				}else{
					alert('图片上传失败，出现未知错误！');
				};
			}
		});
	},
	
	img_link:function(img){
		if(img){
			return '<a href="'+img+'" target="_blank"><img src="'+img+'"></a>';
		}
	},
	
	check_form:function(obj){
		var flag=false;
		obj.each(function(){
			if($(this).val()==''){
				$(this).css('border', '1px solid red');
				flag==false && ($(this).focus());
				flag=true;
			}else{
				$(this).removeAttr('style');
			}
		});
		return flag;
	},
	
	config_form_init:function(){
                        if($('#ReplyImgUpload').length > 0){
                            global_obj.file_upload($('#ReplyImgUpload'), $('#config_form input[name=ReplyImgPath]'), $('#ReplyImgDetail'));
                            $('#ReplyImgDetail').html(global_obj.img_link($('#config_form input[name=ReplyImgPath]').val()));
                        }
		// $('#config_form').submit(function(){return false;});
		// $('#config_form input:submit').click(function(){
			// if(global_obj.check_form($('*[notnull]'))){return false;};
// 			
			// $(this).attr('disabled', true);
			// $.post('?', $('#config_form').serialize(), function(data){
				// if(data.status==1){
					// window.location.reload();
				// }else{
					// alert(data.msg);
					// $('#config_form input:submit').attr('disabled', false);
				// }
			// }, 'json');
		// });
	},
	
	reserve_form_init:function(){
		$('.reverve_field_table .input_add').click(function(){
			$('.reverve_field_table tr[FieldType=text]:hidden').eq(0).show();
			if(!$('.reverve_field_table tr[FieldType=text]:hidden').size()){
				$(this).hide();
			}
		});
		$('.reverve_field_table .input_del').click(function(){
			$('.reverve_field_table .input_add').show();
			$(this).parent().parent().hide().find('input').val('');
		});
		$('.reverve_field_table .select_add').click(function(){
			$('.reverve_field_table tr[FieldType=select]:hidden').eq(0).show();
			if(!$('.reverve_field_table tr[FieldType=select]:hidden').size()){
				$(this).hide();
			}
		});
		$('.reverve_field_table .select_del').click(function(){
			$('.reverve_field_table .select_add').show();
			$(this).parent().parent().hide().find('input').val('');
		});
	},
            win_alert:function(tips, handle,btn_text){
                        var btn_text = btn_text||"好";
		$('body').prepend('<div id="global_win_alert"><div>'+tips+'</div><h1>'+btn_text+'</h1></div>');
		$('#global_win_alert').css({
			position:'fixed',
			left:$(window).width()/2-125,
			top:'30%',
			background:'#fff',
			border:'1px solid #ccc',
			opacity:0.95,
			width:250,
			'z-index':10000,
			'border-radius':'8px'
		}).children('div').css({
			'text-align':'center',
			padding:'30px 10px',
			'font-size':16
		}).siblings('h1').css({
			height:40,
			'line-height':'40px',
			'text-align':'center',
			'border-top':'1px solid #ddd',
			'font-weight':'bold',
			'font-size':20
		});
		$('#global_win_alert h1').click(function(){
			$('#global_win_alert').remove();
		});
		if($.isFunction(handle)){
			$('#global_win_alert h1').click(handle);
		}
	},
	
	map_init:function(){
		var myAddress=$('input[name=Address]').val();
		var destPoint=new BMap.Point($('input[name=PrimaryLng]').val(), $('input[name=PrimaryLat]').val());
		var map=new BMap.Map('map');
		map.centerAndZoom(new BMap.Point(destPoint.lng, destPoint.lat), 20);
		map.enableScrollWheelZoom();
		map.addControl(new BMap.NavigationControl());
		var marker=new BMap.Marker(destPoint);
		map.addOverlay(marker);
		
		map.addEventListener('click', function(e){
			destPoint=e.point;
			set_primary_input();
			map.clearOverlays();
			map.addOverlay(new BMap.Marker(destPoint)); 
		});
		
		var ac=new BMap.Autocomplete({'input':'Address','location':map});
		ac.addEventListener('onhighlight', function(e) {
			ac.setInputValue(e.toitem.value.business);
		});
		
		ac.setInputValue(myAddress);
		ac.addEventListener('onconfirm', function(e) {//鼠标点击下拉列表后的事件
			var _value=e.item.value;
			myAddress=_value.business;
			ac.setInputValue(myAddress);
			
			map.clearOverlays();    //清除地图上所有覆盖物
			local=new BMap.LocalSearch(map, {renderOptions:{map: map}}); //智能搜索
			local.setMarkersSetCallback(markersCallback);
			local.search(myAddress);
		});
		
		var markersCallback=function(posi){
			$('#Primary').attr('disabled', false);
			if(posi.length==0){
				alert('定位失败，请重新输入详细地址或直接点击地图选择地点！');
				return false;
			}
			for(var i=0; i<posi.length; i++){
				if(i==0){
					destPoint=posi[0].point;
					set_primary_input();
				}
				posi[i].marker.addEventListener('click', function(data){
					destPoint=data.target.getPosition(0);
				});  
			}
		}
		
		var set_primary_input=function(){
			$('input').filter('[name=PrimaryLng]').val(destPoint.lng).end().filter('[name=PrimaryLat]').val(destPoint.lat);
		}
		
		$('input[name=Address]').keyup(function(event){
			if(event.which==13){
				$('#Primary').click();
			}
		});
		
		$('#Primary').click(function(){
			if(global_obj.check_form($('input[name=Address]'))){return false};
			$(this).attr('disabled', true);
			local=new BMap.LocalSearch(map, {renderOptions:{map: map}}); //智能搜索
			local.setMarkersSetCallback(markersCallback);
			local.search($('input[name=Address]').val());
			return false;
		});
	}
}

$.fn.numeral = function(bl){
	  $(this).keypress(function(e){
 		  var keyCode=e.keyCode?e.keyCode:e.which;
		if(bl){//浮点数
		  if((this.value.length==0 || this.value.indexOf(".")!=-1) && keyCode==46) return false;
		  return keyCode>=48&&keyCode<=57||keyCode==46||keyCode==8;
		}else{//整数
		  return  keyCode>=48&&keyCode<=57||keyCode==8;
		}
	  });
	  $(this).bind("copy cut paste", function (e) { // 通过空格连续添加复制、剪切、粘贴事件 
		  if (window.clipboardData)//clipboardData.setData('text', clipboardData.getData('text').replace(/\D/g, ''));
			  return !clipboardData.getData('text').match(/\D/);
		  else 
			  event.preventDefault();
	   }); 
	  $(this).bind("dragenter",function(){return false;});
	  $(this).css("ime-mode","disabled");
	  $(this).bind("focus", function() {   
        if (this.value.lastIndexOf(".") == (this.value.length - 1)) {   
            this.value = this.value.substr(0, this.value.length - 1);
        } else if (isNaN(this.value)) {   
            this.value = "";   
        }   
    });   
}