<template>
    <yd-layout class="login-box">
        <div class="login-logo">
            <div class="title">微商城</div>
            <img src="../../assets/img/header.jpg" alt="">
        </div>
        <div class="login-form">
            <yd-cell-group>
                <yd-cell-item>
                    <yd-icon name="phone3" color="#999" slot="left" />
                    <yd-input slot="right" type="tel" v-model="mobile" regex="mobile" min="11" max="11" required ref="mobile" placeholder="请输入手机号码" />
                </yd-cell-item>
                <yd-cell-item>
                    <yd-icon name="shield-outline" color="#999" slot="left" />
                    <input type="number" v-model="code" placeholder="验证码" ref="code" slot="right" maxlength="4">
                    <yd-sendcode slot="right" v-model="startCount" @click.native="sendCode" type="hollow"></yd-sendcode>
                </yd-cell-item>
            </yd-cell-group>
           
            <div class="login-form-submit">
                <yd-button size="large" type="danger" @click.native="codeLoginGo">登录</yd-button>
            </div>
             <div class="login-agreement">
                <!-- <yd-checkbox v-model="checkBox"> 我同意</yd-checkbox> -->
                <span>点击"登录"表示您已阅读并同意 </span>
                <router-link to="/writer"><span style="color: blue;">《用户注册协议》 </span> </router-link>
            </div>
            <div class="fast-login">
                <yd-flexbox>
                    <yd-flexbox-item>
                        <!-- <yd-button type="primary" size="large" @click.native="wechatLogin" v-show="env=='wechat'">授权登录</yd-button>
                            <yd-button type="primary" size="large" @click.native="miniLogin" v-show="env=='mini'">授权登录</yd-button> -->
                        <div class="other-login text-center" v-show="env=='wechat' || env=='mini'">
                            ---------------- 其他登录方式 ----------------
                        </div>
                        <img src="/static/imgs/basewx.png" alt="" class="auth-logo" @click="wechatLogin" v-show="env=='wechat'">
                        <img src="/static/imgs/basewx.png" alt="" class="auth-logo" @click="miniLogin" v-show="env=='mini'">
                    </yd-flexbox-item>
                </yd-flexbox>
            </div>
        </div>
    </yd-layout>
</template>
<script>
    import Qs from "qs";
    import {
        login,
        logout,
        login_state
    } from "../../../tool/login";
    import {
        preious
    } from '../../../tool/history'
    import {
        Env
    } from "../../mixins/env"
    import {
        Auth
    } from "../../mixins/auth"
    export default {
        name: "AuthLogin",
        mixins: [Env, Auth],
        data() {
            return {
                mobile: "",
                code: "",
                invite_code: "", //邀请码
                startCount: false, //开始倒计时
                checkBox: false,
                isMobileValid: false,
            };
        },
        created() {
            //获取邀请码
            this.invite_code = this.$route.query.invitation_id ? this.$route.query.invitation_id : '';
        },
        watch: {
            mobile(val) {
                // 验证手机号码输入格式
                this.isMobileValid = this.$refs.mobile.valid;
            },
            code(val) {
            }
        },
        methods: {
            // 发送验证码
            async sendCode() {
                let _this = this,
                    _data = Qs.stringify({
                        mobile: _this.mobile,
                        msg_code: 5
                    });
                try {
                    if (_this.mobile.length === 0) {
                        throw '请输入手机号';
                    }
                    if (!_this.isMobileValid) {
                        throw '请输入正确的手机号';
                    }
                    _this.$dialog.loading.open('发送中...');
                    _this.startCount = true;
                    let response = await _this.$http.post("/api/v1/common/sendSms", _data);
                    console.log("​sendCode -> response", response)
                    _this.$dialog.loading.close();
                    if (response.data.errno === "0" || response.data === "") {
                        _this.$dialog.toast({
                            mes: "验证码发送成功",
                            timeout: 1500,
                            icon: "success"
                        });
                    } else {
                        throw response.data.errmsg || '验证码发送失败';
                    }
                } catch (err) {
                    _this.startCount = false;
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: err.toString(),
                        timeout: 1500,
                        icon: "error"
                    });
                }
            },
            codeLoginGo() {
                let _this = this,
                    _data = Qs.stringify({
                        mobile: _this.mobile,
                        code: _this.code,
                        invite_code: _this.invite_code
                    });
                try {
                    if (_this.mobile.length === 0) {
                        throw '请输入手机号';
                    }
                    if (!_this.isMobileValid) {
                        throw '请输入正确的手机号';
                    }
                    if (_this.code.length === 0) {
                        throw '请输入验证码';
                    }
                    if (_this.code.length !== 4) {
                        throw '验证码最多4位';
                    }
                    // if (!_this.checkBox) {
                    //     throw '请您先阅读并同意注册协议';
                    // }
                    _this.$dialog.loading.open('正在登录...');
                    _this.$http
                        .post("/api/v1/user/fastLogin", _data)
                        .then(function(response) {
                            _this.$dialog.loading.close();
                            if (response.data.errno === "0") {
                                _this.$dialog.toast({
                                    mes: '登录成功',
                                    timeout: 2000,
                                    icon: 'success',
                                    callback: () => {
                                        login(response.data.result);
                                        preious() ? window.location.href = preious() : _this.$router.replace("/user");
                                    }
                                });
                            } else {
                                _this.$dialog.toast({
                                    mes: response.data.errmsg || '登录失败',
                                    timeout: 1500,
                                    icon: "error"
                                });
                            }
                        })
                        .catch(function(err) {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: err.toString() || '登录失败',
                                timeout: 1500,
                                icon: "error"
                            });
                        });
                } catch (err) {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: err.toString(),
                        timeout: 1500,
                        icon: "error"
                    });
                }
            },
            wechatLogin() {
                let _this = this;
                window.location.href = `${this.$API}/v1/Weixin/wechatlogin/?identif=${this.DOMAIN}&redirect_url=` +
                    encodeURIComponent(
                        window.location.protocol +
                        "//" +
                        window.location.host +
                        "/mobile/user"
                    );
            },
            miniLogin() {
                this.redirectToMiniProgram();
            },
            redirectToMiniProgram() {
                wx.miniProgram.redirectTo({
                    url: "/pages/auth?apiUrl=" +
                        encodeURIComponent("shopm.zhahehe.com") +
                        "&identif=" +
                        this.DOMAIN +
                        "&refer=" + preious()
                });
            }
        }
    };
</script>
<style>

</style>
