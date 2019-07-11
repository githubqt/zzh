<template>
    <section class="orderlist-container">
        <yd-navbar class="fixed-header" title="我的订单">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <yd-tab v-model="orderStatus" active-color="#dc2821" horizontal-scroll :callback="fn">
            <yd-tab-panel v-for="(item, index) in items" :key="index" :label="item.label"></yd-tab-panel>
            <sty-orderload :orderStatus="orderStatus"></sty-orderload>
        </yd-tab>
    </section>
</template>

<script>
    import Qs from 'qs'
    import Orderload from '@/components/user/module/orderLoad'
    import {
        Auth
    } from "../../mixins/auth"
    export default {
        name: 'Orderlist',
        mixins:[Auth],
        components: {
            'sty-orderload': Orderload
        },
        data() {
            return {
                orderStatus: 0,
                items: [{
                        label: '全部'
                    },
                    {
                        label: '待付款'
                    },
                    {
                        label: '待成团'
                    },
                    {
                        label: '待发货'
                    },
                    {
                        label: '待收货'
                    },
                    {
                        label: '已完成'
                    },
                    {
                        label: '已取消'
                    },
                ]
            }
        },
        created() {
            this.orderStatus = parseInt(this.$route.query.orderStatus) || 0
        },
        methods: {
            backGo() {
                window.history.length > 1 ?
                    this.$router.go(-1) :
                    this.$router.push('/')
            },
            fn(label, key) {
                this.orderStatus = key
            }
        }
    }
</script>

<style>

</style>
