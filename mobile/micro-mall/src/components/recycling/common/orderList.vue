<template>
    <yd-infinitescroll :callback="loadList" ref="infinitescrollList" style="margin-bottom:0.75rem;" class="scroll-container">
        <div class="order-list" slot="list">
            <div class="order-item" v-for="(item,i) in listItems" :key="i" @click="showDetail(item)">
                <div class="order-item-number">
                    <div class="order-no">
                        订单号：{{item.id}}
                    </div>
                    <div class="order-status">
                        {{item.status_txt}}
                    </div>
                </div>
                <div class="order-item-product">
                    <img v-lazy="item.cover" alt="">
                    <div class="">
                        <!-- 标题 -->
                        <h3 class="product-title">{{item.product_name}}</h3>
                        <!-- 最终估价 -->
                        <div class="auction-price" v-show="item.recovery_status == '20' && item.remaining_time > 0">当前估价：￥{{item.offer_price}}</div>
                        <div class="auction-price" v-show="item.recovery_status == 30 
                                || item.recovery_status == 50  
                                || item.recovery_status == 60 
                                || item.recovery_status == 70   ">最终估价：￥{{item.offer_price}}</div>
                        <div class="processing-desc1" v-show="item.recovery_status == 30 ">本次出价{{item.recovery_day}}天有效</div>
                        <!-- 售卖价格 -->
                        <div class="final-price" v-show="item.recovery_status == 70">售卖价格：￥{{item.sellout_price}}</div>
                        <!-- 进行中说明 -->
                        <div class="processing-desc" v-show="item.recovery_status == 15 ">审核失败：{{item.fail_reason}}</div>
                        <div class="processing-desc" v-show="item.recovery_status == 80 ">取消原因：{{item.fail_reason}}</div>
                        <div class="processing-desc" v-show="item.recovery_status == 40">无人评估，下次再尝试</div>
                    </div>
                </div>
                <div class="offer-leave" v-show="item.recovery_status == '20' && item.remaining_time > 0">
                    距离估价结束还有：
                    <yd-countdown :time="item.remaining_time" timetype="second"></yd-countdown>
                </div>
            </div>
        </div>
        <!-- 数据全部加载完毕显示 -->
        <span slot="doneTip">没有更多数据啦~~</span>
    </yd-infinitescroll>
</template>
<script>
    import {
        adminLogin,
        adminLogout,
        getAdminState
    } from "../../../../tool/login";
    import Api from "../../../../tool/supplier";
    import {
        forEach
    } from "lodash";
    import Qs from 'qs'
    export default {
        name: "RecyclingOrderList",
        props: ['status', 'tabindex'],
        data() {
            return {
                statusChanged: 0,
                listItems: [],
                page: 1
            }
        },
        created() {},
        watch: {
            status(newVal, oldVal) {
                this.initList();
            }
        },
        methods: {
            initList() {
                const _this = this;
                _this.page = 1;
                _this.$refs.infinitescrollList.$emit('ydui.infinitescroll.reInit');
                _this.$dialog.loading.open('数据加载中...');
                _this.$http.get(`${Api.order.list}?status=${this.status}&page=${this.page}`).then((d) => {
                    try {
                        let errno = d.data.errno;
                        this.$dialog.loading.close();
                        switch (errno) {
                            case '0':
                                _this.listItems = d.data.result.rows;
                                if (_this.listItems.length === d.data.result.total) {
                                    _this.$refs.infinitescrollList.$emit('ydui.infinitescroll.loadedDone');
                                } else {
                                    _this.$refs.infinitescrollList.$emit('ydui.infinitescroll.finishLoad');
                                }
                                _this.page++;
                                break;
                            case '40015':
                                adminLogout();
                                _this.$router.replace('/recyclingLogin');
                                break;
                            default:
                                throw "服务暂不可用";
                                break;
                        }
                    } catch (err) {
                        _this.$dialog.loading.close();
                        console.log("​}catch -> err", err)
                        _this.$dialog.toast({
                            mes: '服务暂不可用',
                            timeout: 1500,
                            icon: 'error'
                        });
                    }
                }).catch((err) => {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: '服务暂不可用',
                        timeout: 1500,
                        icon: 'error'
                    });
                });
            },
            loadList() {
                const _this = this;
                _this.$http.get(`${Api.order.list}?status=${this.status}&page=${this.page}`).then((d) => {
                    try {
                        let errno = d.data.errno;
                        this.$dialog.loading.close();
                        switch (errno) {
                            case '0':
                                if (d.data.result.rows.length) {
                                    _this.listItems = [..._this.listItems, ...d.data.result.rows];
                                    if (_this.listItems.length === d.data.result.total) {
                                        _this.$refs.infinitescrollList.$emit('ydui.infinitescroll.loadedDone');
                                    } else {
                                        _this.$refs.infinitescrollList.$emit('ydui.infinitescroll.finishLoad');
                                    }
                                    _this.page++;
                                } else {
                                    _this.$refs.infinitescrollList.$emit('ydui.infinitescroll.finishLoad');
                                }
                                break;
                            case '40015':
                                adminLogout();
                                _this.$router.replace('/recyclingLogin');
                                break;
                            default:
                                throw "服务暂不可用";
                                break;
                        }
                    } catch (err) {
                        _this.$dialog.loading.close();
                        console.log("​}catch -> err", err)
                        _this.$dialog.toast({
                            mes: '服务暂不可用',
                            timeout: 1500,
                            icon: 'error'
                        });
                    }
                }).catch((err) => {
                    _this.$dialog.loading.close();
                    console.log("​loadList -> err", err)
                });
            },
            showDetail(item) {
                this.$router.push(`/recyclingOrderDetail?id=${item.id}&status=${this.status}&tabindex=${this.tabindex}`);
            }
        }
    }
</script>
