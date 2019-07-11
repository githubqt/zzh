<template>
    <section class="uOrderInfo-container">
        <!-- header -->
		<yd-navbar class="fixed-header" title="订单详情">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <!-- 内容 -->
        <yd-cell-group style="margin-top: 1.24rem;">
            <yd-cell-item>
                <span slot="left">订单状态</span>
                <span slot="right" style="color: #ea3d39;">{{info.child_status_txt}}</span>
            </yd-cell-item>
        </yd-cell-group>
        <yd-flexbox direction="vertical" class="uOrder-box">
            <div class="uOrder-number">订单编号: {{info.child_order_no}}</div>
            <yd-flexbox-item>
            	<yd-cell-group>
		            <yd-cell-item v-for="(item, index) in info.product" :key="index" class="uOrder-product-box">
		                <span slot="left" class="uOrder-product-img">
		                	<img :src="item.logo_url" :alt="item.product_name" :onerror="errorImg">
		                </span>
		                <span slot="right" style="margin-left: 10px;">
		                	<div class="uOrder-product-name">{{item.product_name}}</div>
							<div class="market-price">公价:{{item.market_price}}</div>
							<div class="cart-status-txt">销售价:{{item.sale_price}}</div>
	                		<div class="uOrder-product-num"><em>×</em>{{item.sale_num}}</div>
		                </span>
		            </yd-cell-item>
		        </yd-cell-group>
            </yd-flexbox-item>
        </yd-flexbox>
        <yd-cell-group direction="vertical" class="uOrder-box" v-show="groupShow">
            <div class="uOrder-number" style="text-align: center;background-color: #f4f3f2;">拼团人员</div>
		    <div class="details-slide-box">
		        <div class="details-slide-item" v-for="(item, index) in pinPrveList" :key="index" :id="item.id">
		        	<div class="details-slide-item-image">
		        		<img v-lazy="item.user_img" :onerror="headerrImg" :alt="item.name">
		        		<div class="info-left-img-label">{{item.tuan_type_txt}}</div>
		        	</div>
		        	<span class="details-slide-name">{{item.name}}</span>
		        </div>
		    </div>
        </yd-cell-group>
        <yd-cell-group>
            <yd-cell-item >
                <span slot="left">运费：</span>
                <span slot="right" class="order-delivery-site">¥ {{info.child_freight_charge_actual_amount}}</span>
            </yd-cell-item>
            <yd-cell-item >
                <span slot="left">商品金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{info.child_order_original_amount}}</span>
            </yd-cell-item>
            <yd-cell-item >
                <span slot="left">优惠金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{info.child_order_discount_amount}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">支付金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{info.child_order_actual_amount}}</span>
            </yd-cell-item>
            <yd-cell-item v-show="info.delivery_type==='0'">
                <span slot="left">收货人</span>
                <span slot="right">{{info.accept_name}}</span>
            </yd-cell-item>
            <yd-cell-item v-show="info.delivery_type==='0'">
                <span slot="left">联系方式</span>
                <span slot="right">{{info.accept_mobile}}</span>
            </yd-cell-item>
            <yd-cell-item v-show="info.delivery_type==='0'">
                <span slot="left">收件地址</span>
                <span slot="right">{{info.province_name}}{{info.city_name}}{{info.area_name}}{{info.address}}</span>
            </yd-cell-item>
        </yd-cell-group>
        <yd-cell-group v-show="info.child_status==='50' && info.delivery_type==='0'">
            <yd-cell-item>
                <span slot="left">物流公司</span>
                <span slot="right">{{info.express_name}}</span>
            </yd-cell-item>
            <yd-cell-item arrow @click.native="courierInfoGo(info.id)">
                <span slot="left">快递单号</span>
                <span slot="right">{{info.express_no}}</span>
            </yd-cell-item>
        </yd-cell-group>
        <div style="height:1rem"></div>
        <!-- 底部按钮 -->
		<yd-flexbox v-if="info.child_status==20" class="yd-flexbox yd-flexbox-horizontal details-button" style="height:1rem">

			<yd-flexbox-item class="yd-flexbox-item yd-flexbox-item-center">
				<yd-button class="yd-btn-block yd-btn-danger no-radius" @click.native="orderCancel(info.id)" style="padding:0;background-color:#ccc;color:#000;margin:0">
					取消订单
				</yd-button>
			</yd-flexbox-item>
			<yd-flexbox-item class="yd-flexbox-item yd-flexbox-item-center">
				<yd-button class="yd-btn-block yd-btn-danger no-radius" @click.native="payGo(info.payurl,info.is_normal)" style="padding:0;margin:0">
					去支付
				</yd-button>
			</yd-flexbox-item>
		</yd-flexbox>
		<yd-flexbox v-if="info.child_status==50" class="yd-flexbox yd-flexbox-horizontal details-button" style="height:1rem">

			<yd-flexbox-item class="yd-flexbox-item yd-flexbox-item-center">
				<yd-button class="yd-btn-block yd-btn-danger no-radius" @click.native="courierInfoGo(info.id)" style="padding:0;background-color:#ccc;color:#000;margin:0">
					查看物流
				</yd-button>
			</yd-flexbox-item>
			<yd-flexbox-item class="yd-flexbox-item yd-flexbox-item-center">
				<yd-button class="yd-btn-block yd-btn-danger no-radius" @click.native="orderDelivery(info.id)" style="padding:0;margin:0">
					确认收货
				</yd-button>
			</yd-flexbox-item>
		</yd-flexbox>
		<yd-flexbox v-if="info.child_status==60 || info.child_status==70 || info.child_status==80 || info.child_status==90" class="yd-flexbox yd-flexbox-horizontal details-button" style="height:1rem">

			<yd-flexbox-item class="yd-flexbox-item yd-flexbox-item-center">
				<yd-button class="yd-btn-block yd-btn-danger no-radius" @click.native="orderDelete(info.id,info.child_status)" style="padding:0;margin:0">
					删除
				</yd-button>
			</yd-flexbox-item>
		</yd-flexbox>
    </section>
</template>
<script>
import Qs from 'qs'
export default {
    name: 'UorderInfo',
    components: {},
    data() {
        return {
        	groupShow: false,//团员信息
            pinPrveDetail: [],//团长信息
            pinPrveList: [],//正在进行的拼团
        	info: {},
            errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"',
            headerrImg: 'this.src="' + require('../../../assets/img/headerr.jpg') + '"'
        }
    },
    watch: {},
    created() { this.orderInfoGet() },
    methods: {
    	backGo() { window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/') },
    	orderInfoGet() {
    		let _this = this,
				_data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: _this.$route.query.id });

			_this.$dialog.loading.open('很快加载好了');
			_this.$http.post('/api/v1/Order/detail', _data).then(function(response) {
				if (response.data.errno === '0') {
					_this.info = response.data.result;

		            if (_this.info.discount_type == '4') {
		            	_this.groupdetailGet(_this.info.tuan_id);
		            }
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
    	payGo(payurl,is_normal) {
           // let _this = this,
           //     _productId = [];
           // _this.orderList.forEach(item => {
           //     item.product.forEach(list => {
            //        _productId.push(list.product_id);
            //    })
            //})

            if (is_normal == 'no') {
	            let _this = this;
	            _this.$dialog.toast({ mes: '该订单有商品已下架，不能进行支付操作', timeout: 2000, icon: 'error' });
	            return;
            }

            //this.$router.push({ name: 'Order', query: { product_id: _productId.join(','), num: num } });
            //组装小程序参数
	        if(window.__wxjs_environment == 'miniprogram') {
				payurl = payurl+'&mini=1';
	        }
			var pathURL  = window.location.host;
			var n = (pathURL.split('.')).length-1;
	        if (n < 3) {
	        	payurl = payurl+'&lang=1';
	        }
		    window.location.href = payurl;
        },
        orderDelivery(id) {
            let _this = this,
                _data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: id });

            _this.$dialog.confirm({
                title: '确认收货',
                mes: '您确定已经收到货物了吗?',
                opts: () => {
                    _this.$http.post('/api/v1/Order/delivery', _data).then(function(response) {
                        if (response.data.errno === '0') {
                            _this.$router.push('/orderList?orderStatus=4');
                        } else {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                        }
                    }).catch(function(error) {
                        _this.$dialog.loading.close();
                        // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
                    });
                }
            });
        },
        orderDelete(id,status) {
            let _this = this,
                _data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: id });

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.post('/api/v1/Order/delete', _data).then(function(response) {
                if (response.data.errno === '0') {
                	let order_status = '0';
                	if (status == '60' || status == '70') {
                		order_status = '5';
                	}
                	if (status == '80' || status == '90') {
                		order_status = '6';
                	}
                    _this.$router.push('/orderList?orderStatus='+order_status);
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({ mes: '删除成功', timeout: 1500, icon: 'success' });
                } else {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.loading.close();
                // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        courierInfoGo(id) { this.$router.push({ name: 'CourierInfo', query: { id: id } }) },
        orderCancel(id) {

	            let _this = this,
	                _data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: id });

	            _this.$dialog.loading.open('很快加载好了');
	            _this.$http.post('/api/v1/Order/cancel', _data).then(function(response) {
	                if (response.data.errno === '0') {
	                    //_this.$dialog.toast({ mes: '取消成功', timeout: 1500, icon: 'success' });
	                     _this.$router.push('/orderList?orderStatus=1');
	                } else {
	                    _this.$dialog.loading.close();
	                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
	                }
	            }).catch(function(error) {
	                _this.$dialog.loading.close();
	                // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
	            });

        },
    	courierInfoGo(id) { this.$router.push({ name: 'CourierInfo', query: { id: id } }) },
        groupdetailGet(id) {
            let _this = this,
            	_data = Qs.stringify({ id: id,user_id: localStorage.getItem('userId')});

            _this.$http.post('/api/v1/Group/grouprivplist', _data).then(function(response) {
                if (response.data.errno === '0') {
                	_this.groupShow = true;
                    _this.pinPrveDetail = response.data.result.priv;//拼主
                    _this.pinPrveList = response.data.result.list;//拼团详情
                } else {
                	_this.groupShow = false;
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
    }
}

</script>
<style>
</style>
