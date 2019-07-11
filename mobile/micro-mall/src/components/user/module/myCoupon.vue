<template>
  <section class="coupon">
    <yd-infinitescroll :callback="getCouponsList" ref="infinitescrollDemo" class="coupons-loadList-box">
      <yd-list slot="list">
        <div class="coupons-item" v-for="(item, key ) in couponList" :key="key">
          <div class="item" v-bind:class="{'disabled': status != '1'}">
            <div class="type">
              {{item.use_type_txt | typeFilter}}
            </div>
            <div class="reduce">
              <span class="pre_type" v-if="item.pre_type == '1'">￥</span>
              <span class="num">{{item.pre_value}}</span>
              <span class="pre_type" v-if="item.pre_type == '2'">折</span>
            </div>
            <div class="content">
              <h3>{{item.c_name}}</h3>
              <p class="user-conditions">{{item.sill_txt | sillFilter}}</p>
              <p class="date">{{item.time_txt}}</p>
              <a href="javascript:void(0);" class="btn" v-if="status == '1'" v-on:click="pageJump(item)">立刻使用</a>
              <span class="status" v-if="status != '1'">{{item.status_txt}}</span>
            </div>
          </div>
          <div class="coupon-product" v-if="status == '1'" v-bind:class="{'hide': item.product.total == 0}">
            <div v-if="item.product.total > 0">
              <div v-for="(product, k) in item.product.list" :key="k">
                <div v-if="k == '0'">
                  <div class="product-list">
                    <span class="title">
          				                    	    <span>【{{product.name}}】</span>
                    <span style="color:#FFCC00;margin-left: .3rem;" v-on:click="productJump(product.id)">点击使用</span>
                    </span>
                    <img v-if="item.product.total > 1" src="../../../assets/img/unfold.png" alt="" ref="child_img_un" v-on:click="showall(key)" class="bottom-img">
                    <img v-if="item.product.total > 1" src="../../../assets/img/fold.png" alt="" style="display:none" ref="child_img" v-on:click="showall(key)" class="bottom-img">
                  </div>
                </div>
              </div>
            </div>
            <div v-else>
              <div class="product-list1111" style="display:none">
                <img src="../../../assets/img/unfold.png" alt="" ref="child_img_un" v-on:click="showall(key)" class="bottom-img">
                <img src="../../../assets/img/fold.png" alt="" style="display:none" ref="child_img" v-on:click="showall(key)" class="bottom-img">
              </div>
            </div>
            <div v-show="isShow" ref="child">
              <div v-for="(product, k) in item.product.list" :key="k">
                <div v-if="k != '0'">
                  <div class="product-list-two">
                    <span class="title">
          				                    	         【{{product.name}}】
          				                    		<span style="color:#FFCC00;margin-left: .3rem;" v-on:click="productJump(product.id)">点击使用</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </yd-list>
      <!-- 数据全部加载完毕显示 -->
      <span v-show="isnow" slot="doneTip">~~暂无更多优惠券~~</span>
      <span v-show="ishide" slot="doneTip">~~暂无可用优惠券~~</span>
    </yd-infinitescroll>
  </section>
</template>

<script>
  import Qs from "qs";
  export default {
    components: {},
    data() {
      return {
        couponList: [],
        page: 1,
        rows: 10,
        isShow: false,
        ishide: false,
        isnow: false
      };
    },
    props: ["status"],
    mounted: function() {
      let _this = this;
      _this.$nextTick(function() {
        let user_id = localStorage.getItem("userId");
        if (!user_id) {
          _this.$router.push("/login");
        }
        _this.getCouponsList();
      });
    },
    methods: {
      showall: function(index) {
        if (this.$refs.child[index].style.display === "none") {
          this.$refs.child[index].style.display = "block";
          this.$refs.child_img[index].style.display = "block";
          this.$refs.child_img_un[index].style.display = "none";
        } else {
          this.$refs.child[index].style.display = "none";
          this.$refs.child_img[index].style.display = "none";
          this.$refs.child_img_un[index].style.display = "block";
        }
      },
      getCouponsList: function() {
        let that = this;
        that.$dialog.loading.open("加载中");
        let user_id = localStorage.getItem("userId");
        let data = Qs.stringify({
          user_id: user_id,
          status: that.status,
          page: that.page,
          rows: that.rows
        });
        that
          .$http({
            url: "/api/v1/Coupan/userList",
            method: "POST",
            data: data
          })
          .then(function(res) {
            that.$dialog.loading.close();
            if (res.data.errno == "0") {
              that.couponList = [...that.couponList, ...res.data.result.list];
              if (res.data.result.total > "0") {
                that.isnow = true;
              } else {
                that.ishide = true;
              }
              if (
                res.data.result.list.length < that.rows ||
                res.data.result.total / that.page === 0
              ) {
                that.$refs.infinitescrollDemo.$emit(
                  "ydui.infinitescroll.loadedDone"
                );
              } else {
                that.$refs.infinitescrollDemo.$emit(
                  "ydui.infinitescroll.finishLoad"
                );
                that.page++;
              }
            } else if (res.data.errno == "50006") {
              that.$dialog.confirm({
                title: "系统提示",
                mes: "登录状态失效，重新登录？",
                opts: () => {
                  that.$router.replace("/login");
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
          });
      },
      pageJump: function(item) {
        if (item.product.total == 1 && item.use_type == 2) {
          this.productJump(item.product.list[0].id);
        } else {
          this.$router.push({
            path: "/store",
            query: {
              coupan_id: item.coupan_id
            }
          });
        }
      },
      productJump: function(item) {
        this.$router.push("/details?id=" + item);
      }
    },
    filters: {
      preValueFilter: function(val) {
        return Math.floor(val);
      },
      typeFilter: function(val) {
        return val.replace(/优惠/, "");
      },
      sillFilter: function(val) {
        if (val == "无使用门槛 ") {
          return val;
        } else {
          return val + "使用";
        }
      }
    }
  };
</script>


<style scoped>

</style>
