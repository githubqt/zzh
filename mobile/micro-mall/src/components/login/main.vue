<template>
    <section class="login-container">
        <!-- header -->
        <yd-navbar fontsize=".3rem" bgcolor="#dab461">
            <router-link to="/user" slot="right">
                <yd-icon name="error" size="25px" color="#fff"></yd-icon>
            </router-link>
        </yd-navbar>
        <!-- tab -->
        <yd-tab v-model="tabIndex" class="login-tab-box" :class="{'active_01':activeTab,'active_02':!activeTab}" :callback="toggleTab">
            <yd-tab-panel label="验证码快速登录">
                <!-- portrait -->
                <img src="../../assets/img/header.jpg" alt="" class="login-default-avatar">
                <phoneLogin></phoneLogin>
            </yd-tab-panel>
            <yd-tab-panel label="账号密码登录">
                <!-- portrait -->
                <img src="../../assets/img/header.jpg" alt="" class="login-default-avatar">
                <userLogin></userLogin>
            </yd-tab-panel>
        </yd-tab>
    </section>
</template>

<script>
    import Enter from "@/components/login/enter";
    import Codelogin from "@/components/login/code";
    import {
        login,
        logout,
        login_state
    } from "../../../tool/login";
    export default {
        name: "Login",
        components: {
            userLogin: Enter,
            phoneLogin: Codelogin
        },
        data() {
            return {
                activeTab: this.$route.query.code == "code" ? 0 : 1,
                tabIndex: this.$route.query.code == "code" ? 1 : 0
            };
        },
        created() {
            this.isLogin();
        },
        methods: {
            toggleTab(label, key) {
                this.activeTab = !this.activeTab;
            },
            isLogin() {
                let _this = this;
                _this.$http.post('/api/v1/User/isLogin').then(function(response) {
                    if (parseInt(response.data.errno) === 0) {
                        _this.$router.replace('/user')
                    } else {
                        logout();
                    }
                }).catch(function(error) {
                    logout();
                    _this.$dialog.toast({
                        mes: error,
                        timeout: 1500,
                        icon: 'error'
                    });
                });
            },
        }
    };
</script>

<style>
</style>
