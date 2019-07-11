/***
 * 添加商品
 * @version v0.01
 * @author zhaoyu
 * @time 2018-05-09
 */


    $(function(){

    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Gift&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
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
			onBeforeSelect:function(node){     
				
				if(node.parent_id == node.root_id ){
                    $('#category_id').combotree('tree').tree('toggle',node.target);
                    $('#category_id').combotree('tree').combo('showPanel');
                    return false;
				}         
			}     
		});
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}