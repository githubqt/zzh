<template>
  <section class="enter-container">
    <yd-cell-group class="enter-info-box">
      <yd-cell-item>
        <yd-icon name="phone3" size=".5rem" color="#666666" class="enter-cell-item-label" slot="left" />
        <yd-input slot="right" type="tel" v-model="cellPhone" ref="cellPhone" placeholder="请输入手机号码" />
      </yd-cell-item>
      <yd-cell-item>
        <div class="enter-cell-item-label" slot="left">
          <span class="miconfont micon-pwd"></span>
        </div>
        <yd-input slot="right" type="password" v-model="password" placeholder="请输入密码"></yd-input>
      </yd-cell-item>
      <yd-cell-item class="enter-cell-item-code">
        <yd-icon name="shield-outline" size=".5rem" color="#666666" class="enter-cell-item-label" slot="left" />
        <input type="text" v-model="autnCodeText" placeholder="请输入验证码" slot="left">
        <img :src="autnCodeImg" alt="" class="auth-code" slot="right" @click="authCodeGet">
      </yd-cell-item>
    </yd-cell-group>
    <div class="enter-btn-box">
      <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="loginGo">登录</yd-button>
      <yd-button size="large" type="hollow" style="border:1px solid green;background-color:#fff;color:green" @click.native="wechatLogin" v-show="wxShow">微信授权登录</yd-button>
      <yd-button size="large" type="hollow" style="border:1px solid green;background-color:#fff;color:green" @click.native="miniLogin" v-show="miniShow">其他登录方式</yd-button>
      <ul class="selector-shortcut">
        <li>
          <router-link to="/register">注册账号</router-link>
        </li>
        <li>
          <router-link to="/find">忘记密码</router-link>
        </li>
      </ul>
    </div>
  </section>
</template>

<script>
  import Qs from "qs";
  import {
    login,
    login_state
  } from "../../../tool/login";
  import {
    preious
  } from '../../../tool/history'
  export default {
    name: "Login",
    data() {
      return {
        cellPhone: "",
        password: "",
        autnCodeText: "",
        autnCodeImg: "",
        wxShow: false,
        miniShow: false
      };
    },
    created() {
      if (window.__wxjs_environment == "miniprogram" && !this.$route.query.from) {
        this.redirectToMiniProgram();
      }
    },
    mounted() {
      let _this = this,
        ua = window.navigator.userAgent.toLowerCase();
      if (window.__wxjs_environment == "miniprogram") {
        _this.miniShow = true;
      } else if (ua.match(/MicroMessenger/i) == "micromessenger") {
        _this.wxShow = true;
      } else {
        _this.wxShow = true;
        _this.miniShow = true;
      }
      _this.authCodeGet();
    },
    methods: {
      authCodeGet() {
        this.autnCodeImg =
          "/api/v1/User/imgCode/?identif=" + this.DOMAIN + "&id=" + Math.random();
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
      },
      loginGo() {
        let _this = this,
          _data = Qs.stringify({
            mobile: _this.cellPhone,
            password: _this.password,
            msg_code: _this.autnCodeText
          });
        if (
          _this.cellPhone.length !== 0 &&
          _this.password.length !== 0 &&
          _this.autnCodeText.length !== 0
        ) {
          _this.$http
            .post("/api/v1/User/login", _data)
            .then(function(response) {
              if (response.data.errno === "0") {
                login(response.data.result);
                // _this.$store.commit('setUserId', response.data.result.user_id);
                var url = preious(false, true);
                if (url) {
                  window.location.href = url;
                } else {
                  _this.$router.replace("/user")
                }
              } else {
                _this.$dialog.toast({
                  mes: response.data.errmsg,
                  timeout: 1500,
                  icon: "error"
                });
              }
            })
            .catch(function(error) {
              console.log("loginGo -> error", error);
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
