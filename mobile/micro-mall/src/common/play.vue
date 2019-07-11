<template>
    <yd-layout>
        <yd-navbar>
            <router-link :to="backurl" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
        </yd-navbar>
        <video :src="videourl" controls="controls" width="100%" autoplay></video>
    </yd-layout>
</template>
<script>
    export default {
        name: "Play",
        data() {
            return {
                videourl: '',
                backurl: ''
            }
        },
        created() {
            this.videourl = this.$route.query.url;
            this.backurl = this.$route.query.path
        }
    }
</script>
