<template>
	<section class="create-container">
		<!-- header -->
    	<yd-navbar height=".88rem" fontsize=".34rem" title="地址管理" class="fixed-header">
    		<div @click="backGo" slot="left">
	            <yd-navbar-back-icon></yd-navbar-back-icon>
	        </div>
    	</yd-navbar>
    	<!-- content -->
    	<yd-cell-group style="padding-top: .89rem;">
	        <yd-cell-item>
	            <span slot="left" class="create-cell-babel" style="letter-spacing: .3em;">收货人：</span>
	            <yd-input slot="right" v-model="name" placeholder="请输入姓名"></yd-input>
	        </yd-cell-item>
	        <yd-cell-item>
	            <span slot="left" class="create-cell-babel">联系方式：</span>
	            <yd-input slot="right" v-model="mobile" placeholder="请输入手机号码" type="tel"></yd-input>
	        </yd-cell-item>
	        <yd-cell-item>
	            <span slot="left" class="create-cell-babel">所在地区：</span>
	            <sty-area slot="right" v-bind:cityNames="cityNames" v-on:cityIds="cityIds"></sty-area>
	        </yd-cell-item>
	        <yd-cell-item>
	            <span slot="left" class="create-cell-babel">详细地址：</span>
	            <yd-input slot="right" v-model="address" placeholder="填写详细地址"></yd-input>
	        </yd-cell-item>
	        <yd-cell-item>
                <yd-checkbox-group v-model="is_default" size="15" color="#dc2821" class="create-list-checkbox" slot="right">
			        <yd-checkbox val="2" shape="circle">默认地址</yd-checkbox>
			    </yd-checkbox-group>
	        </yd-cell-item>
	    </yd-cell-group>
        <!-- create -->
        <div class="create-site-box">
            <yd-button size="large" type="danger" color="#fff" v-if="title" @click.native="createSiteSave">保存</yd-button>
            <yd-button size="large" type="danger" color="#fff" v-else @click.native="updateSiteSave">修改</yd-button>
        </div>
    </section>
</template>

<script>
import Qs from 'qs'
import Area from '@/common/area'
import {logout} from "../../../../tool/login";
import {
        Auth
    } from "../../../mixins/auth"
export default {
	name: 'Create',
	mixins: [Auth],
	components: { 'sty-area': Area },
	data() {
		return {
			title: false,
			name: '',
			mobile: '',
			province: '',
			city: '',
			area: '',
			street: '',
			address: '',
			is_default: [],
            cityNames: ''
		}
	},
    created() {
    	var _this = this,
    		_address_id = _this.$route.query.id;
    	if (_address_id) {
            _this.siteInfo(_address_id);
    	} else {
    		_this.title = true;
    	}
    },
    methods: {
        backGo() { window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/') },
        siteInfo(address_id) {
            let _this = this,
                _data = Qs.stringify({ user_id: localStorage.getItem('userId'), address_id: address_id });

            _this.$http.post('/api/v1/address/addressInfo', _data).then(function (response) {
                if (response.data.errno === '0') {
                    _this.name = response.data.result.name;
                    _this.mobile = response.data.result.mobile;
                    _this.province = response.data.result.province;
                    _this.city = response.data.result.city;
                    _this.area = response.data.result.area;
                    _this.street = response.data.result.street;
                    _this.address = response.data.result.address;
                    _this.cityNames = response.data.result.province_txt + response.data.result.city_txt + response.data.result.area_txt + response.data.result.street_txt;
                    if (response.data.result.is_default == 2) { _this.is_default = [2] };
                } else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function (error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
    	cityIds(data) {
    		this.province = data.provinceId;
    		this.city = data.cityId;
    		this.area = data.areaId;
    		this.street = data.streetId;
    	},
    	createSiteSave() {

    		let _is_default,
    			_this = this;

    		_this.is_default.length === 0 ? _is_default = 1 : _is_default = 2;

    		let	_data = Qs.stringify({
    				user_id: localStorage.getItem('userId'),
    				name: _this.name,
					mobile: _this.mobile,
					province: _this.province,
					city: _this.city,
					area: _this.area,
					street: _this.street,
					address: _this.address,
					is_default: _is_default
    			});

    		_this.$http.post('/api/v1/address/add', _data).then(function (response) {
            	if (response.data.errno === '0') {
                    _this.$router.go(-1);
            	} else {
	                _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
            	}
            }).catch(function (error) {
            	_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
    	},
        updateSiteSave() {
           let _is_default,
                _this = this;

            _this.is_default.length === 0 ? _is_default = 1 : _is_default = 2;

            let _data = Qs.stringify({
                    user_id: localStorage.getItem('userId'),
                    address_id: _this.$route.query.id,
                    name: _this.name,
                    mobile: _this.mobile,
                    province: _this.province,
                    city: _this.city,
                    area: _this.area,
                    street: _this.street,
                    address: _this.address,
                    is_default: _is_default
                });

            _this.$http.post('/api/v1/address/updateInfo', _data).then(function (response) {
                if (response.data.errno === '0') {
                    _this.$router.go(-1);
                } else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function (error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        }
    }
}
</script>

<style>
@import "../../../assets/css/components/user/module/create";

</style>
