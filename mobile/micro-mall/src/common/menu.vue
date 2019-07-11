<template>
    <yd-tabbar fixed active-color="#dc2821" ref="footerBar" active-class="active">
        <yd-tabbar-item title="首页" link="/" :active="path=='/'">
            <span class="miconfont micon-home" slot="icon"></span>
        </yd-tabbar-item>
        <yd-tabbar-item title="品类" link="/brand" :active="path=='/brand'">
            <span class="miconfont micon-brand" slot="icon"></span>
        </yd-tabbar-item>
        <yd-tabbar-item title="商城" link="/store" :active="path=='/store'">
            <span class="miconfont micon-shop" slot="icon"></span>
        </yd-tabbar-item>
        <!-- <yd-tabbar-item title="售卖" link="/pawn" :active="path=='/pawn'">
            <span class="miconfont micon-pawn" slot="icon"></span>
        </yd-tabbar-item> -->
        <yd-tabbar-item title="我的" link="/user" :active="path=='/user'">
            <span class="miconfont micon-user" slot="icon"></span>
        </yd-tabbar-item>
    </yd-tabbar>
</template>

<script>
    import Qs from 'qs'
    export default {
        name: 'Menu',
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
