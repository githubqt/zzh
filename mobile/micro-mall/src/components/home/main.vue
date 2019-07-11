<template>
	<yd-layout class="home-container">
		<!-- header -->
		<yd-navbar slot="navbar" fixed :title="company"></yd-navbar>
		<!-- 总浏览量 -->
		<sty-count-navbar :title="company" :views="browse_num" :likes="collect_num"></sty-count-navbar>
		<!--slider -->
		<yd-slider autoplay="3000" :show-pagination="true">
			<yd-slider-item v-for="(item, index) in sliderData" :key="index">
				<a :id="item.id" @click="mainJump(item.data_type, item.details)">
					<img v-lazy="item.img_path" :alt="item.title_name">
				</a>
			</yd-slider-item>
		</yd-slider>
		<!--4个图标 -->
		<sty-middle-nav></sty-middle-nav>
		<!--新增轮播广告 -->
		<!--<yd-slider autoplay="3000" :show-pagination="true">-->
		<!--<yd-slider-item v-for="(item, index) in ads" :key="index">-->
		<!--<a>-->
		<!--<img v-lazy="item.url" >-->
		<!--</a>-->
		<!--</yd-slider-item>-->
		<!--</yd-slider>-->
		<!--滚动公告 -->
		<!--<sty-notice :newest="newest" :hotest="hotest" />-->
		<!--最新上架 -->
		<sty-home-newest :newarrival="newarrival"> </sty-home-newest>
		<!-- 猜你喜欢 -->
		<!-- <sty-home-like :guesslike="guesslike"></sty-home-like> -->
		<section class="guess-like">
			<h3 class="title"><span class="circle"></span> 猜你喜欢 <span class="circle"></span></h3>
			<yd-infinitescroll :callback="loadList" ref="lsdemo">
				<yd-list theme="4" slot="list">
					<yd-list-item v-for="(item, key) in guesslike" :key="key" :id="item.id" type="link" :href="{name:'Details',query:{id:item.id}}">
						<img slot="img" v-lazy="item.logo_url">
						<span slot="title">{{item.name}}</span>
						<yd-list-other slot="other">
							<yd-flexbox-item>
								<span class="sale-price">
															￥{{item.sale_price}}
														</span>
								<span class="market-price">
															￥{{item.market_price}}
														</span>
							</yd-flexbox-item>
						</yd-list-other>
						<yd-list-other slot="other">
							<yd-flexbox-item align="bottom">
								<div class="favor-num">
									浏览数 {{item.browse_num}}
								</div>
							</yd-flexbox-item>
							<yd-flexbox-item align="top" class="buy">
								<yd-button type="danger" >去抢购</yd-button>
							</yd-flexbox-item>
						</yd-list-other>
					</yd-list-item>
				</yd-list>
			</yd-infinitescroll>
		</section>

		<!-- ICP备案提示 start
									<div style="position:fixed;bottom:1rem;text-align:center;width:100%;color:#999;background:#fff;padding:0.01rem">
										京ICP备18022684号
									</div> -->
		<!-- ICP备案提示 end -->
		<sty-menu></sty-menu>
	</yd-layout>
</template>

<script>
	import Qs from "qs";
	import Newarrival from "@/components/home/module/newarrival";
	import Notice from "@/components/home/module/notice";
	import CountNavbar from "@/components/home/module/count-navbar";
	import MiddleNav from "@/components/home/module/middle-nav";
	import HomeNewest from "@/components/home/module/newest";
	import MenuBar from "@/common/menu";
	import {
		login,
		login_state,
		logout
	} from "../../../tool/login";
	export default {
		name: "Home",
		components: {
			"sty-newarrival": Newarrival,
			"sty-notice": Notice,
			"sty-count-navbar": CountNavbar,
			"sty-middle-nav": MiddleNav,
			"sty-home-newest": HomeNewest,
			"sty-menu": MenuBar
		},
		data() {
			return {
				page: 1,
				company: "",
				browse_num: 0,
				collect_num: 0,
				sliderData: [],
				newarrival: [],
				guesslike: [],
				newest: [{
						'name': ''
					},
					{
						'name': ''
					}
				],
				hotest: [{
						'name': ''
					},
					{
						'name': ''
					}
				],
				ads: [{
					url: ''
				}, {
					url: ''
				}]
			};
		},
		created() {
			this.statusLogin();
			this.mainDataGet();
		},
		methods: {
			supplierGet() {
				return this.$http.post("/api/v1/Home/supplier");
			},
			sliderDataGet() {
				return this.$http.post("/api/v1/Home/indexData");
			},
			newarrivalGet() {
				let _data = Qs.stringify({
					sort: "now_at",
					order: "DESC",
					page: 1,
					rows: 10
				});
				return this.$http.post("/api/v1/Product/list", _data);
			},
			guessLikeGet() {
				let _data = Qs.stringify({
					page: 1,
					rows: 10
				});
				return this.$http.post("/api/v1/Product/like", _data);
			},
			mainDataGet() {
				let _this = this;
				_this.$dialog.loading.open("很快加载好了");
				_this.$http
					.all([
						_this.supplierGet(),
						_this.sliderDataGet(),
						_this.guessLikeGet(), // 猜你喜欢
						_this.newarrivalGet()  //最新上架
					])
					.then(
						_this.$http.spread(function(l, s,  g,n) {
							_this.company = l.data.result.company;
							_this.browse_num = l.data.result.browse_num;
							_this.collect_num = l.data.result.collect_num;
							_this.sliderData = s.data.result;

							_this.guesslike = g.data.result.list;
							console.log(_this.guesslike);
							_this.guesslike.forEach(function(item, index) {
								_this.ads[1].url = _this.guesslike[0].url;
								_this.newest[1].name = _this.guesslike[0].name;
								_this.hotest[1].name = _this.guesslike[1].name;
							});

							_this.newarrival = n.data.result.list;
							_this.newarrival.forEach(function(item, index) {
								_this.ads[1].url = _this.newarrival[0].url;
								_this.newest[1].name = _this.newarrival[0].name;
								_this.hotest[1].name = _this.newarrival[1].name;
							});

							_this.page++;

							_this.$nextTick(function() {
								_this.$dialog.loading.close();
							});
						})
					);
			},
			mainJump(type, id) {
				switch (type) {
					case "1":
						this.$router.push({
							name: "Details",
							query: {
								id: id
							}
						});
						break;
					case "2":
						this.$router.push({
							name: "Store",
							query: {
								category_id: id
							}
						});
						break;
					case "3":
						window.location.href = id;
						break;
					default:
				}
			},
			statusLogin() {
				let state = login_state();
				let _this = this,
					_data = Qs.stringify({
						user_id: state.user_id
					});
				_this.$http
					.post("/api/v1/User/isLogin", _data)
					.then(function(response) {
						if (response.data.errno !== "0") {
							logout();
						}
					})
					.catch(function(error) {});
			},
			loadList() {
				let _this = this;

				let _data = Qs.stringify({
					page: this.page,
					rows: 10
				});
				return this.$http.post("/api/v1/Product/like", _data).then(function(response) {

					const _list = response.data.result.list;

					_this.guesslike = [..._this.guesslike, ..._list];
					if (_this.guesslike.length == response.data.result.total) {
						// 所有数据加载完毕
						_this.$refs.lsdemo.$emit('ydui.infinitescroll.loadedDone');
						return;
					}
					// 单次请求数据完毕
					_this.$refs.lsdemo.$emit('ydui.infinitescroll.finishLoad');
					_this.page++;
				});
			}
		}
	};
</script>

<style>
</style>
