<template>
    <section class="orderinfo-container">
        <yd-navbar class="fixed-header" title="订单">
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <yd-cell-group class="m-t-1">
            <!-- <yd-cell-item arrow @click.native="deliveryShow=true">
                <span slot="left">快递方式：</span>
                <span slot="right">{{deliveryType}}</span>
            </yd-cell-item> -->
            <yd-cell-item arrow >
                <span slot="left">取货方式：</span>
                <yd-radio-group v-model="adress_type" slot="left">
                    <yd-radio val="0">快递</yd-radio>
                    <yd-radio val="1">门店自提</yd-radio>
                </yd-radio-group>
            </yd-cell-item>
            <yd-cell-item arrow v-show="deliveryNum!==1" @click.native="siteShow=true">
                <span slot="left">配送地址：</span>
                <div slot="right" class="orderinfo-delivery-site">
                    <span style="padding-top: .12rem;">{{address_phone}}</span>
                    <span style="max-width: 4rem; padding-bottom: .12rem; text-align: justify;">{{address_name}}</span>
                </div>
            </yd-cell-item>
        </yd-cell-group>
        <yd-preview :buttons="btns" class="m-b-_24" v-for="(item, index) in productList" :key="index">
            <yd-preview-header>
                <div class="orderinfo-product-detail-box" slot="left">
                    <img :src="item.logo_url" :onerror="errorImg">
                </div>
                <div slot="right" class="orderinfo-product-detail-title">
                    <div style="white-space: normal;">{{item.name}}</div>
                    <div class="orderinfo-product-detail-price">
                        <div class="market-price">
                            <span>公价:{{item.market_price}}</span>
                        </div>
                        <div class="orderinfo-product-header-price">
                            <span class="emphasis"><em>￥</em>{{item.sale_price}}</span>
                            <span class="emphasis-right"><em>×</em>{{item.num}}</span>
                        </div>
                    </div>
                </div>
            </yd-preview-header>
        </yd-preview>
        <yd-cell-group>
            <yd-cell-item arrow @click.native="seckillShow=true">
                <span slot="left">优惠券：</span>
                <span slot="right" class="orderinfo-delivery-site">{{coupan_name}}</span>
            </yd-cell-item>
        </yd-cell-group>
        <yd-cell-group>
            <yd-cell-item>
                <span slot="left">运费：</span>
                <span slot="right" class="orderinfo-delivery-site">¥ {{carriage}}</span>
            </yd-cell-item>
            <yd-cell-item >
                <span slot="left">原始金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{order_original_amount}}</span>
            </yd-cell-item>
            <yd-cell-item >
                <span slot="left">优惠金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{order_discount_amount}}</span>
            </yd-cell-item>
            <yd-cell-item>
                <span slot="left">实际金额：</span>
                <span slot="right" class="order-delivery-site">¥ {{resultMoney}}</span>
            </yd-cell-item>
        </yd-cell-group>
        <!-- 底部按钮 -->
        <yd-flexbox class="orderinfo-submit-btn">
            <yd-flexbox-item class="cart-button-right">
                <yd-button size="large" type="danger" color="#fff" @click.native="orderinfoSubmit">确认订单</yd-button>
            </yd-flexbox-item>
        </yd-flexbox>
        <!-- 选择快递方式 -->
        <yd-actionsheet :items="deliveryItems" v-model="deliveryShow" cancel="取消"></yd-actionsheet>
        <!-- 选择地址 -->
        <yd-popup v-model="siteShow" position="bottom" height="auto" class="orderinfo-popup-box">
            <yd-cell-group>
                <yd-cell-item type="radio" v-for="(item, index) in addressData" :key="index" @click.native="siteShow=false">
                    <span slot="left">
                        <div style="padding-top: .12rem;">{{item.mobile}}</div>
                        <div class="cell-group-padding">{{item.province}}{{item.city}}{{item.area}}{{item.street}}{{item.address}}</div>
                    </span>
                    <input slot="right" type="radio" :value="item.id" v-model="address_id" />
                </yd-cell-item>
            </yd-cell-group>
            <router-link to="/create" class="orderinfo-btn-box">新增地址</router-link>
        </yd-popup>
        <!-- 选择优惠券  -->
        <yd-popup v-model="seckillShow" position="bottom" height="auto" class="orderinfo-popup-box">
            <yd-cell-group>
                <yd-cell-item type="radio" v-for="(item, index) in coupanData" :key="index" v-if="item.can_use===1" @click.native="seckillShow=false">
                    <div slot="left">{{item.pre_txt}}</div>
                    <input slot="right" type="radio" :value="item.user_coupan_id" v-model="coupan_id" />
                </yd-cell-item>
            </yd-cell-group>
            <yd-button @click.native="delCoupan()" class="order-btn-box yd-btn-order-coupan">取消使用</yd-button>
            <router-link to="/couponsCenter" class="orderinfo-btn-box" style="width: 50%;float: left;background-color:#dab461;color:#fff">去领券</router-link>
        </yd-popup>
    </section>
</template>
<script>
import Qs from "qs";
export default {
  name: "Store",
  components: {},
  data() {
    return {
      adress_type: 0,
      deliveryType: "快递",
      deliveryNum: 0,
      deliveryShow: false,
      siteShow: false,
      seckillShow: false,
      deliveryItems: [
        {
          label: "快递",
          callback: () => {
            this.deliveryNum = 0;
            this.deliveryType = "快递";
          }
        },
        {
          label: "门店自提",
          callback: () => {
            this.deliveryNum = 1;
            this.deliveryType = "门店自提";
          }
        }
      ],
      defaultAddressData: "",
      addressData: "",
      address_id: "",
      address_name: "",
      address_phone: "",
      btns: [],
      coupanData: "",
      coupan_id: "",
      coupan_name: "",
      carriage: "0.00",
      orderinfoId: "",
      resultMoney: "0.00",
      order_original_amount: "0.00",
      order_discount_amount: "0.00",
      productList: [],
      productInfo: [],
      errorImg: 'this.src="' + require("../../../assets/img/err.jpg") + '"'
    };
  },
  watch: {
    address_id(val, oldVal) {
      var _this = this;
      if (_this.deliveryNum == 0) {
        _this.addressData.forEach(item => {
          if (item.id === val) {
            _this.address_phone = item.mobile;
            _this.address_name =
              item.province +
              item.city +
              item.area +
              item.street +
              item.address;
          }
        });
      }
      _this.moneyGet(JSON.stringify(_this.productInfo), val);
    },
    deliveryNum(val, oldVal) {
      var _this = this;
      if (val !== 1) {
        _this.address_id = _this.defaultAddressData.id;
      } else {
        _this.address_id = 0;
      }
    },
    coupan_id(val, oldVal) {
      var _this = this;
      _this.coupanData.forEach(item => {
        if (item.user_coupan_id === val) {
          _this.deduction = item.pre_txt;
          _this.coupan_name = item.pre_txt;
        }
      });
      _this.seckillShow = false;
      _this.moneyGet(JSON.stringify(_this.productInfo), _this.address_id);
    },
    adress_type(val, oldVal) {
      var _this = this;
      if (val == 1) {
        _this.deliveryNum = 1;
        _this.address_id = 0;
      } else {
        _this.deliveryNum = 0;
        _this.address_id = _this.defaultAddressData.id;
      }
      _this.moneyGet(JSON.stringify(_this.productInfo), _this.address_id);
    }
  },
  created() {
    let _this = this,
      _cart_id = this.$route.query.product_id.split(",");
    _this.productListGet(_cart_id);
  },
  methods: {
    backGo() {
        window.history.length > 1
            ? this.$router.go(-1)
            : this.$router.push('/')
    },
    //取消使用优惠券
    delCoupan() {
      let _this = this;
      _this.coupan_id = "";
      _this.deduction = "";
      _this.coupan_name = "";
      _this.moneyGet(JSON.stringify(_this.productInfo), _this.address_id);
    },
    // 地址列表
    siteList() {
      let _data = Qs.stringify({ user_id: localStorage.getItem("userId") });
      return this.$http.post("/api/v1/address/list", _data);
    },
    // 默认地址
    defaultSite() {
      let _data = Qs.stringify({ user_id: localStorage.getItem("userId") });
      return this.$http.post("/api/v1/address/getDefault", _data);
    },
    // 可用优惠券
    coupanGet(product) {
      let _data = Qs.stringify({
        user_id: localStorage.getItem("userId"),
        product: product
      });
      return this.$http.post("/api/v1/Order/coupan", _data);
    },
    // 并发请求
    assignment(product) {
      let _this = this;

      _this.$dialog.loading.open("很快加载好了");
      _this.$http
        .all([_this.siteList(), _this.defaultSite(), _this.coupanGet(product)])
        .then(
          _this.$http.spread(function(s, d, c) {
            _this.addressData = s.data.result.list; // 地址列表
            //设置默认收货地址
            if (d.data.result) {
              _this.address_id = d.data.result.id;
              _this.defaultAddressData = d.data.result;
            } else {
              if (_this.addressData.length > 0) {
                _this.address_id = _this.addressData[0].id;
                _this.defaultAddressData = _this.addressData[0];
              } else {
                _this.address_id = 0;
              }
            }
            _this.coupanData = c.data.result; // 优惠券列表
              if(_this.coupanData){
                  _this.coupanData.forEach(item => {
                      if (item.is_more_price == "1") {
                          _this.coupan_id = item.user_coupan_id;
                          _this.deduction = item.pre_txt;
                          _this.coupan_name = item.pre_txt;
                      }
                  });
              }
          })
        );
    },
    // 订单详情
    productListGet(c_id) {
      let _this = this,
        _data = Qs.stringify({
          user_id: localStorage.getItem("userId"),
          cart_id: c_id
        });

      _this.$dialog.loading.open("很快加载好了");
      _this.$http
        .post("/api/v1/Cart/getCartProduct", _data)
        .then(function(response) {
          if (response.data.errno === "0") {
            _this.productList = response.data.result;
            _this.productList.forEach(item => {
              _this.productInfo.push({
                product_id: item.product_id,
                num: item.num
              });
            });

            _this.assignment(JSON.stringify(_this.productInfo));
          } else {
            _this.$dialog.loading.close();
            _this.$router.replace("/cart");
          }
        })
        .catch(function(error) {
          _this.$dialog.loading.close();
          _this.$dialog.toast({ mes: error, timeout: 1500, icon: "error" });
        });
    },
    // 获取金额
    moneyGet(product, site_id) {
      let _this = this,
        _data = Qs.stringify({
          user_id: localStorage.getItem("userId"),
          delivery_type: _this.deliveryNum,
          address_id: site_id,
          product: product,
          user_coupan_id: _this.coupan_id
        });

      _this.$dialog.loading.open("很快加载好了");
      _this.$http
        .post("/api/v1/Order/getMoney", _data)
        .then(function(response) {
          if (response.data.errno === "0") {
            _this.resultMoney = response.data.result.order_actual_amount;
            _this.order_original_amount =
              response.data.result.order_original_amount;
            _this.order_discount_amount =
              response.data.result.order_discount_amount;
            _this.carriage = response.data.result.freight_charge_actual_amount;
            _this.$nextTick(function() {
              _this.$dialog.loading.close();
            });
          } else {
            _this.$dialog.loading.close();
            _this.$dialog.toast({
              mes: response.data.errmsg,
              timeout: 1500,
              icon: "error"
            });
          }
        })
        .catch(function(error) {
          _this.$dialog.loading.close();
          _this.$dialog.toast({ mes: error, timeout: 1500, icon: "error" });
        });
    },
    // 确认订单
    orderinfoSubmit() {
      let _this = this,
        _data = Qs.stringify({
          user_id: localStorage.getItem("userId"),
          delivery_type: _this.deliveryNum,
          address_id: _this.address_id,
          product: JSON.stringify(_this.productInfo),
          user_coupan_id: _this.coupan_id,
          callback: _this.$API + "/orderinfoList?orderinfoStatus=0"
        });

      if (
        _this.deliveryNum == 0 &&
        (_this.address_id <= 0 || _this.address_id == "")
      ) {
        _this.$dialog.toast({
          mes: "请选择收货地址",
          timeout: 500,
          icon: "error"
        });
        return;
      }

      _this.$dialog.loading.open("很快加载好了");
      _this.$http
        .post("/api/v1/Order/add", _data)
        .then(function(response) {
          if (response.data.errno === "0") {
            _this.$dialog.loading.close();
            var payurl = "";
            if (window.__wxjs_environment == "miniprogram") {
              payurl = response.data.result.payurl + "&mini=1";
            } else {
              payurl = response.data.result.payurl;
            }
            var pathURL = window.location.host;
            var n = pathURL.split(".").length - 1;
            if (n < 3) {
              payurl = payurl + "&lang=1";
            }
            window.location.href = payurl;
          } else {
            _this.$dialog.loading.close();
            _this.$dialog.toast({
              mes: response.data.errmsg,
              timeout: 1500,
              icon: "error"
            });
          }
        })
        .catch(function(error) {
          _this.$dialog.loading.close();
          _this.$dialog.toast({ mes: error, timeout: 1500, icon: "error" });
        });
    }
  }
};
</script>
<style>
@import "../../../assets/css/components/store/module/orderinfo";
</style>
