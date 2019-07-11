<template>
    <section class="product-container">
        <yd-navbar title="拍卖详情" class="fixed-header">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>

            </div>
            <!--<span slot="right" @click="share"><yd-icon name="share1" size=".5rem"></yd-icon></span>-->
        </yd-navbar>

        <yd-slider>
            <yd-slider-item v-for="(item, index) in productData.imglist" :key="index">
                <a href="javascript:;" :id="item.id">
                    <img v-lazy="item.img_url" :onerror="errorImg" :alt="item.img_type">
                </a>

            </yd-slider-item>

        </yd-slider>

        <div style="    width: 100%; position: absolute; z-index: 3;">
            <span v-show="productData.is_restrictions==1" class="product-trailer-title">距离拍卖开始 <yd-countdown :time="productData.time"  timetype="second" :callback="refresh" done-text=" " ></yd-countdown></span>
            <span v-show="productData.is_restrictions==2" class="product-proceed-title">距离拍卖结束 <yd-countdown :time="productData.time_proceed"  timetype="second" :callback = "refresh" done-text=" " ></yd-countdown></span>
        </div>

        <section class="product-header">
            <h3 class="product-header-title">{{productData.product_name}}</h3>
            <span class="product-header-bazaar ">

            </span>
            <span class="product-header-price emphasis ">
                                                                                                                <span  v-show="reveal">当前价: <em>￥</em>  {{productData.total_price}} </span>
            <span v-show="!reveal">成交价: <em>￥</em>  {{productData.total_price}}</span>
            <span class="product-header-price current"> {{productData.apply_num}}人报名 | {{productData.onlookers_num}}人围观</span>
            </span>
            <span class="product-header-price emphasis ">
                                                                                                                <span>起拍价: <em>￥</em> {{productData.start_price}}</span>
            <span class="product-header-price current"> 加价幅度: <em>￥</em> {{productData.bid_lncrement}}</span>
            </span>
        </section>
        <div class="bid-box" @click="gorecord(productData)">
            <yd-cell-group>
                <yd-cell-item class="bid-record" arrow >
                    <div slot="left">
                        <span>出价记录 : <em v-if="productData.count!=false" class="bid-num">{{productData.count}}</em> <em v-else class="bid-num">0</em> 次出价</span>
                    </div>
                    <span slot="right"></span>
                </yd-cell-item>
            </yd-cell-group>
            <yd-flexbox class="yf-bid-box">
                <yd-flexbox-item v-for="(item, index) in recordData.list" :key="index" v-if="index < 3">
                    <div class="bid-box-item">
                        <div class="bid-box-title">
                            <span class="bid-box-tag  active" v-if="item.status==1">{{status == '2'?'成交':item.status_txt}}</span>
                            <span class="bid-box-tag " v-if="item.status==2">{{item.status_txt}}</span>
                            <span class="bid-box-name">{{item.user_txt}}</span>
                        </div>
                        <div class="bid-box-content">￥ {{item.money}}</div>
                    </div>
                </yd-flexbox-item>
                <yd-flexbox-item v-if="recordDataLength==1"></yd-flexbox-item>
                <yd-flexbox-item v-if="recordDataLength==1"></yd-flexbox-item>
                <yd-flexbox-item v-if="recordDataLength==2"></yd-flexbox-item>
            </yd-flexbox>
        </div>
        <yd-cell-group class="product-cell-record" style="margin-top: 0.2rem !important;">
            <hr/>
            <yd-cell-item arrow type="link" href="copywriter">
                <div slot="left">
                    <span style="font-weight: bold;">拍卖金规则    <em style="font-size: 10px;">未拍到24小时内自动退款</em></span>
                </div>
                <span slot="right"></span>
            </yd-cell-item>
        </yd-cell-group>
        <section class="product-bittorrent " style="margin-top: 0.3rem;" v-show="detailsData.brand_description!==''">
            <div class="order">
                <span style="white-space:pre; margin-left: 2.3rem;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">品牌介绍</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="detailsData.brand_description" class="details-seckill-txt"></div>
        </section>
        <section class="product-bittorrent " style="margin-top: 0.3rem;" v-show="detailsData.category_description!==''">
            <div class="order">
                <span style="white-space:pre; margin-left: 2.3rem;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">分类介绍</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="detailsData.category_description" class="details-seckill-txt"></div>
        </section>
        <section class="product-bittorrent" style="margin-top: 0.3rem;" v-show="detailsData.attribute.length!==0">
            <div class="order">
                <span style="white-space:pre;  margin-left: 2.3rem;">  </span><span class="line"></span>
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
        <section class="product-bittorrent " style="margin-top: 0.3rem;">
            <div class="order">
                <span style="white-space:pre; margin-left: 2.3rem;">  </span><span class="line"></span>
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
                <span style="white-space:pre;margin-left: 2.3rem;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">本店说明</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="supplierInfo.shop_instructions" class="details-seckill-txt"></div>
        </section>
        <section class="product-bittorrent" style="margin-top: 0.3rem;" v-show="supplierInfo.company_introduction!==''">
            <div class="order">
                <span style="white-space:pre; margin-left: 2.3rem;">  </span><span class="line"></span>
                <span style="white-space:pre;">  </span><span class="txt">公司介绍</span>
                <span style="white-space:pre;">  </span><span class="line"></span>
            </div>
            <div v-html="supplierInfo.company_introduction" class="details-seckill-txt"></div>
        </section>
        <!-- 底部按钮 -->
        <yd-flexbox class="product-button">
            <yd-flexbox-item v-show="status==1">
                <yd-button size="large" bgcolor="#fff" class="no-radius no-mt">
                    <div class="product-header-price margin">保证金￥ <span>{{productData.order_sale_price}}</span></div>
                    <div class="txt">（未拍到全额退还）</div>
                </yd-button>
            </yd-flexbox-item>
            <yd-flexbox-item v-show="status==4">
                <yd-button size="large" bgcolor="#fff" class="no-radius no-mt">
                    <div class="product-header-price margin">加价幅度￥ <span>{{productData.bid_lncrement}}</span></div>
                    <div class="txt">（未拍到全额退还）</div>
                </yd-button>
            </yd-flexbox-item>
            <yd-flexbox-item>
                <yd-button size="large" class="no-radius no-mt" v-show="status==1" type="danger" @click.native="payVerify">交保证金报名</yd-button>
                <yd-button size="large" v-show="status==2" class="no-radius no-mt" type="disabled">竞拍已结束!</yd-button>
                <yd-button v-show="status==3" size="large" class="no-radius no-mt" type="disabled">已交保证金，等待
                    <yd-countdown :time="productData.time" timetype="second"></yd-countdown>开拍!</yd-button>
                <yd-button size="large" class="no-radius no-mt" v-show="status==4 && productData.is_restrictions == 2" type="danger" @click.native="payPremium">确定加价!</yd-button>
            </yd-flexbox-item>
        </yd-flexbox>
        <div class="product-cart-box">
            <yd-icon name="type" class="product-btn-icon" @click.native="btnTpye()"></yd-icon>
        </div>
        <div class="btn-status-box" v-show="btnstatus==true">
            <img src="/../../static/imgs/home.png" v-on:click="staHome()">
            <img src="/../../static/imgs/user.png" v-on:click="staUser()">
        </div>
    </section>
</template>


<script>
    import Sa from "../../../tool/wechatshare";
    import {logout} from "../../../tool/login";
    import Qs from "qs";
    export default {
        name: "Store",
        components: {},
        data() {
            return {
                infoShow: false,
                productData: "",
                reveal: true,
                status: "",
                recordData: "",
                imgData: "",
                recordDataLength: 0,
                btnstatus: false,
                amount: 1,
                detailsData: {
                    introduction: "", //商品详情
                    brand_description: "", //品牌详情
                    category_description: "", //分类详情
                    imglist: [], //商品图片
                    attribute: [] //商品参数
                },
                supplierInfo: {
                    company_introduction: ""
                },
                errorImg: 'this.src="' + require("../../assets/img/err.jpg") + '"'
            };
        },
        created() {
            this.setResult();
            this.share();
        },
        methods: {
            btnTpye() {
                if (this.btnstatus == false) {
                    this.btnstatus = true;
                } else if (this.btnstatus == true) {
                    this.btnstatus = false;
                }
            },
            staHome() {
                this.$router.push("/bidding");
            },
            refresh() {
                location.reload();
            },
            staUser() {
                this.$router.push("/auctionList");
            },
            gorecord(p) {
                this.$router.push("/record?id=" + p.id + "&product_id=" + p.product_id + '&status='+ this.status);
            },
            backGo() {
                window.history.length > 1
                    ? this.$router.go(-1)
                    : this.$router.push('/')
            },
            recordDataGet() {
                let _data = Qs.stringify({
                    id: this.$route.query.id,
                    product_id: this.$route.query.product_id
                });
                return this.$http.post("/api/v1/Bidding/record", _data);
            },
            productDataGet() {
                let _data = Qs.stringify({
                    id: this.$route.query.id,
                    user_id: localStorage.getItem("userId")
                });
                return this.$http.post("/api/v1/Bidding/product", _data);
            },
            detailListGet() {
                let _data = Qs.stringify({
                    id: this.$route.query.product_id,
                    u_id: localStorage.getItem("userId")
                });
                return this.$http.post("/api/v1/Product/detail", _data);
            },
            supplierGet() {
                return this.$http.post("/api/v1/Home/supplier");
            },
            setResult() {

                let _this = this;
                _this.$http
                    .all([
                        _this.productDataGet(),
                        _this.detailListGet(),
                        _this.supplierGet(),
                        _this.recordDataGet()
                    ])
                    .then(
                        _this.$http.spread(function(p, d, b, r) {
                            if (p.data.errno === "0") {
                                _this.productData = p.data.result;
                                _this.recordData = r.data.result;
                                _this.recordDataLength = r.data.result ?
                                    r.data.result.list.length :
                                    0;
                                _this.status = 1; // 交保证金

                                let userId = localStorage.getItem("userId");

                                switch (p.data.result.is_restrictions) {
                                    case "3": // 活动已结束
                                        _this.status = 2; //竞拍已结束
                                        _this.reveal = !_this.reveal;
                                        break;
                                    case "2": // 活动进行中
                                        // 是否有交保证金的用户
                                        if (p.data.result.orderList != null &&
                                            p.data.result.orderList.user_id == userId &&
                                            p.data.result.orderList.is_margin == "2") {
                                            _this.status = 4; // 加价
                                        }
                                        break;
                                    case "1": // 活动预告中
                                        // 是否有交保证金的用户
                                        if (p.data.result.orderList != null &&
                                            p.data.result.orderList.user_id == userId &&
                                            p.data.result.orderList.is_margin == "2") {
                                            _this.status = 3; //已交保证金，等待开拍
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                            if (d.data.errno === "0") {
                                _this.detailsData = d.data.result;
                            }
                            if (b.data.errno === "0") {
                                _this.supplierInfo = b.data.result;
                            }
                            _this.$nextTick(function() {
                                _this.$dialog.loading.close();
                            });
                        })
                    );
            },
            payVerify() {
                if (
                    this.productData.is_restrictions == 1 || this.productData.is_restrictions == 2 && this.productData.is_restrictions != 3
                ) {
                    let _this = this,
                        _data = Qs.stringify({
                            user_id: localStorage.getItem("userId")
                        });
                    _this.$http
                        .post("/api/v1/User/isLogin", _data)
                        .then(function(response) {
                            if (response.data.errno === "0") {
                                _this.infoShow = true;
                                _this.$router.push("/marginorder?id=" + _this.productData.id);
                            } else {
                                logout();
                                _this.$router.push("/login");
                                _this.$dialog.toast({
                                    mes: response.data.errmsg,
                                    timeout: 1500,
                                    icon: "error"
                                });
                            }
                        })
                        .catch(function(error) {
                            _this.$dialog.toast({
                                mes: error,
                                timeout: 1500,
                                icon: "error"
                            });
                        });
                } else {
                    this.$dialog.toast({
                        mes: "活动已结束！",
                        timeout: 500,
                        icon: "error"
                    });
                    return;
                }
            },
            payPremium() {
                let _this = this,
                    _data = Qs.stringify({
                        id: _this._data.productData.id,
                        user_id: localStorage.getItem("userId")
                    });
                _this.$http
                    .post("/api/v1/User/isLogin", _data)
                    .then(function(response) {
                        if (response.data.errno === "0") {} else {
                            logout();
                            _this.$router.push("/login");
                            _this.$dialog.toast({
                                mes: response.data.errmsg,
                                timeout: 1500,
                                icon: "error"
                            });
                        }
                    })
                    .catch(function(error) {
                        _this.$dialog.toast({
                            mes: error,
                            timeout: 1500,
                            icon: "error"
                        });
                    });
                this.$dialog.confirm({
                    title: "确定加价！",
                    opts: () => {
                        _this.$http
                            .post("/api/v1/Bidding/premium", _data)
                            .then(function(e) {
                                if (e.data.errno === "0") {
                                    window.location.href = window.location.href;
                                } else {
                                    _this.$dialog.toast({
                                        mes: e.data.errmsg,
                                        timeout: 1500,
                                        icon: "error"
                                    });
                                }
                            })
                            .catch(function(error) {
                                _this.$dialog.toast({
                                    mes: error,
                                    timeout: 1500,
                                    icon: "error"
                                });
                            });
                    }
                });
            },
            share(){

                        let _this = this,
                            _data = Qs.stringify({
                                url: encodeURIComponent(location.href.split('#')[0])
                            });

                        _this.$http.post('/api/v1/weixin/getSingJsSign', _data).then(function(response) {
                            if (response.data.errno === '0') {


                                Sa.weChat(_this.productData.product_name,
                                                _this.detailsData.describe,
                                                response.data.result.url,
                                                _this.detailsData.icon_log,
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


        },

    };
</script>

<style>
</style>
