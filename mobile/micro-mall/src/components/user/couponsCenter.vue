<template>
    <section>
        <!-- header -->
        <yd-navbar title="领券中心" class="fixed-header">
            <div slot="left" v-on:click="goHistory">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>

        <coupon style="padding-top: 1.2rem;"></coupon>
    </section>
</template>

<script>
    import coupon from "./module/couponCenter";
    import {login, login_state} from "../../../tool/login";

    export default {
        components: {
            coupon
        },
        data() {
            return {
                user_id: ""
            };
        },
        created() {
            let _cookie_user_id = this.$route.query.user_id;
            let _cookie_token = this.$route.query.token;

            if (_cookie_user_id) {
                login({
                    user_id: _cookie_user_id,
                    token: _cookie_token
                });
            }

            let _this = this,
                ua = window.navigator.userAgent.toLowerCase();
            let user_id = localStorage.getItem("userId");
            if (window.__wxjs_environment == "miniprogram") {
            } else if (ua.match(/MicroMessenger/i) == "micromessenger" && !user_id) {
                _this.wechatLogin();
            }
        },
        mounted: function () {
            /*let _this = this;
                _this.$nextTick(function () {
                    let user_id = localStorage.getItem("userId");
                    if (!user_id) {
                        _this.$router.push('/login');
                    }
                })*/
        },
        methods: {
            goHistory: function () {
                window.history.go(-1);
            },
            wechatLogin() {
                let _this = this;
                window.location.href =
                    _this.$API +
                    "/v1/Weixin/wechatlogin/?identif=" +
                    this.DOMAIN +
                    "&redirect_url=" +
                    encodeURIComponent(
                        window.location.protocol +
                        "//" +
                        window.location.host +
                        "/couponsCenter"
                    );
            }
        }
    };
</script>

<style scoped>

</style>
