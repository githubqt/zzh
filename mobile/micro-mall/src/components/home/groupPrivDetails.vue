<template>
    <section class="details-container">
        <yd-navbar title="参与拼团" class="fixed-header">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <yd-slider>
            <yd-slider-item v-for="(item, index) in detailsData.imglist" :key="index">
                <a href="javascript:;" :id="item.id">
                    <img v-lazy="item.img_url" :onerror="errorImg" :alt="detailsData.product_name">
                </a>
            </yd-slider-item>
        </yd-slider>
        <section class="details-header">
            <h3 class="details-header-title">{{detailsData.product_name}}</h3>
            <span class="details-header-price">
                <span class="market-price">原价: <em>￥</em>{{detailsData.sale_price}}</span>
            </span>
            <span class="details-header-price emphasis">团购价: <em>￥</em>{{detailsData.group_price}}</span>
        </section>
        <section v-show="groupShow">
            <div class="order" style="font-size: 16px;">
            	还差{{pinPrveDetail.dump_num}}人拼成
    			<yd-countdown
    				:time="pinPrveDetail.dump_time"
    				timetype="second"
    				format="剩余{%h}:{%m}:{%s}"
    				done-text="拼团已超时"
    				:callback="time_out"
    			>
    			</yd-countdown>
            </div>
		    <div class="details-slide-box">
		        <div class="details-slide-item" v-for="(item, index) in pinPrveList" :key="index" :id="item.id">
		        	<div class="details-slide-item-image">
		        		<img v-lazy="item.user_img" :onerror="headerrImg" :alt="item.name">
		        		<div class="info-left-img-label">{{item.tuan_type_txt}}</div>
		        	</div>
		        	<span class="details-slide-name">{{item.name}}</span>
		        </div>
		    </div>
        </section>
        <section class="details-parameter" v-show="detailsData.brand_description!==''">
            <div class="order">
                <span style="white-space:pre;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">品牌介绍</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="detailsData.brand_description" class="details-seckill-txt"></div>
        </section>
        <section class="details-parameter" v-show="detailsData.category_description!==''">
            <div class="order">
                <span style="white-space:pre;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">分类介绍</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="detailsData.category_description" class="details-seckill-txt"></div>
        </section>
        <section class="details-parameter" v-show="detailsData.attribute.length!==0">
            <div class="order">
                <span style="white-space:pre;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">产品参数</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <yd-cell-group>
                <yd-cell-item class="details-parameter-content" v-for="(item, index) in detailsData.attribute" :key="index">
                    <span slot="left"><span class="label">{{item.attribute_name}}</span></span>
                    <span slot="right">{{item.attribute_value_name}}</span>
                </yd-cell-item>
            </yd-cell-group>
        </section>
        <section class="details-activity">
            <div class="order">
                <span style="white-space:pre;"></span><span class="line"></span>
                <span style="white-space:pre;"></span><span class="txt">拼团活动</span>
                <span style="white-space:pre;"></span><span class="line"></span>
            </div>
            <yd-cell-group>
                <yd-cell-item class="details-parameter-content">
                    <span slot="left">开始时间:</span>
                    <span slot="right">{{detailsData.starttime}}</span>
                </yd-cell-item>
                <yd-cell-item class="details-parameter-content">
                    <span slot="left">结束时间:</span>
                    <span slot="right">{{detailsData.endtime}}</span>
                </yd-cell-item>
                <yd-cell-item class="details-parameter-content">
                    <span slot="left">成团人数:</span>
                    <span slot="right">{{detailsData.number}}人</span>
                </yd-cell-item>
                <yd-cell-item class="details-parameter-content">
                    <span slot="left">是否限购:</span>
                    <span slot="right" v-if="detailsData.is_restrictions==='1'">不限购</span>
                    <span slot="right" v-else>限购{{detailsData.restrictions_num}}件/人</span>
                </yd-cell-item>
            </yd-cell-group>
        </section>
        <section class="details-parameter">
            <div class="order">
                <span style="white-space:pre;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">商品详情</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="detailsData.introduction" class="details-seckill-txt"></div>
        </section>
        <section class="details-parameter" v-show="supplierInfo.company_introduction!==''">
            <div class="order">
                <span style="white-space:pre;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">公司介绍</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="supplierInfo.company_introduction" class="details-seckill-txt"></div>
        </section>
        <!-- 底部按钮 -->
        <yd-flexbox class="details-button">
            <div class="details-icon-box">
                <router-link to="/" class="miconfont micon-home details-btn-icon"></router-link>
            </div>
            <yd-flexbox-item>
                <yd-button size="large" :disabled="!canbuy"  type="danger" @click.native="payVerify()">
                	<span><em>￥</em>{{detailsData.group_price}}</span><br>
                	参与拼团
                </yd-button>
            </yd-flexbox-item>
        </yd-flexbox>
        <!-- 确认商品信息 -->
        <yd-popup v-model="infoShow" position="center" width="80%">
           <yd-flexbox>
                <div class="info-left-img">
                    <img v-lazy="detailsData.logo_url" :onerror="errorImg" :alt="detailsData.product_name">
                </div>
                <yd-flexbox-item style="margin-left: 10px;">
                    <div style="font-size: 15px;">{{detailsData.product_name}}</div>
                    <div style="font-size: 12px; padding-bottom: .4rem;">{{detailsData.brand_name}}</div>
                    <div class="details-header-price emphasis">
                    	<em>￥</em>{{detailsData.group_price}}
                    </div>
                </yd-flexbox-item>
            </yd-flexbox>
            <yd-cell-group class="info-group">
                <yd-cell-item>
                    <span slot="left">购买数量</span>
                    <yd-spinner slot="right" v-if="detailsData.is_restrictions===1" :max="detailsData.stock" min="1" v-model="amount"/>
                    <yd-spinner slot="right" v-else :max="detailsData.restrictions_num" min="1" v-model="amount"/>
                </yd-cell-item>
            </yd-cell-group>
            <div style="text-align: right; font-size: 13px;">库存: {{detailsData.stock}}件</div>
            <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="orderGo">确认</yd-button>
        </yd-popup>
    </section>
</template>
<script>
    import Sa from "../../../tool/wechatshare";
    import Qs from 'qs'
    import {logout} from "../../../tool/login";
export default {
    name: 'groupPrivDetails',
    components: {},
    data() {
        return {
            infoShow: false,//确认信息
            canbuy: false,//参团按钮
            groupShow: false,//团员信息
            detailsData: {
            	introduction: '',//商品详情
                brand_description: '',//品牌详情
                category_description: '',//分类详情
            	imglist: [],//商品图片
            	attribute: [],//商品参数
            },
            amount: 1,
            shareData : '',
            tuan_id: '',
            pinPrveDetail: [],//团长信息
            pinPrveList: [],//正在进行的拼团
            supplierInfo: { company_introduction:''},
            errorImg: 'this.src="' + require('../../assets/img/err.jpg') + '"',
            headerrImg: 'this.src="' + require('../../assets/img/headerr.jpg') + '"'
        }
    },
    created() {
    	this.tuan_id = this.$route.query.tuan_id;
    	this.setResult();
    	this.share();
    },
    methods: {
        backGo() {  window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/') },
        detailDataGet() {
            let _data = Qs.stringify({ id: this.$route.query.id});
            return this.$http.post('/api/v1/Group/detail', _data);
        },
        supplierGet() {
            return this.$http.post('/api/v1/Home/supplier');
        },
        groupdetailGet(id) {
            let _this = this,
            	_data = Qs.stringify({ id: id,user_id: localStorage.getItem('userId')});

            _this.$http.post('/api/v1/Group/grouprivplist', _data).then(function(response) {
                if (response.data.errno === '0') {
                	_this.groupShow = true;
                    _this.pinPrveDetail = response.data.result.priv;//拼主
                    _this.pinPrveList = response.data.result.list;//拼团详情
                    _this.canbuy = _this.pinPrveDetail.canbuy;
                    if (_this.canbuy) {
                    	_this.infoShow = true;
                    }
                } else {
                	_this.canbuy = false;
                	_this.infoShow = false;
                	_this.groupShow = false;
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        setResult() {
            let _this = this;
            _this.$dialog.loading.open('很快加载好了');
            _this.$http.all([_this.detailDataGet(), _this.supplierGet()]).then(_this.$http.spread(function(d, b) {
                if (d.data.errno === '0') {
                    _this.detailsData = d.data.result;
                    _this.shareData = d.data.result;
                };
                if (b.data.errno === '0') {
                    _this.supplierInfo = b.data.result;
                };
                _this.$nextTick(function() { _this.$dialog.loading.close() });
            }));
            if (_this.tuan_id) {
            	_this.groupdetailGet(_this.tuan_id);
            }
        },
        orderGo() {
            var _this = this,
            	_data = Qs.stringify({ user_id: localStorage.getItem('userId') });

            if (_this.amount > _this.detailsData.stock) {
                _this.$dialog.toast({
                    mes: '库存不足',
                    timeout: 500,
                    icon: 'error'
                })
                return;
            }

            _this.$http.post('/api/v1/User/isLogin', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.$router.push({ name: 'GroupOrder', query: { id: _this.detailsData.id, num: _this.amount, tuan_id:_this.tuan_id} });
                } else {
                    _this.infoShow = false;
                    logout();
                    _this.$router.push('/login');
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        payVerify() {
            let _this = this,
                _data = Qs.stringify({ user_id: localStorage.getItem('userId') });

            _this.$http.post('/api/v1/User/isLogin', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.infoShow = true;
                } else {
                    logout();
                    _this.$router.push('/login');
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        time_out(){
        	this.canbuy = false;
        	this.infoShow = false;
        },
        share(){
            console.log(this.detailsData);
            let _this = this,
                _data = Qs.stringify({
                    url: encodeURIComponent(location.href.split('#')[0])
                });

            _this.$http.post('/api/v1/weixin/getSingJsSign', _data).then(function(response) {
                if (response.data.errno === '0') {


                    Sa.weChat(_this.shareData.product_name,
                        _this.shareData.describe,
                        response.data.result.url,
                        _this.shareData.logo_url,
                        response.data.result.appId,
                        response.data.result.timestamp,
                        response.data.result.nonceStr,
                        response.data.result.signature);

                } else {
                    _this.$dialog.loading.close();
                    //_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }

            }).catch(function(error) {
                _this.$dialog.loading.close();
                //_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });

        }
    }
}

</script>
<style>
.details-container {
    position: relative;
    width: 100%;
    padding-bottom: 1.24rem;
}
.details-container .yd-slider {
    margin-top: 1rem;
}
.details-header {
    position: relative;
    width: 100%;
    padding: .24rem;
    background-color: #fff;
}
.details-container .order:after,
.details-header:after {
    position: absolute;
    content: '';
    right: 0;
    bottom: 0;
    left: 0;
    height: 1px;
    background-color: #E7E7E7;
    transform: scale3d(1, .5, 1);
}

.details-container .yd-slider-item {
    width:100%;
    height: 375px;
    -ms-flex-negative: 0;
    flex-shrink: 0;
}

.details-container .yd-slider-item img {
    margin: auto;
    height: 375px;
    display: block;
    background-color: #f1f1f1;
    width: auto;
}

.details-header-title {
    font-size: 15px;
    font-weight: normal;
    padding: 0 0 .12rem 0;
}
.details-header-price {
    display: block;
    font-size: 13px;
    padding: 0 0 .12rem 0;
}
.details-header-price em {
    font-size: .1rem;
}
.details-header-price.emphasis {
    padding: 0;
    font-size: .15rem;
    color: #E93B3A;
}
.details-activity,
.details-parameter,
.details-group {
    position: relative;
    margin-top: .24rem;
    background-color: #fff;
}
.details-group .order {
    position: relative;
    width: 100%;
    padding: .12rem .24rem;
    text-align: center;
    height: .7rem;
}
.details-group .order .more{
    min-height: 1rem;
    height: 1rem;
    line-height: .5rem;
}
.details-container .order {
    position: relative;
    width: 100%;
    padding: .12rem .24rem;
    text-align: center;
}
.details-container .order .line {
    display: inline-block;
    width: .4rem;
    border-top: 1px solid #1A191E;
}
.details-container .order .txt {
    font-size: 14px;
    vertical-align: -.1rem;
}
.details-parameter-content {
    padding-left: 0;
}
.details-parameter-content div[class^="yd-cell"] {
    min-height: .8rem;
    text-align: left;
    padding-left: .24rem;
    justify-content: flex-start;
}
.details-parameter-content .yd-cell-left {
    color: #525252;
}
.details-parameter-content .yd-cell-left .label {
    position: relative;
    letter-spacing: 1em;
}
.details-parameter-content .yd-cell-left .label:after {
    position: absolute;
    content: ':';
    top: 0;
    right: -.6em;
}
.details-parameter-content .yd-cell-right {
    color: #1A191E;
}
.details-parameter-content:after {
    border-bottom: 1px dotted #d9d9d9 !important;
}
.details-amount {
    text-align: right;
    padding: .12rem .24rem;
    margin-bottom: 1.12rem;
    background-color: #fff;
}
.details-button {
    position: fixed;
    z-index: 10;
    right: 0;
    bottom: 0;
    left: 0;
    background-color: #fff;
}
.details-button:after {
    position: absolute;
    content: '';
    top: 0;
    right: 0;
    left: 0;
    height: 1px;
    background-color: #E7E7E7;
    transform: scale3d(1, .5, 1);
}
.details-icon-box {
    text-align: center;
    padding: 0 .24rem 0 .12rem;
}
.details-btn-icon {
    display: inline-block;
    position: relative;
    font-size: .6rem !important;
    padding: 0 .12rem;
}
.corner-mark {
    position: absolute;
    top: 0;
    right: -.06rem;
    width: .4rem;
    height: .4rem;
    color: #fff;
    font-size: 13px;
    text-align: center;
    border-radius: 50%;
    line-height: .4rem;
    background-color: #DAB461;
}
.details-button .yd-btn-block {
    margin: 0;
}
.details-container .details-seckill-txt {
    color: #525252;
    font-size: .3rem;
    padding: .12rem .24rem;
    overflow: hidden;
}

.details-container .details-seckill-txt img {
    width: 100%;
}

.details-container .details-seckill-txt img[src*="emoticons"] {
    width: auto;
}

.details-container .yd-popup-content {
    border-radius: .05rem;
    padding: .24rem;
    background-color: #fff;
}
.info-group .yd-cell-item,
.info-group .yd-cell-right{
    padding: 0 !important;
}
.info-left-img {
    width: 2.6rem;
}
.info-left-img img {
    width: 100%;
}

.pinPrve .info-left-img {
	width: 2rem;
    margin: 1.2em;
}
.pinPrve .info-left-img img {
    width: 100%;
}
.group-priving{
	height: 100%;
	width: 100%;
}
.group-image-box {
	width: 100%;
	height: 100%;
	text-align: center;
	vertical-align: middle;
	padding: .12rem .48rem;
}
.group-content-image {
	width: 100%;
	height: 100%;
	object-fit: contain;
}
.groupsale-item-name {
	text-align: center;
	font-size: .26rem;
}
.groupsale-item-num,
.groupsale-item-time {
	text-align: center;
	font-size: .28rem;
}
.groupsale-item-btn {
	margin: .2rem .1rem .2rem .8rem;
    width: 1.4rem;
    height: .6rem;
}
.details-slide-box{
    display: -webkit-box;
    overflow-x: scroll;
    overflow-y: hidden;
    -webkit-overflow-scrolling:touch;
    background: #ffffff;
}
.details-slide-item{
	position: relative;
	display: block;
	width: 2rem;
	padding: .24rem;
}
.details-slide-item:before {
	content: '';
	position: absolute;
	right: 0;
	bottom: 0;
	left: 0;
	height: 1px;
	background: #b2b2b2;
	transform: scale3d(1, .5, 1);
}
.details-slide-item:after {
	content: '';
	position: absolute;
	top: .12rem;
	right: 0;
	bottom: .12rem;
	width: 1px;
	background: #b2b2b2;
	transform: scale3d(.5, 1, 1);
}
.details-slide-item:last-child:after {
	display: none;
}
.details-slide-item-image {
	width: 100%;
	overflow: hidden;
    text-align: right;
	vertical-align: middle;
	border-radius: 100px;
}
.details-slide-item-image img {
	width: 100%;
	height: 100%;
	object-fit:cover;
}
.details-slide-name {
	display: inline-block;
    font-size: .3rem;
    width: 100%;
    text-align: center;
}
.info-left-img-label {
    text-align: center;
    font-size: 13px;
    background-color: rgb(218, 180, 97);
    color: white;
    position: absolute;
    margin-top: -20px;
    margin-left: 20px;
    border-radius: 10px;
    width: .7rem;
}
</style>
