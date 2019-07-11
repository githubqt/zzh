<template>
    <section class="order-container">
        <yd-navbar class="fixed-header" title="拼团订单">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <yd-cell-group class="m-t-1">
            <!-- <yd-cell-item arrow @click.native="deliveryShow=true">
                <span slot="left">快递方式：</span>
                <span slot="right">{{deliveryType}}</span>
            </yd-cell-item> -->
            <yd-cell-item >
                <span slot="left">取货方式：</span>
                <yd-radio-group v-model="adress_type" slot="left">
                    <yd-radio val="0">快递</yd-radio>
                    <yd-radio val="1">门店自提</yd-radio>
                </yd-radio-group>
            </yd-cell-item>
            <yd-cell-item arrow v-show="deliveryNum!==1" @click.native="siteShow=true">
                <span slot="left">配送地址：</span>
                <div slot="right" class="order-delivery-site">
                    <span style="padding-top: .12rem;">{{address_phone}}</span>
                    <span style="max-width: 4rem; padding-bottom: .12rem; text-align: justify;">{{address_name}}</span>
                </div>
            </yd-cell-item>
        </yd-cell-group>
        <yd-preview :buttons="btns" class="m-b-_24">
            <yd-preview-header>
                <div class="order-product-detail-box" slot="left">
                    <img :src="productDetail.logo_url" :onerror="errorImg">
                </div>
                <div slot="right" class="order-product-detail-title">
                    <div style="white-space: normal;">{{productDetail.product_name}}</div>
                    <div class="order-product-detail-price">
                        <div class="market-price">
                            <span>原价:{{productDetail.sale_price}}</span>
                        </div>
                        <div class="order-product-header-price">
                            <span class="emphasis">拼团价：<em>￥</em>{{productDetail.group_price}}</span>
                            <span class="emphasis-right"><em>×</em>{{amount}}</span>
                        </div>
                    </div>
                </div>
            </yd-preview-header>
        </yd-preview>
        <yd-cell-group>
            <yd-cell-item>
                <span slot="left">运费：</span>
                <span slot="right" class="order-delivery-site">¥ {{carriage}}</span>
            </yd-cell-item>
            <yd-cell-item >
                <span slot="left">原始金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{order_original_amount}}</span>
            </yd-cell-item>
            <yd-cell-item >
                <span slot="left">优惠金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{order_discount_amount}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">实际金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{resultMoney}}</span>
            </yd-cell-item>
        </yd-cell-group>
         <!-- 底部按钮 -->
        <yd-flexbox class="order-submit-btn">
            <yd-flexbox-item class="cart-button-right">
                <yd-button size="large" type="danger" class="no-radius"  @click.native="orderSubmit">确认订单</yd-button>
            </yd-flexbox-item>
        </yd-flexbox>
        <!-- 选择快递方式 -->
        <yd-actionsheet :items="deliveryItems" v-model="deliveryShow" cancel="取消"></yd-actionsheet>
        <!-- 选择地址 -->
        <yd-popup v-model="siteShow" position="bottom" height="auto" class="order-popup-box">
            <yd-cell-group>
                <yd-cell-item type="radio" v-for="(item, index) in addressData" :key="index" @click.native="siteShow=false">
                    <span slot="left">
                        <div style="padding-top: .12rem;">{{item.mobile}}</div>
                        <div class="cell-group-padding">{{item.province}}{{item.city}}{{item.area}}{{item.street}}{{item.address}}</div>
                    </span>
                    <input slot="right" type="radio" :value="item.id" v-model="address_id" />
                </yd-cell-item>
            </yd-cell-group>
            <router-link to="/create" class="order-btn-box">新增地址</router-link>
        </yd-popup>
    </section>
</template>
<script>
import Qs from 'qs'
export default {
    name: 'Store',
    components: {},
    data() {
        return {
        	adress_type:0,
            deliveryType: '快递',
            deliveryNum: 0,
            deliveryShow: false,
            siteShow: false,
            deliveryItems: [{
                    label: '快递',
                    callback: () => {
                        this.deliveryNum = 0;
                        this.deliveryType = '快递';
                    }
                },
                {
                    label: '门店自提',
                    callback: () => {
                        this.deliveryNum = 1;
                        this.deliveryType = '门店自提';
                    }
                }
            ],
            defaultAddressData: '',
            addressData: '',
            address_id: '',
            address_name: '',
            address_phone: '',
            btns: [],
            productDetail: {},
            amount: 1,
            carriage: '0.00',
            resultMoney: '0.00',
            order_original_amount: '0.00',
            order_discount_amount: '0.00',
            tuan_id: '',//拼团id
            errorImg: 'this.src="' + require('../../assets/img/err.jpg') + '"'
        }
    },
    watch: {
        address_id(val, oldVal) {
            var _this = this;
            if (_this.deliveryNum == 0) {
	            _this.addressData.forEach(item => {
	                if (item.id === val) {
	                    _this.address_phone = item.mobile;
	                    _this.address_name = item.province + item.city + item.area + item.street + item.address;
	                }
	            });
            }
            _this.getMoney();
        },
        deliveryNum(val, oldVal) {
            var _this = this;
            if (val !== 1) {
           		_this.address_id = _this.defaultAddressData.id;
            } else {
            	_this.address_id = 0;
            }
        },
        adress_type(val, oldVal) {
        	var _this = this;
        	if (val == 1) {
           		_this.deliveryNum = 1;
            	_this.address_id = 0;
            } else {
            	_this.deliveryNum = 0;
           		_this.address_id = _this.defaultAddressData.id;
            }
            _this.getMoney();
        }
    },
    created() {
        this.amount = this.$route.query.num;
        this.tuan_id = this.$route.query.tuan_id;
        this.assignment();
    },
    methods: {
        backGo() { window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/') },
        // 地址列表
        siteList() {
            let _data = Qs.stringify({ user_id: localStorage.getItem('userId') });
            return this.$http.post('/api/v1/address/list', _data);
        },
        // 默认地址
        defaultSite() {
            let _data = Qs.stringify({ user_id: localStorage.getItem('userId') });
            return this.$http.post('/api/v1/address/getDefault', _data);
        },
        // 商品详情
        detailProduct() {
            let _data = Qs.stringify({ id: this.$route.query.id});
            return this.$http.post('/api/v1/Group/detail', _data);
        },
        // 并发请求
        assignment() {
            let _this = this;

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.all([_this.siteList(), _this.defaultSite(), _this.detailProduct()])
                .then(_this.$http.spread(function(s, d, c) {
                    _this.addressData = s.data.result.list; // 地址列表
                    //设置默认收货地址
                    if (d.data.result) {
                    	_this.address_id = d.data.result.id;
                    	_this.defaultAddressData = d.data.result;
                    } else {
                    	if (_this.addressData.length > 0) {
                    		_this.address_id = _this.addressData[0].id;
                    		_this.defaultAddressData = _this.addressData[0];
                    	} else {
                    		_this.address_id = 0;
                    	}
                    }
                    _this.productDetail = c.data.result; // 商品详情
                    _this.$nextTick(function() { _this.$dialog.loading.close() });
                }));
        },
        getMoney() {
            let _this = this,
                _data = Qs.stringify({
                    user_id: localStorage.getItem('userId'),
                    delivery_type: _this.deliveryNum,
                    address_id: _this.address_id,
                    id: _this.$route.query.id,
                    num: _this.amount
                });

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.post('/api/v1/Order/getGroupMoney', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.resultMoney = response.data.result.order_actual_amount;
                    _this.order_original_amount = response.data.result.order_original_amount;
                    _this.order_discount_amount = response.data.result.order_discount_amount;
                    _this.carriage = response.data.result.freight_charge_actual_amount;
                    _this.$nextTick(function() { _this.$dialog.loading.close() });
                } else {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.loading.close();
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        // 确认订单
        orderSubmit() {
            let _this = this,
                _data = Qs.stringify({
                    user_id: localStorage.getItem('userId'),
                    delivery_type: _this.deliveryNum,
                    address_id: _this.address_id,
                    id: _this.$route.query.id,
                    num: _this.amount,
                    tuan_id: _this.tuan_id
                });

            if (_this.deliveryNum == 0 && (_this.address_id <= 0 || _this.address_id == '') ) {
                _this.$dialog.toast({
                    mes: '请选择收货地址',
                    timeout: 500,
                    icon: 'error'
                })
                return;
            }

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.post('/api/v1/Order/addGroup', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.$dialog.loading.close();
                    var payurl = '';
			        if(window.__wxjs_environment == 'miniprogram') {
						payurl = response.data.result.payurl+'&mini=1';
			        } else {
			        	payurl = response.data.result.payurl;
			        }
					var pathURL  = window.location.host;
					var n = (pathURL.split('.')).length-1;
			        if (n < 3) {
			        	payurl = payurl+'&lang=1';
			        }
                    window.location.href = payurl;
                } else {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.loading.close();
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        }
    }
}

</script>
<style>
    @import "../../assets/css/components/store/grouporder";
</style>
