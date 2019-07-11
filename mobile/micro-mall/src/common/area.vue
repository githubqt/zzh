<template>
    <section class="area-container">
        <input v-model="areaNames" readonly placeholder="请选择省,市,区" type="text" @click.stop="show = true">
        <!-- 省市级联 -->
        <yd-popup v-model="show" position="bottom" height="50%">
            <header>请选择地址</header>
            
            <main>
                <div v-if="cityIndex === 0">
                    <span v-for="(item, index) in provinceData" :key="index" @click="selectCity(item.area_id, cityIndex, $event)">
                        {{item.area_name}}
                    </span>
                </div>
                <div v-else-if="cityIndex === 1">
                    <span v-for="(item, index) in cityData" :key="index" @click="selectCity(item.area_id, cityIndex, $event)">
                        {{item.area_name}}
                    </span>
                </div>
                <div v-else-if="cityIndex === 2">
                    <span v-for="(item, index) in areaData" :key="index" @click="selectCity(item.area_id, cityIndex, $event)">
                        {{item.area_name}}
                    </span>
                </div>
                <div v-else-if="cityIndex === 3">
                    <span v-for="(item, index) in streetData" :key="index" @click="selectCity(item.area_id, cityIndex, $event)">
                        {{item.area_name}}
                    </span>
                </div>
            </main>
            <footer>
                <span :id="provinceId" @click="clearCity(0)">{{provinceName}}</span>
                <span :id="cityId" @click="clearCity(1)">{{cityName}}</span>
                <span :id="areaId" @click="clearCity(2)">{{areaName}}</span>
                <span :id="streetName" @click="clearCity(3)">{{streetName}}</span>
            </footer>
        </yd-popup>
    </section>
</template>

<script>
import Qs from 'qs'
export default {
    name: 'Area',
    props: {
        cityNames: { type: String, default: '' }
    },
    data() {
        return {
            show: false,
            cityIndex: 0,
            areaNames: this.cityNames,

            provinceData: '',
            provinceId: '',
            provinceName: '',

            cityData: '',
            cityId: '',
            cityName: '',

            areaData: '',
            areaId: '',
            areaName: '',

            streetData: '',
            streetId: '',
            streetName: ''
        }
    },
    created() { this.showCity(0) },
    watch: {
        cityNames(val, oldVal) { this.areaNames = val }
    },
    methods: {
        showCity(pid) {
            let _this = this,
                _data = Qs.stringify({ pid: pid });

            _this.$http.post('/api/v1/Common/area', _data).then(function (response) {
                if (response.data.errno === '0') {
                    _this.provinceData = response.data.result;
                }else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function (error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        selectCity(pid, index, e) {
            let _this = this,
                _data = Qs.stringify({ pid: pid });

            _this.$http.post('/api/v1/Common/area', _data).then(function (response) {
                if (response.data.errno === '0') {
                    switch(index) {
                        case 0:
                            _this.cityIndex = 1;
                            _this.provinceId = pid;
                            _this.provinceName = e.target.innerText;
                            _this.cityData = response.data.result;
                            break;
                        case 1:
                            _this.cityIndex = 2;
                            _this.cityId = pid;
                            _this.cityName = e.target.innerText;
                            _this.areaData = response.data.result;
                            break;
                        case 2:
                            _this.cityIndex = 3;
                            _this.areaId = pid;
                            _this.areaName = e.target.innerText;
                            _this.streetData = response.data.result;
                            break;
                        default:
                            _this.streetId = pid;
                            _this.streetName = e.target.innerText;
                            _this.areaNames = _this.provinceName + _this.cityName + _this.areaName + _this.streetName;
                            _this.$emit('cityIds', {
                                provinceId: _this.provinceId,
                                cityId: _this.cityId,
                                areaId: _this.areaId,
                                streetId: _this.streetId
                            });
                            _this.show = false;
                    }
                }else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function (error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        clearCity(type) {
            var _this = this;
            switch(type) {
                case 0:
                    _this.cityIndex = 0;
                    _this.cityName = '';
                    _this.areaName = '';
                    _this.streetName = '';
                    break;
                case 1:
                    _this.cityIndex = 1;
                    _this.areaName = '';
                    _this.streetName = '';
                    break;
                case 2:
                    _this.cityIndex = 2;
                    _this.streetName = '';
                    break;
                case 3:
                    _this.cityIndex = 3;
                    break;
                default:
            }
        }
    }
}
</script>

<style>
.yd-popup{
    z-index: 99999;
}
.yd-cell-right .area-container,
.yd-cell-right .area-container input[type=text] {
    display: block;
    width: 100%;
}
.area-container .yd-popup-content > div {
    width: 100%;
    height: 100%;
    position: relative;
    font-size: .4rem;
}
.area-container header,
.area-container footer {
    text-align: center;
    line-height: .8rem;
    position: absolute;
    right: 0;
    left: 0;
    height: .8rem;
    padding: 0 .24rem;
    background-color: #f5f6fa;
    z-index: 99999999;
}
.area-container header {
    top: 0;
}
.area-container footer {
    top: 0;
    text-align: left;
}
.area-container main {
    width: 100%;
    height: 90%;
    overflow-y: auto;
    overflow-x: hidden;
    padding: .8rem .12rem;
}
.area-container main span {
    display: block;
    padding: .12rem;
    text-align: center;
}
.area-container footer span {
    color: #dab461;
    margin-right: .24rem;
    background-position: bottom;
    background-repeat: no-repeat;
    background-size: 100% 1px;
    background-image: linear-gradient(90deg, currentColor 100%, transparent 100%);
}
</style>
