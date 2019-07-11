<template>
    <yd-layout title="订单详情" :link="`/recyclingOrderList?status=${req.status}&tabindex=${req.tabindex}`" class="recycling-order-container">
        <div class="order-count-time" v-show="initialize.isLoaded && detailItem.recovery_status == 20 && detailItem.remaining_time > 0">
            距离结束还有：
            <yd-countdown :time="detailItem.remaining_time" timetype="second"></yd-countdown>
        </div>
        <div class="order-product" v-show="initialize.isLoaded">
            <div class="order-product-title">
                <div class="title-left">订单信息</div>
                <div class="title-right">{{detailItem.status_txt}}</div>
            </div>
            <div class="order-product-detail">
                <yd-lightbox v-if="detailItem.img_count > 1" class="img-lightbox">
                    <yd-lightbox-img v-for="(d, key) in detailItem.imglist" :key="key" onerror="this.src='/static/imgs/err.jpg'" :src="d.img_url"></yd-lightbox-img>
                </yd-lightbox>
                <img v-lazy="detailItem.cover" alt="" v-else>
                <div class="img-count">
                    共 {{detailItem.img_count}} 张
                </div>
                <div class="product-order">
                    <div class="product-title">{{detailItem.product_name}}</div>
                    <div class="product-price">
                        <label class="llabel">估　价</label><span class="price">￥{{detailItem.offer_price}}</span> <span class="tips">(出价保留三个月)</span>
                    </div>
                    <div class="product-price" v-show="detailItem.recovery_status == 70">
                        <label class="llabel">销售价</label><span class="price">￥{{detailItem.sellout_price}}</span>
                    </div>
                    <div class="product-order-no"><label class="llabel">订单号</label>{{detailItem.id}}</div>
                    <div class="product-order-time"><label class="llabel">提交时间</label>{{detailItem.created_at}}</div>
                </div>
            </div>
            <div class="operator-box" v-show="canDelete()">
                <yd-button type="hollow" @click.native="editItem(detailItem)">重新发布</yd-button>
                <yd-button type="danger" @click.native="deleteItem(detailItem)">删除</yd-button>
            </div>
        </div>
        <!-- 原因 -->
        <div class="order-status-box" v-show="initialize.isLoaded && detailItem.fail_reason">
            <div class="order-status-title">
                审核信息
            </div>
            <div class="order-status-desc">
                {{detailItem.fail_reason}}
            </div>
        </div>
        <!-- 待审核 -->
        <div class="order-status-box" v-show="initialize.isLoaded && detailItem.recovery_status == 10">
            <div class="order-status-title">
                审核信息
            </div>
            <div class="order-status-desc">
                提交的商品正在审核中，请稍后查看结果
            </div>
        </div>
        
        <!-- 待出价 -->
        <div class="order-status-box" v-show="initialize.isLoaded && detailItem.recovery_status == 20">
            <div class="order-status-title">
                出价信息
            </div>
            <div class="order-status-desc">
                正在估价中,请稍后查看最终出价
            </div>
        </div>

    </yd-layout>
</template>
<script>
    import {
        adminLogin,
        adminLogout,
        getAdminState
    } from "../../../../tool/login";
    import Api from "../../../../tool/supplier";
    import RecyclingForm from "../../../../tool/recyclingForm";
    import {
        forEach
    } from "lodash";
    import Qs from 'qs'
    export default {
        name: "RecyclingOrderDetail",
        data() {
            return {
                req: {},
                detailItem: [],
                initialize: {
                    isLoaded: false
                }
            };
        },
        created() {
            this.req = this.$route.query;
        },
        mounted() {
            this.getDetail();
        },
        methods: {
            getDetail() {
                const _this = this;
                _this.$dialog.loading.open('数据加载中...');
                _this.$http.get(`${Api.order.detail}?id=${_this.req.id}`).then((d) => {
                    let errno = d.data.errno;
                    this.$dialog.loading.close();
                    switch (errno) {
                        case '0':
                            _this.detailItem = d.data.result;
                            _this.initialize.isLoaded = true;
                            break;
                        case '40015':
                            adminLogout();
                            _this.$router.replace('/recyclingLogin');
                            break;
                        default:
                            throw "服务暂不可用";
                            break;
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
            editItem(item) {
                const _this = this;
                _this.$dialog.confirm({
                    title: '提示',
                    mes: '确定要重新发布吗?',
                    opts: () => {
                        RecyclingForm.setEdit();
                        RecyclingForm.setStorage('brand_id', item.brand_id);
                        RecyclingForm.setStorage('brand_name', item.brand_name);
                        RecyclingForm.setStorage('category_id', item.category_id);
                        RecyclingForm.setStorage('category_name', item.last_category_name);
                        RecyclingForm.setStorage('enclosure_ids', item.enclosure_ids);
                        RecyclingForm.setStorage('extras', item.extras);
                        RecyclingForm.setStorage('flaw_ids', item.flaw_ids);
                        RecyclingForm.setStorage('havetime', item.use_time_note);
                        RecyclingForm.setStorage('material', item.recovery_material);
                        RecyclingForm.setStorage('note', item.recovery_note);
                        RecyclingForm.setStorage('position', item.position);
                        RecyclingForm.setStorage('size', item.recovery_size);
                        _this.$router.replace(`/createRecycling?pid=${item.category_parent_id}&edit=1&id=${item.id}`);
                    }
                });
            },
            deleteItem(item) {
                const _this = this;
                _this.$dialog.confirm({
                    title: '提示',
                    mes: '确定删除？',
                    opts: () => {
                        _this.$dialog.loading.open('数据加载中...');
                        _this.$http.post(`${Api.order.delete}?id=${_this.detailItem.id}`).then((d) => {
                            let errno = d.data.errno;
                            _this.$dialog.loading.close();
                            switch (errno) {
                                case '0':
                                    _this.$dialog.toast({
                                        mes: '删除成功',
                                        timeout: 2000,
                                        icon: 'success',
                                        callback: () => {
                                            _this.$router.replace(`/recyclingOrderList?status=${_this.req.status}&tabindex=${_this.req.tabindex}`);
                                        }
                                    });
                                    break;
                                case '40015':
                                    adminLogout();
                                    _this.$router.replace('/recyclingLogin');
                                    break;
                                default:
                                    throw d.data.errmsg || '服务暂不可用';
                                    break;
                            }
                        }).catch((err) => {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: err.toString() || '服务暂不可用',
                                timeout: 1500,
                                icon: 'error'
                            });
                        });
                    }
                });
            },
            canDelete() {
                return this.detailItem.recovery_status == '15' || this.detailItem.recovery_status == '40' || this.detailItem.recovery_status == '80';
            }
        }
    }
</script>

