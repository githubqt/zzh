<template>
    <section class="Delivery-container">
        <!-- header -->
		<yd-navbar class="fixed-header" title="扫码自提"></yd-navbar>
        <!-- 内容 -->
        <yd-cell-group style="margin-top: 1.24rem;">
            <yd-cell-item>
            	<span slot="left">自提码：</span>
            	<yd-input slot="left" v-model="code" placeholder="请输入自提码"></yd-input>
            	<yd-button slot="right" class="scan" bgcolor="#fff" color="#000" v-if="wxShow" @click.native="scan">
            		<yd-icon name="qrscan" color="#000" size="0.5rem"></yd-icon>
            	</yd-button>
            	<yd-button slot="right" bgcolor="#dab461" color="#fff" @click.native="search">搜索</yd-button>
            </yd-cell-item>
        </yd-cell-group>
        <yd-flexbox direction="vertical" class="uOrder-box">
            <div class="uOrder-number">用户信息</div>
            <yd-flexbox-item>
            	<yd-cell-group>
		            <yd-cell-item >
		                <span slot="left">手机号：</span>
		                <span slot="right" class="order-delivery-site">{{user.mobile}}</span>
		            </yd-cell-item>
		            <yd-cell-item >
		                <span slot="left">姓名：</span>
		                <span slot="right" class="order-delivery-site">{{user.name}}</span>
		            </yd-cell-item>
		            <yd-cell-item >
		                <span slot="left">性别：</span>
		                <span slot="right" class="order-delivery-site">{{user.sex_txt}}</span>
		            </yd-cell-item>
	            </yd-cell-group>
            </yd-flexbox-item>
        </yd-flexbox>
        <yd-flexbox direction="vertical" class="uOrder-box">
            <div class="uOrder-number">订单信息</div>
            <yd-flexbox-item>
            	<yd-cell-group>
		            <yd-cell-item >
		                <span slot="left">订单号：</span>
		                <span slot="right" class="order-delivery-site">{{info.child_order_no}}</span>
		            </yd-cell-item>
		            <yd-cell-item>
		                <span slot="left">订单状态</span>
		                <span slot="right" style="color: #ea3d39;">{{info.child_status_txt}}</span>
		            </yd-cell-item>
		            <yd-cell-item >
		                <span slot="left">下单时间：</span>
		                <span slot="right" class="order-delivery-site">{{info.created_at}}</span>
		            </yd-cell-item>
		            <yd-cell-item >
		                <span slot="left">原始金额：</span>
		                <span slot="right" class="order-delivery-site">¥ {{info.child_order_original_amount}}</span>
		            </yd-cell-item>
		            <yd-cell-item >
		                <span slot="left">优惠金额：</span>
		                <span slot="right" class="order-delivery-site">¥ {{info.child_order_discount_amount}}</span>
		            </yd-cell-item>
		            <yd-cell-item>
		                <span slot="left">实际金额：</span>
		                <span slot="right" class="order-delivery-site">¥ {{info.child_order_actual_amount}}</span>
		            </yd-cell-item>
	            </yd-cell-group>
            </yd-flexbox-item>
        </yd-flexbox>
        <yd-flexbox direction="vertical" class="uOrder-box" style="margin-bottom: 1.2rem">
            <div class="uOrder-number">商品信息</div>
            <yd-flexbox-item>
            	<yd-cell-group>
		            <yd-cell-item v-for="(item, index) in info.product" :key="index" class="uOrder-product-box">
		                <span slot="left" class="uOrder-product-img">
		                	<img :src="item.logo_url" :alt="item.product_name" :onerror="errorImg">
		                </span>
		                <span slot="right">
		                	<div class="uOrder-product-name">{{item.product_name}}</div>
							<div class="market-price">公价:{{item.market_price}}</div>
							<div class="cart-status-txt">销售价:{{item.sale_price}}</div>
	                		<div class="uOrder-product-num"><em>×</em>{{item.sale_num}}</div>
		                </span>
		            </yd-cell-item>
		        </yd-cell-group>
            </yd-flexbox-item>
        </yd-flexbox>
         <!-- 底部按钮 -->
        <yd-flexbox class="order-submit-btn">
            <yd-flexbox-item>
                <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="delivery">确定提货</yd-button>
            </yd-flexbox-item>
        </yd-flexbox>
    </section>
</template>
<script>
import wxX from 'weixin-js-sdk'
import Qs from 'qs'
export default {
    name: 'Delivery',
    components: {},
    data() {
        return {
        	user: {},
        	info: {},
        	wxShow: false,
        	code: '',
            errorImg: 'this.src="' + require('../../assets/img/err.jpg') + '"'
        }
    },
    watch: {},
    created() {
    	let _this = this;
    	if (_this.$route.query.code) {
	    	_this.code = _this.$route.query.code;
	    	_this.search();
    	}
    },
    mounted() {
        let _this = this,
            ua = window.navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i) == 'micromessenger') {
            _this.wxRegist();
        } else {
            _this.wxShow = false;
        }
    },
    methods: {
    	wxRegist(){
            let _this = this,
                _data = Qs.stringify({
                    url: encodeURIComponent(location.href.split('#')[0])
                });

			_this.$http.post('/api/v1/weixin/getSingJsSign', _data).then(function(response) {
				if (response.data.errno === '0') {
		            wx.config({
		                // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
		                debug: false,
		                // 必填，公众号的唯一标识
		                appId: response.data.result.appId,
		                // 必填，生成签名的时间戳
		                timestamp: "" + response.data.result.timestamp,
		                // 必填，生成签名的随机串
		                nonceStr: response.data.result.nonceStr,
		                // 必填，签名
		                signature: response.data.result.signature,
		                // 必填，需要使用的JS接口列表，所有JS接口列表
		                jsApiList: ['checkJsApi', 'scanQRCode']
		            });
			        wx.error(function (res) {
			        	//_this.$dialog.toast({ mes: '配置出错啦', timeout: 1500, icon: 'error' });
			        });
			        wx.ready(function () {
			            wx.checkJsApi({
			                jsApiList: ['checkJsApi', 'scanQRCode', 'chooseImage'],
			                success: function (res) {
                                _this.wxShow = true;
				            }
				        });
			        });
				} else {
					_this.$dialog.loading.close();
					//_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
				}
			}).catch(function(error) {
				_this.$dialog.loading.close();
				//_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
			});

    	},
    	scan(){
        	let _this = this;
            wx.scanQRCode({
                needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                scanType: ["qrCode"], // 可以指定扫二维码还是一维码，默认二者都有
                success: function (res) {
                	_this.code = res.resultStr;
                	_this.search();
                },
			    error: function(res){
		            if(res.errMsg.indexOf('function_not_exist') > 0){
		                _this.$dialog.toast({ mes: '版本过低请升级', timeout: 1500, icon: 'error' });
		            } else {
		            	_this.$dialog.toast({ mes: '扫码出错啦', timeout: 1500, icon: 'error' });
		            }
			    }
            });
    	},
    	search() {
            let _this = this,
                _data = Qs.stringify({
                    code: _this.code
                });

            if (_this.code == '') {
                _this.$dialog.toast({
                    mes: '请输入自提码',
                    timeout: 500,
                    icon: 'error'
                })
                return;
            }

			_this.$dialog.loading.open('很快加载好了');
			_this.$http.post('/api/v1/Order/codedetail', _data).then(function(response) {
				if (response.data.errno === '0') {
					_this.info = response.data.result;
					_this.user = response.data.result.user;
					_this.$nextTick(function() { _this.$dialog.loading.close() });
				} else {
					_this.code = '';
					_this.$dialog.loading.close();
					_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
				}
			}).catch(function(error) {
				_this.$dialog.loading.close();
				//_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
			});
    	},
    	delivery(){
            let _this = this,
                _data = Qs.stringify({
                    id: _this.info.id
                });

            if (!_this.info.id) {
                _this.$dialog.toast({
                    mes: '请确认订单',
                    timeout: 500,
                    icon: 'error'
                })
                return;
            }

            _this.$dialog.confirm({
                title: '确认提货',
                mes: '您确定订单信息无误吗?',
                opts: () => {
                    _this.$http.post('/api/v1/Order/codedelivery', _data).then(function(response) {
                        if (response.data.errno === '0') {
                        	_this.$dialog.toast({ mes: '提货成功', timeout: 1500});
				        	_this.info = {};
				        	_this.user = {};
				        	_this.code = '';
                        } else {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                        }
                    }).catch(function(error) {
                        _this.$dialog.loading.close();
                    });
                }
            });
    	}
    }
}

</script>
<style>
@import "../../assets/css/components/delivery/main";
</style>
