<template>
    <yd-layout class="coupons" title="我的优惠券" link="/user">
        <!--  tabs start -->
        <yd-tab active-color="#dc2821">
            <yd-tab-panel label="未使用">
                <couponList v-bind:status="type1"></couponList>
            </yd-tab-panel>
            <yd-tab-panel label="已使用">
                <couponList v-bind:status="type2"></couponList>
            </yd-tab-panel>
            <yd-tab-panel label="已过期">
                <couponList v-bind:status="type3"></couponList>
            </yd-tab-panel>
        </yd-tab>
        <!--  tabs end -->
        <!-- 领券中心 start -->
        <yd-tabbar slot="tabbar" fixed>
            <router-link to="/couponsCenter" class="coupon-center">
                领券中心
            </router-link>
        </yd-tabbar>
        <!-- 领券中心 end  -->
    </yd-layout>
</template>

<script>
    import couponList from './coupon/list'
    export default {
        name: "Coupons",
        components: {
            couponList
        },
        data() {
            return {
                type1: '1',
                type2: '2',
                type3: '3'
            }
        },
        mounted: function() {
        },
        methods: {
        }
    }
</script>

<style scoped>

</style>
