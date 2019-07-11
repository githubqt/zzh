<template>
    <yd-layout class="user-invite">
        <!-- 头部 start -->
        <yd-navbar slot="navbar" title="邀请列表" arrow>
            <router-link to="/user" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
            <router-link to="/invitation" slot="right" class="primary-color">
                我要邀请
            </router-link>
        </yd-navbar>
        <!-- 头部 end -->
        <!-- 已邀请数 start -->
        <div class="user-invite-amount" slot="navbar" v-show="isLoaded && total">
            <div class="user-invite-amount-body">
                <h3 class="mt-0 mb-1 amount-title">邀请好友并给他们发送信息</h3>
                <div>
                    已有{{total}}位好友加入！
                </div>
            </div>
            <yd-icon custom name="yaoqinghaoyou" size="1.5rem" class="ml-2 user-invite-amount-icon"></yd-icon>
        </div>
        <!-- 已邀请数 end -->
        <!-- 邀请用户列表 start -->
        <yd-infinitescroll :callback="loadList" ref="infinitescrollList" v-show="isLoaded && total">
            <div class="user-invite-list" slot="list">
                <div class="user-invite-list-item" v-for="(item,i) in invitationList" :key="i">
                    <img class="user-avatar" v-lazy="item.user_img" alt="image">
                    <div class="user-invite-list-item-body">
                        <h4 class="user-name"><span v-show="item.name">{{item.name}} ·</span> {{item.mobile}} </h4>
                        <div>
                            {{item.created_at_date}} 加入
                        </div>
                    </div>
                </div>
            </div>
            <!-- 数据全部加载完毕显示 -->
            <span slot="doneTip">啦啦啦，啦啦啦，没有数据啦~~</span>
            <!-- 加载中提示，不指定，将显示默认加载中图标 -->
            <img slot="loadingTip" src="../../../assets/img/loading10.svg" />
        </yd-infinitescroll>
        <!-- 邀请用户列表 end -->
        <!-- 邀请列表为空时显示 start -->
        <div class="invite-empty" v-show="isLoaded && total === 0 ">
            <div class="no-data-icon">
                <yd-icon custom name="yiyaoqing" size="2rem"></yd-icon>
            </div>
            <div class="no-data-btn">
                <yd-button type="danger" size="large" @click.native="invite">点击邀请</yd-button>
            </div>
        </div>
        <!-- 邀请列表为空时显示 end -->
    </yd-layout>
</template>
<script>
    import Qs from "qs";
    const INVITATION_API = "/api/v1/User/invitation";
    export default {
        name: "InviteList",
        data() {
            return {
                page: 1,
                total: 0,
                isLoaded: false,
                invitationList: []
            };
        },
        created() {
            this.$dialog.loading.open("正在加载...");
            this.getList();
        },
        mounted() {
            
        },
        methods: {
            loadList() {
                const _this = this;
                _this.getList();
            },
            invite() {
                this.$router.push('/invitation');
            },
            getList() {
                const _this = this;
                let _data = Qs.stringify({
                    page: _this.page
                });
                _this.$http.post(INVITATION_API, _data).then(response => {
                    let json = response.data;
                    let result = response.data.result;
                    if (parseInt(json.errno) > 0) {
                        _this.$refs.infinitescrollList.$emit("ydui.infinitescroll.finishLoad");
                        _this.isLoaded = true;
                        _this.$dialog.loading.close();

                        if(parseInt(json.errno) === 40015){
                            _this.$router.replace('/login');
                        }
                        return false;
                    }
                    const _list = result.rows;
                    _this.invitationList = [..._this.invitationList, ..._list];
                    _this.total = result.total;
                    if (_this.invitationList.length == result.total) {
                        // 所有数据加载完毕
                        _this.$refs.infinitescrollList.$emit("ydui.infinitescroll.loadedDone");
                        _this.$dialog.loading.close();
                       _this.isLoaded = true;
                        return;
                    }
                    // 单次请求数据完毕
                    _this.$refs.infinitescrollList.$emit("ydui.infinitescroll.finishLoad");
                    _this.$dialog.loading.close();
                    _this.isLoaded = true;
                    _this.page++;
                });
            }
        }
    }
</script>
<style>

</style>
