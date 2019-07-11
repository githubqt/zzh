<template>
    <section class="register-container">
        <!-- header -->
        <yd-navbar fontsize=".3rem"></yd-navbar>
        <!-- portrait -->
        <img src="../../assets/img/header.jpg" alt="" class="login-default-avatar">
        <!-- content -->
        <yd-cell-group class="register-info-box">
            <yd-cell-item>
                <yd-icon name="phone3" size=".5rem" color="#666666" class="register-cell-item-label" slot="left" />
                <yd-input slot="right" type="tel" v-model="cellPhone" regex="mobile" ref="cellPhone" placeholder="请输入手机号码" />
            </yd-cell-item>
            <yd-cell-item class="register-cell-item-code">
                <yd-icon name="verifycode" size=".5rem" color="#666666" class="register-cell-item-label" slot="left" />
                <input type="text" v-model="phoneCode" placeholder="验证码" slot="left">
                <yd-button type="danger" color="#fff" v-if="timeCode == 0" @click.native="getPhoneCode" slot="right">
                    获取验证码
                </yd-button>
                <yd-button type="disabled" v-else disabled slot="right">{{timeCode}}s后重新获取</yd-button>
            </yd-cell-item>
            <yd-cell-item>
                 <yd-icon name="shield-outline" size=".5rem" color="#666666" class="register-cell-item-label" slot="left" />
                <yd-input slot="right" readonly :show-clear-icon="false" v-model="invitation_id" type="text"  placeholder="邀请码"></yd-input>
            </yd-cell-item>
            <yd-cell-item>
                <div class="register-cell-item-label" slot="left">
                    <span class="miconfont micon-pwd"></span>
                </div>
                <yd-input slot="right" type="password" v-model="password" placeholder="请输入密码"></yd-input>
            </yd-cell-item>
            <yd-cell-item>
                <div class="register-cell-item-label" slot="left">
                    <span class="miconfont micon-pwd"></span>
                </div>
                <yd-input slot="right" type="password" v-model="againPwd" placeholder="请输入密码"></yd-input>
            </yd-cell-item>
        </yd-cell-group>
        <div class="register-btn-box">
            <yd-button size="large" type="danger" color="#fff" @click.native="registerGo">完成注册</yd-button>
        </div>
    </section>
</template>
<script>
import Qs from 'qs'
export default {
    name: 'Register',
    data() {
        return { 
	        cellPhone: '',
	        invitation_id: '', 
	        phoneCode: '', 
	        timeCode: 0, 
	        password: '', 
	        againPwd: ''
        }
    },
    created() { 
    	this.getcode() 
    },
    mounted() {
       // this.authCodeGet();
    },
    methods: {
    	getcode() {
    		let _this = this; 
    		let user_id = localStorage.getItem('userId');
    		if (user_id) {
    			_this.getencode();
    		}
    	},
        getencode() {
        	let _this = this;
            let user_id = localStorage.getItem('userId');
            //传到后台进行编码
            _this.$http.post('/api/v1/common/xencode', Qs.stringify({ code: user_id})).then(function(response) {
                if (response.data.errno === '0') {
                	_this.invitation_id = response.data.result.code;         
                } else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        },
        authCodeGet() {
            this.authCode = '/api/v1/User/imgCode/?identif=' + this.DOMAIN + '&id=' + Math.random();
        },
        getPhoneCode() {
            let _this = this,
                _isVerify = _this.$refs.cellPhone.valid,
                _data = Qs.stringify({ mobile: _this.cellPhone, msg_code: 2 });
            if (_this.cellPhone.length !== 0 && _isVerify) {
                _this.$http.post('/api/v1/common/sendSms', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.timeCode = 60;
                        let _countdown = setInterval(function() {
                            _this.timeCode === 0 ? clearInterval(_countdown) : _this.timeCode--;
                        }, 1000);
                        _this.$dialog.toast({ mes: '请查收短信', timeout: 1500, icon: 'success' });
                    } else {
                        _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                    }
                }).catch(function(error) {
                    _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
                });
            } else {
                _this.$dialog.toast({ mes: '请输入正确的手机号', timeout: 1500, icon: 'error' });
            }
        },
        registerGo() {
            let _this = this,
                _data = Qs.stringify({
                    mobile: _this.cellPhone,
                    password: _this.password,
                    repassword: _this.againPwd,
                    msg_code: _this.autnCodeText,
                    phone_code: _this.phoneCode,
                    invitation_id:_this.invitation_id,
                    type:'help'
                });
            _this.$http.post('/api/v1/User/reg', _data).then(function(response) {
                if (response.data.errno === '0') {
                    _this.$router.push('/user?u_id='+response.data.result.invitation_id);
                } else {
                    _this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                _this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });
        }
    }
}

</script>
<style>
</style>
