<template>
    <section class="auctionLoad-container">
        <div>
            <dialog-bar v-model="sendVal" type="danger" title="" content='
	        <div class="qr-item" >
		        <div id="qrcode" ></div>
		        <h3><b>向店员展示提货码</b></h3>
	        </div>
	        '>
            </dialog-bar>
        </div>
        <yd-infinitescroll :callback="orderListLoad" ref="infinitescrollDemo">
            <yd-preview :buttons="btns" class="m-b-_24" slot="list" v-for="(item, index) in auctionList" :key="index">
                <yd-preview-header v-show="item.time_type=='3'">
                    <div slot="left" class="auction-header-statime">报名时间 ： {{item.created_at}}</div>
                </yd-preview-header>

                <yd-preview-header v-show="item.time_type=='2'">
                    <div slot="left" v-show="item.time_type=='2'">距离结束
                        <yd-countdown :time="item.time_end_txt" timetype="second"></yd-countdown>
                    </div>
                    <!--<div slot="left" v-show="item.time_type=='3'" ><yd-countdown :time="item.time_end_txt" timetype="second"  ></yd-countdown></div>-->
                    <div slot="right">
                        <router-link :to="{name:'Product',query:{id:item.seckill_id,product_id:item.product_id}}"
                                     style="color: #ff0000;">去出价
                        </router-link>
                    </div>
                </yd-preview-header>


                <yd-preview-header v-show="item.time_type=='1'">
                    <div slot="left" class="auction-header-time">距离开始
                        <yd-countdown :time="item.time_start_txt" timetype="second"></yd-countdown>
                    </div>
                </yd-preview-header>


                <yd-preview-item v-for="(list , key) in item.list" :key="key">

                    <router-link class="auctionLoad-preview-img"
                                 :to="{name:'Product',query:{id:item.seckill_id,product_id:item.product_id}}"
                                 slot="left">
                        <span v-show="item.status==1" class="auctionLoad-proceed-title">{{list.status_txt}}</span>
                        <span v-show="item.status==2" class="auctionLoad-trailer-title">{{list.status_txt}}</span>
                        <span v-show="item.status==3" class="auctionLoad-over-title">{{list.status_txt}}</span>
                        <span v-show="item.status==6" class="auctionLoad-over-title">未支付</span>
                        <img :src="list.logo_url" :onerror="errorImg">
                    </router-link>

                    <div slot="right" v-show="item.end_type==1">
                        <div>{{list.product_name}}</div>
                        <div>
                            <span>起拍价:￥{{list.start_price}}</span>
                        </div>
                        <div>当前价格:￥{{list.total_price}}</div>
                    </div>

                    <div slot="right" v-show="item.end_type==2">
                        <div>{{list.product_name}}</div>
                        <div>
                            <span>起拍价:￥{{list.start_price}}</span>
                        </div>
                        <div>获拍价:￥{{list.total_price}}</div>
                    </div>

                </yd-preview-item>

                <yd-preview-item>

                    <div slot="left" v-show="item.end_type==1">
                        <img src="/../../static/imgs/money.png" class="img_money">
                        <span>保证金: ￥{{item.margin}} </span>|<span v-show="item.is_margin == '2'" class="pay_type_ok"> {{item.margin_txt}}</span>
                        <span v-show="item.is_margin == '3'" class="pay_type_no"> {{item.margin_txt}}</span></img>
                    </div>

                    <div slot="left" v-show="item.end_type==2" v-for="(list , key) in item.list" :key="key">
                        <img src="/../../static/imgs/money.png" class="img_money">
                        <span>成交价: ￥{{list.total_price}} </span>|<span
                            v-show="list.order_status == '60' || list.order_status != '20'" class="pay_type_ok"> {{item.order_txt}}</span>
                        <span v-show="list.order_status == '20'   " class="pay_type_no"> {{item.order_txt}}</span>
                        <span v-show="list.order_status == '80'   " class="pay_type_no"> 未支付</span>
                        </img>
                    </div>

                    <div slot="right" class="auctionLoad-bottom-btn" v-show="item.pay_status=='20'">
                        <yd-button type="danger" style="float:right" @click.native="payGo(item.payurl)">去支付</yd-button>
                    </div>

                </yd-preview-item>
            </yd-preview>
            <!-- 数据全部加载完毕显示 -->
            <span slot="doneTip">啦啦啦，啦啦啦，没有数据啦~~</span>
        </yd-infinitescroll>
    </section>
</template>
<script>
    import QRCode from 'qrcodejs2'
    import dialogBar from '../../invitation/dialog.vue'
    import Qs from 'qs'

    export default {
        name: 'auctionLoad',
        components: {
            'dialog-bar': dialogBar,
        },
        props: {auctionStatus: {type: Number, default: 0}},
        data() {
            return {
                status: 0,
                page: 1,
                rows: 10,
                btns: [],
                auctionList: '',
                sendVal: false,
                errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"'
            }
        },
        mounted() {
            this.auctionListGet()
        },
        watch: {
            auctionStatus(val, oldVal) {
                if (val < 2) {
                    this.status = val;
                } else if (val == 2) {
                    this.status = 2;
                } else if (val > 2) {
                    this.status = val;
                }
                this.auctionListGet();
            }
        },
        methods: {
            auctionListGet() {
                let _this = this;

                _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
                _this.orderList = '';
                let _data = Qs.stringify({
                    user_id: localStorage.getItem('userId'),
                    status: _this.status,
                });

                _this.$dialog.loading.open('很快加载好了');
                _this.$http.post('/api/v1/Bidding/personal', _data).then(function (response) {
                    if (response.data.errno === '0') {
                        _this.auctionList = response.data.result;
                        _this.$nextTick(function () {
                            _this.$dialog.loading.close()
                        });
                    } else {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({mes: response.data.errmsg, timeout: 1500, icon: 'error'});
                    }
                }).catch(function (error) {
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

                _this.$http.post('/api/v1/Order/list', _data).then(function (response) {
                    if (response.data.errno === '0') {

                        _this.orderList = [..._this.orderList, ...response.data.result.list];
                        if ((response.data.result.list.length < _this.rows) || (response.data.result.total / _this.page === 0)) {
                            _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.loadedDone');
                        } else {
                            _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.finishLoad');
                            _this.page++;
                        }
                    } else {
                        _this.$dialog.toast({mes: response.data.errmsg, timeout: 1500, icon: 'error'});
                    }
                }).catch(function (error) {
                    // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
                });
            },
            payGo(payurl) {
                // let _this = this,
                //     _productId = [];
                // _this.orderList.forEach(item => {
                //     item.product.forEach(list => {
                //        _productId.push(list.product_id);
                //    })
                //})
                //this.$router.push({ name: 'Order', query: { product_id: _productId.join(','), num: num } });
                //组装小程序参数
                if (window.__wxjs_environment == 'miniprogram') {
                    payurl = payurl + '&mini=1';
                }
                var pathURL = window.location.host;
                var n = (pathURL.split('.')).length - 1;
                if (n < 3) {
                    payurl = payurl + '&lang=1';
                }
                window.location.href = payurl;
            },
            courierInfoGo(id) {
                this.$router.push({name: 'CourierInfo', query: {id: id}})
            },
            orderInfoGo(id) {
                this.$router.push({name: 'UorderInfo', query: {id: id}})
            },
        }
    }

</script>
<style>
</style>
