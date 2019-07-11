/***
 * 添加群发
 * @version v0.01
 * @author  lqt
 * @time 2018-08-15
 */


    $(function(){
		var submit_value = true;
    	$('#ff').form({
			onSubmit: function(){
				if (!submit_value) {
					$.messager.alert('提示', '请勿重复提交！');
					return false;
				}
				submit_value = false;
				
				//手机号验证
				var mobilesHtml = $('#mobiles').val();
				var mobilesArray = mobilesHtml.split('\n');
				var num = 0;
				var mobile_style = true;
				if (mobilesArray.length > 0) {
					$.each(mobilesArray,function(i,item){
			            var myreg=/^[1][3,4,5,7,8][0-9]{9}$/;
			            if (!myreg.test(item)) {
			            	mobile_style = false;
			            } else {
			                num ++;
			            }						
					});
				} 
			
	            if (num == 0) {
	            	$.messager.alert('提示', '至少输入一个正确的手机号！');
	            	submit_value = true;
	                return false;
	            }	
				
	            if (!mobile_style) {
					$.messager.alert('提示', '请输入正确的手机号！');
					submit_value = true;
	                return false;	
	            }	
	            
				var content = $('#content').val();
	            if (!content) {
	            	$.messager.alert('提示', '请输入短信内容！');
	            	submit_value = true;
	                return false;
	            }		
	            
				var type = $("input[name='info[type]']:checked").val();
				var send_time = $('#send_time').datebox('getValue'); 
	            if (type == 2 && !send_time) {
	            	$.messager.alert('提示', '请输入定时发送时间！');
	            	submit_value = true;
	                return false;
	            }		
	            
		    	if (!$("#read").is(":checked")) {
		    		$.messager.alert('提示', '请阅读并同意《短信服务协议》');
		    		submit_value = true;
		    		return false;
		    	}	
		    	
		    	return true;				
			},    		
			success:function(data){
				submit_value = true;
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Msgmass&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}
	
	//会员弹框	
	function clearUserForm(){
		$('#user_ff').form('clear');
		searchUser();
	}
	
	var userFields =  [ [ {
		field:'operate',
		title:'选择',
		width: 100,
		align:'left', 
		checkbox:true
	}, {		  
	    field : 'id',  
	    width : 150, 
	    checkout : true, 
	    title : 'ID'  
	}, {  
	    field : 'name',  
	    width : 300,  
	    title : '会员名称'  
	}, {  
	    field : 'mobile',  
	    width : 300,   
	    title : '会员手机'  
	} 
	] ];


		
	function searchUser() {
		var queryData = new Object();
		queryData['info[user_name]'] = $('#search_user_name').val();
		queryData['info[user_mobile]'] = $('#search_user_mobile').val();		
	    
		$('#user_dg').datagrid({
			title:'',
			width:'100%',
			height:'auto',
			nowrap: true,
			autoRowHeight: true,
			striped: true,
		    url: '/index.php?m=User&c=User&a=list&format=list',
			remoteSort: false,
			idField:'id',
			loadMsg:'数据加载中......',  
			pageList: [10,20,50],		
			columns: userFields,
		    singleSelect:false,
			pagination:true,
			rownumbers:true,
			queryParams:queryData
		});
			
	}
	
	function userSelect() {
		var mobilesHtml = $('#mobiles').val();
		var mobilesArray = mobilesHtml.split('\n');
		var num = 0;
        var mobilesArrayNew = [];
		if (mobilesArray.length > 0) {
			$.each(mobilesArray,function(i,item){
				if (item != '') {
					num ++;
                    mobilesArrayNew.push(item);
				}
			});
		}
			
		var rows = $('#user_dg').datagrid('getChecked');
		if (rows.length > 0) {
			$.each(rows,function(i,item){
                if (item.mobile) {
                    if ($.inArray(item.mobile, mobilesArray) == -1) {
                        num++;
                        mobilesArrayNew.push(item.mobile);
                    }
                }
			});
		}
        mobilesHtml = mobilesArrayNew.join('\n');
		$('#mobiles').val(mobilesHtml);	
		$('#mobile_num').html(num);	
		$('#user_dialog').window('close');
	}
	
	$("#mobiles").blur(function(){
        var mobilesHtml = $('#mobiles').val();
        var mobilesArray = mobilesHtml.split('\n');
        var num = 0;
        var mobilesArrayNew = [];
        if (mobilesArray.length > 0) {
            $.each(mobilesArray,function(i,item){
                if (item != '') {
                    num ++;
                    mobilesArrayNew.push(item);
                }
            });
        }
        mobilesHtml = mobilesArrayNew.join('\n');
        $('#mobiles').val(mobilesHtml);
        $('#mobile_num').html(num);
    });
	
	$("#content").keyup(function(){
		var content = $('#content').val();
			content = '【'+sms_name+'】'+content;
		var content_length = content.length; 
		/**
		 * 短信长度判断规则
		 * 1.英文，符号，汉字算一个字
		 * 2.长度小于等于70，算一条短信
		 * 3.长度大于70，每条短信按照67个字来算。
		 * */	
		var content_num = 1;
		if (content_length > 70) {
			content_num = Math.ceil(content_length / 67);
		}		 
		$('#sms-content').html(content);	    
		$('#content_length').html(content_length);	    
		$('#content_num').html(content_num);	    
	});	

	