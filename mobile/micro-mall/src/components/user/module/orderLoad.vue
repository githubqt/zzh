<template>
    <section class="orderLoad-container">
        <div>
	        <dialog-bar v-model="sendVal" type="danger" title="" content='
	        <div class="qr-item" >
		        <div id="qrcode" ></div>
		        <h3><b>向店员展示提货码</b></h3>
	        </div>
	        ' >
	        </dialog-bar>
	    </div>
        <yd-infinitescroll :callback="orderListLoad" ref="infinitescrollDemo">
            <yd-preview :buttons="btns" class="m-b-_24" slot="list" v-for="(item, index) in orderList" :key="index">
                <yd-preview-header>
                    <div slot="left">订单号：{{item.child_order_no}}</div>
                    <div slot="right" class="orderLoad-status-txt">{{item.child_status_txt}}</div>
                </yd-preview-header>
                <yd-preview-item v-for="(list, key) in item.product" :key="key">
                    <router-link class="orderLoad-preview-img" :to="{name:'Product',query:{id:item.discount_id}}" slot="left" v-if="list.discount_type == '3'" >
                        <img v-lazy="list.logo_url" >
                    </router-link>
                    <router-link class="orderLoad-preview-img" :to="{name:'GroupDetails',query:{id:item.discount_product_id}}" slot="left" v-else-if="list.discount_type == '4'" >
                        <img v-lazy="list.logo_url" >
                    </router-link>
                    <router-link class="orderLoad-preview-img" :to="{name:'Details',query:{id:list.product_id}}" slot="left" v-else >
                        <img v-lazy="list.logo_url" >
                    </router-link>

                    <div slot="right" v-if="list.discount_type != 3">
                        <div>{{list.product_name}}</div>
                        <div>
                            <span class="market-price">公价:{{list.market_price}}</span>
                        </div>
                        <div>销售价:{{list.sale_price}}</div>
                    </div>

                    <div slot="right"  v-else>
                        <div>{{list.product_name}}</div>
                        <div>
                            <span class="market-price">起拍价:{{item.sckill.start_price}}</span>
                        </div>
                        <div>结束价:{{item.sckill.total_price}}</div>
                    </div>

                </yd-preview-item>
                <yd-preview-item>
                    <div slot="left">合计总量: {{item.sale_num}}</div>
                    <div slot="right">实付款: {{item.child_order_actual_amount}}</div>
                </yd-preview-item>
                <yd-preview-item>
                    <div slot="right" class="orderLoad-bottom-btn">
                        <yd-button type="hollow" @click.native="orderInfoGo(item.id)">订单详情</yd-button>
                        <yd-button type="hollow" v-show="item.child_status==='10' || item.child_status==='20'" @click.native="orderCancel(item.id,item.type)">取消订单</yd-button>
                        <yd-button type="danger" v-show="item.child_status==='60' || item.child_status==='70' || item.child_status==='80' || item.child_status==='90'" @click.native="orderDelete(item.id)">删除</yd-button>
                        <yd-button type="hollow" v-show="item.child_status==='50' && item.delivery_type==='0'" @click.native="courierInfoGo(item.id)">查看物流</yd-button>
                        <yd-button type="danger" v-show="item.child_status==='50' && item.delivery_type==='0'" @click.native="orderDelivery(item.id)">确认收货</yd-button>
                        <yd-button type="danger" v-show="item.child_status==='50' && item.delivery_type==='1'" @click.native="openMask(item.delivery_no)">提货码</yd-button>
                        <yd-button type="danger" v-show="item.child_status==='22'" @click.native="to_share(item.discount_product_id,item.tuan_id)">邀请好友拼单</yd-button>
                        <yd-button type="danger" v-show="item.status===1" @click.native="payGo(item.payurl,item.is_normal)">去支付</yd-button>
                    </div>
                </yd-preview-item>
            </yd-preview>
            <!-- 数据全部加载完毕显示 -->
            <span slot="doneTip">啦啦啦，啦啦啦，没有数据啦~~</span>
        </yd-infinitescroll>
    </section>
</template>
<script>
import wxX from 'weixin-js-sdk'
import QRCode from 'qrcodejs2'
import dialogBar from '../../invitation/dialog.vue'
import Qs from 'qs'
export default {
    name: 'Orderload',
    components: {
    	'dialog-bar': dialogBar,
    },
    props: { orderStatus: { type: Number, default: 0 } },
    data() {
        return {
            status: 0,
            page: 1,
            rows: 10,
            btns: [],
            orderList: '',
            sendVal: false,
        }
    },
    mounted() {
        this.orderListGet();
    },
    watch: {
        orderStatus(val, oldVal) {
            if (val < 2) {
            	this.status = val;
            } else if (val == 2) {
            	this.status = 7;
            } else if (val>2) {
            	this.status = val-1;
            }
            this.orderListGet();
        }
    },
    methods: {
        orderListGet() {
            let _this = this;

            _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
            _this.page = 1;
            _this.orderList = '';
            let _data = Qs.stringify({
                user_id: localStorage.getItem('userId'),
                status: _this.status,
                page: _this.page,
                rows: _this.rows
            });

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.post('/api/v1/Order/list', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.orderList = response.data.result.list;
                    _this.page = 2;
                    _this.$nextTick(function() { _this.$dialog.loading.close() });
                } else {
                    _this.$dialog.loading.close();
                }
            }).catch(function(error) {
                _this.$dialog.loading.close();
                // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        orderListLoad() {
            let _this = this,
                _data = Qs.stringify({
                    user_id: localStorage.getItem('userId'),
                    status: _this.status,
                    page: _this.page,
                    rows: _this.rows
                });

            _this.$http.post('/api/v1/Order/list', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.orderList = [..._this.orderList, ...response.data.result.list];
                    if ((response.data.result.list.length < _this.rows) || (response.data.result.total / _this.page === 0)) {
                        _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.loadedDone');
                    } else {
                        _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.finishLoad');
                        _this.page++;
                    }
                } else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        orderDelete(id) {
            let _this = this,
                _data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: id });

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.post('/api/v1/Order/delete', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.orderListGet();
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
        orderCancel(id,type) {

        	if (type == 1) {
        	 this.$dialog.confirm({
                    title: '确定取消订单？',
                    mes: '订单取消后，保证金将不会退还！',
                    opts: () => {
			            let _this = this,
			                _data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: id });

			            _this.$dialog.loading.open('很快加载好了');
			            _this.$http.post('/api/v1/Order/cancel', _data).then(function(response) {
			                if (response.data.errno === '0') {
			                    _this.orderListGet();
			                    _this.$dialog.loading.close();
			                    _this.$dialog.toast({ mes: '取消成功', timeout: 1500, icon: 'success' });
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

        	} else {
	            let _this = this,
	                _data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: id });

	            _this.$dialog.loading.open('很快加载好了');
	            _this.$http.post('/api/v1/Order/cancel', _data).then(function(response) {
	                if (response.data.errno === '0') {
	                    _this.orderListGet();
	                    _this.$dialog.loading.close();
	                    _this.$dialog.toast({ mes: '取消成功', timeout: 1500, icon: 'success' });
	                } else {
	                    _this.$dialog.loading.close();
	                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
	                }
	            }).catch(function(error) {
	                _this.$dialog.loading.close();
	                // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
	            });
	        }

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
                            _this.orderListGet();
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
        openMask(delivery_no){
            this.sendVal = true;
            this.qrcode(delivery_no);
        },
        qrcode (delivery_no) {
	        let qrcode = new QRCode('qrcode', {
		        width: 250,
		        height: 250, // 高度
		        text: delivery_no // 二维码内容
		        // render: 'canvas' // 设置渲染方式（有两种方式 table和canvas，默认是canvas）
		        // background: '#f0f'
		        // foreground: '#ff0'
		    })
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
	        if (window.__wxjs_environment == 'miniprogram') {
				payurl = payurl+'&mini=1';
	        }
			var pathURL  = window.location.host;
			var n = (pathURL.split('.')).length-1;
	        if (n < 3) {
	        	payurl = payurl+'&lang=1';
	        }
		    window.location.href = payurl;
        },
        courierInfoGo(id) { this.$router.push({ name: 'CourierInfo', query: { id: id } }) },
        orderInfoGo(id) { this.$router.push({ name: 'UorderInfo', query: { id: id } }) },
        to_share(id,tuan_id){
            let _this = this;
            _this.$router.push({ name: 'GroupPrivDetails', query: { id: id, tuan_id: tuan_id} });
        },
    }
}

</script>
<style>
@import "../../../assets/css/components/user/module/orderload";
</style>
