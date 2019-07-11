<template>
  <yd-layout class="group-up">
    <yd-navbar title="多人拼团" slot="navbar">
      <div @click="backGo" slot="left">
        <yd-navbar-back-icon></yd-navbar-back-icon>
      </div>
    </yd-navbar>
    <!-- 搜索框 -->
    <div class="search-box clearfix" slot="navbar">
      <div :class="searchOrder?'order active': 'order'" @click="showSearchOption('order')">
        <span class="txt">{{clickStatus.order?orderList[chooseOrderIndex]:'排序'}}</span>
        <yd-icon custom :name="searchOrder?'jt-up':'jt-down'" size="0.3rem"></yd-icon>
      </div>
    </div>
    <!-- 搜索框面板 -->
    <div class="tab-mask" @click="hideSearchOption()" v-show="searchOrder">
      <div class="tab-box" id="tab2" v-show="searchOrder">
        <div :class="index === chooseOrderIndex?'tab-item  active':'tab-item'" v-for="(item,index) in orderList" :key="index" @click="chooseOrder(index)">
          <div class="title">{{item}}</div>
        </div>
      </div>
    </div>
    <!-- 拼团列表 -->
    <yd-infinitescroll :callback="groupUpDataGet" ref="infinitescrollDemo">
      <yd-list theme="4" slot="list">
        <yd-list-item v-for="(item, index) in groupUpData" :key="index">
          <div slot="img" @click=godetail(item) class="product-img">
            <img v-lazy="item.logo_url">
            <!-- 拼团状态标识 -->
            <span class="status-txt yellow">{{item.status_txt}}</span>
          </div>
          <!-- 商品名称 -->
          <span slot="title" @click=godetail(item)>{{item.product_name}}</span>
          <!-- 开团 -->
          <yd-list-other slot="other">
            <yd-flexbox-item>
              <!-- 拼团人数 -->
              <span class="yellow">0</span> 人拼
              <span class="sale-price">
                    ￥{{item.group_price}}
                  </span>
            </yd-flexbox-item>
            <!-- 去开团 -->
            <div class="text-right">
              <yd-button type="danger" shape="circle" @click.native="godetail(item)">去开团</yd-button>
            </div>
          </yd-list-other>
          <!-- 成团记录 -->
          <yd-list-other slot="other">
            <yd-flexbox-item>
              <span class="market-price">
            								￥{{item.sale_price}}
            							</span>
            </yd-flexbox-item>
            <yd-flexbox-item class="text-right">
              <span class="yellow"> {{item.oredr_product_num}}</span> 次成团
            </yd-flexbox-item>
          </yd-list-other>
          <!-- 倒计时 -->
          <yd-list-other slot="other">
            <yd-flexbox-item v-show="item.status_num== 1">
              <span class="yellow" >距离开始<yd-countdown :time="item.time"   timetype="second" ></yd-countdown></span>
            </yd-flexbox-item>

            <yd-flexbox-item v-show="item.status_num== 2">
              <span class="yellow" >距离结束<yd-countdown :time="item.time"   timetype="second" ></yd-countdown>结束</span>
            </yd-flexbox-item>
          </yd-list-other>
        </yd-list-item>
      </yd-list>
      <!-- 数据全部加载完毕显示 -->
      <span slot="doneTip">~~没有数据啦~~</span>
    </yd-infinitescroll>
  </yd-layout>
</template>

<script>
  import Qs from "qs";
  export default {
    name: "Home",
    components: {},
    data() {
      return {
        page: 1,
        rows: 10,
        groupUpData: "",
        searchOrder: false,
        chooseOrderIndex: 0,
        orderList: [],
        clickStatus: {
          order: false
        }
      };
    },
    created() {
      this.groupUpDataGet();
      this.orderConf();
    },
    methods: {
      backGo() {
          window.history.length > 1
              ? this.$router.go(-1)
              : this.$router.push('/')
      },
      godetail(e) {
        this.$router.push("/groupDetails?id=" + e.id);
      },
      groupUpDataGet() {
        let _this = this,
          _data = Qs.stringify({
            page: _this.page,
            rows: _this.rows,
              order:_this.chooseOrderIndex
          });
        _this.$http
          .post("/api/v1/Group/list", _data)
          .then(function(response) {
            if (response.data.errno === "0") {
              _this.groupUpData = [
                ..._this.groupUpData,
                ...response.data.result.list
              ];
              if (
                response.data.result.list.length < _this.rows ||
                response.data.result.total / _this.page === 0
              ) {
                _this.$refs.infinitescrollDemo.$emit(
                  "ydui.infinitescroll.loadedDone"
                );
              } else {
                _this.$refs.infinitescrollDemo.$emit(
                  "ydui.infinitescroll.finishLoad"
                );
                _this.page++;
              }
              _this.$nextTick(function() {
                _this.$dialog.loading.close();
              });
            } else {
              _this.$dialog.loading.close();
            }
          })
          .catch(function(error) {
            _this.$dialog.loading.close();
            _this.$dialog.toast({
              mes: error,
              timeout: 1500,
              icon: "error"
            });
          });
      },
      // 排序搜索条件
      orderConf() {
        this.orderList = [
          '默认排序', '价格从高到低', '价格从低到高', '成团次数由低到高', '成团次数由高到低'
        ];
      },
      //显示搜索条件
      showSearchOption(type) {
        this.searchOrder = true;
      },
      //隐藏搜索条件
      hideSearchOption() {
        this.searchOrder = false;
      },
      //选择排序索引
      chooseOrder(index) {
        this.chooseOrderIndex = index;
        this.clickStatus.order = true;
        this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
        this.groupUpData = [];
        this.page = 1;
        this.$dialog.loading.open("很快加载好了");
        this.groupUpDataGet();
      }
    }
  };
</script>

<style>
</style>
