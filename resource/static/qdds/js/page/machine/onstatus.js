/***
 * 上架商品
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */
	
    $(function(){
        var parent_id = 0;
        var selected_category_id = $('#selected_category_id').val();
		var self = $('#category_id').combotree(
		{ 	   
			url:'/?c=Public&a=category',
			lines:true, 	   
			animate:true, 
			editable:true,   	   
			loadFilter: function(data){     	   	
				if (data.success==false) { 	   		
					$.messager.alert('错误', data.msg, 'error');             
				} else {             	
					return data;             
				} 	   
			},   	 		
			required: true, 		
			missingMessage: "请选择分类", 		
			onlyLeafCheck:true, 	   
			onLoadError: function(jqXHR, textStatus, errorThrown) { 	   	
				$.messager.alert('错误', errorThrown, 'error'); 	   
			},
            onLoadSuccess:function(node, data) {
                $('#category_id').combotree('setValues', selected_category_id);
                var tree = $('#category_id').combotree('tree');
                var selected = tree.tree('getSelected');
                //var parent = tree.tree('getParent', selected.target);
                if (selected && selected.parent_id == 797) {
                    if ($("#sale_is_up").is(":checked")) {
                        $('#sale_price').numberbox('disable');
                        $('#sale_up_price').numberbox({required: true});
                    }
                    if ($("#channel_is_up").is(":checked")) {
                        $('#channel_price').numberbox('disable');
                        $('#channel_up_price').numberbox({required: true});
                    }
                    $('.is_up').show();
                } else {
                    $('.is_up').hide();
                    $('#sale_price').numberbox('enable');
                    $('#channel_price').numberbox('enable');
                }
                parent_id = selected?selected.parent_id:0;
            },
			onBeforeSelect:function(node){     
				
				if(node.parent_id == node.root_id ){
                    $('#category_id').combotree('tree').tree('toggle',node.target);
                    $('#category_id').combotree('tree').combo('showPanel');
                    return false;
				}
			},
            onSelect:function (node) {
                //判断是否是黄金
                //var tree = $('#category_id').combotree('tree');
                //var parent = tree.tree('getParent', node.target);
                if (node.parent_id == 797) {
                    if (parent_id != 797) {
                        $('#sale_is_up').attr('checked',false);
                        $('#channel_is_up').attr('checked',false);
                        $('#sale_price').numberbox('enable');
                        $('#channel_price').numberbox('enable');
                        $('#sale_up_price').numberbox({required: false});
                        $('#channel_up_price').numberbox({required: false});
                        $('.is_up').show();
                    }
                } else {
                    $('.is_up').hide();
                    $('#sale_price').numberbox('enable');
                    $('#channel_price').numberbox('enable');
                }
                parent_id = node.parent_id;
            }
		});

        $('#sale_is_up').change(function () {
            if (this.checked) {
                $('#sale_price').numberbox('disable');
                $('#sale_up_price').numberbox({required: true});
            } else {
                $('#sale_price').numberbox('enable');
                $('#sale_up_price').numberbox({required: false});
            }
        });
        $('#channel_is_up').change(function () {
            if (this.checked) {
                $('#channel_price').numberbox('disable');
                $('#channel_up_price').numberbox({required: true});
            } else {
                $('#channel_price').numberbox('enable');
                $('#channel_up_price').numberbox({required: false});
            }
        });
	 });
	
    
	function clearForm(){
		$('#ff').form('clear');
	}
	
	function clearAttrForm(){
		$('#attr').form('clear');
	}



	var fields =  [ [ {
		field : 'id',
		width : 150,
		checkbox : true ,
		title : 'ID'
	}, {
		field : 'name',
		width : 415,
		title : '属性名称'
	}, {
		field : 'alias',
		width : 413,
		title : '属性别称'
	}
	] ];

	var options = {
		url: '/index.php?m=Product&c=Attribute&a=list&format=list',
		singleSelect:false,
	};

		
		// function searchInfo() {
		// var queryData = new Object();
		// queryData['info[name]'] = $('#name').val();
		// queryData['info[alias]'] = $('#alias').val(),
		// queryData['info[value]'] = $('#value').val();
		// queryData['info[value_alias]'] = $('#value_alias').val();
		// queryData['info[start_time]'] = $('#start_time').datebox('getValue');
		// queryData['info[end_time]'] = $('#end_time').datebox('getValue');
		//
	    //
	// 	$('#dg').datagrid({
	// 		title:'',
	// 		width:'100%',
	// 		height:'auto',
	// 		nowrap: true,
	// 		autoRowHeight: true,
	// 		striped: true,
	// 	    url: '/index.php?m=Product&c=Attribute&a=list&format=list',
	// 		remoteSort: false,
	// 		singleSelect:true,
	// 		idField:'id',
	// 		loadMsg:'数据加载中......',
	// 		pageList: [10,20,50],
	// 		columns: fields,
	// 	    singleSelect:false,
	// 		pagination:true,
	// 		rownumbers:true,
	// 		queryParams:queryData,
	// 		onLoadSuccess: function(data){
	//           var panel = $(this).datagrid('getPanel');
	//           var tr = panel.find('div.datagrid-body tr');
	//           tr.each(function(){
	//               var td = $(this).children('td[field="userNo"]');
	//               td.children("div").css({
	//                   //"text-align": "right"
	//                   "height": "50px"
	//               });
	//           });
	//        }
	// 	});
	//
	// }
	// $(function(){
	// 	searchInfo();
	// })
	//
	//  $(".more").click(function(){
	//     $(this).closest(".conditions").siblings().toggleClass("hide");
	// });

	function onstatus(on_status) {
		$("#on_status").val(on_status);
		
		var isValid = $("#ff").form('validate');
		if ( isValid == false) {
			return isValid;
		}
		$("#myEditor").val(editor.html());
		$.ajax({
             url : $('#ff').attr("action"),
             type : "post",
             dataType : "jsonp",
             jsonp : "jsonpcallback",
             data : $('#ff').serialize(),
             success : function(data){
             	
                if (data.code == '200') {
                	if (origin == 'recovery') {
                        location.href="/index.php?m=Recovery&c=Recovery&a=list"
					} else {
                        location.href="/index.php?m=Product&c=Product&a=list"
					}
				} else {
					$.messager.alert('提示', data.msg);
				}
             }
        });
	}

	function shopSetadd() {
		var selectrow = $("#dg").datagrid("getChecked");//获取的是数组，多行数据

		if (selectrow.length == 0) {
			$.messager.alert('提示', '请选择属性！');
			return;
		}
		
		
		for(var i=0;i<selectrow.length;i++){
			var val = selectrow[i];
			var temp_obj=$("tr[data-id='" + val.id + "']");
			
			if (temp_obj.length > 0 ) {
				
			
			} else {
        		var attribute_value = val.values;	
        		var TR_HTML = '<tr data-id="'+val.id+'" id="values_'+val.id+'">' + 
        		'<th class="w9">'+val.name+'</th>';
        		 /*单选*/
        		TR_HTML += '<th>';
        		if (val.input_type == 1) {
        			TR_HTML += '<select name="select[]"  style="width:300px;height:35px;line-height:35px;">';
					$.each(attribute_value,function(k, kval){
						var obj = new Object();
						obj.attribute_id = val.id;
						obj.attribute_name = val.name;
						obj.attribute_value_id = kval.id;
						obj.attribute_value_name = kval.value;
						
						TR_HTML += '<option value=\''+JSON.stringify(obj)+'\'> '+kval.value + '</option>';
						});	
					TR_HTML += '</select>';			
        		}
        		
        		/*多选*/
				if(val.input_type == 2) {
					$.each(attribute_value,function(k, kval){
						var obj = new Object();
						obj.attribute_id = val.id;
						obj.attribute_name = val.name;
						obj.attribute_value_id = kval.id;
						obj.attribute_value_name = kval.value;
						
						TR_HTML += ' <input name="checkbox[]" value=\''+JSON.stringify(obj)+'\' type="checkbox" id="checkbox-'+kval.id+ '"> <label for="checkbox-'+kval.id+ '">'+kval.value + '&nbsp;</label>';
					});	
						
				}
									
        		if(val.input_type == 3) {
					TR_HTML += '<input type="text" class="easyui-textbox"  style="width:300px;height:35px;line-height:35px;" name="input['+val.id+ ']" class="input-text" placeholder="" />';
				}
        		
        		TR_HTML += '</th>';
        		TR_HTML += '<th class="w9">';
				TR_HTML += '<a href="javascript:void(0);" onclick=\'$("#values_'+val.id+ '").remove();\'>删除</a></th></tr>';
				$("#erpAttributeList").append(TR_HTML);	

			}
		}
		$('#passw').window('close')
	}
