<template>
	<section class="user-container">
		<!-- header -->
		<yd-navbar class="fixed-header" height=".88rem" fontsize=".34rem" bgcolor="#E25B56" color="#fff" title="个人中心"></yd-navbar>
		<!-- content -->
		<yd-cell-group class="user-info-box m-0">
			<yd-cell-item class="user-cell-item" arrow type="link" href="userinfo" v-if="isLogined">
				<div slot="left">
					<img v-lazy="userImg" alt="" class="user-head-portrait">
					<span class="user-head-name" v-if="userName">{{userName}}</span>
					<span class="user-head-name" v-else>{{phone}}</span>
				</div>
			</yd-cell-item>
			<yd-cell-item class="user-cell-item" arrow type="link" href="login" v-else>
				<div slot="left">
					<img src="../../assets/img/header.jpg" alt="" class="user-head-portrait">
					<span class="user-head-name">登录OR注册</span>
				</div>
			</yd-cell-item>
		</yd-cell-group>
		<div class="user-content-box">
			<!-- info bar -->
			<yd-flexbox class="user-info-bar">
				<yd-flexbox-item>
					<span class="user-info-bar-title"><em>0</em>元</span>
					<span class="user-info-bar-content">真的钱包</span>
				</yd-flexbox-item>
				<yd-flexbox-item v-on:click.native="enterMyCoupon">
					<span class="user-info-bar-title"><em>{{coupanNum}}</em>张</span>
					<span class="user-info-bar-content">优惠券</span>
				</yd-flexbox-item>
				<yd-flexbox-item @click.native="tapToInvite">
					<span class="user-info-bar-title"><em>{{invitationNum}}</em>个</span>
					<span class="user-info-bar-content">邀请数</span>
				</yd-flexbox-item>
			</yd-flexbox>
			<!-- order -->
			<yd-cell-group class="m-0">
				<yd-cell-item arrow type="link" :href="{name:'OrderList',query:{orderStatus:0}}">
					<span slot="left">我的订单</span>
				</yd-cell-item>
			</yd-cell-group>
			<yd-grids-group :rows="5">
				<yd-grids-item type="link" :link="{name:'OrderList',query:{orderStatus:1}}">
					<span class="miconfont micon-pay" slot="icon"></span>
					<span slot="text">待付款</span>
				</yd-grids-item>
				<span class="showIf2" v-show="pending_payment != 0" v-cloak>
												    				     <span class="num">{{pending_payment}}</span>
				</span>
				<yd-grids-item type="link" :link="{name:'OrderList',query:{orderStatus:3}}">
					<span class="miconfont micon-cons" slot="icon"></span>
					<span slot="text">待发货</span>
				</yd-grids-item>
				<span class="showIf2" v-show=" pending_delivery != 0" v-cloak>
												    				     <span class="num">{{pending_delivery}}</span>
				</span>
				<yd-grids-item type="link" :link="{name:'OrderList',query:{orderStatus:4}}">
					<span class="miconfont micon-receive" slot="icon"></span>
					<span slot="text">待收货</span>
				</yd-grids-item>
				<span class="showIf2" v-show="goods_to_be_received != 0" v-cloak>
												    				     <span class="num">{{goods_to_be_received}}</span>
				</span>
				<yd-grids-item type="link" :link="{name:'OrderList',query:{orderStatus:5}}">
					<span class="miconfont micon-finish" slot="icon"></span>
					<span slot="text">已完成</span>
				</yd-grids-item>
				<span class="showIf2" v-show="completed_over != 0" v-cloak>
												    				     <span class="num">{{completed_over}}</span>
				</span>
				<yd-grids-item type="link" link="/afterSale">
					<span class="miconfont micon-sales" slot="icon"></span>
					<span slot="text">退货/售后</span>
				</yd-grids-item>
				<span class="showIf2" v-show="after_sale != 0" v-cloak>
												    				     <span class="num">{{after_sale}}</span>
				</span>
			</yd-grids-group>
			<div>
				<div class="user-menu-box"></div>
				<yd-cell-group class="m-0">
					<yd-cell-item arrow type="link" :href="{name:'AuctionList',query:{auctionStatus:0}}">
						<span slot="left">我的拍品</span>
						<span slot="right" class="showIf" v-show="number != null && number != 0" v-cloak>
												       								<span class="num">{{number}}</span>
						</span>
					</yd-cell-item>
				</yd-cell-group>
			</div>
		</div>
		<div class="user-menu-box">
			<yd-grids-group :rows="4">
				<!-- 签到 -->
				<yd-grids-item @click.native="showSign()" style="display:none">
					<yd-icon custom name="qiandao" size=".65rem" slot="icon"></yd-icon>
					<span slot="text">签到送积分</span>
				</yd-grids-item>

				<yd-grids-item link="/collect">
					<span class="miconfont micon-collect" slot="icon"></span>
					<span slot="text">收藏商品</span>
				</yd-grids-item>
				<yd-grids-item link="/site">
					<span class="miconfont micon-site" slot="icon"></span>
					<span slot="text">地址管理</span>
				</yd-grids-item>
				<yd-grids-item link="/contact">
					<span class="miconfont micon-contact" slot="icon"></span>
					<span slot="text">联系我们</span>
				</yd-grids-item>
				<yd-grids-item v-if="complaints==0 || complaints == null" link="/complainthome">
					<span class="miconfont micon-complain" slot="icon"></span>
					<span slot="text">投诉建议</span>
				</yd-grids-item>
				<yd-grids-item v-else link="/suggest">
					<span class="miconfont micon-complain" slot="icon"></span>
					<span slot="text">投诉建议</span>
				</yd-grids-item>
				<yd-grids-item link="/about">
					<span class="miconfont micon-us" slot="icon"></span>
					<span slot="text">关于我们</span>
				</yd-grids-item>
				<!--<yd-grids-item link="/pawn">-->
				<!--<span class="miconfont micon-pawn" slot="icon"></span>-->
				<!--<span slot="text">在线售卖</span>-->

				<!--</yd-grids-item>-->
				<yd-grids-item @click.native="showCertificate()">
					<yd-icon custom name="jiandingzhengshu" size=".65rem" slot="icon"></yd-icon>
					<span slot="text">鉴定查询</span>
				</yd-grids-item>
			</yd-grids-group>
		</div>
		<div class="switchover-user-box" v-if="isLogined">
			<a class="switchover-user-btn" @click="signOut">切换账号</a>
		</div>
		<sty-menu></sty-menu>
		<!-- 签到弹出框 -->
		<yd-popup v-model="signStatus" position="center" width="90%">
			<div class="user-sign-modal">
				<div class="close">
					<yd-icon name="error" size="0.4rem" color="#D9D9D9" @click.native="signStatus = false"></yd-icon>
				</div>
				<div class="title">签到有奖</div>

				<!-- 按周签到 -->
				<div class="sign-date"  style="display:none">
					
					<ul>
						<li v-for="n in 7" :key="n"  :class="n==2?'signed':'unsigned'" >
							<span class="circle">
								<span class="text">+{{n}} </span>
							</span>
							<span class="line"></span>
						</li>
					</ul>
				</div>
				<!-- 按月签到 -->
				<div class="sign-month-date">
					<ul class="sign-week">
						<li>天</li>
						<li>一</li>
						<li>二</li>
						<li>三</li>
						<li>四</li>
						<li>五</li>
						<li>六</li>
					</ul>
					<ul class="sign-month">
						<li v-for="n in 30" :key="n">
							<span :class="n==2?'signed':''">{{n}}</span>
						</li>
					</ul>
				</div>

				<div class="sign-points">
					<div class="points-name">总积分</div>
					<div class="points">+106</div>
				</div>
				<div class="sign-desc">
					每连续签到7天，可得26积分，并可获得 <span class="link">抽奖机会</span>
				</div>
				<!-- 签到按钮 -->
				<div class="sign-button">
					<yd-button size="large" class="sign-btn signed"  color="#fff" bgcolor="#169BD5">签到领大奖</yd-button>
				</div>
			</div>
		</yd-popup>
	</section>
</template>

<script>
	import Qs from "qs";
	import {
		login,
		login_state,
		logout
	} from "../../../tool/login";
	import {
		preious
	} from '../../../tool/history';
	import MenuBar from "@/common/menu";
	import {
		Env
	} from "../../mixins/env"
	import {
		Auth
	} from "../../mixins/auth"
	import {
		Base
	} from "../../mixins/base"
	export default {
		name: "User",
		mixins: [Env, Auth, Base],
		components: {
			"sty-menu": MenuBar
		},
		data() {
			return {
				userImg: require("../../assets/img/headerr.jpg"),
				userName: "",
				phone: "",
				list: "",
				number: "0",
				coupanNum: "0",
				complaints: '',
				pending_payment: "",
				pending_delivery: "",
				goods_to_be_received: "",
				completed_over: "",
				after_sale: "",
				invitationNum: 0,
				signStatus: false
			};
		},
		watch: {
			number(val, oldVal) {
				return (this.number = val);
			}
		},
		created() {},
		mounted() {},
		methods: {
			/**
			 * 登录成功后操作
			 */
			afterLoginOK(res) {
				let _this = this;
				_this.userInfoGet();
				_this.suggest();
				_this.personalNum();
			},
			/**
			 * 登录失败后操作
			 */
			afterLoginFail(err) {
				let _this = this;
				_this.tokenLogin();
				let state = login_state();
				let user_id = state.user_id || 0;
				if (user_id) {
					_this.userInfoGet();
					_this.suggest();
					_this.personalNum();
				}
			},
			suggest() {
				let _this = this;
				_this.$http.post('/api/v1/User/userProposal').then(function(response) {
					if (response.data.errno === '0') {
						_this.complaints = response.data.result.length;
					} else {
						_this.$dialog.loading.close();
						//_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
					}
				}).catch(function(error) {
					_this.$dialog.loading.close();
				});
			},
			personalNum() {
				let _this = this;
				_this.$http
					.post("/api/v1/Bidding/bubble")
					.then(function(response) {
						if (response.data.errno === "0") {
							_this.number = response.data.result;
						} else {
							_this.$dialog.loading.close();
						}
					})
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
			/**
			 * 微信公众号和小程序跳转跨端登录
			 */
			tokenLogin() {
				// 微信公众号转递过来的token 及  小程序传递过来的参数 user_id 和 token
				if (this.$route.query.user_id && this.$route.query.token) {
					login({
						user_id: this.$route.query.user_id,
						token: this.$route.query.token
					});
				}
			},
			userInfoGet() {
				let state = login_state();
				let _this = this,
					_data = Qs.stringify({
						user_id: state.user_id
					});
				_this.$http
					.post("/api/v1/User/userInfo", _data)
					.then(function(response) {
						if (response.data.errno === "0") {
							if (!response.data.result.mobile) {
								_this.$router.push("/addMobile");
							}
							_this.userImg = response.data.result.user_img;
							_this.phone = response.data.result.mobile;
							_this.userName = response.data.result.name;
							_this.coupanNum = response.data.result.coupan_num;
							_this.pending_payment = response.data.result.pending_payment;
							_this.pending_delivery = response.data.result.pending_delivery;
							_this.goods_to_be_received = response.data.result.goods_to_be_received;
							_this.completed_over = response.data.result.completed_over;
							_this.after_sale = response.data.result.after_sale;
							_this.invitationNum = response.data.result.invitation_num;
						}
					})
			},
			signOut() {
				logout();
				this.$router.push("/login");
			},
			enterMyCoupon: function() {
				this.$router.push("/Coupons");
			},
			tapToInvite() {
				this.$router.push("/inviteList");
			},
			showCertificate() {
				window.location.href = this.$JD;
			},
			showSign() {
				console.log('签到');
				this.signStatus = !this.signStatus;
			}
		}
	};
</script>

<style>

</style>
