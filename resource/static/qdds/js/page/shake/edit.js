/***
 * 摇一摇
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-21
 */

    $(function(){
    	
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Shake&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		setm();
		select_coupan();
		select_gift();
    });
    
	$('.easyui-tabs1').tabs({
      tabHeight: 36
    });
    $(window).resize(function(){
    	$('.easyui-tabs1').tabs("resize");
    }).resize();
	$('.easyui-tabs2').tabs({
      tabHeight: 36
    });
    $(window).resize(function(){
    	$('.easyui-tabs2').tabs("resize");
    }).resize();    
	
	function next() {
		$('.easyui-tabs1').tabs({
	      	tabHeight: 36,
	      	selected:1  
	    });		
	}		
	
	function per() {
		$('.easyui-tabs1').tabs({
	      	tabHeight: 36,
	      	selected:0  
	    });			
	}
	
	//设置默认选中
	function setm() {
		var prize_type_1 = $("input[name='info[prize][1][prize_type]']:checked").val(); 
		var prize_type_2 = $("input[name='info[prize][2][prize_type]']:checked").val(); 
		var prize_type_3 = $("input[name='info[prize][3][prize_type]']:checked").val(); 
		var prize_type_4 = $("input[name='info[prize][4][prize_type]']:checked").val(); 
		prize_type(1,prize_type_1);
		prize_type(2,prize_type_2);
		prize_type(3,prize_type_3);
		prize_type(4,prize_type_4);
	}
	
	function prize_type(level,type) {
		$("#prize_type_"+level+"_1").hide();
		$("#prize_type_"+level+"_2").hide();
		$("#prize_type_"+level+"_3").hide();		
		$("#prize_type_"+level+"_"+type).show();		
	}
	
    $('#name').textbox({
	    stopFirstChangeEvent: false,
	    onChange: function() {
	        var options = $(this).textbox('options');
	        if(options.stopFirstChangeEvent) {
	            options.stopFirstChangeEvent = false;
	            return;
	        }
	        notew();
	    }
	});  
	
    $('#starttime').datetimebox({
	    stopFirstChangeEvent: false,
	    onChange: function() {
	        var options = $(this).datetimebox('options');
	        if(options.stopFirstChangeEvent) {
	            options.stopFirstChangeEvent = false;
	            return;
	        }
	        notew();
	    }
	});  
	  	  		
    $('#endtime').datetimebox({
	    stopFirstChangeEvent: false,
	    onChange: function() {
	        var options = $(this).datetimebox('options');
	        if(options.stopFirstChangeEvent) {
	            options.stopFirstChangeEvent = false;
	            return;
	        }
	        notew();
	    }
	});    	 	
    $('#number_1').numberbox({
	    stopFirstChangeEvent: false,
	    onChange: function() {
	        var options = $(this).numberbox('options');
	        if(options.stopFirstChangeEvent) {
	            options.stopFirstChangeEvent = false;
	            return;
	        }
	        notew();
	    }
	});    	 	
    $('#number_2').numberbox({
	    stopFirstChangeEvent: false,
	    onChange: function() {
	        var options = $(this).numberbox('options');
	        if(options.stopFirstChangeEvent) {
	            options.stopFirstChangeEvent = false;
	            return;
	        }
	        notew();
	    }
	});    	 	
	
	function notew() {
		var noteHtml = '';
		//活动名称
		var name = $('#name').val();
		name = name?name:'';
		noteHtml += name + '\r';
		//开始时间
		var start_time = $("#starttime").datebox('getValue');
		start_time = start_time?start_time:'';
		noteHtml += '开始时间：'+ start_time + '\r';		
		//结束时间
		var end_time = $("#endtime").datebox('getValue');
		end_time = end_time?end_time:'';	
		noteHtml += '结束时间：'+ end_time + '\r';		
		//参与次数
		var content = "参与次数：";
		var spey = $("input[name='info[spey]']:checked").val(); 
		spey = spey?spey:1;
		if (spey == 1) {//每人每天
			var number_1 = $("#number_1").numberbox('getValue');
			content += '每人每天 '+number_1+'次';
		} else if (spey == 2) {//每天共
			var number_2 = $("#number_2").numberbox('getValue');
			content += '每天共 '+number_2+'次';
		}	
		noteHtml += content + '\r';	
		$('#note').val(noteHtml);
	}
	
	function select_coupan()
	{
		$.ajax({
		        type: "POST",
		        url: '/index.php?m=Marketing&c=Coupan&a=list&format=list',
			  	data: {
			  		info:{
			  			status:2
			  		},
			  		page:1,
			  		rows:50
			  	},
			  	dataType:"json",
			    success:function(res){
			    	if (res.code == '200') {
			    		//
				        $('.select_coupan').combobox({
				            data:res.rows,
				            valueField: 'id',
				            textField: 'name'
				        });			    		
					}
				}	 	    	
		    });	
	}
	
	function select_gift()
	{
		$.ajax({
		        type: "POST",
		        url: '/index.php?m=Marketing&c=Gift&a=list&format=list',
			  	data: {
			  		page:1,
			  		rows:50
			  	},
			  	dataType:"json",
			    success:function(res){
			    	if (res.code == '200') {
				        $('.select_gift').combobox({
				            data:res.rows,
				            valueField: 'id',
				            textField: 'name'
				        });			    		
					}
				}	 	    	
		    });	
	}
