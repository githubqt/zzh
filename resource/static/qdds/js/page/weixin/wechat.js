
var wechat_obj={
	material_init:function(){
		$('#material>.list').masonry({itemSelector:'.item', columnWidth:367});
	},	
	material_multi_init:function(){
		var material_multi_list_even=function(){
			$('.multi .first, .multi .list').each(function(){
				var children=$(this).children('.control');
				$(this).mouseover(function(){children.css({display:'block'});});
				$(this).mouseout(function(){children.css({display:'none'});});
				
				children.children('a[href*=#del]').click(function(){
					if($('.multi .list').size()<=1){
						alert('无法删除，多条图文至少需要2条消息！');
						return false;
					}
					if(confirm('删除后不可恢复，继续吗？')){
						$(this).parent().parent().remove();
						$('.multi .first a[href*=#mod]').click();
						$('.mod_form').css({top:37});
						subid = $(this).parent().parent().children('input[name=subid\\[\\]]');
						if(subid.val() > 0) {
							$.get('/member/material/index', {do_action:'material.material_multi_del', subid:subid.val()});
						}
					}
				});
				
				children.children('a[href*=#mod]').click(function(){
					var position=$(this).parent().offset();
					var material_form_position=$('#material_form').offset();
					var cur_id='#'+$(this).parent().parent().attr('id');
					$('.mod_form').css({top:position.top-material_form_position.top});
					//$('.mod_form input[name=inputName]').val($(cur_id+' input[name=Name\\[\\]]').val());
					$('.mod_form input[name=inputTitle]').val($(cur_id+' input[name=Title\\[\\]]').val());					
					$('.mod_form input[name=inputAuthor]').val($(cur_id+' input[name=Author\\[\\]]').val());
					$('.mod_form input[name=inputImgMedia]').val($(cur_id+' input[name=ImgMedia\\[\\]]').val());
					$('.mod_form input[name=inputContentUrl]').val($(cur_id+' input[name=ContentUrl\\[\\]]').val());
					$('.mod_form input[name=thumb_media_id]').val($(cur_id+' input[name=thumb_media_id\\[\\]]').val());
					if($(cur_id+' input[name=ShowCoverPic\\[\\]]').val()==2){
						$('.mod_form input[name=inputShowCoverPic]').prop("checked",true);
					}else{ 
					    $('.mod_form input[name=inputShowCoverPic]').prop("checked",false);
					}
					editor.html($(cur_id+' textarea[name=Richtext\\[\\]]').text()); 
					$('.mod_form input[name=inputUrl]').val($(cur_id+' input[name=Url\\[\\]]').val());
					$('.mod_form select[name=inputUrl]').find("option[value='"+$(cur_id+' input[name=Url\\[\\]]').val()+"']").attr("selected", true);
//					if($("select[name=inputUrl]").val()!=""){
//						$('#baidu_editor').hide();
//					}else{
//						$('#baidu_editor').show();
//					}
					$('.big_img_size_tips').html(cur_id=='#multi_msg_0'?'640*360px':'300*300px');
					$('.multi').data('cur_id', cur_id);
					// global_obj.file_upload($('#MsgFileUpload'), $(cur_id+' input[name=ImgPath\\[\\]]'), $(cur_id+' .img'));
					
					if (cur_id=='#multi_msg_0') {
						$("#imgsize").html("（图片建议尺寸：900 x 500像素）")
					} else {
						$("#imgsize").html("（图片建议尺寸：200 x 200像素）")
					}
				});
				$('.mod_form select[name=inputUrl]').find("option[value='"+$('input[name=Url\\[\\]]').val()+"']").attr("selected", true);
			});
		}
		
		//global_obj.file_upload($('#MsgFileUpload'), $('.multi .first input[name=ImgPath\\[\\]]'), $('.first .img'));
		$('.multi').data('cur_id', '#'+$('.multi .first').attr('id'));
        $('.mod_form input').filter('[name=inputName]').on('keyup paste blur', function(){
			var cur_id=$('.multi').data('cur_id');
			$(cur_id+' input[name=Name\\[\\]]').val($(this).val());
			$(cur_id+' .name').html($(this).val());
		})		
		$('.mod_form input').filter('[name=inputTitle]').on('keyup paste blur', function(){
			var cur_id=$('.multi').data('cur_id');
			$(cur_id+' input[name=Title\\[\\]]').val($(this).val());
			$(cur_id+' .title').html($(this).val());
		})
        $('.mod_form input').filter('[name=inputAuthor]').on('keyup paste blur', function(){
			var cur_id=$('.multi').data('cur_id');
			$(cur_id+' input[name=Author\\[\\]]').val($(this).val());
			$(cur_id+' .author').html($(this).val());
		})
        $('.mod_form input').filter('[name=inputImgMedia]').on('keyup paste blur', function(){
			var cur_id=$('.multi').data('cur_id');
			$(cur_id+' input[name=ImgMedia\\[\\]]').val($(this).val());
			$(cur_id+' .imgmedia').html($(this).val());
		})		
		$('.mod_form input').filter('[name=thumb_media_id]').on('keyup paste blur', function(){
			var cur_id=$('.multi').data('cur_id');
			$(cur_id+' input[name=thumb_media_id\\[\\]]').val($(this).val());
			$(cur_id+' .imgmedia').html($(this).val());
		})		
        $('.mod_form input').filter('[name=inputContentUrl]').on('keyup paste blur', function(){
			var cur_id=$('.multi').data('cur_id');
			$(cur_id+' input[name=ContentUrl\\[\\]]').val($(this).val());
			$(cur_id+' .contenturl').html($(this).val());
		})		
                $('.mod_form input').filter('[name=inputShowCoverPic]').click(function(){
			var cur_id=$('.multi').data('cur_id');
            var scd = $(this).prop("checked")?2:'';
			$(cur_id+' input[name=ShowCoverPic\\[\\]]').val(scd);
		});
		$('.mod_form select').filter('[name=inputUrl]').change(function(){
			var cur_id=$('.multi').data('cur_id');
			$(cur_id+' input[name=Url\\[\\]]').val($(this).val());
		});
		
		if($("select[name=inputUrl]").val()!=""){
			$('#baidu_editor').hide();
		}
		$("select[name=inputUrl]").change(function(){
			if($(this).val()==""){
				$('#baidu_editor').show();
			}else{
				$('#baidu_editor').hide();
			}
		});
		material_multi_list_even();
		$('a[href=#add]').click(function(){
			$(this).blur();
			if($('.multi .list').size()>=7){
				alert('你最多只可以加入8条图文消息！');
				return false;
			}
			$('.multi .list, a[href*=#mod], a[href*=#del]').off();
			$('<div class="list" id="id_'+Math.floor(Math.random()*1000000)+'">'+$('.multi .list:last').html()+'</div>').insertAfter($('.multi .list:last'));
			$('.multi .list:last').children('.info').children('.title').html('标题').siblings('.img').html('缩略图');
			$('.multi .list:last input').filter('[name=subid\\[\\]]').val('').end()
				.filter('[name=ID\\[\\]]').val('').end()
				.filter('[name=Title\\[\\]]').val('').end()
				.filter('[name=Author\\[\\]]').val('').end()
				.filter('[name=ImgMedia\\[\\]]').val('').end()
				.filter('[name=thumb_media_id\\[\\]]').val('').end()
				.filter('[name=ContentUrl\\[\\]]').val('').end()
				.filter('[name=ShowCoverPic\\[\\]]').val('').end()
				.filter('[name=ImgPath\\[\\]]').val('');
            	$('.multi .list:last textarea[name=Richtext\\[\\]]').text('');
			material_multi_list_even();
		});
	},
	
	url_init:function(){
		// $('#add_form').submit(function(){
			// if(global_obj.check_form($('*[notnull]'))){return false};
			// $('#add_form input:submit').attr('disabled', true);
			// return true;
		// });
	},
	
	attention_init:function(){
		var display_row=function(){
			if($('select[name=replay_type]').val()==0){
				$('#text_msg_row').show();
				$('#img_msg_row').hide();
			}else{
				$('#text_msg_row').hide();
				$('#img_msg_row').show();
			}
		}
		
		display_row();
		$('select[name=replay_type]').on('change blur', display_row);
		// $('#attention_reply_form').submit(function(){
			// $('#attention_reply_form input:submit').attr('disabled', true);
		// });
	},
	
	reply_keyword_init:function(){
		var display_row=function(){
			if($('select[name=replay_type]').val()==0){
				$('#text_msg_row').show();
				$('#img_msg_row').hide();
			}else{
				$('#text_msg_row').hide();
				$('#img_msg_row').show();
			}
		}
		
		display_row();
		$('select[name=replay_type]').on('change blur', display_row);
		// $('#keyword_reply_form').submit(function(){return false;});
		// $('#keyword_reply_form input:submit').click(function(){
			// if($('select[name=replay_type]').val()==0){			
				// if(global_obj.check_form($('*[notnull], textarea[name=TextContents]'))){return false};
			// }else{
				// if(global_obj.check_form($('*[notnull]'))){return false};
			// }
// 			
			// $(this).attr('disabled', true);
			// $.post('?', $('form').serialize(), function(data){
				// if(data.status==1){
					// window.location='/member/wechat/reply_keyword';
				// }else{
					// alert(data.msg);
					// $('#keyword_reply_form input:submit').attr('disabled', false);
				// }
			// }, 'json');
		// })
	},
	
	set_token_init:function(){
		// $('#set_token_form').submit(function(){return false;});
		// $('#set_token_form input:submit').click(function(){
			// if(global_obj.check_form($('*[notnull]'))){return false};
// 			
			// var btn_value=$('#set_token_form input:submit').val();
			// $('.set_token_msg').css({display:'none'}).html('');
			// $(this).val('对接中，请耐心等待...').attr('disabled', true);
// 			
			// $.post('?', $('form').serialize(), function(data){
				// if(data.status==1){
					// window.location='/member/wechat/set_token';
				// }else{
					// $('.set_token_msg').css({display:'block'}).html(data.msg);
					// $('#set_token_form input:submit').val(btn_value).attr('disabled', false);
				// }
			// }, 'json');
		// });
		// $('#set_token_form input[name=submit_button_sd]').click(function() {
			// if(global_obj.check_form($('*[notnull]'))){return false};
			// $.post('?', $('form').serialize()+'&do_action=wechat.token_set_sd', function(data){
				// if(data.status==1){
					// window.location='/member/wechat/set_token';
				// }else{
					// $('.set_token_msg').css({display:'block'}).html(data.msg);
				// }
			// }, 'json');
		// });
	},
	
	menu_init:function(){
		$('#wechat_menu .m_lefter dl').dragsort({
			dragSelector:'dd',
			dragEnd:function(){
				var data=$(this).parent().children('dd').map(function(){
					return $(this).attr('MId');
				}).get();
				$.get('/member/wechat/menu', {do_action:'wechat.menu_order', sort_order:data.join('|')});
			},
			dragSelectorExclude:'ul, a',
			placeHolderTemplate:'<dd class="placeHolder"></dd>',
			scrollSpeed:5
		});
		
		$('#wechat_menu .m_lefter ul').dragsort({
			dragSelector:'li',
			dragEnd:function(){
				var data=$(this).parent().children('li').map(function(){
					return $(this).attr('MId');
				}).get();
				$.get('/member/wechat/menu', {do_action:'wechat.menu_order', sort_order:data.join('|')});
			},
			dragSelectorExclude:'a',
			placeHolderTemplate:'<li class="placeHolder"></li>',
			scrollSpeed:5
		});
		
		$('#wechat_menu .m_lefter ul li').hover(function(){
			$(this).children('.opt').show();
		}, function(){
			$(this).children('.opt').hide();
		});
		
		var display_row=function(){
			var v=$('#wechat_menu_form select[name=replay_type]').val();
			if(v==0){
				$('#img_msg_row, #url_msg_row').hide();
				$('#text_msg_row').show();
			}else if(v==1){
				$('#text_msg_row, #url_msg_row').hide();
				$('#img_msg_row').show();
			}else{
				$('#text_msg_row, #img_msg_row').hide();
				$('#url_msg_row').show();
			}
		}
		
		display_row();
		$('#wechat_menu_form select[name=replay_type]').on('change blur', display_row);
		// $('#wechat_menu_form').submit(function(){return false;});
		// $('#wechat_menu_form input:submit').click(function(){
			// if(global_obj.check_form($('*[notnull]'))){return false};
// 			
			// $(this).attr('disabled', true);
			// $.post('?', $('form').serialize(), function(data){
				// if(data.status==1){
					// window.location='/member/wechat/menu';
				// }else{
					// alert(data.msg);
					// $('#wechat_menu_form input:submit').attr('disabled', false);
				// }
			// }, 'json');
		// })
		
		$('#wechat_menu .publish .btn_green').click(function(){
			var btn_value=$(this).val();
			$(this).val('发布中，请耐心等待...').attr('disabled', true);
			$.get('?do_action=wechat.menu_publish', '', function(data){
				$('#wechat_menu .publish .btn_green').val(btn_value).attr('disabled', false);
				if(data.status==1){
					alert('菜单发布成功，24小时后可看到效果，或取消关注再重新关注可即时看到效果！');
				}else{
					alert(data.msg);
				}
			}, 'json');
		});
		
		$('#wechat_menu .publish .btn_gray').click(function(){
			var btn_value=$(this).val();
			$(this).val('删除中...').attr('disabled', true);
			$.get('?do_action=wechat.menu_wx_del', '', function(data){
				$('#wechat_menu .publish .btn_gray').val(btn_value).attr('disabled', false);
				if(data.status==1){
					alert('菜单删除成功，24小时后可看到效果，或取消关注再重新关注可即时看到效果！');
				}else{
					alert(data.msg);
				}
			}, 'json');
		});
	},
	
	auth_init:function(){
		// $('#wechat_auth_form').submit(function(){
			// if(global_obj.check_form($('*[notnull]'))){return false};
			// return true;
		// });
		// $('#wechat_auth_form input:submit').click(function(){
			// if(global_obj.check_form($('*[notnull]'))){return false};
			// $(this).submit();
			// return true;
			// $(this).attr('disabled', true);
			// $.post('?', $('#wechat_auth_form').serialize(), function(data){
				// if(data.status==1){
					// window.location='?m=wechat&a=auth';
				// }else{
					// alert('设置失败，出现未知错误！');
				// }
			// }, 'json');
		// });
	},
	
	paytype_init:function() {
		$('#wechat_paytype_form input:checkbox').each(function(index, element){
			var obj = $(element).parent().parent().parent().next();
			if(!$(element).is(':checked')){
				obj.hide();
			}
			$(element).click(function(){
				if($(this).is(':checked')){
					obj.show();
				}else{
					obj.hide();
				}
			});
		});
		
		// $('#wechat_paytype_form').submit(function(){return false;});
		// $('#wechat_paytype_form .submit input').click(function(){
			// $(this).attr('disabled', true);
			// $.post('?', $('#wechat_paytype_form').serialize(), function(data){
				// if(data.status == 1){
					// window.location='/member/wechat/paytype';
				// }else{
					// $('#wechat_paytype_form .submit input').attr('disabled', false);
				// }
			// }, 'json');
		// });
	}
}