<template>
    <yd-layout class="coupon-commodity" title="店铺优惠券" link="/coupons">
        <div v-for="(info, key) in couponList" :key="key">
        <div class="card-sawtooth"></div>
        <div class="coupon-card">
            <h3 class="">{{info.company}}</h3>
            <div class="coupon-time">
                <yd-flexbox>
                    <yd-flexbox-item>
                        <span class="t-line"></span>
                    </yd-flexbox-item>
                    <yd-flexbox-item class="valid-time">使用期限 {{info.start_time}} - {{info.end_time}}</yd-flexbox-item>
                    <yd-flexbox-item>
                        <span class="t-line"></span>
                    </yd-flexbox-item>
                </yd-flexbox>
            </div>
            <yd-flexbox class="coupon-desc">
                <yd-flexbox-item>
                    <div v-if="info.pre_type == 2 " class="coupon-price">{{info.pre_txt}}</div>
                    <div  v-else class="coupon-price">￥{{info.pre_value}}</div>
                    <div class="coupon-price-range">{{info.sill_txt}}</div>
                </yd-flexbox-item>
                <yd-flexbox-item>
                    <yd-icon custom name="gouwudai" size="2rem"></yd-icon>
                </yd-flexbox-item>
            </yd-flexbox>
        </div>
        <!-- 网点列表 start -->
        <!--<div class="network-list">-->
            <!--<yd-cell-group>-->
                <!--<yd-cell-item arrow>-->
                    <!--<span slot="left"> <yd-icon custom name="wdzs" size="0.3rem"></yd-icon> <span class="network-name">扎呵呵-朝阳区青年路店</span> </span>-->
                    <!--<span slot="right"></span>-->
                <!--</yd-cell-item>-->
                <!--<yd-cell-item arrow>-->
                    <!--<span slot="left"> <yd-icon custom name="wdzs" size="0.3rem"></yd-icon> <span class="network-name">扎呵呵-朝阳区大悦城店</span> </span>-->
                    <!--<span slot="right"></span>-->
                <!--</yd-cell-item>-->
                <!--<yd-cell-item arrow>-->
                    <!--<span slot="left"> <yd-icon custom name="wdzs" size="0.3rem"></yd-icon> <span class="network-name">扎呵呵-朝阳区通州北关店</span> </span>-->
                    <!--<span slot="right"></span>-->
                <!--</yd-cell-item>-->
                <!--<yd-cell-item arrow>-->
                    <!--<span slot="left"> <yd-icon custom name="wdzs" size="0.3rem"></yd-icon> <span class="network-name">扎呵呵-朝阳区通州北关店</span> </span>-->
                    <!--<span slot="right"></span>-->
                <!--</yd-cell-item>-->
                <!--<yd-cell-item arrow>-->
                    <!--<span slot="left"> <yd-icon custom name="wdzs" size="0.3rem"></yd-icon> <span class="network-name">扎呵呵-朝阳区通州北关店</span> </span>-->
                    <!--<span slot="right"></span>-->
                <!--</yd-cell-item>-->
                <!--<yd-cell-item arrow>-->
                    <!--<span slot="left"> <yd-icon custom name="wdzs" size="0.3rem"></yd-icon> <span class="network-name">扎呵呵-朝阳区通州北关店</span> </span>-->
                    <!--<span slot="right"></span>-->
                <!--</yd-cell-item>-->
                <!--<yd-cell-item arrow>-->
                    <!--<span slot="left"> <yd-icon custom name="wdzs" size="0.3rem"></yd-icon> <span class="network-name">扎呵呵-朝阳区通州北关店</span> </span>-->
                    <!--<span slot="right"></span>-->
                <!--</yd-cell-item>-->
            <!--</yd-cell-group>-->
        <!--</div>-->
        <!-- 网点列表 end  -->
        <!-- 使用 start -->
        <yd-tabbar slot="tabbar" fixed>
            <yd-button type="danger" size="large"  @click.native="shopProduct()" class="use-btn">立即使用</yd-button>
        </yd-tabbar>
        <!-- 使用 end  -->
            </div>
    </yd-layout>
</template>
<script>
    import Qs from "qs";
    export default {
        name: "CouponCommodity",
        components: {},
        data() {
            return {
                couponList:'',
            }
        },
        created() {
            this.CouponGetData();
        },
        mounted() {


        },
        methods: {
            shopProduct(){
                this.$router.push("/store");
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