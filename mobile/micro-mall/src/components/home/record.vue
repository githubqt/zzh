<template>
    <yd-layout>
        <!-- 头部导航 satrt -->
        <yd-navbar title="出价记录"  slot="navbar" >
            <div @click="backGo" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </div>
        </yd-navbar>
        <!-- 头部导航 end -->
        <!-- 标题 satrt -->
        <yd-flexbox class="record-title"  slot="navbar">
            <yd-flexbox-item class="flex1">状态</yd-flexbox-item>
            <yd-flexbox-item class="flex1">出价人</yd-flexbox-item>
            <yd-flexbox-item class="flex2">手机号</yd-flexbox-item>
            <yd-flexbox-item class="flex2">金额(元)</yd-flexbox-item>
            <yd-flexbox-item class="flex2">时间</yd-flexbox-item>
        </yd-flexbox>
        <!-- 标题 end -->
        <!-- 数据 satrt -->
        <yd-pullrefresh :callback="pullList" ref="pr" >
            <yd-infinitescroll :callback="scrollList" ref="ls">
                <yd-list theme="1" slot="list">
                    <yd-flexbox class="record-data" v-for="(item, key) in recordData" :key="key">
                        <yd-flexbox-item class="flex1">
                            <yd-badge type="danger" shape="square" v-if="item.status==1">{{status==2?'成交':item.status_txt}}</yd-badge>
                            <yd-badge v-else shape="square">{{item.status_txt}}</yd-badge>
                        </yd-flexbox-item>
                        <yd-flexbox-item class="flex1 data-label">
                            <span>

                    </span> {{item.name}}
                        </yd-flexbox-item>
                        <yd-flexbox-item class="flex2">{{item.user_txt}}</yd-flexbox-item>
                        <yd-flexbox-item class="flex2">
                            <yd-countup :endnum="item.money" duration="1" decimals="2" separator="," prefix="￥"></yd-countup>
                        </yd-flexbox-item>
                        <yd-flexbox-item class="flex2">{{item.created_at}}</yd-flexbox-item>
                    </yd-flexbox>
                </yd-list>
                <!-- 数据 end -->
                <!-- 无数据 start -->
                <!-- 数据全部加载完毕显示 -->
                <span slot="doneTip" v-if="recordData.length == 0">目前还没有出价记录哟</span>
                <span slot="doneTip" v-else>没有更多出价记录啦</span>
            </yd-infinitescroll>
        </yd-pullrefresh>
        <!-- 无数据 end -->
        <!-- 回到顶部 start -->
        <yd-backtop></yd-backtop>
        <!-- 回到顶部 end -->
    </yd-layout>
</template>


<script>
import Qs from "qs";
export default {
  name: "Home",
  components: {},
  data() {
    return {
      page1: 1,
      page2: 1,
      pageSize: 20,
      recordData: [],
      status:''
    };
  },
  created() {
    this.scrollList();
    this.status = this.$route.query.status;
  },
  methods: {
    /**
     * 下拉刷新
     */
    pullList() {
      const url = "/api/v1/Bidding/record";
      let _data = Qs.stringify({
        id: this.$route.query.id,
        product_id: this.$route.query.product_id,
        type: "pulldown",
        page: this.page1,
        pagesize: this.pageSize
      });
      this.$http.post(url, _data).then(response => {
        if (parseInt(response.data.errno) > 0) {
          this.$dialog.toast({
            mes: response.data.errmsg
          });
          this.$refs.pr.$emit("ydui.pullrefresh.finishLoad");
          return false;
        }
        const _list = response.data.result.list;
        this.recordData = _list;
        // 下拉刷新API接口未处理,注释
        //this.recordData = [..._list, ...this.recordData];
        this.$dialog.toast({
          mes: "已是最新"
        });
        this.$refs.pr.$emit("ydui.pullrefresh.finishLoad");
        // this.page1++;
        this.page2 = 1;
      });
    },
    /**
     * 上拉加载更多
     */
    scrollList() {
      const url = "/api/v1/Bidding/record";
      let _data = Qs.stringify({
        id: this.$route.query.id,
        product_id: this.$route.query.product_id,
        page: this.page2,
        pagesize: this.pageSize
      });
      this.$http.post(url, _data).then(response => {
        if (parseInt(response.data.errno) > 0) {
          this.$refs.lsdemo.$emit("ydui.infinitescroll.finishLoad");
          this.$dialog.toast({
            mes: response.data.errmsg
          });
          return false;
        }
        const _list = response.data.result.list;
        this.recordData = [...this.recordData,..._list];
        if (this.recordData.length == response.data.result.total) {
          // 所有数据加载完毕
          this.$refs.ls.$emit("ydui.infinitescroll.loadedDone");
          return;
        }
        // 单次请求数据完毕
        this.$refs.ls.$emit("ydui.infinitescroll.finishLoad");
        this.page2++;
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
<style>
</style>
