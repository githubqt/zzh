<template>
    <section>
        <yd-navbar class="fixed-header" title="补全账号">
        	<div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <!-- content -->
        <yd-cell-group class="find-info-box" style="margin-top:1rem">
            <yd-cell-item>
                <yd-icon name="phone3" size=".5rem" color="#666666" class="find-cell-item-label" slot="left" />
                <yd-input slot="right" type="tel" v-model="cellPhone" regex="mobile" ref="cellPhone" placeholder="请输入手机号码" />
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
                <yd-input slot="right" type="password" v-model="againPwd" placeholder="请再次输入密码"></yd-input>
            </yd-cell-item>
        </yd-cell-group>
        <div class="find-btn-box">
            <yd-button size="large" type="danger" color="#fff" @click.native="findGo">提交</yd-button>
        </div>
    </section>
</template>
<script>
import Qs from "qs";
import { login, login_state } from "../../../tool/login";
export default {
  name: "find",
  data() {
    return {
      cellPhone: "",
      authCode: "",
      autnCodeText: "",
      phoneCode: "",
      timeCode: 0,
      password: "",
      againPwd: ""
    };
  },
  mounted() {
    this.authCodeGet();
  },
  methods: {
    backGo() {
      this.$router.push("/login");
    },
    authCodeGet() {
      this.authCode =
        "/api/v1/User/imgCode/?identif=" + this.DOMAIN + "&id=" + Math.random();
    },
    getPhoneCode() {
      let _this = this,
        _isVerify = _this.$refs.cellPhone.valid,
        _data = Qs.stringify({ mobile: _this.cellPhone, msg_code: 6 });
      if (_this.cellPhone.length !== 0 && _isVerify) {
        _this.$http
          .post("/api/v1/common/sendSms", _data)
          .then(function(response) {
            if (response.data.errno === "0") {
              _this.timeCode = 60;
              let _countdown = setInterval(function() {
                _this.timeCode === 0
                  ? clearInterval(_countdown)
                  : _this.timeCode--;
              }, 1000);
              _this.$dialog.toast({
                mes: "请查收短信",
                timeout: 1500,
                icon: "success"
              });
            } else {
              _this.$dialog.toast({
                mes: response.data.errmsg,
                timeout: 1500,
                icon: "error"
              });
            }
          })
          .catch(function(error) {
            _this.$dialog.toast({ mes: error, timeout: 1500, icon: "error" });
          });
      } else {
        _this.$dialog.toast({
          mes: "请输入正确的手机号",
          timeout: 1500,
          icon: "error"
        });
      }
    },
    findGo() {
      var state = login_state();
      let _this = this,
        _data = Qs.stringify({
          user_id: state.user_id,
          mobile: _this.cellPhone,
          password: _this.password,
          repassword: _this.againPwd,
          phone_code: _this.phoneCode
        });
      _this.$http
        .post("/api/v1/User/addMobile", _data)
        .then(function(response) {
          if (response.data.errno === "0") {
            login(response.data.result);
            _this.$router.push("/user");
          } else {
            _this.$dialog.toast({
              mes: response.data.errmsg,
              timeout: 1500,
              icon: "error"
            });
          }
        })
        .catch(function(error) {
          _this.$dialog.toast({ mes: error, timeout: 1500, icon: "error" });
        });
    }
  }
};
</script>
<style>
@import "../../assets/css/components/user/addmobile";
</style>
