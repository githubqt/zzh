<template>
    <section class="coupon">
        <yd-infinitescroll :callback="getCouponsList" ref="infinitescrollDemo" class="coupons-loadList-box">
            <yd-list slot="list">
                <div  class="coupons-item" v-for="item, key, index in couponList" :key="key">
                    <div class="item">
                        <div class="type">
                            {{item.use_type_txt | typeFilter}}
                        </div>
                        <div class="reduce">
                            <span class="type" v-if="item.pre_type == '1'">￥</span>
                            <span class="num">{{item.pre_value}}</span>
                            <span class="type" v-if="item.pre_type == '2'">折</span>
                        </div>
                        <div class="content">
                            <h3>{{item.c_name}}</h3>
                            <p class="user-conditions">{{item.sill_txt | sillFilter}}</p>
                            <p class="date">{{item.time_txt}}</p>

                            <a v-if="item.user_is_ok=='ok'" user_is_ok href="javascript:void(0);" class="btn" v-on:click="receiveCoupon(item, index)">立刻领取</a>
                            <a v-if="item.user_is_ok=='no'" user_is_ok href="javascript:void(0);" class="btn" style="background-color:#ccc">立刻领取</a>
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
import Qs from 'qs';

export default {
    components: {

    },
    data() {
        return {
            user_id: '',
            couponList: [],
            page: 1,
            rows: 10,
            ishide:false,
            isnow:false,
        }
    },
    mounted: function () {
    	let _this = this;
        _this.$nextTick(function () {
            _this.user_id = localStorage.getItem("userId")
            _this.getCouponsList();


        })
    },
    methods: {
        getCouponsList: function() {
            let that = this;

            that.$dialog.loading.open('加载中')

            let data = Qs.stringify({
                'page': that.page,
                'rows': that.rows,
                'user_id': that.user_id,
                'coupan_id':that.$route.query.coupan_id
            });

            that.$http({
                url: '/api/v1/Coupan/list',
                method: 'POST',
                data: data
            }).then(function(res) {
                that.$dialog.loading.close();
                if (res.data.errno == '0') {
                    that.couponList = [...that.couponList, ...res.data.result.list]
                    if (res.data.result.total > '0') {
                    	that.isnow = true;
                    } else {
                    	that.ishide = true;
                    }
                    if ((res.data.result.list.length < that.rows) || (res.data.result.total / that.page === 0)) {
                    	that.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.loadedDone');
                    }else {
                    	that.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.finishLoad');
                    	that.page++;
                    }
                } else if (res.data.errno == '50006') {
                    that.$dialog.confirm({
                        title: '系统提示',
                        mes: '登录状态失效，重新登录？',
                        opts: () => {
                            that.$router.push('/login')
                        }
                    })
                } else {
                    that.$dialog.toast({ mes: res.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(err) {
                that.$dialog.loading.close();
                that.$dialog.toast({ mes: err, timeout: 1500, icon: 'error' });
            })
        },
        receiveCoupon: function (item, index) {
            let that = this
			that.$dialog.loading.open('领取中');
			let _data = Qs.stringify({ user_id: localStorage.getItem('userId') });

            that.$http.post('/api/v1/User/userInfo', _data).then(function(response) {
                if (response.data.errno === '0') {
                	if (!response.data.result.mobile) {
                		 that.$router.push('/addMobile?back=1');
                	}
                } else {
                    that.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(error) {
                that.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
            });

            let data =  Qs.stringify({
                'user_id': that.user_id,
                'coupan_id': item.id
            })

            that.$http({
                url: '/api/v1/Coupan/get',
                method: 'POST',
                data: data
            }).then(function(res) {
                that.$dialog.loading.close();
                if (res.data.errno == '0') {

                    that.$dialog.toast({ mes: '领取成功', timeout: 1000, icon: 'success' });
                    // that.couponList.splice(index, 1)
                    setTimeout(() => {
                        that.$router.go(0);
                    }, 1000);
                } else if (res.data.errno == '50006') {
                    that.$dialog.confirm({
                        title: '系统提示',
                        mes: '登录状态失效，重新登录？',
                        opts: () => {
                            that.$router.push('/login')
                        }
                    })
                } else {
                    that.$dialog.toast({ mes: res.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(err) {
                that.$dialog.loading.close();
                that.$dialog.toast({ mes: err, timeout: 1500, icon: 'error' });
            })
        }
    },
    filters: {
        preValueFilter: function (val) {
            return  Math.floor(val)
        },
        typeFilter: function (val) {
            return  val.replace(/优惠/, "")
        },
        sillFilter: function (val) {
            if (val == "无使用门槛 ") {
                return val
            } else {
                return val + '使用'
            }
        }
    }
}
</script>

<style scoped>
</style>
