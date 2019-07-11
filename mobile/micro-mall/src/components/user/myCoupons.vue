<template>
    <section class="myCoupon">
        <!-- header -->
        <yd-navbar title="我的优惠券" class="yhq-navbar">
            <router-link slot="left" to="/user">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
        </yd-navbar>
        <yd-tab class="yhq-tab" active-color="#dab461">
            <yd-tab-panel class="yhq-tab-panel" label="未使用">
                <coupon v-bind:status="type1"></coupon>
            </yd-tab-panel>
            <yd-tab-panel class="yhq-tab-panel" label="已使用">
                <coupon v-bind:status="type2"></coupon>
            </yd-tab-panel>
            <yd-tab-panel class="yhq-tab-panel" label="已过期">
                <coupon v-bind:status="type3"></coupon>
            </yd-tab-panel>
        </yd-tab>
        <div class="tab-footer">
            <router-link to="/couponsCenter"   class="btn-receive-coupon">
                领券中心
            </router-link>
        </div>
    </section>
</template>

<script>
import coupon from './module/myCoupon'

export default {
    components: {
        coupon
    },
    data() {
        return {
            type1: '1',
            type2: '2',
            type3: '3'
        }
    },
    mounted: function () {
        this.$nextTick(function () {

        })
    },
    methods: {

    }
}
</script>

<style scoped>
</style>
