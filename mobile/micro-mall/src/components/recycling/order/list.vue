<template>
    <yd-layout title="我的订单" link="/recycling" class="recycling-order-container">
        <yd-tab horizontal-scroll v-model="tabIndex" :item-click="itemClick" :prevent-default="false">
            <yd-tab-panel v-for="(item,i) in items" :label="item.label" :key="i"></yd-tab-panel>
        </yd-tab>
        <recycling-order-list :status="status" :tabindex="tabIndex"></recycling-order-list>
        <recycling-menu></recycling-menu>
    </yd-layout>
</template>
<script>
    import RecyclingMenu from '@/components/recycling/common/recyclingMenu';
    import RecyclingOrderComponent from '@/components/recycling/common/orderList';
    export default {
        name: "RecyclingOrderList",
        data() {
            return {
                tabIndex: 0,
                status: [],
                items: [{
                        label: '全部',
                        status: '0'
                    },
                    {
                        label: '待审核',
                        status: '10'
                    },
                    {
                        label: '审核拒绝',
                        status: '15'
                    },
                    {
                        label: '估价中',
                        status: '20'
                    },
                    {
                        label: '已估价',
                        status: '30'
                    },
                    {
                        label: '无人估价',
                        status: '40'
                    },
                    {
                        label: '回收中',
                        status: '50'
                    },
                    {
                        label: '已回收',
                        status: '60'
                    },
                    {
                        label: '已售出',
                        status: '70'
                    },
                    {
                        label: '取消',
                        status: '80'
                    }
                ]
            };
        },
        components: {
            "recycling-menu": RecyclingMenu,
            "recycling-order-list": RecyclingOrderComponent,
        },
        created() {},
        mounted() {
            this.status = this.$route.query.status || 0;
            this.tabIndex = parseInt(this.$route.query.tabindex) || 0;
        },
        methods: {
            itemClick(key) {
                this.tabIndex = key;
                this.status = this.items[key].status;
            }
        }
    }
</script>

