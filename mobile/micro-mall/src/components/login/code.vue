<template>
    <section class="code-container">
        <yd-cell-group class="code-info-box">
            <yd-cell-item>
                <yd-icon name="phone3" size=".5rem" color="#666666" class="code-cell-item-label" slot="left" />
                <yd-input slot="right" type="tel" v-model="cellPhone" ref="cellPhone" placeholder="请输入手机号码" />
            </yd-cell-item>
            <yd-cell-item class="code-cell-item-code">
                <yd-icon name="shield-outline" size=".5rem" color="#666666" class="code-cell-item-label" slot="left" />
                <input type="text" v-model="authCode" placeholder="验证码" slot="left">
                <yd-button bgcolor="#dab461" color="#fff" v-if="timeCode == 0" @click.native="getPhoneCode" slot="right">
                    获取验证码
                </yd-button>
                <yd-button type="disabled" v-else disabled slot="right">{{timeCode}}s后重新获取</yd-button>
            </yd-cell-item>
        </yd-cell-group>
        <div class="code-btn-box">
            <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="codeLoginGo">登录</yd-button>
        </div>
    </section>
</template>
<script>
import Qs from "qs";
import { login, login_state } from "../../../tool/login";
import {preious} from '../../../tool/history'
export default {
  name: "Codelogin",
  data() {
    return {
      cellPhone: "",
      authCode: "",
      timeCode: 0
    };
  },
  methods: {
    getPhoneCode() {
      let _this = this,
        _isVerify = _this.$refs.cellPhone.valid,
        _data = Qs.stringify({
          mobile: _this.cellPhone,
          msg_code: 5
        });
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
            _this.$dialog.toast({
              mes: error,
              timeout: 1500,
              icon: "error"
            });
          });
      } else {
        _this.$dialog.toast({
          mes: "请输入正确的手机号",
          timeout: 1500,
          icon: "error"
        });
      }
    },
    codeLoginGo() {
      let _this = this,
        _data = Qs.stringify({
          mobile: _this.cellPhone,
          phone_code: _this.authCode
        });
      if (_this.cellPhone.length !== 0 && _this.authCode.length !== 0) {
        _this.$http
          .post("/api/v1/user/fastLogin", _data)
          .then(function(response) {
            if (response.data.errno === "0") {
              login(response.data.result);
              preious() ? window.location.href = preious() : _this.$router.push("/user");
            } else {
              _this.$dialog.toast({
                mes: response.data.errmsg,
                timeout: 1500,
                icon: "error"
              });
            }
          })
          .catch(function(error) {
            _this.$dialog.toast({
              mes: error,
              timeout: 1500,
              icon: "error"
            });
          });
      } else {
        _this.$dialog.toast({
          mes: "输入项不可为空",
          timeout: 1500,
          icon: "error"
        });
      }
    }
  }
};
</script>
<style>
</style>
