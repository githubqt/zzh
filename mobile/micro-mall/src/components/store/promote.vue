<template>
    <yd-layout class="promote">
        <yd-navbar title="分享" slot="navbar">
            <yd-icon name="error" slot="right" @click.native="backGo" color="#dc2821"></yd-icon>
        </yd-navbar>
        <img v-lazy="url" class="share-img">
        <!-- 底部按钮 -->
        <!--<yd-tabbar class="download-img" fixed>-->
            <!--<yd-flexbox-item>-->
                <!--<yd-button size="large" bgcolor="#fff" @click.native="copy()">-->
                    <!--<img src="/../../static/imgs/copy.png" />-->
                    <!--<div>保存至手机</div>-->
                <!--</yd-button>-->
            <!--</yd-flexbox-item>-->
        <!--</yd-tabbar>-->
    </yd-layout>
</template>

<script>
    import Qs from "qs";
    export default {
        name: 'promote',
        data() {
            return {
                url: '',
            }
        },
        created() {
            this.setProductData();
        },
        methods: {
            backGo() {
                window.history.length > 1
                    ? this.$router.go(-1)
                    : this.$router.push('/')
            },
            copy() {
                //     _data = Qs.stringify({
                //         url: 'http://static.ydcss.com/uploads/lightbox/meizu_s1.jpg',//_this.url,
                //     });
                var alink = document.createElement("a");
                alink.href = this.url;
                alink.download = "pic"; //图片名
                alink.click();
                // _this.$http
                //     .post("/api/v1/Product/copy", _data)
                //     .then(function(response) {
                //         if (response.data.errno == "0") {
                //
                //         console.log(response);
                //
                //         } else {
                //             _this.$dialog.loading.close();
                //         }
                //     })
                //     .catch(function(error) {
                //         _this.$dialog.loading.close();
                //     });
            },
            setProductData() {
                let _this = this,
                    _data = Qs.stringify({
                        id: _this.$route.query.id,
                    });
                _this.$http
                    .post("/api/v1/Product/promote", _data)
                    .then(function(response) {
                        if (response.data.errno == "0") {
                            _this.url = response.data.result;
                            _this.url = _this.url + '?time=' + new Date().getTime();
                            _this.$dialog.toast({
                                mes: '长按保存至手机',
                                timeout: 2500,
                            });
                        } else {
                            _this.$dialog.loading.close();
                        }
                    })
                    .catch(function(error) {
                        _this.$dialog.loading.close();
                    });
            }
        }
    }
</script>

<style scoped>
    .promote .share-img {
        /*position: fixed;*/
        /*top: 2.6rem;*/
        /*display: block;*/
        /*padding-top: 1.6rem;*/
        width: 7.5rem;
        /*transform: rotate(90deg);*/
        /*-ms-transform: rotate(90deg);*/
        /*-moz-transform: rotate(90deg);*/
        /*-webkit-transform: rotate(90deg);*/
        /*-o-transform: rotate(90deg);*/
    }
    .download-img img {
        margin: auto;
        display: block;
        height: 0.5rem;
    }
    .download-img button div {
        color: #6E9EF7;
    }
    .download-img button {
        margin: 0;
    }
</style>
