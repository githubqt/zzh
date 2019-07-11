<template>
	<yd-layout class="bidding">
		<yd-navbar title="在线拍卖" slot="navbar">
			<div @click="backGo" slot="left">
				<yd-navbar-back-icon></yd-navbar-back-icon>
			</div>
		</yd-navbar>
		<!-- 搜索框 -->
		<div class="search-box clearfix" slot="navbar">
			<div :class="searchStatus?'status active': 'status'" @click="showSearchOption('status')">
				<span class="txt">{{clickStatus.status?statusList[chooseStatusIndex]:'状态'}} </span>
				<yd-icon custom :name="searchStatus?'jt-up':'jt-down'" size="0.3rem"></yd-icon>
			</div>
			<div :class="searchOrder?'order active': 'order'" @click="showSearchOption('order')">
				<span class="txt">{{clickStatus.order?orderList[chooseOrderIndex]:'排序'}}</span>
				<yd-icon custom :name="searchOrder?'jt-up':'jt-down'" size="0.3rem"></yd-icon>
			</div>
		</div>
		<!-- 搜索框面板 -->
		<div class="tab-mask" @click="hideSearchOption()" v-show="searchStatus || searchOrder">
			<div class="tab-box" id="tab1" v-show="searchStatus">
				<div :class="index === chooseStatusIndex?'tab-item  active':'tab-item'" v-for="(item,index) in statusList" :key="index" @click="chooseStatus(index)">
					<div class="title">{{item}}</div>
				</div>
			</div>
			<div class="tab-box" id="tab2" v-show="searchOrder">
				<div :class="index === chooseOrderIndex?'tab-item  active':'tab-item'" v-for="(item,index) in orderList" :key="index" @click="chooseOrder(index)">
					<div class="title">{{item}}</div>
				</div>
			</div>
		</div>
		<!-- 拍卖列表 -->
		<yd-infinitescroll :callback="biddingDataGet" ref="infinitescrollDemo">
			<yd-list theme="4" slot="list">
				<yd-list-item v-for="(item, index) in biddingData" :key="index">
					<div class="product-img" slot="img" @click="godetail(item)">
						<img v-lazy="item.logo_url" :onerror="errorImg">
						<span v-show="item.status==6" class="status-txt yellow">{{item.status_txt}}</span>
						<span v-show="item.status==5" class="status-txt yellow">{{item.status_txt}}</span>
						<span v-show="item.status==7" class="status-txt yellow">{{item.status_txt}}</span>
					</div>
					<span slot="title" style="max-width:3.5rem;max-height:0.34rem;" @click="godetail(item)">{{item.product_name}}</span>
					<yd-list-other slot="other">
						<yd-flexbox-item>
							<span class="sale-price">
								￥{{item.total_price_txt}}
							</span>
						</yd-flexbox-item>
						<yd-flexbox-item class="text-right"  v-show="item.status==5 || item.status == 6">
							<yd-button type="danger"  @click.native="godetail(item)" shape="circle">立即抢拍</yd-button>
						</yd-flexbox-item>
					</yd-list-other>
					<yd-list-other slot="other">
						<yd-flexbox-item>
							<div> <span class="market-price">{{item.bigding_price}}</span> </div>
						</yd-flexbox-item>
						<yd-flexbox-item   v-if="item.status==6">
							<div class="text-right"> <span class="yellow">{{item.count}}</span> 次出价</div>
						</yd-flexbox-item>
						<yd-flexbox-item  v-else>
							<div class="text-right"> <span class="yellow">{{item.onlookers_num}}</span> 次围观</div>
						</yd-flexbox-item>
					</yd-list-other>
					<yd-list-other slot="other">
						<yd-flexbox-item class="yellow">
							{{item.time_txt}}
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
	import Qs from 'qs'
	export default {
		name: 'Home',
		components: {},
		data() {
			return {
				page: 1,
				rows: 10,
				biddingData: '',
				errorImg: 'this.src="' + require('../../assets/img/err.jpg') + '"',
				searchStatus: false,
				searchOrder: false,
				chooseStatusIndex: '',
				chooseOrderIndex: '',
				statusList: [],
				orderList: [],
				clickStatus: {
					status: false,
					order: false
				}
			}
		},
		created() {
			this.$dialog.loading.open("很快加载好了");
			this.biddingDataGet();
			this.statusConf();
			this.orderConf();
		},
		methods: {
			backGo() {
                window.history.length > 1
                    ? this.$router.go(-1)
                    : this.$router.push('/')
			},
			timedown() { //定时器
				let _this = this;
				setInterval(function CountDown() {
					var groupUpData = _this.biddingData;
					if (groupUpData.length > 0) {
						groupUpData.forEach(function(c) {
							if (c.status != 7) {
								c.time--;
								c.time_txt = _this.timeToString(c.time, c.status);
								if (c.time <= 0) {
									window.location.reload();
								}
							}
						});
						_this.biddingData = groupUpData;
					}
				}, 1000);
			},
			timeToString(time, type) {
				var time_d = Math.floor(time / 86400);
				time -= time_d * 86400;
				var time_h = Math.floor(time / 3600);
				time -= time_h * 3600;
				var time_i = Math.floor(time / 60);
				time -= time_i * 60;
				var time_s = Math.floor(time % 60);
				if (type == 6) {
					return "距离结束仅剩:" + time_d + "天" + time_h + "时" + time_i + "分";
				} else if (type == 5) {
					return "距离开始仅剩:" + time_d + "天" + time_h + "时" + time_i + "分";
				}
			},
			godetail(e) {
				this.$router.push("/product?id=" + e.id + "&product_id=" + e.product_id);
			},
			biddingDataGet() {
				let _this = this,
					_data = Qs.stringify({
						page: _this.page,
						rows: _this.rows,
                        status:_this.chooseStatusIndex,
                        order:_this.chooseOrderIndex
					});

				_this.$http.post('/api/v1/Bidding/list', _data).then(function(response) {
					if (response.data.errno === '0') {
						_this.biddingData = [..._this.biddingData, ...response.data.result.list];
						if ((response.data.result.list.length < _this.rows) || (response.data.result.total / _this.page === 0)) {
							_this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.loadedDone');
						} else {
							_this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.finishLoad');
							_this.page++;
						}
						_this.timedown();
						_this.$nextTick(function() {
							_this.$dialog.loading.close()
						});
					}
				}).catch(function(error) {
					_this.$dialog.loading.close();
					_this.$dialog.toast({
						mes: error,
						timeout: 1500,
						icon: 'error'
					});
				});
			},
			// 状态搜索条件
			statusConf() {
				// this.statusList = [
				// 	'未开始', '进行中', '已结束'
				// ];
                this.statusList = [
                    '所有状态', '已开始', '未开始'
                ];
			},
			// 排序搜索条件
			orderConf() {
				this.orderList = [
					'默认排序', '商品原价从高到低', '商品原价从低到高', '出价次数由低到高', '出价次数由高到低'
				];
			},
			//显示搜索条件
			showSearchOption(type) {
				if (type === 'status') {
					this.searchStatus = true;
					this.searchOrder = false;
				} else {
					this.searchStatus = false;
					this.searchOrder = true;
				}
			},
			//隐藏搜索条件
			hideSearchOption() {
				this.searchStatus = false;
				this.searchOrder = false;
			},
			//选择状态索引
			chooseStatus(index) {
				this.chooseStatusIndex = index;
				this.clickStatus.status = true;
                this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
                this.biddingData = [];
				this.page = 1;
				this.$dialog.loading.open("很快加载好了");
                this.biddingDataGet();
			},
			//选择排序索引
			chooseOrder(index) {
				this.chooseOrderIndex = index;
				this.clickStatus.order = true;
                this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
                this.biddingData = [];
				this.page = 1;
				this.$dialog.loading.open("很快加载好了");
                this.biddingDataGet();
			}
		}
	}
</script>

<style scoped>
</style>
