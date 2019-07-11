<template>
    <yd-layout class="tencentmap" title="附近网点" link="/store">
        <div id="container">
        </div>
    </yd-layout>
</template>

<script>
    import {
        TMap
    } from '../../../tool/Tmap'
    import Qs from 'qs'
    import $ from 'jquery'
    export default {
        name: 'tencentmap',
        data() {
            return {
                longitude: 0, //经度
                latitude: 0, //纬度
                city: '',
                meter: '',
                cityData: '',
                polylineData: [],
            }
        },
        created() {},
        mounted() { //获取定位
            this.getCurrentPosition();
        },
        methods: {
            // 获取定位
            getCurrentPosition() {
                try {
                    if (!navigator.geolocation) {

                        throw '地理位置服务不可用';

                    }
                    navigator.geolocation.getCurrentPosition(this.showPosition, this.posOnError);
                } catch (err) {
                    console.log('TCL: getCurrentPosition -> err', err);
                }
            },
            // 定位失败
            posOnError(res) {
                let _this  = this;
                console.log('TCL: positionFailed -> res', res);
                setTimeout(function () {
                    _this.showPosition();
                },3000);
            },
            // 定位成功
            showPosition(position) {
                let _this = this;
                _this.provinceData();
                _this.latitude = position.coords.latitude;
                _this.longitude = position.coords.longitude;
                // _this.latitude = 39.991851;
                //  _this.longitude = 116.371307;
                _this.city = position.city;
                //
                _this.GetDistance(_this.latitude, _this.longitude, _this.$route.query.longitude, _this.$route.query.dimension);
                TMap().then(qq => {
                    // 网点坐标
                    let concent = new qq.maps.LatLng(_this.$route.query.longitude, _this.$route.query.dimension);
                    // 显示地图
                    var map = new qq.maps.Map(document.getElementById("container"), {
                        // 地图的中心地理坐标。
                        center: concent,
                        zoom: 11,
                    });
                    // 网点标记
                    var end_marker = new qq.maps.Marker({
                        //设置Marker的位置坐标
                        position: concent,
                        //设置显示Marker的地图
                        map: map,
                    });
                    // 网点说明
                    var label = new qq.maps.Label({
                        map: map,
                        content: _this.cityData.name + "             距离:<em style='font-size: 14px'>"+_this.meter + "<em/>"+
                            "<br/><em style='font-size: 12px'>" + _this.cityData.address + "<em/>"

                    });
                    var cssP = {
                        position: "fixed",
                        whiteSpace: "pre",
                        border: "1px solid rgb(153, 153, 153)",
                        fontSize: "12px",
                        padding: "10px 15px",
                        backgroundColor: "rgb(255, 255, 255)",
                        wordWrap: "break-word",
                        width: '200px',
                        marginTop: '-86px',
                        marginLeft: '-104px'
                    };
                    label.setPosition(concent);
                    label.setStyle(cssP);
                    qq.maps.event.addListener(map, 'zoom_changed', function() {
                        var zoomLevel = map.getZoom();
                        if (zoomLevel < 10) {
                            label.setVisible(false);
                        } else {
                            label.setVisible(true);
                        }
                    });
                    // 当前坐标点标记
                    let currentPoint = new qq.maps.LatLng(_this.latitude, _this.longitude);
                    var start_marker = new qq.maps.Marker({
                        //设置Marker的位置坐标
                        position: currentPoint,
                        //设置显示Marker的地图
                        map: map,
                    });
                    //设置Marker自定义图标的属性，size是图标尺寸，该尺寸为显示图标的实际尺寸，origin是切图坐标，该坐标是相对于图片左上角默认为（0,0）的相对像素坐标，anchor是锚点坐标，描述经纬度点对应图标中的位置
                    // 当前位置
                    var anchor = new qq.maps.Point(15, 33),
                        size = new qq.maps.Size(33, 33),
                        origin = new qq.maps.Point(0, 0),
                        icon = new qq.maps.MarkerImage(
                            "/../../static/imgs/icon.png",
                            size,
                            origin,
                            anchor
                        );
                    //设置Marker自定义图标的属性，size是图标尺寸，该尺寸为显示图标的实际尺寸，origin是切图坐标，该坐标是相对于图片左上角默认为（0,0）的相对像素坐标，anchor是锚点坐标，描述经纬度点对应图标中的位置
                    // 网点位置
                    var anchorb = new qq.maps.Point(15, 33),
                        sizeb = new qq.maps.Size(40,50),
                        origin = new qq.maps.Point(0, 0),
                        iconb = new qq.maps.MarkerImage(
                            "/../../static/imgs/supplier.png",
                            sizeb,
                            origin,
                            anchorb
                        );
                    end_marker.setIcon(iconb);
                    start_marker.setIcon(icon);
                    //调用 ds
                    _this.drivingService(map, currentPoint, concent, start_marker, end_marker);
                });
            },
            GetDistance(lat1, lng1, lat2, lng2) {
                var radLat1 = lat1 * Math.PI / 180.0;
                var radLat2 = lat2 * Math.PI / 180.0;
                var a = radLat1 - radLat2;
                var b = lng1 * Math.PI / 180.0 - lng2 * Math.PI / 180.0;
                var s = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(a / 2), 2) +
                    Math.cos(radLat1) * Math.cos(radLat2) * Math.pow(Math.sin(b / 2), 2)));
                s = s * 6378.137; // EARTH_RADIUS;
                s = Math.round(s * 10000) / 10000;
                var qm = s * 1000;
                var m = parseInt(qm);
                if (m < 1000) {
                    this.meter = m + "m";
                } else {
                    s = s.toFixed(2);
                    this.meter = s + "km";
                }
                console.log(this.meter);
            },
            provinceData() {
                let _this = this;
                _this.$dialog.loading.open("很快加载好了");

                $.ajax({
                    url: '/api/v1/Multipoint/address',
                    type: 'POST', //GET
                    async: false, //或false,是否异步
                    data: {
                        id: _this.$route.query.id,
                        identif: _this.DOMAIN
                    },
                    timeout: 5000, //超时时间
                    dataType: 'json', //返回的数据格式：
                    success: function(data, textStatus, jqXHR) {
                        _this.cityData = data.result;
                        _this.$nextTick(function() {
                            _this.$dialog.loading.close()
                        });
                        console.log(_this.cityData);
                    },
                    error: function(xhr, textStatus) {
                        console.log(xhr);
                    },
                });
            },
            // 驾车路线规划
            drivingService(map, start, end) {
                console.log('TCL: drivingService -> drivingService', 'drivingService');
                let directions_routes;
                let ds = new qq.maps.DrivingService({
                    complete: function(response) {
                        directions_routes = response.detail.routes;
                        //所有可选路线方案
                        for (var i = 0; i < directions_routes.length; i++) {
                            var route = directions_routes[i];
                            //调整地图窗口显示所有路线
                            map.fitBounds(response.detail.bounds);
                            //所有路程信息
                            new qq.maps.Polyline({
                                path: route.path,
                                strokeColor: '#3893F9',
                                strokeWeight: 5,
                                map: map
                            })
                        }
                    }
                });
                ds.setError(function(data) {
                    console.log('TCL: drivingService -> data', data);
                });
                ds.search(start, end);
            }
        }
    }
</script>

<style scoped>
    #container {
        height: 100%;
        width: 100%;
    }
</style>