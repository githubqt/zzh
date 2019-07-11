<template>
    <yd-layout class="register-container" style="padding-top:1.5rem;background:#f5f5f5">
        <div style="padding:0.2rem;text-align:center;font-size:0.6rem;margin-bottom:1.5rem;color:#dc2821">
            <div>回收</div>
            <div style="font-size:0.3rem;padding:0.3rem 0;">随时随地 触手可及</div>
        </div>
        <!-- content -->
        <yd-cell-group class="register-info-box">
            <yd-cell-item>
                <yd-icon name="home" size=".5rem" color="#666666" class="register-cell-item-label" slot="left" />
                <yd-input slot="right" required type="number" v-model="supplierId" ref="supplierId" placeholder="请输入商户ID" />
            </yd-cell-item>
            <yd-cell-item>
                <yd-icon name="ucenter" size=".5rem" color="#666666" class="register-cell-item-label" slot="left" />
                <yd-input slot="right" required type="text" v-model="username" ref="username" placeholder="请输入商户名" />
            </yd-cell-item>
            <yd-cell-item>
                <div class="register-cell-item-label" slot="left">
                    <span class="miconfont micon-pwd"></span>
                </div>
                <yd-input slot="right" type="password" v-model="password" placeholder="请输入密码"></yd-input>
            </yd-cell-item>
        </yd-cell-group>
        <div class="register-btn-box">
            <yd-button size="large" type="danger" @click.native="handleLogin">登录</yd-button>
        </div>
    </yd-layout>
</template>
<script>
    import Qs from 'qs'
    import {
        adminLogin,
        adminLogout,
        getAdminState
    } from "../../../../tool/login";
    export default {
        name: 'RecyclingLogin',
        data() {
            return {
                supplierId: '',
                username: '',
                password: '',
                loginState: {},
            }
        },
        created() {
            this.loginState = getAdminState();
            if (this.loginState.token) {
                this.$router.replace('/recycling');
            } 
        },
        mounted() {},
        methods: {
            handleLogin() {
                let _this = this,
                    _data = Qs.stringify({
                        supplier_id: _this.supplierId,
                        username: _this.username,
                        password: _this.password,
                    });
                _this.$dialog.loading.open('正在登录...');
                _this.$http.post('/api/v1/Recycling/login', _data).then(function(response) {
                    let d = response.data;
                    _this.$dialog.loading.close();
                    if (parseInt(d.errno) === 0) {
                        _this.$dialog.toast({
                            mes: '登录成功',
                            timeout: 2000,
                            icon: 'success',
                            callback: () => {
                                adminLogin(d.result);
                                _this.$router.replace('/recycling');
                            }
                        });
                    } else {
                        throw d.errmsg || '登录失败，请稍后重试';
                    }
                }).catch(function(err) {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: err.toString(),
                        timeout: 2000,
                        icon: 'error'
                    });
                });
            }
        }
    }
</script>
<style>

</style>
