<template>
    <section class="order-container">
        <yd-navbar class="fixed-header" title="支付保证金">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>

        <yd-cell-group class="m-t-1">
        <div style=" background-color: #f1f1f1;font-size: .2rem;height: 0.5rem;font-weight: 400;position:relative;">
        <span style="position:absolute;left;0;bottom: .1rem;color:#696969;">请确认收货信息(竞拍成功后拍品将快递到此地址)</span>
        </div>
            <yd-cell-item arrow @click.native="deliveryShow=true">
                <span slot="left">快递方式：</span>
                <span slot="right">{{deliveryType}}</span>
            </yd-cell-item>
            <yd-cell-item arrow v-show="deliveryNum!==1" @click.native="siteShow=true">
                <span slot="left">配送地址：</span>
                <div slot="right" class="order-delivery-site">
                    <span style="padding-top: .12rem;">{{address_phone}}</span>
                    <span style="max-width: 4rem; padding-bottom: .12rem; text-align: justify;">{{address_name}}</span>
                </div>
            </yd-cell-item>
        </yd-cell-group>

        <div style="background-color: #fff;font-size: .27rem;padding: 0.3rem;">

            <yd-flexbox>
                <yd-flexbox-item>保证金：<span  class="order-delivery-margin">¥ {{productDeta.order_sale_price}}</span></yd-flexbox-item>
            </yd-flexbox>

            <yd-flexbox>
                <yd-flexbox-item  style="font-size: 13px;color:#696969;">1.若竞拍不成功，保证金将全额退还</yd-flexbox-item>
            </yd-flexbox>
            <yd-flexbox>
                <yd-flexbox-item  style="font-size: 13px; color:#696969;">2.若竞拍成功，保证金如果小于成交价，将转为货款的一部分。</yd-flexbox-item>
            </yd-flexbox>
            <yd-flexbox>
                <yd-flexbox-item  style="font-size: 13px; color:#696969;">注:竞拍成功后会产生邮寄费,会累加到竞拍成功的商品中</yd-flexbox-item>
            </yd-flexbox>
        </div>



         <!-- 底部按钮 -->
        <yd-flexbox class="order-submit-btn">
            <yd-flexbox-item class="cart-button-right">
                <yd-button size="large" type="danger" @click.native="orderSubmit">确定支付保证金</yd-button>
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
            productDeta:'',
            amount: 1,
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

        },
        deliveryNum(val, oldVal) {
            var _this = this;
            if (val !== 1) {
           		_this.address_id = _this.defaultAddressData.id;
            } else {
            	_this.address_id = 0;
            }
        },
        coupan_id(val, oldVal) {
            var _this = this;
            _this.coupanData.forEach(item => {
                if (item.user_coupan_id === val) {
                    _this.deduction = item.pre_txt;
                    _this.coupan_name = item.pre_txt;
                }
            });
        }
    },
    created() {
        this.amount = this.$route.query.num;
        this.assignment();
    },
    methods: {
        backGo() {  window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/') },
        // 地址列表
        siteList() {
            let _data = Qs.stringify({ user_id: localStorage.getItem('userId') });
            return this.$http.post('/api/v1/address/list', _data);
        },
        //商品详情
        productDataGet() {
            let _data = Qs.stringify({ id: this.$route.query.id,user_id: localStorage.getItem('userId')});
            return this.$http.post('/api/v1/Bidding/product', _data);
        },
        // 默认地址
        defaultSite() {
            let _data = Qs.stringify({ user_id: localStorage.getItem('userId') });
            return this.$http.post('/api/v1/address/getDefault', _data);
        },
        // 并发请求
        assignment() {
            let _this = this;

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.all([_this.siteList(), _this.defaultSite(), _this.productDataGet()])
                .then(_this.$http.spread(function(s, d, p) {
                    _this.addressData = s.data.result.list; // 地址列表

                    _this.productDeta = p.data.result;

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

                    _this.$nextTick(function() { _this.$dialog.loading.close() });
                }));
        },
        // 确认订单
        orderSubmit() {

            let _this = this,
                _data = Qs.stringify({
                    user_id: localStorage.getItem('userId'),
                    address_id: _this.address_id,
                    product_id: _this.productDeta.product_id,
                    delivery_type: _this.deliveryNum,
                    margin : _this.productDeta.order_sale_price,
                    id : _this.productDeta.id,
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
            _this.$http.post('/api/v1/Order/addMargin', _data).then(function(response) {
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
                    location.href = payurl+'&user_id='+response.data.result.user_id+'&address_id='+response.data.result.address_id+'&product_id='+response.data.result.product_id+'&delivery_type='+response.data.result.delivery_type+'&id='+response.data.result.id;

                } else {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }

            }).catch(function(error) {
                _this.$dialog.loading.close();
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
    }
}

</script>
<style>
.order-container {
    padding-bottom: 1.24rem;
}
.m-t-1 {
    margin-top: 1rem;
}
.order-delivery-margin,
.order-delivery-margin span {
        color: #FF0000;
        font-size: 18px;
}
.order-delivery-site,
.order-delivery-site span {
        display:block;
}
.order-container .yd-preview-header {
    height: auto !important;
}
.order-container .yd-preview-header {
    height: 1.6rem;
    justify-content: flex-start
}
.order-product-detail-box {
    flex: 0 1 auto !important;
    width: 2rem;
    height: 1.4rem;
    overflow: hidden;
    text-align: center;
    vertical-align: middle;
}
.order-product-detail-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.order-product-detail-title {
    flex: 1 !important;
    padding: .12rem .24rem !important;
    font-size: 14px !important;
    text-align: left !important;
}
.order-container .yd-preview-item {
    padding-bottom: 0;
}
.order-product-detail-price {
    padding: .12rem .12rem 0 0;
}
.m-b-_24 {
    margin-bottom: .24rem;
}
.order-submit-btn {
    position: fixed;
    z-index: 10;
    right: 0;
    bottom: 0;
    left: 0;
    justify-content: flex-end;
    background-color: #ffffff;
}
.order-submit-btn .cart-button-right {
    max-width: 3.4rem;
}
.order-submit-btn button {
    margin: 0 !important;
}
.order-container .yd-cell-radio-icon:after {
    color: #dab461 !important;
}
.order-popup-box .yd-popup {
    background-color: #efeff4;
}
.order-popup-box .yd-cell-box {
    margin-bottom: .12rem;
}
.order-btn-box {
    display: block;
    text-align: center;
    font-size: .36rem;
    margin: 0;
    height: 1rem;
    line-height: 1rem;
    background-color: #fff;
}
.cell-group-padding {
    white-space: normal;
    padding: 0 .24rem .12rem 0;
}
.emphasis {
list-style-type:none;
    display: inline-block;
    width: 50%;
    padding: 0;
    font-size: 15px;
    color: #E93B3A;
}
.emphasis-right {
list-style-type:none;
    display: inline-block;
    width: 50%;
    text-align: right;
    letter-spacing: .2em;
}


</style>
