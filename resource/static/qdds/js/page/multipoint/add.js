/***
 * 添加多点
 * @version v0.01
 * @author huangxainguo
 * @time 2018-05-21
 */


    $(function(){
    	area_child(0,1);	 
    	$('#ff').form({
			success:function(data){
				var data = JSON.parse(data);
				if (data.code == '200') {
					location.href="/index.php?m=Marketing&c=Multipoint&a=list"
				} else {
					$.messager.alert('提示', data.msg);
				}
			}
		});
		
    	init();
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}
	
	
	function editType() {
		var type = $("#data_type").val();
		if (type == '1') {
			$("#type_one").show();
			$("#type_two").hide();
			$("#type_three").hide();
		} else if (type == '2') {
			$("#type_one").hide();
			$("#type_two").show();
			$("#type_three").hide();
		} else if (type == '3') {
			$("#type_one").hide();
			$("#type_two").hide();
			$("#type_three").show();
		} 
	}
	
	
	
	function timeType(type) {
		if (type == 1) {
			$("#hide_num").hide();
		} else {
			$("#hide_num").show();
		}
	}
	
	
	
	
	
	// function clearAttrForm(){
	// 	$('#attr').form('clear');
	// 	searchInfo();
	// }
	
	
	
	// var fields =  [ [
	//  {
	// 	field:'operate',
	// 	title:'选择',
	// 	width: 40,
	// 	align:'left',
	//     formatter:function(value, row, index){
	//         var str = '<input type="radio" style="width: 16px;height: 16px" name="status" value="'+row.id+'" data-name="'+row.name+'" />';
	//
	//          return str;
	//     }
	// } , {
	//     field : 'id',
	//     width : 50,
	//     title : 'ID'
	// }, {
	//     field : 'name',
	//     width : 200,
	//     title : '商品名称'
	// }, {
	//     field : 'self_code',
	//     width : 100,
	//     title : '商品编码'
	// } , {
	//     field : 'brand_name',
	//     width : 103,
	//     title : '品牌名称'
	// }  , {
	//     field : 'category_name',
	//     width : 180,
	//     title : '分类名称'
	// }  , {
	//     field : 'market_price',
	//     width : 83,
	//     title : '公价'
	// }   , {
	//     field : 'sale_price',
	//     width : 83,
	//     title : '销售价'
	// }
	// ] ];

	// function productSetadd() {
	// 	var id = $("input[name='status']:checked").val();
	// 	var name = $("input[name='status']:checked").data("name");
	//
	// 	if (!id) {
	// 		$.messager.alert('提示', '请选择后保存');
	// 		return;
	// 	}
	//
	// 	$("#show").show();
	// 	$("#product_id").val(id);
	// 	$("#product_name").val(name);
	// 	$("#pn").html(name);
	// 	$('#product').window('close');
	// }

// var default_params = {
//     'info[on_status]':2,
//     'info[not_in]':'1'
// };
	
		
	// 	function searchInfo() {
	// 		var queryData = new Object();
	// 		queryData['info[name]'] = $('#name').val();
	// 		queryData['info[id]'] = $('#id').val(),
	// 		queryData['info[on_status]'] = 2,
	// 		queryData['info[brand_name]'] = $('#brand_name').val();
	// 		queryData['info[category_name]'] = $('#category_name').val();
	// 		queryData['info[self_code]'] = $('#self_code').val();
	// 		queryData['info[not_in]'] = '1';
	//
	//
	// 		$('#dg').datagrid({
	// 			title:'',
	// 			width:'100%',
	// 			height:'auto',
	// 			nowrap: true,
	// 			autoRowHeight: true,
	// 			striped: true,
	// 		    url: '/index.php?m=Product&c=Product&a=list&format=list',
	// 			remoteSort: false,
	// 			singleSelect:true,
	// 			idField:'id',
	// 			loadMsg:'数据加载中......',
	// 			pageList: [10,20,50],
	// 			columns: fields,
	// 		    //singleSelect:false,
	// 			pagination:true,
	// 			rownumbers:true,
	// 			queryParams:queryData,
	// 			onLoadSuccess: function(data){
	// 	          var panel = $(this).datagrid('getPanel');
	// 	          var tr = panel.find('div.datagrid-body tr');
	// 	          tr.each(function(){
	// 	              var td = $(this).children('td[field="userNo"]');
	// 	              td.children("div").css({
	// 	                  //"text-align": "right"
	// 	                  "height": "50px"
	// 	              });
	// 	          });
	// 	       }
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
	
	
	
	function addclass(id) {
		$("#ui-"+id).addClass('skyblue');
	}
	function removeclass(id) {
		$("#ui-"+id).removeClass('skyblue');
	}

	var searchService,map,markers = [];
	function init () {
	   
	    var map = new qq.maps.Map(document.getElementById('map_canvas'),{
	        zoom: 3
	    });
	    //获取城市列表接口设置中心点
	    citylocation = new qq.maps.CityService({
	        complete : function(result){
	            map.setCenter(result.detail.latLng);
	        }
	    });
	    //调用searchLocalCity();方法    根据用户IP查询城市信息。
	    citylocation.searchLocalCity();
	    var latlngBounds = new qq.maps.LatLngBounds();
	    //调用Poi检索类
	    var marker = searchService = new qq.maps.SearchService({
	        complete : function(results){
	           
	            var pois = results.detail.pois;
	            if (pois) {
	               // for(var i = 0,l = pois.length;i < l; i++){
	                    var poi = pois[0];
	                    latlngBounds.extend(poi.latLng);  
	                    var marker = new qq.maps.Marker({
	                        map:map,
	                        draggable: true,
	                        position: poi.latLng
	                    });
	                    document.getElementById("poi_cur").value = poi.latLng.getLat().toFixed(6) + "," + poi.latLng.getLng().toFixed(6);
	                    document.getElementById("addr_cur").value = poi.address;
	                    url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + poi.latLng.getLat() + "," + poi.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
                        $.getJSON(url3, function (result) {
                            if(result.result!=undefined){
                                document.getElementById("addr_cur").value = result.result.address;
                                var c_name = result.result.address_component;
                                area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
                            }else{
                                document.getElementById("addr_cur").value = "";
                            }
                        })
	                    
	                   // marker.setTitle(i+1);
	                    marker.setTitle(1);
	                    var data = poi;
	                    marker.id = data.id;
	                    marker.name = data.name;
	                    marker.locate = data.latLng;
	                    qq.maps.event.addListener(marker, 'click', function(e) {    //获取标记的点击事件
	                        console.log(e);

	                        document.getElementById("poi_cur").value = e.latLng.getLat().toFixed(6) + "," + e.latLng.getLng().toFixed(6);
	                        url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + e.latLng.getLat() + "," + e.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	                        $.getJSON(url3, function (result) {console.log(11111)
	                            if(result.result!=undefined){
	                                document.getElementById("addr_cur").value = result.result.address;
	                                var c_name = result.result.address_component;
	                                area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
	                            }else{
	                                document.getElementById("addr_cur").value = "";
	                            }
	                        })
	                    	//document.getElementById("poi_cur").value = this.locate.lat.toFixed(6) + "," + this.locate.lng.toFixed(6);
	                        //document.getElementById("addr_cur").value = this.name;
	                        
	                    });
	                    qq.maps.event.addListener(marker, 'dragend', function(e) {console.log(22222)
	                    	document.getElementById("poi_cur").value = e.latLng.getLat().toFixed(6) + "," + e.latLng.getLng().toFixed(6);
	                        url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + e.latLng.getLat() + "," + e.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	                        $.getJSON(url3, function (result) {
	                            if(result.result!=undefined){console.log(result.result);
	                                document.getElementById("addr_cur").value = result.result.address;
	                                var c_name = result.result.address_component;
	                                area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
	                            }else{
	                                document.getElementById("addr_cur").value = "";
	                            }
	                        })
	                    });
	                    markers.push(marker);
	              //  }
	            }
	            map.fitBounds(latlngBounds);
	        }
	    });
	    
	    /* //添加监听事件   获取鼠标单击事件
	    qq.maps.event.addListener(map, 'click', function(e) {
	       var marker=new qq.maps.Marker({
	                position:e.latLng, 
	                map:map
	          });    
	      qq.maps.event.addListener(map, 'click', function(e) {
	            marker.setMap(null);      
	    	});
	      document.getElementById("poi_cur").value = e.latLng.getLat().toFixed(6) + "," + e.latLng.getLng().toFixed(6);
	      url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + e.latLng.getLat() + "," + e.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	      $.getJSON(url3, function (result) {
	          if(result.result!=undefined){
	              document.getElementById("addr_cur").value = result.result.address;
	          }else{
	              document.getElementById("addr_cur").value = "";
	          }
	      })
	    }); */

	 
	}

	var url2;
	function get_more(e) {
	        url2 = encodeURI("https://apis.map.qq.com/ws/place/v1/suggestion/?keyword=" + document.getElementById("keyword").value + "&region=" + $("#city option:selected").text() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	        $.getJSON(url2, function (result) {
	        	
	           if (result.data) {
	        	   $("#selecto").html("");
	        	   $("#selecto").show();
				   var Ahtml = ''
				   $.each(result.data, function (i, item) {
					   Ahtml += '<li id="ui-'+i+'" class="" onmouseover="addclass('+i+')" onclick="addfont(\''+item.title+'\')" onmouseout="removeclass('+i+')">'+	
	             	    		'<a id="ui-id-'+i+'" class="ui-corner-all">'+item.title+'</a>'+
	                            '</li>';
				   })
				   $("#selecto").html(Ahtml);
	           } else {
	        	   $("#selecto").hide();
	        	   $("#selecto").html("");
	           }
	        });
	       
	    
	};

	function addfont(text) {
		$("#keyword").val(text);
		$("#selecto").hide();
		$("#selecto").html("");
	}

	//清除地图上的marker
	 function clearOverlays(overlays){
	    var overlay;
	    while(overlay = overlays.pop()){
	        overlay.setMap(null);
	    }
	} 
	function searchKeyword() {
	    var keyword = document.getElementById("keyword").value;
	    //$("#province option:selected").text()+
	    var region = $("#city option:selected").text()+$("#area option:selected").text();
	    clearOverlays(markers);
	    searchService.setLocation(region);
	    searchService.search(keyword);
	}

	function searchKeyword2() {
	    var keyword = '政府';//document.getElementById("keyword").value;
	    //$("#province option:selected").text()+
	    var region = $("#city option:selected").text()+$("#area option:selected").text();
	    clearOverlays(markers);
	    searchService.setLocation(region);
	    searchService.search(keyword);
	    
	}

	
	