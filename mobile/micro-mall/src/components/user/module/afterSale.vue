<template>
    <section class="afterSale-container">
        <yd-navbar title="售后">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <yd-tab active-color="#dab461" :callback="fn">
            <!-- 售后申请 -->
            <yd-tab-panel label="售后申请">
                <yd-preview :buttons="btns" class="m-b-_24" v-for="(item, index) in saleList" :key="index">
                    <yd-preview-header>
                        <div slot="left">订单编号:{{item.child_order_no}}</div>
                        <div slot="right" class="afterSale-status-txt">{{item.child_status_txt}}</div>
                    </yd-preview-header>
                    <yd-preview-item v-for="(list, key) in item.product" :key="key">
                        <router-link class="afterSale-preview-img" :to="{name:'Details',query:{id:list.product_id}}" slot="left">
                            <img :src="list.logo_url" :onerror="errorImg">
                        </router-link>
                        <div slot="right">
                            <div>{{list.product_name}}</div>
                            <div>公价:{{list.market_price}}</div>
                            <div>销售价:{{list.sale_price}}</div>
                        </div>
                    </yd-preview-item>
                    <yd-preview-item>
                        <div slot="left">合计总量: {{item.sale_num}}</div>
                        <div slot="right">实付款: {{item.child_order_actual_amount}}</div>
                    </yd-preview-item>

                    <yd-preview-item    v-show="item.is_return == 2">
                        <div slot="right" class="afterSale-bottom-btn">
                            <yd-button type="hollow" @click.native="returnShowM(item.id)">申请售后</yd-button>
                        </div>
                    </yd-preview-item>
                </yd-preview>
            </yd-tab-panel>
            <!-- 申请记录 -->
            <yd-tab-panel label="申请记录">
                <yd-preview :buttons="btns" class="m-b-_24" v-for="(item, index) in returnList" :key="index">
                    <yd-preview-header>
                        <div slot="left">退货编号:{{item.order_no}}</div>
                        <div slot="right" class="afterSale-status-txt">{{item.child_status_name}}</div>
                    </yd-preview-header>
                    <yd-preview-item v-for="(list, key) in item.product_list" :key="key">
                        <router-link class="afterSale-preview-img" :to="{name:'Details',query:{id:list.product_id}}" slot="left">
                            <img :src="list.logo_url" :onerror="errorImg">
                        </router-link>
                        <div slot="right">
                            <div>{{list.product_name}}</div>
                            <div>公价:{{list.market_price}}</div>
                            <div>销售价:{{list.sale_price}}</div>
                        </div>
                    </yd-preview-item>
                    <yd-preview-item>
                        <div slot="left">实际付钱: {{item.child_order_actual_amount}}</div>
                        <div slot="right">退款金额: {{item.back_money}}</div>
                    </yd-preview-item>
                    <yd-preview-item v-show="item.child_status==='20'">
                        <div slot="right" class="afterSale-bottom-btn">
                            <yd-button type="hollow" @click.native="orderExpressId(item.id)">填写快递信息</yd-button>
                        </div>
                    </yd-preview-item>
                </yd-preview>
            </yd-tab-panel>
        </yd-tab>
        <!-- 底部弹窗-申请售后 -->
        <yd-popup v-model="returnShow" position="bottom" height="auto">
            <yd-cell-group>
                <yd-cell-item type="radio" v-for="(item, index) in productList" :key="index">
                    <span slot="left" class="text-ellipsis" style="display:block;width:5rem;">{{item.product_name}}</span>
                    <!-- <input slot="right" type="radio" :value="item.product_id" v-model="activechildId" /> -->
                    <yd-switch slot="right" color="#dab461" :true-value="item.product_id" v-model="activechildId"></yd-switch>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">退货数量</span>
                    <yd-spinner slot="right" min="1" :max="returnNumMax" v-model="returnNum"></yd-spinner>
                </yd-cell-item>
                <yd-cell-item>
                    <yd-textarea slot="right" v-model="returnNote" placeholder="请输入退货原因" maxlength="100"></yd-textarea>
                </yd-cell-item>
                <imgUpload v-bind:housingImg="housing_img" v-bind:fileType="fileType" v-on:picUrlSet="uploadAfterSale"></imgUpload>
            </yd-cell-group>
            <div class="return-btn-box">
                <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="orderReturn">确认申请</yd-button>
            </div>
        </yd-popup>
        <!-- 底部弹窗-填写快递信息 -->
        <yd-popup v-model="goodsShow" position="bottom" height="auto">
            <yd-cell-group>
                <yd-cell-item>
                    <span slot="left">快递公司</span>
                    <yd-input slot="right" v-model="expressName" required :show-success-icon="false" :show-error-icon="false" placeholder="请输入快递公司"></yd-input>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">快递单号</span>
                    <yd-input slot="right" v-model="expressNum" required :show-success-icon="false" :show-error-icon="false" placeholder="请输入快递单号"></yd-input>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">快递说明</span>
                    <yd-input slot="right" v-model="expressNote" required :show-success-icon="false" :show-error-icon="false" placeholder="请输入快递说明"></yd-input>
                </yd-cell-item>
            </yd-cell-group>
            <div class="return-btn-box">
                <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="orderExpress">确认</yd-button>
            </div>
        </yd-popup>
    </section>
</template>
<script>
    import Qs from 'qs'
    import imgUpload from './../../pawn/module/imgUpload';
    import {logout} from "../../../../tool/login";
    export default {
        name: 'Orderload',
        components: { imgUpload },
        data() {
            return {
                page: 1,
                rows: 15,
                btns: [],
                saleList: [],
                returnShow: false,
                productList: [],
                activeId: '',
                activechildId: '',
                returnNumMax: 100,
                returnNum: 0,
                returnNote: '',
                housing_img: [],
                returnList: [],
                goodsShow: false,
                expressId: '',
                expressName: '',
                expressNum: '',
                expressNote: '',
                errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"',
                fileType: '4'
            }
        },
        mounted() { this.isLogin() },
        watch: {
            //控制退货最大数量
            activechildId(val, oldVal) {
                console.log(oldVal);
                var _this = this;
                if (_this.activechildId) {
                    _this.productList.forEach(item => {
                        if (item.product_id === _this.activechildId) {
                            _this.returnNumMax = item.sale_num;
                            if (_this.returnNum>item.sale_num) {
                                _this.returnNum = item.sale_num;
                            }
                        }
                    });
                }
            }
        },
        methods: {
            backGo() { this.$router.go(-1) },
            isLogin() {
                let _this = this,
                    _data = Qs.stringify({ user_id: localStorage.getItem('userId') });

                _this.$http.post('/api/v1/User/isLogin', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.afterSaleGet();
                    } else {
                        logout();
                        _this.$dialog.confirm({ title: '提示', mes: '登录失效,请重新登录', opts: () => { _this.$router.push('/login') } });
                    }
                }).catch(function(error) {
                    _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
                });
            },
            afterSaleGet() {
                let _this = this,
                    _data = Qs.stringify({
                        user_id: localStorage.getItem('userId'),
                        status: 6,
                        page: _this.page,
                        rows: _this.rows
                    });

                _this.$dialog.loading.open('很快加载好了');
                _this.$http.post('/api/v1/Order/list', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.saleList = response.data.result.list;
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
            returnShowM(id) {
                let _this = this,
                    _data = Qs.stringify({ user_id: localStorage.getItem('userId'), id: id });

                _this.$http.post('/api/v1/Order/detail', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        if ( response.data.result.discount_type === '3') {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({ mes: '该订单暂不支持申请售后', timeout: 1500, icon: 'error' });
                        } else {
                            _this.activeId = id;
                            _this.productList = response.data.result.product;
                            _this.$nextTick(function() { _this.returnShow = !_this.returnShow });
                        }
                    } else {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                    }
                }).catch(function(error) {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
                });
            },
            uploadAfterSale(housingImg) { this.housing_img = housingImg},
            orderReturn() {
                let _this = this,
                    _data = Qs.stringify({
                        user_id: localStorage.getItem('userId'),
                        order_child_id: _this.activeId,
                        product_id: _this.activechildId,
                        num: _this.returnNum,
                        note: _this.returnNote,
                        items: _this.housing_img
                    });
                if (_this.activeId == '') {
                    _this.$dialog.toast({ mes: '请选择订单', timeout: 1500, icon: 'error' });
                    return;
                }
                if (_this.activechildId ==  '') {
                    _this.$dialog.toast({ mes: '请选择商品', timeout: 1500, icon: 'error' });
                    return;
                }
                if (_this.returnNum <= 0) {
                    _this.$dialog.toast({ mes: '请输入商品数量', timeout: 1500, icon: 'error' });
                    return;
                }
                if (_this.returnNum > _this.returnNumMax) {
                    _this.$dialog.toast({ mes: '退货数量不能大于可退数量', timeout: 1500, icon: 'error' });
                    return;
                }


                _this.$dialog.confirm({
                    title: '售后',
                    mes: '您确定申请售后吗?',
                    opts: () => {
                        _this.$http.post('/api/v1/Orderreturn/add', _data).then(function(response) {
                            if (response.data.errno === '0') {
                                _this.returnShow = false;
                                _this.afterSaleGet();
                                _this.$dialog.toast({ mes: '申请成功', timeout: 1500, icon: 'success' });
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
            fn(label, key) {
                key === 0 ? this.afterSaleGet() : this.recordSaleGet();
            },
            recordSaleGet() {
                let _this = this,
                    _data = Qs.stringify({ user_id: localStorage.getItem('userId'), page: _this.page, rows: _this.rows });

                _this.$dialog.loading.open('很快加载好了');
                _this.$http.post('/api/v1/Orderreturn/list', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.returnList = response.data.result.list;
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
            orderExpressId(id) {
                this.expressId = id;
                this.goodsShow = !this.goodsShow;
            },
            orderExpress() {
                let _this = this,
                    _data = Qs.stringify({
                        user_id: localStorage.getItem('userId'),
                        id: _this.expressId,
                        express_name: _this.expressName,
                        express_num: _this.expressNum,
                        express_note: _this.expressNote
                    });

                _this.$dialog.confirm({
                    title: '信息',
                    mes: '请确认信息无误',
                    opts: () => {
                        _this.$http.post('/api/v1/Orderreturn/addExpress', _data)
                            .then(function(response) {
                                if (response.data.errno === '0') {
                                    _this.recordSaleGet();
                                    _this.goodsShow = !_this.goodsShow;
                                    _this.$dialog.toast({ mes: '成功', timeout: 1500, icon: 'success' });
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
            }
        }
    }

</script>
<style>
    @import "../../../assets/css/components/user/module/aftersale";

</style>
