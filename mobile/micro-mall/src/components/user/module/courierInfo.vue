<template>
    <section class="courierInfo-container">
        <yd-navbar title="物流信息">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <yd-cell-group v-show="isShow">
            <yd-cell-item>
                <span slot="left">快递公司</span>
                <span slot="right">{{courierData.express_name}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">快递单号</span>
                <span slot="right">{{courierData.express_no}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">当前状态</span>
                <span slot="right" v-if="courierData.state==='0'">快件运输中</span>
                <span slot="right" v-else-if="courierData.state==='1'">快递公司已揽件</span>
                <span slot="right" v-else-if="courierData.state==='2'">快件信息有误</span>
                <span slot="right" v-else-if="courierData.state==='3'">已签收</span>
                <span slot="right" v-else-if="courierData.state==='4'">快件退回发货人并签收</span>
                <span slot="right" v-else-if="courierData.state==='5'">正在派件</span>
                <span slot="right" v-else-if="courierData.state==='6'">货物正在返回发货人途中</span>
            </yd-cell-item>
        </yd-cell-group>
        <yd-timeline>
            <yd-timeline-item v-for="(item, index) in courierData.data" :key="index">
                <p>{{item.context}}</p>
                <p style="margin-top: 10px;">{{item.ftime}}</p>
            </yd-timeline-item>
        </yd-timeline>
    </section>
</template>
<script>
import Qs from 'qs'
import Orderload from '@/components/user/module/orderLoad'
export default {
    name: 'OrderList',
    components: { 'sty-orderload': Orderload },
    data() {
        return { isShow:false, courierData: {} }
    },
    created() { this.orderDetailsGet() },
    methods: {
        backGo() { window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/') },
        orderDetailsGet() {
            let _this = this,
                _data = Qs.stringify({ id: _this.$route.query.id });

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.post('/api/v1/Freight/express', _data).then(function (response) {
                if (response.data.errno === '0') {
                    if (response.data.result.data) {
                        _this.isShow = true;
                        _this.courierData = response.data.result;
                    }else {
                        _this.$dialog.toast({ mes: response.data.result.message, timeout: 1500 });
                    }
                    _this.$nextTick(function() { _this.$dialog.loading.close() });
                }else {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function (error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        }
    }
}

</script>
<style>
@import "../../../assets/css/components/user/module/courierinfo";
</style>
