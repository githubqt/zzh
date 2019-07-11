<template>
    <yd-layout class="snap-up">
        <yd-navbar title="限时抢购" slot="navbar">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <!-- 搜索框 -->
        <div class="search-box clearfix" slot="navbar">
            <div :class="searchStatus?'status active': 'status'" @click="showSearchOption('status')">
                <span class="txt">{{clickStatus.status?statusList[chooseStatusIndex]:'状态'}} </span>
                <yd-icon custom :name="searchStatus?'jt-up':'jt-down'" size="0.3rem"></yd-icon>
            </div>
            <div :class="searchOrder?'order active': 'order'" @click="showSearchOption('order')">
                <span class="txt">{{clickStatus.order?orderList[chooseOrderIndex]:'排序'}}</span>
                <yd-icon custom :name="searchOrder?'jt-up':'jt-down'" size="0.3rem"></yd-icon>
            </div>
        </div>
        <!-- 搜索框面板 -->
        <div class="tab-mask" @click="hideSearchOption()" v-show="searchStatus || searchOrder">
            <div class="tab-box" id="tab1" v-show="searchStatus">
                <div :class="index === chooseStatusIndex?'tab-item  active':'tab-item'"
                     v-for="(item,index) in statusList" :key="index" @click="chooseStatus(index)">
                    {{item}}
                </div>
            </div>
            <div class="tab-box" id="tab2" v-show="searchOrder">
                <div :class="index === chooseOrderIndex?'tab-item  active':'tab-item'" v-for="(item,index) in orderList"
                     :key="index" @click="chooseOrder(index)">
                    {{item}}
                </div>
            </div>
        </div>
        <!-- 秒杀列表 -->
        <yd-infinitescroll :callback="snapUpDataGet" ref="infinitescrollDemo" class="snap-up-list">
            <yd-list theme="4" slot="list">
                <yd-list-item v-for="(item, index) in snapUpData" :key="index" @click="godetail(item)">
                    <div slot="img" class="product-img">
                        <img v-lazy="item.logo_url">
                        <span class="status-txt yellow">{{item.status_txt}}</span>
                    </div>
                    <span slot="title" @click="godetail(item)">{{item.product_name}}</span>
                    <!-- 秒杀价 -->
                    <yd-list-other slot="other">
                        <yd-flexbox-item>
                            <span class="sale-price">￥{{item.seckill_price}}</span>
                        </yd-flexbox-item>
                        <yd-flexbox-item class="text-right" v-show="item.status_num == 2">
                            <yd-button @click.native="godetail(item)" shape="circle" type="danger">立即秒杀</yd-button>
                        </yd-flexbox-item>
                    </yd-list-other>
                    <!-- 市场价 -->
                    <yd-list-other slot="other">
                        <yd-flexbox-item>
                            <span class="market-price">¥{{item.market_price}}</span>
                        </yd-flexbox-item>
                    </yd-list-other>
                    <!-- 倒计时 -->
                    <yd-list-other slot="other">

                        <yd-flexbox-item v-show="item.status_num== 1">
                            <span class="yellow">距离开始<yd-countdown :time="item.time" :callback="refresh" done-text=" " timetype="second"></yd-countdown></span>
                        </yd-flexbox-item>

                        <yd-flexbox-item v-show="item.status_num== 2">
                            <span class="yellow">距离结束<yd-countdown :time="item.time" :callback="refresh" done-text=" " timetype="second"></yd-countdown></span>
                        </yd-flexbox-item>

                    </yd-list-other>
                </yd-list-item>
            </yd-list>
            <!-- 数据全部加载完毕显示 -->
            <span slot="doneTip">~~没有数据啦~~</span>
        </yd-infinitescroll>
    </yd-layout>
</template>

<script>
    import Qs from "qs";

    export default {
        name: "Home",
        components: {},
        data() {
            return {
                page: 1,
                rows: 10,
                snapUpData: "",
                searchStatus: false,
                searchOrder: false,
                chooseStatusIndex: '',
                chooseOrderIndex: '',
                statusList: [],
                orderList: [],
                clickStatus: {
                    status: false,
                    order: false
                }
            };
        },
        created() {
            this.snapUpDataGet();
            this.statusConf();
            this.orderConf();
        },
        methods: {
            backGo() {
                window.history.length > 1
                    ? this.$router.go(-1)
                    : this.$router.push('/')
            },
            refresh() {
                location.reload();
            },
            godetail(e) {
                let _this = this;
                if (e.status_txt == "未开始") {
                    let msg = "活动还未开始呢，请在<br>" + e.starttime_txt + "<br>再来吧~";
                    _this.$dialog.toast({
                        mes: msg,
                        timeout: 2000,
                        icon: "error"
                    });
                    return;
                } else if (e.status_txt == "已结束") {
                    let msg = "活动已经结束了哟，下次早点来哦~";
                    _this.$dialog.toast({
                        mes: msg,
                        timeout: 2000,
                        icon: "error"
                    });
                    return;
                } else if (e.status_txt == "抢购完") {
                    let msg = "宝贝都被抢光了哟，下次早点来哦~";
                    _this.$dialog.toast({
                        mes: msg,
                        timeout: 2000,
                        icon: "error"
                    });
                    return;
                }
                _this.$router.push("/details?id=" + e.product_id);
            },
            snapUpDataGet() {
                let _this = this,
                    _data = Qs.stringify({
                        page: _this.page,
                        rows: _this.rows,
                        status: _this.chooseStatusIndex,
                        order: _this.chooseOrderIndex
                    });
                _this.$http
                    .post("/api/v1/Product/seckillList", _data)
                    .then(function (response) {
                        if (response.data.errno === "0") {
                            _this.snapUpData = [
                                ..._this.snapUpData,
                                ...response.data.result.list
                            ];
                            console.log(_this.snapUpData);
                            if (
                                response.data.result.list.length < _this.rows ||
                                response.data.result.total / _this.page === 0
                            ) {
                                _this.$refs.infinitescrollDemo.$emit(
                                    "ydui.infinitescroll.loadedDone"
                                );
                            } else {
                                _this.$refs.infinitescrollDemo.$emit(
                                    "ydui.infinitescroll.finishLoad"
                                );
                                _this.page++;
                            }
                            _this.$nextTick(function () {
                                _this.$dialog.loading.close();
                            });
                        } else {
                            _this.$dialog.loading.close();
                            _this.$dialog.confirm({
                                title: "提示",
                                mes: "登录失效,请重新登录",
                                opts: () => {
                                    _this.$router.push("/login");
                                }
                            });
                        }
                    })
                    .catch(function (error) {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({
                            mes: error,
                            timeout: 1500,
                            icon: "error"
                        });
                    });
            },
            // 状态搜索条件
            statusConf() {
                this.statusList = [
                    '未开始', '抢购中', '已结束'
                ];
            },
            // 排序搜索条件
            orderConf() {
                this.orderList = [
                    '默认排序', '价格从高到低', '价格从低到高', '秒杀次数由低到高', '秒杀次数由高到低'
                ];
            },
            //显示搜索条件
            showSearchOption(type) {
                if (type === 'status') {
                    this.searchStatus = true;
                    this.searchOrder = false;
                } else {
                    this.searchStatus = false;
                    this.searchOrder = true;
                }
            },
            //隐藏搜索条件
            hideSearchOption() {
                this.searchStatus = false;
                this.searchOrder = false;
            },
            //选择状态索引
            chooseStatus(index) {
                this.chooseStatusIndex = index;
                this.clickStatus.status = true;
                this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
                this.snapUpData = [];
                this.page = 1;
                this.$dialog.loading.open("很快加载好了");
                this.snapUpDataGet();
            },
            //选择排序索引
            chooseOrder(index) {
                this.chooseOrderIndex = index;
                this.clickStatus.order = true;
                this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
                this.snapUpData = [];
                this.page = 1;
                this.$dialog.loading.open("很快加载好了");
                this.snapUpDataGet();
            }
        }
    };
</script>

<style>
</style>
