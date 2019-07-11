<template>
    <section class="machineLog-container">
        <yd-navbar title="设备信息"></yd-navbar>
        <yd-cell-group>
            <yd-cell-item>
                <span slot="left">设备名称</span>
                <span slot="right">{{machineData.machine_name}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">设备编码</span>
                <span slot="right">{{machineData.machine_self_code}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">自定义码</span>
                <span slot="right">{{machineData.machine_custom_code}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">备注</span>
                <span slot="right">{{machineData.machine_note}}</span>
            </yd-cell-item>
        </yd-cell-group>
        <yd-timeline>
            <yd-timeline-item v-for="(item, index) in machineData.log" :key="index">
                <p>{{item.admin_name}}</p>
                <p style="margin-top: 10px;">{{item.created_at}}</p>
                <p style="margin-top: 10px;">{{item.note}}</p>
            </yd-timeline-item>
        </yd-timeline>
    </section>
</template>
<script>
import Qs from 'qs'
export default {
    name: 'MachineLog',
    components: {},
    data() {
        return { machineData: {} }
    },
    created() { this.machineDetailsGet() },
    methods: {
        machineDetailsGet() {
            let _this = this,
                _data = Qs.stringify({ identif:'test', self_code: _this.$route.query.self_code });

            _this.$dialog.loading.open('很快加载好了');
            _this.$http.post('/api/v1/Machine/log', _data).then(function (response) {
                if (response.data.errno === '0') {
                    _this.machineData = response.data.result;
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
@import "../../assets/css/components/machine/log";
</style>
