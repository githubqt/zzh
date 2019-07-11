<template>
    <section class="auctionList-container">
        <yd-navbar class="fixed-header" title="我的拍品">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <yd-tab v-model="auctionStatus" active-color="#dc2821" horizontal-scroll :callback="fn">
            <yd-tab-panel v-for="(item, index) in items" :key="index" :label="item.label"></yd-tab-panel>
            <sty-auctionLoad :auctionStatus="auctionStatus"></sty-auctionLoad>
        </yd-tab>
    </section>
</template>

<script>
    import Qs from 'qs'
    import AuctionLoad from '@/components/user/module/auctionLoad'
    import {
        Auth
    } from "../../mixins/auth"
    export default {
        name: 'AuctionList',
        mixins:[Auth],
        components: {
            'sty-auctionLoad': AuctionLoad
        },
        data() {
            return {
                auctionStatus: 10,
                items: [{
                        label: '全部拍品'
                    },
                    {
                        label: '已参拍'
                    },
                    {
                        label: '已获拍'
                    },
                    {
                        label: '未获拍'
                    },
                ]
            }
        },
        created() {
            this.auctionStatus = parseInt(this.$route.query.auctionStatus) || 0;
        },
        methods: {
            backGo() {
                window.history.length > 1 ?
                    this.$router.go(-1) :
                    this.$router.push('/')
            },
            fn(label, key) {
                this.auctionStatus = key
            }
        }
    }
</script>

<style>

</style>
