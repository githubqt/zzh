<template>
    <section class="contact user-contact">

        <!-- header -->
        <yd-navbar title="联系我们" class="fixed-header">
            <div slot="left" @click="backGo" >
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>

        <yd-layout title="" style="padding-top: 1rem;">

            <div class="banner">
                <img v-bind:src="src" alt="">
            </div>

            <div class="title">
                <p>您可以在此留下您的联系方式</p>
                <p>我们的客服会尽快和您联系</p>
            </div>

            <yd-cell-item>
                <yd-input slot="right" type="tel" placeholder="请输入手机号码" v-model="cellPhone" ref="cellPhone" regex="mobile"></yd-input>
            </yd-cell-item>

            <div class="box" v-on:click="submitInfo()">
                <span class="btn-submit" shape="circle">提交</span>
            </div>

            <div class="title2" v-show="mobile || phone">
                <p>您也可以拨打我们的客服电话</p>
                <h3 v-show="phone">{{phone}}</h3>
                <h3 v-show="!phone">{{mobile}}</h3>
            </div>

            <div class="box" v-show="mobile || phone">
                <a class="btn-submit" v-bind:href="telHref" shape="circle">呼叫</a>
            </div>

            <!-- <div class="address">
                <yd-icon name="location"></yd-icon>
                北京市东城区箭场胡同22号1011栋
                <span>导航</span>
            </div> -->

        </yd-layout>

    </section>
</template>

<script>
import Qs from "qs";

export default {
  components: {},
  data() {
    return {
      mobile: "",
      phone: "",
      cellPhone: "",
      telHref: "",
      user_id: "",
      src: "./../../static/imgs/contact.png"
    };
  },
  mounted: function() {
    this.$nextTick(function() {
      this.user_id = localStorage.getItem("userId");
      this.getPhone();
    });
  },
  methods: {
    getPhone: function() {
      var that = this;
      that
        .$http({
          url: "/api/v1/Home/supplier",
          method: "POST"
        })
        .then(function(res) {
          if (res.data.errno == "0") {
            that.mobile = res.data.result.mobile;
            that.phone = res.data.result.phone;
            if (that.phone) {
              that.telHref = "tel:" + that.phone;
            } else {
              that.telHref = "tel:" + that.mobile;
            }
          } else {
            that.$dialog.toast({
              mes: res.data.errmsg,
              timeout: 1500,
              icon: "error"
            });
          }
        })
        .catch(function(err) {
          that.$dialog.toast({ mes: err, timeout: 1500, icon: "error" });
        });
    },
    submitInfo: function() {
      let that = this;
      let isVerify = that.$refs.cellPhone.valid;
      if (!that.cellPhone) {
        that.$dialog.toast({
          mes: "请输入联系方式",
          timeout: 500,
          icon: "error"
        });
        return;
      } else {
        if (!isVerify) {
          that.$dialog.toast({
            mes: "请输入正确的手机号",
            timeout: 500,
            icon: "error"
          });
          return;
        }
      }

      that.$dialog.loading.open("提交中");

      let data = Qs.stringify({
        mobile: that.cellPhone
      });
      //'user_id': that.user_id,

      that
        .$http({
          url: "/api/v1/User/contactUs",
          method: "POST",
          data: data
        })
        .then(function(res) {
          that.$dialog.loading.close();
          if (res.data.errno == "0") {
            that.$dialog.toast({
              mes: "提交成功",
              timeout: 500,
              icon: "success"
            });
            that.$refs.cellPhone.value = "";
          } else if (res.data.errno == "50006") {
            that.$dialog.confirm({
              title: "系统提示",
              mes: "登录状态失效，重新登录？",
              opts: () => {
                that.$router.push("/login");
              }
            });
          } else {
            that.$dialog.toast({
              mes: res.data.errmsg,
              timeout: 1500,
              icon: "error"
            });
          }
        })
        .catch(function(err) {
          that.$dialog.loading.close();
          that.$dialog.alert({ mes: "系统繁忙，请稍后再试" });
        });
    },
    backGo() {
        window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/')
    }
  }
};
</script>

<style scoped>
</style>
