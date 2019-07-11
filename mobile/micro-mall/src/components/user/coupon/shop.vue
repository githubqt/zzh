<template>
    <yd-layout class="coupon-commodity" title="商品优惠券" link="/coupons">
        <div v-for="(info, key) in couponList" :key="key">
            <div class="card-sawtooth"></div>
            <div class="coupon-card">
                <h3 class="">{{info.company}}商品优惠卷</h3>
                <div class="coupon-time">
                    <yd-flexbox>
                        <yd-flexbox-item>
                            <span class="t-line"></span>
                        </yd-flexbox-item>
                        <yd-flexbox-item class="valid-time">使用期限 {{info.start_time}} - {{info.end_time}}
                        </yd-flexbox-item>
                        <yd-flexbox-item>
                            <span class="t-line"></span>
                        </yd-flexbox-item>
                    </yd-flexbox>
                </div>
                <yd-flexbox class="coupon-desc">
                    <yd-flexbox-item>
                        <div v-if="info.pre_type == 2 " class="coupon-price">{{info.pre_txt}}</div>
                        <div v-else class="coupon-price">￥{{info.pre_value}}</div>
                        <div class="coupon-price-range">{{info.sill_txt}}</div>
                    </yd-flexbox-item>
                    <yd-flexbox-item>
                        <yd-icon custom name="gouwudai" size="2rem"></yd-icon>
                    </yd-flexbox-item>
                </yd-flexbox>
            </div>
            <div class="coupon-range-desc">适用商品：</div>
            <!-- 商品列表 start -->
            <yd-infinitescroll class="products-list">
                <yd-list theme="4" slot="list">
                    <yd-list-item v-for="(item, key) in info.product.list" :key="key"
                                  @click.native="showDetails(item.id)">
                        <img slot="img" :src="item.logo_url">
                        <span slot="title">{{item.name}}</span>
                        <yd-list-other slot="other">
                            <div>
                                <span class="sale-price"><em>¥</em>{{item.sale_price}}</span>
                                <span class="market-price">¥{{item.market_price}}</span>
                            </div>
                        </yd-list-other>
                    </yd-list-item>
                </yd-list>
                <!-- 数据全部加载完毕显示 -->
                <span slot="doneTip">啦啦啦，啦啦啦，没有数据啦~~</span>
                <!-- 加载中提示，不指定，将显示默认加载中图标 -->
                <img slot="loadingTip" src="../../../assets/img/loading10.svg"/>
            </yd-infinitescroll>
            <!-- 商品列表 end  -->
            <!-- 使用 start -->
            <div>
            <yd-tabbar slot="tabbar" fixed >
                <yd-button type="danger" size="large" @click.native="shopProduct(couponList[0].product.list)" class="use-btn">立即使用</yd-button>
            </yd-tabbar>
            </div>

             <!--使用 end-->
        </div>
    </yd-layout>
</template>
<script>
    import Qs from "qs";

    export default {
        name: "CouponShop",
        components: {},
        data() {
            return {
                couponList: '',
                list:[]
            }
        },
        created() {
            this.CouponGetData();

        },
        methods: {
            showDetails(id) {
                this.$router.push("/details?id=" + id);
            },
            shopProduct(data) {
                let _this   =this;
                if(data.length == 1){
                    _this.$router.push("/details?id=" + data[0].id);
                }else{
                    data.forEach(function (c,i) {
                        _this.list[i] = c.id;
                    });
                     _this.$router.push("/store?data="+_this.list);
                }

            },
            CouponGetData() {
                let _this = this,
                    _data = Qs.stringify({
                        coupon_id: _this.$route.query.coupon_id,
                        status: _this.$route.query.status
                    });
                _this.$dialog.loading.open('很快加载好了');
                _this.$http
                    .post("/api/v1/Coupan/solaCoupan", _data)
                    .then(function (response) {
                        if (response.data.errno == "0") {

                            _this.couponList = response.data.result.list;
                                console.log(_this.couponList);
                            _this.$dialog.loading.close();
                        } else {
                            _this.$dialog.loading.close();
                        }
                    })
                    .catch(function (error) {
                        _this.$dialog.loading.close();
                    });

            }
        }
    }
</script>

<style scoped>

</style>