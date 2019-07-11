<template>
    <section class="details-container">
        <yd-navbar title="拼团详情" class="fixed-header">
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
        <section class="details-group" v-show="group_total>0 && detailsData.status==='6'">
			<yd-cell-item class="order">
				<span slot="left">{{group_total}}人正在拼单，可直接参与</span>
				<span slot="right" class="more" @click="more">更多></span>
	        </yd-cell-item>
	        <yd-flexbox class="group-priving" v-for="(item, index) in group2List" :key="index" :id="item.id">
	            <yd-flexbox-item>
		            <div class="group-image-box">
		                <img style="border-radius: 100px;" v-lazy="item.user_img" :onerror="headerrImg" :alt="item.name" class="group-content-image">
		            </div>
		            <div class="groupsale-item-name">{{item.name}}</div>
	            </yd-flexbox-item>
	            <yd-flexbox-item>
		            <div class="groupsale-item-num">还差{{item.dump_num}}人拼成</div>
	        		<div class="groupsale-item-time">
	        			<yd-countdown
	        				:time="item.dump_time"
	        				timetype="second"
	        				format="剩余{%h}:{%m}:{%s}"
	        				done-text="拼团已超时"
	        				:callback="group2listGet"
	        			>
	        			</yd-countdown>
	        		</div>
	            </yd-flexbox-item>
	            <yd-flexbox-item>
	                <yd-button class="groupsale-item-btn" type="danger"  v-if="!item.canyu" @click.native="to_pin(item.id)">
	                	去拼单
	                </yd-button>
	                <yd-button class="groupsale-item-btn" type="danger"  v-else-if="item.canyu" @click.native="to_share(item.id)">
	                	去分享
	                </yd-button>
	            </yd-flexbox-item>
	        </yd-flexbox>
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
        <div v-show="detailsData.is_return==1" class="details-seckill-txt">
            <span>不退货声明：</span>
            <div style="font-size: .1rem;">1. 由于绝当品的特殊性，购买后非质量问题不支持退换货;</div>
            <div style="font-size: .1rem;">2. 若因商品质量问题退货时，请先与卖家沟通;</div>
            <div style="font-size: .1rem;">3. 为保证商品安全，邮寄商品时请使用顺丰速运，请勿货到付款;</div>
            <div style="font-size: .1rem;">4. 卖家收到商品后，会对退货商品进行鉴定确认，确认无误后才可退款。</div>
        </div>
        <section class="details-parameter" v-show="supplierInfo.shop_instructions!==''">
            <div class="order">
                <span style="white-space:pre;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">本店说明</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="supplierInfo.shop_instructions" class="details-seckill-txt"></div>
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
                <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="payVerify(1)">
                	<span><em>￥</em>{{detailsData.sale_price}}</span><br>
                	单独购买
                </yd-button>
            </yd-flexbox-item>
            <yd-flexbox-item>
                <yd-button size="large" bgcolor="#ccc" color="#fff" v-if="detailsData.status==='5'">
                	<span><em>￥</em>{{detailsData.group_price}}</span><br>
                	拼团未开始
                </yd-button>
                <yd-button size="large" type="danger" v-else-if="detailsData.status==='6'" @click.native="payVerify(2)">
                	<span><em>￥</em>{{detailsData.group_price}}</span><br>
                	发起拼团
                </yd-button>
                <yd-button size="large" bgcolor="#ccc" color="#fff" v-else="detailsData.status==='7'">
                	<span><em>￥</em>{{detailsData.group_price}}</span><br>
                	拼团已结束
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
                    <div class="details-header-price emphasis" v-if="paytype===1">
                        <em>￥</em>{{detailsData.sale_price}}
                    </div>
                    <div class="details-header-price emphasis" v-else>
                    	<em>￥</em>{{detailsData.group_price}}
                    </div>
                </yd-flexbox-item>
            </yd-flexbox>
            <yd-cell-group class="info-group">
                <yd-cell-item>
                    <span slot="left">购买数量</span>
                    <yd-spinner slot="right" v-if="paytype===1 || detailsData.is_restrictions===1" :max="detailsData.stock" min="1" v-model="amount"/>
                    <yd-spinner slot="right" v-else :max="detailsData.restrictions_num" min="1" v-model="amount"/>
                </yd-cell-item>
            </yd-cell-group>
            <div style="text-align: right; font-size: 13px;">库存: {{detailsData.stock}}件</div>
            <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="orderGo">确认</yd-button>
        </yd-popup>
        <!-- 确认拼团 -->
        <yd-popup v-model="pinShow" position="center" width="80%">
            <div style="text-align: center; font-size: .40rem;">参与{{pinPrveList.name}}的拼单</div>
            <div style="text-align: center; font-size: .30rem;">仅剩{{pinPrveList.dump_num}}个名额
				<yd-countdown
    				:time="pinPrveList.dump_time"
    				timetype="second"
    				format="{%h}:{%m}:{%s}后结束"
    				done-text="拼团已超时"
    				:callback="groupdetailout"
    			>
    			</yd-countdown>
            </div>
	        <yd-flexbox class="pinPrve">
	            <yd-flexbox-item>
		            <div class="info-pin-left-img">
		                <img  v-lazy="pinPrveList.user_img" :onerror="headerrImg" :alt="pinPrveList.name">
		                <span class="info-left-img-label" >拼主</span>
		            </div>
	            </yd-flexbox-item>
	            <yd-flexbox-item>
		            <div class="info-pin-left-img">
		                <img src="./../../../static/imgs/empty.jpg">
		            </div>
	            </yd-flexbox-item>
	        </yd-flexbox>
            <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="to_share(pinPrveList.id)">参与拼单</yd-button>
        </yd-popup>
        <!-- 更多 -->
        <yd-popup class="details-group" v-model="moreShow" >
			<yd-cell-item class="order">
				<span slot="left">正在拼单</span>
	        </yd-cell-item>
	        <yd-flexbox class="group-priving" v-for="(item, index) in groupAllList" :key="index" :id="item.id">
	            <yd-flexbox-item>
		            <div class="group-image-box">
		                <img v-lazy="item.user_img" :onerror="headerrImg" :alt="item.name" class="group-content-image">
		            </div>
		            <div class="groupsale-item-name">{{item.name}}</div>
	            </yd-flexbox-item>
	            <yd-flexbox-item>
		            <div class="groupsale-item-num">还差{{item.dump_num}}人拼成</div>
	        		<div class="groupsale-item-time">
	        			<yd-countdown
	        				:time="item.dump_time"
	        				timetype="second"
	        				format="剩余{%h}:{%m}:{%s}"
	        				done-text="拼团已超时"
	        				:callback="group10listGet"
	        			>
	        			</yd-countdown>
	        		</div>
	            </yd-flexbox-item>
	            <yd-flexbox-item>
	                <yd-button class="groupsale-item-btn" type="danger"  v-if="!item.canyu" @click.native="to_pin(item.id)">
	                	去拼单
	                </yd-button>
	                <yd-button class="groupsale-item-btn" type="danger"  v-else-if="item.canyu" @click.native="to_share(item.id)">
	                	去分享
	                </yd-button>
	            </yd-flexbox-item>
	        </yd-flexbox>
        </yd-popup>
    </section>
</template>
<script>
    import Sa from "../../../tool/wechatshare";
    import {logout} from "../../../tool/login";
    import Qs from 'qs'
export default {
    name: 'groupDetails',
    components: {},
    data() {
        return {
            infoShow: false,
            pinShow:false,
            moreShow:false,
            detailsData: {
            	introduction: '',//商品详情
                brand_description: '',//品牌详情
                category_description: '',//分类详情
            	imglist: [],//商品图片
            	attribute: [],//商品参数
            },
            amount: 1,
            shareData : '',
            paytype: 1,
            group_total:0,
            tuan_id: '',
            pinPrveList: {},//正在进行的拼团
            group2List: [],//前两个待成团
            groupAllList: [],//全部待成团
            supplierInfo: { company_introduction:''},
            errorImg: 'this.src="' + require('../../assets/img/err.jpg') + '"',
            headerrImg: 'this.src="' + require('../../assets/img/headerr.jpg') + '"'
        }
    },
    created() {
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
        group2listGet() {
            let _this = this,
            	_data = Qs.stringify({ id: this.$route.query.id,page: 1,rows: 2,user_id: localStorage.getItem('userId')});

            _this.$http.post('/api/v1/Group/grouplist', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.group2List = response.data.result.list;
                    _this.group_total = response.data.result.total;
                } else {
                    _this.group2List = [];
                    _this.group_total = 0;
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        group10listGet() {
            let _this = this,
            	_data = Qs.stringify({ id: this.$route.query.id,page: 1,rows: 10,user_id: localStorage.getItem('userId')});

            _this.$http.post('/api/v1/Group/grouplist', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.groupAllList = response.data.result.list;
                } else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        groupdetailGet(id) {
            let _this = this,
            	_data = Qs.stringify({ id: id});

            _this.$http.post('/api/v1/Group/groupdetail', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.pinPrveList = response.data.result;
		        	_this.tuan_id = id;
		        	_this.pinShow = true;
		        	_this.moreShow = false;
                } else {
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
                    if (_this.detailsData.status == 6) {
                    	_this.group2listGet();
                    }
                };
                if (b.data.errno === '0') {
                    _this.supplierInfo = b.data.result;
                };
                _this.$nextTick(function() { _this.$dialog.loading.close() });
            }));
        },
        orderGo() {
            var _this = this;

            if (_this.amount > _this.detailsData.stock) {
                _this.$dialog.toast({
                    mes: '库存不足',
                    timeout: 500,
                    icon: 'error'
                })
                return;
            }

            if (_this.paytype === 1) {
            	_this.$router.push({ name: 'Order', query: { product_id: _this.detailsData.product_id, num: _this.amount } });
            } else if (_this.paytype === 2) {
            	_this.$router.push({ name: 'GroupOrder', query: { id: _this.detailsData.id, num: _this.amount} });
            }
        },
        payVerify(op) {
            let _this = this,
                _data = Qs.stringify({ user_id: localStorage.getItem('userId') });
			_this.paytype = op;

            _this.$http.post('/api/v1/User/isLogin', _data).then(function(response) {
                if (response.data.errno === '0') {
	                if (op == 3) {
	                	_this.pinShow = false;
	                	_this.$router.push({ name: 'GroupPrivDetails', query: { id: _this.detailsData.id, tuan_id:_this.tuan_id} });
	                } else {
	               		_this.infoShow = true;
	                }
                } else {
                    logout();
                    _this.$router.push('/login');
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        more() {
            let _this = this;
            _this.group10listGet();
            _this.moreShow = true;
        },
        to_pin(id){
            let _this = this;
            _this.groupdetailGet(id);
        },
        groupdetailout(){
        	this.pinShow = false;
        },
        to_share(id){
            let _this = this;
            _this.tuan_id = id;
            _this.$router.push({ name: 'GroupPrivDetails', query: { id: _this.detailsData.id, tuan_id:_this.tuan_id} });
        },
        share(){

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
.details-header-title {
    font-size: 15px;
    font-weight: normal;
    padding: 0 0 .12rem 0;
}
.details-header-price {
    display: block;
    font-size: .13rem;
    padding: 0 0 .12rem 0;
}
.details-header-price em {
    font-size: .1rem;
}
.details-header-price.emphasis {
    padding: 0;
    font-size: 15px;
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
.pinPrve .info-pin-left-img .info-left-img-label {
    text-align: center;
    font-size: 13px;
    background-color: rgb(218, 180, 97);
    color: white;
    position: absolute;
    margin-top: -20px;
    margin-left: 25px;
    width: 50px;
    border-radius: 10px;
}
.pinPrve .info-pin-left-img {
	width: 2rem;
    margin: 1.2em;
}
.pinPrve .info-pin-left-img img {
    width: 100%;
    border-radius: 100px;
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
.group-image-box img {
    border-radius: 100px;
}
.group-content-image {
	width: 100%;
	height: 100%;
	object-fit: contain;
}
.groupsale-item-name {
	text-align: center;
	font-size: .26rem;
	margin: -5px 0 5px 0;
}
.groupsale-item-num,
.groupsale-item-time {
	text-align: right;
	font-size: .28rem;
}
.groupsale-item-btn {
	margin: .2rem .1rem .2rem .8rem;
    width: 1.4rem;
    height: .6rem;
}
</style>
