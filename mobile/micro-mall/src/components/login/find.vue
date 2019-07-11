<template>
    <section class="find-container">
        <!-- header -->
        <yd-navbar fontsize=".3rem"></yd-navbar>
        <!-- portrait -->
        <img src="../../assets/img/header.jpg" alt="" class="login-default-avatar">
        <!-- content -->
        <yd-cell-group class="find-info-box">
            <yd-cell-item>
                <yd-icon name="phone3" size=".5rem" color="#666666" class="find-cell-item-label" slot="left" />
                <yd-input slot="right" type="tel" v-model="cellPhone" regex="mobile" ref="cellPhone" placeholder="请输入手机号码" />
            </yd-cell-item>
            <yd-cell-item class="find-cell-item-code">
                <yd-icon name="shield-outline" size=".5rem" color="#666666" class="find-cell-item-label" slot="left" />
                <input type="text" v-model="autnCodeText" placeholder="请输入验证码" slot="left">
                <img :src="authCode" alt="" class="auth-code" slot="right" @click="authCodeGet">
            </yd-cell-item>
            <yd-cell-item class="find-cell-item-code">
                <yd-icon name="verifycode" size=".5rem" color="#666666" class="find-cell-item-label" slot="left" />
                <input type="text" v-model="phoneCode" placeholder="验证码" slot="left">
                <yd-button bgcolor="#dab461" color="#fff" v-if="timeCode == 0" @click.native="getPhoneCode" slot="right">
                    获取验证码
                </yd-button>
                <yd-button type="disabled" v-else disabled slot="right">{{timeCode}}s后重新获取</yd-button>
            </yd-cell-item>
            <yd-cell-item>
                <div class="find-cell-item-label" slot="left">
                    <span class="miconfont micon-pwd"></span>
                </div>
                <yd-input slot="right" type="password" v-model="password" placeholder="请输入密码"></yd-input>
            </yd-cell-item>
            <yd-cell-item>
                <div class="find-cell-item-label" slot="left">
                    <span class="miconfont micon-pwd"></span>
                </div>
                <yd-input slot="right" type="password" v-model="againPwd" placeholder="请输入密码"></yd-input>
            </yd-cell-item>
        </yd-cell-group>
        <div class="find-btn-box">
            <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="findGo">重置</yd-button>
            <router-link class="find-to-celerity" to="/login">已有账号</router-link>
        </div>
    </section>
</template>
<script>
    import Qs from 'qs'
    export default {
        name: 'find',
        data() {
            return {
                cellPhone: '',
                authCode: '',
                autnCodeText: '',
                phoneCode: '',
                timeCode: 0,
                password: '',
                againPwd: ''
            }
        },
        mounted() {
            this.authCodeGet();
        },
        methods: {
            authCodeGet() {
                this.authCode = '/api/v1/User/imgCode/?identif=' + this.DOMAIN + '&id=' + Math.random();
            },
            getPhoneCode() {
                let _this = this,
                    _isVerify = _this.$refs.cellPhone.valid,
                    _data = Qs.stringify({
                        mobile: _this.cellPhone,
                        msg_code: 3
                    });
                if (_this.cellPhone.length !== 0 && _isVerify) {
                    _this.$http.post('/api/v1/common/sendSms', _data).then(function(response) {
                        if (response.data.errno === '0') {
                            _this.timeCode = 60;
                            let _countdown = setInterval(function() {
                                _this.timeCode === 0 ? clearInterval(_countdown) : _this.timeCode--;
                            }, 1000);
                            _this.$dialog.toast({
                                mes: '请查收短信',
                                timeout: 1500,
                                icon: 'success'
                            });
                        } else {
                            _this.$dialog.toast({
                                mes: response.data.errmsg,
                                timeout: 1500,
                                icon: 'error'
                            });
                        }
                    }).catch(function(error) {
                        _this.$dialog.toast({
                            mes: error,
                            timeout: 1500,
                            icon: 'error'
                        });
                    });
                } else {
                    _this.$dialog.toast({
                        mes: '请输入正确的手机号',
                        timeout: 1500,
                        icon: 'error'
                    });
                }
            },
            findGo() {
                let _this = this,
                    _data = Qs.stringify({
                        mobile: _this.cellPhone,
                        password: _this.password,
                        repassword: _this.againPwd,
                        msg_code: _this.autnCodeText,
                        phone_code: _this.phoneCode,
                    });
                _this.$http.post('/api/v1/User/resetPassword', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.$router.push('/login');
                    } else {
                        _this.$dialog.toast({
                            mes: response.data.errmsg,
                            timeout: 1500,
                            icon: 'error'
                        });
                    }
                }).catch(function(error) {
                    _this.$dialog.toast({
                        mes: error,
                        timeout: 1500,
                        icon: 'error'
                    });
                });
            }
        }
    }
</script>
<style>
</style>
