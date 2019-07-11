/***
 * 编辑鉴定
 * @version v0.01
 * @author huangxianguo
 * @time 2018-11-27
 */

    $(function(){

    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Appraisal&c=Appraisal&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
    	var parent_id = 0;
        var selected_category_id = $('#selected_category_id').val();
		var self = $('#category_id').combotree(
		{ 	   
			url:'/?c=Public&a=getBadCategory',
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
                if (selected.parent_id == 797) {
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
                parent_id = selected.parent_id;
            },
			onBeforeSelect:function(node){     
				
				if(node.parent_id == node.root_id ){
                    $('#category_id').combotree('tree').tree('toggle',node.target);
                    $('#category_id').combotree('tree').combo('showPanel');
                    return false;
				}         
			}

		});
    });
