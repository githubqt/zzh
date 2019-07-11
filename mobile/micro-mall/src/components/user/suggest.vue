<template>
    <yd-layout class="suggest">
        <!-- header -->
        <yd-navbar title="投诉建议" slot="navbar">
            <router-link slot="left" to="/user">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
            <span style="font-size: .35rem; color:#EE9495" slot="right" v-on:click="showSuggest()">我要投诉</span>
        </yd-navbar>
        <yd-tab active-color="#dab461">
            <sty-Suggest :SuggestStatus="SuggestStatus"></sty-Suggest>
        </yd-tab>
        <!-- 数据全部加载完毕显示 -->
        <span slot="doneTip">~~没有数据啦~~</span>
    </yd-layout>
</template>

<script>
    import Suggest from '@/components/user/module/suggestList'
    import Qs from 'qs'
    export default {
        name: 'suggest',
        components: {
            'sty-Suggest': Suggest
        },
        data() {
            return {
                SuggestStatus: 0,
            }
        },
        created() {},
        methods: {
            showSuggest() {
                this.$router.push("/suggestions");
            }
        }
    }
</script>

<style scoped>

</style>
