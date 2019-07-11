<template>
    <yd-tabbar fixed active-color="#dc2821" ref="footerBar" active-class="active">
        <yd-tabbar-item title="我的售卖" link="/recycling" :active="path=='/recycling'">
            <yd-icon name="chaxun" slot="icon" size="0.54rem" custom></yd-icon>
        </yd-tabbar-item>
        <yd-tabbar-item title="我的订单" link="/recyclingOrderList" :active="path=='/recyclingOrderList'">
            <yd-icon name="dingdan" slot="icon" size="0.54rem" custom></yd-icon>
        </yd-tabbar-item>
    </yd-tabbar>
</template>

<script>
    import Qs from 'qs'
    export default {
        name: 'RecyclingMenu',
        data() {
            return {
                path: '/'
            }
        },
        created() {
            this.path = this.$route.path;
        }
    }
</script>

<style>

</style>
