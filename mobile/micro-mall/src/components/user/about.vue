<template>
    <section class="contact">

        <!-- header -->
        <yd-navbar title="关于我们" class="fixed-header">
            <div slot="left"  @click="backGo" >
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>

        <div class="artical" style="padding-top: 1.4rem;">
            <div class="part" v-if="intro">
              <h3>公司介绍</h3>
              <div v-html="intro"></div>
            </div>
            <div class="part"  v-if="honor">
              <h3>公司荣誉</h3>
              <div v-html="honor"></div>
            </div>
        </div>

    </section>
</template>

<script>
import Qs from 'qs';

export default {
    components: {

    },
    data() {
        return {
            name: '',
            intro: '',
            honor: ''
        }
    },
    mounted: function () {
        this.$nextTick(function () {
            this.getArtical()
        })
    },
    methods: {
        getArtical: function () {
            var that = this
            that.$http({
                url: '/api/v1/Home/supplier',
                method: 'POST'
            }).then(function (res) {
                if (res.data.errno == '0') {
                    that.name = res.data.result.company
                    that.intro = res.data.result.company_introduction
                    that.honor = res.data.result.company_honors
                } else {
                    that.$dialog.toast({ mes: res.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch (function (err) {
                that.$dialog.toast({ mes: err, timeout: 1500, icon: 'error' });
            })
        },
        backGo() {
            window.history.length > 1
                ? this.$router.go(-1)
                : this.$router.push('/')
        }
    }
}
</script>

<style >

@import "../../assets/css/components/user/about";

</style>
