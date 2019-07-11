<template>
    <section class="collect-container">
        <!-- header -->
        <yd-navbar class="fixed-header" title="收藏商品">
            <router-link to="/user" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
        </yd-navbar>

        <!-- content -->
        <yd-list theme="4" calss="list">
            <yd-list-item v-for="(item, key) in list" :key="key" type="link"
                          :href="{name:'Details',query:{id:item.id}}">
                <img slot="img" v-lazy="item.logo_url">
                <span slot="title">{{item.name}}</span>
                <yd-list-other slot="other" class="sale-price">
                    <span class="market-price">公价: <em>¥</em>{{item.market_price}}</span>
                </yd-list-other>
                <yd-list-other slot="other" class="other-quote-price">
                    <span><em>¥</em>{{item.sale_price}}</span>
                </yd-list-other>
            </yd-list-item>
        </yd-list>
    </section>
</template>
<script>
    import Qs from 'qs'
    import {
        Auth
    } from "../../../mixins/auth"

    export default {
        name: 'Collect',
        mixins: [Auth],
        data() {
            return {
                list: [],
                page: 1,
                rows: 10,
                searchStatus: false,
                searchOrder: false,
                clickStatus: {
                    status: false,
                    order: false
                }
            }
        },
        mounted() {
            this.concernListGet();
        },
        methods: {
            concernListGet() {
                let _this = this,
                    _data = Qs.stringify({user_id: localStorage.getItem('userId'), page: _this.page, rows: _this.rows});

                _this.$dialog.loading.open('很快加载好了');
                _this.$http.post('/api/v1/User/concernList', _data).then(function (response) {
                    if (response.data.errno === '0') {
                        _this.list = [..._this.list, ...response.data.result.list];
                        if ((response.data.result.list.length < _this.rows) || (response.data.result.total % _this.page === 0)) {
                            _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.loadedDone');
                        } else {
                            _this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.finishLoad');
                            _this.page++;
                        }
                        _this.$nextTick(function () {
                            _this.$dialog.loading.close()
                        });
                    } else {
                        _this.$dialog.loading.close();
                        // _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                    }
                }).catch(function (error) {
                    _this.$dialog.loading.close();
                    // _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
                });
            },
            // 状态搜索条件
            statusConf() {
                // this.statusList = [
                // 	'未开始', '进行中', '已结束'
                // ];
                this.statusList = [
                    '所有状态', '已开始', '未开始'
                ];
            },
            // 排序搜索条件
            orderConf() {
                this.orderList = [
                    '默认排序', '商品原价从高到低', '商品原价从低到高', '出价次数由低到高', '出价次数由高到低'
                ];
            },
        }
    }

</script>
<style>
    @import "../../../assets/css/components/user/module/collect";


</style>
