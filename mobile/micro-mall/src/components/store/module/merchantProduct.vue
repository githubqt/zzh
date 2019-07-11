<template>
    <div class="cart-merchant" v-show="!isDel">
        <yd-flexbox class="product-list-item">
            <router-link :to="toDetail(info,{name:'Details',query:{id:info.product_id}})">
                <img v-lazy="info.logo_url">
            </router-link>
            <yd-flexbox-item class="price-right-box" align="top">
                <yd-flexbox>
                    <yd-flexbox-item>
                        <div>{{info.name}}</div>
                    </yd-flexbox-item>
                </yd-flexbox>
                <!-- 销售价 -->
                <yd-flexbox>
                    <yd-flexbox-item>
                        <div class="cart-status-txt">￥{{info.sale_price}}</div>
                    </yd-flexbox-item>
                </yd-flexbox>
                <!-- 市场价 -->
                <yd-flexbox>
                    <yd-flexbox-item>
                        <div class="market-price">￥{{info.market_price}}</div>
                    </yd-flexbox-item>
                    <yd-flexbox-item>
                        <yd-flexbox class="spinner-container" v-if="isProductValid(info)">
                            <yd-spinner v-model="productNum" ref="num" :cart_id="info.cart_id" :product_id="info.product_id" :price="info.sale_price"></yd-spinner>
                        </yd-flexbox>
                    </yd-flexbox-item>
                </yd-flexbox>
                <!-- 标签 -->
                <yd-flexbox>
                    <yd-flexbox-item>
                        <yd-badge shape="square" type="hollow" v-if="!isProductValid(info)">失效</yd-badge>
                        <yd-badge shape="square" type="hollow" v-if="!hasStock(info)">库存不足</yd-badge>
                        <yd-badge shape="square" type="warning" v-show="info.is_return==1">不支持退货</yd-badge>
                    </yd-flexbox-item>
                </yd-flexbox>
            </yd-flexbox-item>
        </yd-flexbox>
        <yd-flexbox class="product-extra-info">
            <yd-flexbox-item>
                <!--<div class="fight-group">-->
                <!--<yd-badge type="danger">拼团</yd-badge>-->
                <!--<span class="fight-group-price">拼团购买价¥100</span>-->
                <!--<span class="fight-group-link">立即拼团</span>-->
                <!--</div>-->
            </yd-flexbox-item>
            <yd-icon name="delete" @click.native="removeCart(info.cart_id)"></yd-icon>
        </yd-flexbox>
    </div>
</template>

<script>
    import Qs from "qs";
    export default {
        name: 'CartMerchant',
        props: ['info'],
        data() {
            return {
                isCheckAll: false,
                checklist: [],
                productNum: this.info.num,
                isDel: false
            }
        },
        created() {},
        watch: {
            productNum(newVal, oldVal) {
                let param = [],
                    cart_id = this.$refs.num.$el.getAttribute('cart_id'),
                    product_id = this.$refs.num.$el.getAttribute('product_id'),
                    price = this.$refs.num.$el.getAttribute('price');
                param.push(cart_id);
                param.push({
                    num: newVal,
                    price: price,
                    total: parseFloat(newVal * price)
                });
                this.$emit('handleBuyNum', param);
                if (newVal != oldVal) {
                    this.editNum({
                        product_id: product_id,
                        num: newVal,
                    });
                }
            }
        },
        methods: {
            // 判断是否可选
            canCheck(item) {
                return this.isProductValid(item) && this.hasStock(item);
            },
            //商品是否有效（下架）
            isProductValid(item) {
                switch (item.product_from) {
                    case "自营":
                        if (item.on_status == 2) {
                            return true;
                        }
                        break;
                    case "供应":
                        if (item.channel_on_status == 2) {
                            return true;
                        }
                        break;
                    default:
                        return false;
                        break;
                }
                return false;
            },
            //是否有库存
            hasStock(item) {
                return item.stock > 0;
            },
            // 商品详细
            toDetail(item, param) {
                if (this.canCheck(item)) {
                    return param;
                }
                return "#";
            },
            // 从购物车移除
            removeCart(id) {
                let _this = this,
                    _data = Qs.stringify({
                        id: id
                    });
                _this.$dialog.loading.open("删除中...");
                _this.$http
                    .post("/api/v1/Cart/delCart", _data)
                    .then(function(response) {
                        if (parseInt(response.data.errno) === 0) {
                            _this.$emit('isReload', {
                                cart_id: id
                            });
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: "删除成功",
                                timeout: 1500,
                                icon: "success"
                            });
                        } else {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: '删除失败',
                                timeout: 1500,
                                icon: "error"
                            });
                        }
                    })
                    .catch(function(error) {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({
                            mes: '网络错误,请稍候再试',
                            timeout: 1500,
                            icon: "error"
                        });
                    });
            },
            // 修改商品数量
            editNum(param) {
                var _this = this,
                    _data = Qs.stringify({
                        product_id: param.product_id,
                        num: param.num
                    });
                _this.$http
                    .post("/api/v1/Cart/editNum", _data)
                    .then(function(response) {
                        if (parseInt(response.data.errno) > 0) {
                            _this.$dialog.toast({
                                mes: response.data.errmsg,
                                timeout: 1500,
                                icon: "error"
                            });
                        }
                    })
                    .catch(function(error) {
                        _this.$dialog.toast({
                            mes: '网络错误，请稍候重试',
                            timeout: 1500,
                            icon: "error"
                        });
                    });
            },
        }
    }
</script>

<style scoped>

</style>