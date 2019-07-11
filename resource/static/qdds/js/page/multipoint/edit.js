/***
 * 添加多点
 * @version v0.01
 * @author huangxainguo
 * @time 2018-07-11
 */


    $(function(){
    	area_child(0,1,$("#province_id").val(),$("#city_id").val(),$("#area_id").val());	 
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

    	getaddress();
    	init();
    });
    
	function clearForm(){
		$('#ff').form('clear');
	}
	
	
	function addclass(id) {
		$("#ui-"+id).addClass('skyblue');
	}
	function removeclass(id) {
		$("#ui-"+id).removeClass('skyblue');
	}

	var searchService,map,markers = [];
	function init () {
		var center = new qq.maps.LatLng($("#poi_cur").val());
	    var map = new qq.maps.Map(document.getElementById('map_canvas'),{
	    	//center: center,
	        zoom: 16
	    });
	   
	    map.setCenter(new qq.maps.LatLng($("#longitude").val(),$("#dimension").val()))
	    var latlngBounds = new qq.maps.LatLngBounds();
	    //调用Poi检索类
	    var marker = searchService = new qq.maps.SearchService({
	        complete : function(results){
	           
	            var pois = results.detail.pois;
	            if (pois) {
	               // for(var i = 0,l = pois.length;i < l; i++){
	                    var poi = pois[0];
	                    latlngBounds.extend(poi.latLng);  
	                  
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

	                    var marker = new qq.maps.Marker({
	                        map:map,
	                        position: poi.latLng
	                    });
	                    
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
	                        $.getJSON(url3, function (result) {
	                            if(result.result!=undefined){
	                                document.getElementById("addr_cur").value = result.result.address;
	                                var c_name = result.result.address_component;
	                                area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
	                            }else{
	                                document.getElementById("addr_cur").value = "";
	                            }
	                        })
	                    	
	                    });
	                    qq.maps.event.addListener(marker, 'dragend', function(e) {
	                    	document.getElementById("poi_cur").value = e.latLng.getLat().toFixed(6) + "," + e.latLng.getLng().toFixed(6);
	                        url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + e.latLng.getLat() + "," + e.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	                        $.getJSON(url3, function (result) {
	                            if(result.result!=undefined){
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
	    
	    var url4
		url4 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + $("#poi_cur").val() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	    $.getJSON(url4, function (result) {
	    	var center = new qq.maps.LatLng(result.result.location.lat,result.result.location.lng);
	    	
	  //设置Marker自定义图标的属性，size是图标尺寸，该尺寸为显示图标的实际尺寸，origin是切图坐标，该坐标是相对于图片左上角默认为（0,0）的相对像素坐标，anchor是锚点坐标，描述经纬度点对应图标中的位置
        var anchor = new qq.maps.Point(0, 39),
            size = new qq.maps.Size(32, 68),
            origin = new qq.maps.Point(0, 0),
            icon = new qq.maps.MarkerImage(
                "http://static.qudiandang.com/qdds/img/marker.png",
                size,
                origin,
                anchor
            );
	    	//设置Marker阴影图片属性，size是图标尺寸，该尺寸为显示图标的实际尺寸，origin是切图坐标，该坐标是相对于图片左上角默认为（0,0）的相对像素坐标，anchor是锚点坐标，描述经纬度点对应图标中的位置
	    	var anchorb = new qq.maps.Point(3, -10),
            sizeb = new qq.maps.Size(32, 21),
            origin = new qq.maps.Point(0, 0),
            iconb = new qq.maps.MarkerImage(
                "",
                sizeb,
                origin,
                anchorb
            );
	    		var marker = new qq.maps.Marker({
	    		    position: center,
	    		    map: map
	    		}); 
	    	marker.setIcon(icon);
            marker.setShadow(iconb);
            qq.maps.event.addListener(marker, 'click', function(e) {    //获取标记的点击事件
                
                document.getElementById("poi_cur").value = e.latLng.getLat().toFixed(6) + "," + e.latLng.getLng().toFixed(6);
                url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + e.latLng.getLat() + "," + e.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
                $.getJSON(url3, function (result) {
                    if(result.result!=undefined){
                        document.getElementById("addr_cur").value = result.result.address;
                        var c_name = result.result.address_component;
                        area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
                    }else{
                        document.getElementById("addr_cur").value = "";
                    }
                })
            	
            });
	    })
	    
	     //添加监听事件   获取鼠标单击事件
	    qq.maps.event.addListener(map, 'click', function(e) {
	    	console.log(e);
	       var marker=new qq.maps.Marker({
	                position:e.latLng, 
                    draggable: true,
	                map:map
	          });    
	       marker.setTitle(2);
	      qq.maps.event.addListener(map, 'click', function(e) {
	            marker.setMap(null);      
	    	});
	      document.getElementById("poi_cur").value = e.latLng.getLat().toFixed(6) + "," + e.latLng.getLng().toFixed(6);
	      url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + e.latLng.getLat() + "," + e.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	      $.getJSON(url3, function (result) {
	          if(result.result!=undefined){
	              document.getElementById("addr_cur").value = result.result.address;
                  var c_name = result.result.address_component;
                  area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
	          }else{
	              document.getElementById("addr_cur").value = "";
	          }
	      })
	      qq.maps.event.addListener(marker, 'dragend', function(e) {
	        	document.getElementById("poi_cur").value = e.latLng.getLat().toFixed(6) + "," + e.latLng.getLng().toFixed(6);
	            url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + e.latLng.getLat() + "," + e.latLng.getLng() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	            $.getJSON(url3, function (result) {
	                if(result.result!=undefined){
	                    document.getElementById("addr_cur").value = result.result.address;
                        var c_name = result.result.address_component;
                        area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
	                }else{
	                    document.getElementById("addr_cur").value = "";
	                }
	            })
	        });
	    }); 
	    
	 
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
	
	function getaddress() {
		var url3
		url3 = encodeURI("https://apis.map.qq.com/ws/geocoder/v1/?location=" + $("#poi_cur").val() + "&key=HP2BZ-VDCC5-2FVIR-QQ6ZF-Y265H-C2BDV&output=jsonp&&callback=?");
	    $.getJSON(url3, function (result) {
	        if(result.result!=undefined){
	            document.getElementById("addr_cur").value = result.result.address;
                var c_name = result.result.address_component;
                area_child_name(c_name.province,1,c_name.province,c_name.city,c_name.district);
	        }else{
	            document.getElementById("addr_cur").value = "";
	        }
	    })
	}
	
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
		var keyword = $("#city option:selected").text()+$("#area option:selected").text()+'政府';
	    
	    searchService.search(keyword);
	}

	
	