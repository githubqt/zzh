<template>
    <yd-flexbox class="home-notice">
        <div class="notice-title">
            <div>售卖</div>
            <div>头条</div>
        </div>
        <yd-flexbox-item class="notice-content">
            <yd-flexbox>
                <div class="newest">最新</div>
                <yd-rollnotice autoplay="3000" height="20">
                    <template v-for="(item,index) in newestList">
                        <yd-rollnotice-item :key="index" class="text-ellipsis">{{item.name}}</yd-rollnotice-item>
                    </template>
                </yd-rollnotice>
            </yd-flexbox>
            <yd-flexbox>
                <div class="hotest">最热</div>
                <yd-rollnotice autoplay="3000" height="20">
                    <template v-for="(item,index) in hotestList">
                        <yd-rollnotice-item :key="index" class="text-ellipsis">
                            {{item.name}}</yd-rollnotice-item>
                    </template>
                </yd-rollnotice>
            </yd-flexbox>
        </yd-flexbox-item>
    </yd-flexbox>
</template>

<script>
    export default {
        name: 'Notice',
        props: ['newest', 'hotest'],
        data() {
            return {
                newestList: this.newest || [],
                hotestList: this.hotest || [],
                errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"'
            }
        },
        watch: {
            /** 最新 */
            newest(val, oldVal) {
                this.newestList = val
            },
            /** 最热 */
            hotest(val, oldVal) {
                this.hotestList = val
            }
        }
    }
</script>

<style>
    @import "../../../assets/css/components/home/module/notice";
</style>


