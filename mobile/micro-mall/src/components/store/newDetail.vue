<template>
	<section class="details-container">
		<yd-navbar title="商品详情" class="fixed-header">
			<div @click="backGo" slot="left">
				<yd-navbar-back-icon></yd-navbar-back-icon>
			</div>
			<!--<yd-icon slot="right" size=".45rem"  @click.native="promote(detailsData.id)" name="share1"></yd-icon>-->
		</yd-navbar>
		<div class="detail-medias">
			<div class="show-pic">
				<yd-slider>
					<yd-slider-item v-for="(item, index) in detailsData.imglist" :key="index">
						<a href="javascript:;" :id="item.id">
							<img v-lazy="item.img_url" :onerror="errorImg" :alt="item.img_type">
						</a>
					</yd-slider-item>
				</yd-slider>
			</div>
			<!-- 商品视频 -->
			<div class="detail-medias-tabs-box" v-show="isVideo == true && detailsData.video_url != null">
				<div class="play">
					<div class="play-btn" @click="playMedia(detailsData.video_url)">
						<yd-icon name="play" size="0.4rem"></yd-icon> <span>视频</span></div>
				</div>
				<!-- <div class="detail-medias-tabs" >
						<div class=" mtab mtab-l">视频</div>
						<div class=" active mtab mtab-r">图片</div>
					</div> -->
			</div>
		</div>
		<section class="details-header">
			<!-- 商品名称 、收藏量-->
			<yd-flexbox>
				<yd-flexbox-item style="flex:4" align="top">
					<h3 class="details-header-title">{{detailsData.name}}</h3>
				</yd-flexbox-item>
				<yd-flexbox-item class="lb">
					<div class="detail-favor-num">
						<yd-icon :name="detailsData.is_like==='0'?'like-outline':'like'" :color="detailsData.is_like==='0'?'#000':'#ef4f4f'" class="details-btn-icon" @click.native="collectGo(detailsData.is_like)"></yd-icon>
						<div class="favor-num">
							{{detailsData.collect_num}}
						</div>
					</div>
				</yd-flexbox-item>
			</yd-flexbox>
			<!-- 公价 -->
			<yd-flexbox>
				<yd-flexbox-item>
					<div class="details-header-price">
						<span class="market-price">公价: <em>￥</em>{{detailsData.market_price}}</span>
					</div>
				</yd-flexbox-item>
			</yd-flexbox>
			<!-- 秒杀价、商品价、浏览量 -->
			<yd-flexbox>
				<yd-flexbox-item>
					<div class="details-header-price emphasis" v-if="detailsData.seckill.length!==0">
						<em>￥</em>{{detailsData.seckill.seckill_price}}
					</div>
					<div class="details-header-price emphasis" v-else><em>￥</em>{{detailsData.sale_price}}</div>
				</yd-flexbox-item>
				<!-- 浏览量 -->
				<yd-flexbox-item>
					<div class="view-num" style="color:#ccc">{{detailsData.browse_num}}人已浏览</div>
				</yd-flexbox-item>
			</yd-flexbox>

			<!--距离 -->
			<yd-flexbox class="distance">
				<yd-flexbox-item>
					<yd-icon name="location" size="0.4rem" color="#dc2821"></yd-icon>  距离100m（北京市朝阳区）
				</yd-flexbox-item>
			</yd-flexbox>

			<!--鉴定 -->
			<yd-flexbox class="appraisal">
				<yd-flexbox-item>
					<div class="pcode">商品编码  {{detailsData.supplier_id}}   |   {{detailsData.self_code}}  </div>
					<div class="pcode-tips" v-show="detailsData.appraisal_status == 2">本商品已经专业鉴定师鉴定为真品，并出具鉴定证书。 <span @click="showCertificate">点击查看</span> </div>
				</yd-flexbox-item>
			</yd-flexbox>



		</section>
		<section class="details-parameter" v-show="isVideo == true && detailsData.video_url != null">
			<div class="order">
				<span style="white-space:pre;">  </span><span class="line"></span>
				<span style="white-space:pre;">  </span><span class="txt">商品视频</span>
				<span style="white-space:pre;">  </span><span class="line"></span>
			</div>
			<div class="details-seckill-txt">
				<video :src="detailsData.video_url" controls="controls" width="100%" autoplay></video>
			</div>
		</section>

		<section class="details-parameter" v-show="detailsData.brand_description!==''">
			<div class="order">
				<span style="white-space:pre;">  </span><span class="line"></span>
				<span style="white-space:pre;">  </span><span class="txt">品牌介绍</span>
				<span style="white-space:pre;">  </span><span class="line"></span>
			</div>
			<div v-html="detailsData.brand_description" class="details-seckill-txt"></div>
		</section>

		<section class="details-parameter" v-show="detailsData.category_description!==''">
			<div class="order">
				<span style="white-space:pre;">  </span><span class="line"></span>
				<span style="white-space:pre;">  </span><span class="txt">分类介绍</span>
				<span style="white-space:pre;">  </span><span class="line"></span>
			</div>
			<div v-html="detailsData.category_description" class="details-seckill-txt"></div>
		</section>
		<section class="details-parameter" v-show="detailsData.attribute.length!==0">
			<div class="order">
				<span style="white-space:pre;">  </span><span class="line"></span>
				<span style="white-space:pre;">  </span><span class="txt">产品参数</span>
				<span style="white-space:pre;">  </span><span class="line"></span>
			</div>
			<yd-cell-group>
				<yd-cell-item class="details-parameter-content" v-for="(item, index) in detailsData.attribute" :key="index">
					<span slot="left"><span class="label">{{item.attribute_name}}</span></span>
					<span slot="right">{{item.attribute_value_name}}</span>
				</yd-cell-item>
			</yd-cell-group>
		</section>
		<section class="details-activity" v-show="detailsData.seckill.length!==0">
			<div class="order">
				<span style="white-space:pre;"></span><span class="line"></span>
				<span style="white-space:pre;"></span><span class="txt">商品活动</span>
				<span style="white-space:pre;"></span><span class="line"></span>
			</div>
			<yd-cell-group>
				<yd-cell-item class="details-parameter-content">
					<span slot="left">开始时间:</span>
					<span slot="right">{{detailsData.seckill.starttime}}</span>
				</yd-cell-item>
				<yd-cell-item class="details-parameter-content">
					<span slot="left">结束时间:</span>
					<span slot="right">{{detailsData.seckill.endtime}}</span>
				</yd-cell-item>
				<yd-cell-item class="details-parameter-content">
					<span slot="left">是否限购:</span>
					<span slot="right" v-if="detailsData.seckill.is_restrictions==='1'">不限购</span>
					<span slot="right" v-else>限购</span>
				</yd-cell-item>
				<yd-cell-item class="details-parameter-content" v-show="detailsData.seckill.is_restrictions==='2'">
					<span slot="left">限购个数:</span>
					<span slot="right">{{detailsData.seckill.restrictions_num}}</span>
				</yd-cell-item>
				<yd-cell-item class="details-parameter-content">
					<span slot="left">秒杀价格:</span>
					<span slot="right">{{detailsData.seckill.seckill_price}}</span>
				</yd-cell-item>
				<yd-cell-item class="details-parameter-content">
					<span slot="left">注意:</span>
					<span slot="right">未付款订单{{detailsData.seckill.order_del}}分钟后失效</span>
				</yd-cell-item>
			</yd-cell-group>
		</section>
		<section class="details-parameter">
			<div class="order">
				<span style="white-space:pre;">  </span><span class="line"></span>
				<span style="white-space:pre;">  </span><span class="txt">商品详情</span>
				<span style="white-space:pre;">  </span><span class="line"></span>
			</div>
			<div v-html="detailsData.introduction" class="details-seckill-txt"></div>
		</section>
		<div v-show="detailsData.is_return==1" class="details-seckill-txt">
			<span>不退货声明：</span>
			<div style="font-size: .1rem;">1. 由于绝当品的特殊性，购买后非质量问题不支持退换货;</div>
			<div style="font-size: .1rem;">2. 若因商品质量问题退货时，请先与卖家沟通;</div>
			<div style="font-size: .1rem;">3. 为保证商品安全，邮寄商品时请使用顺丰速运，请勿货到付款;</div>
			<div style="font-size: .1rem;">4. 卖家收到商品后，会对退货商品进行鉴定确认，确认无误后才可退款。</div>
		</div>
		<section class="details-parameter" v-show="supplierInfo.shop_instructions!==''">
			<div class="order">
				<span style="white-space:pre;">  </span><span class="line"></span>
				<span style="white-space:pre;">  </span><span class="txt">本店说明</span>
				<span style="white-space:pre;">  </span><span class="line"></span>
			</div>
			<div v-html="supplierInfo.shop_instructions" class="details-seckill-txt"></div>
		</section>
		<section class="details-parameter" v-show="supplierInfo.company_introduction!==''">
			<div class="order">
				<span style="white-space:pre;">  </span><span class="line"></span>
				<span style="white-space:pre;">  </span><span class="txt">公司介绍</span>
				<span style="white-space:pre;">  </span><span class="line"></span>
			</div>
			<div v-html="supplierInfo.company_introduction" class="details-seckill-txt"></div>
		</section>
		<!-- 底部按钮 -->
		<yd-flexbox class="details-button" v-if="detailsData.id">
			<div class="details-icon-box">
				<router-link to="/" class="miconfont micon-home details-btn-icon"></router-link>
				<router-link to="/cart" class="miconfont micon-shopping details-btn-icon">
					<i class="corner-mark" v-show="shoppingNum!=='0'">{{shoppingNum}}</i>
				</router-link>
				<div class="detail-kefu" @click="showKefu = true" v-show="supplierPhone != ''">
				<yd-icon custom name="ke_fu"></yd-icon>
			</div>
			</div>
			<!-- 非秒杀商品可加入购物车 -->
			<yd-flexbox-item v-show="detailsData.seckill.length===0">
				<yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="addCartGo" class="no-radius">加入购物车</yd-button>
			</yd-flexbox-item>
			<yd-flexbox-item>
				<yd-button size="large" type="danger" @click.native="payVerify" class="no-radius">立即购买</yd-button>
			</yd-flexbox-item>
		</yd-flexbox>
		<yd-flexbox class="details-button" v-if="detailsData.on_status == 1 && detailsData.is_channel_status != null">
			<yd-flexbox-item>
				<yd-button size="large" bgcolor="#ccc" color="#fff" class="no-radius">该商品已下架</yd-button>
			</yd-flexbox-item>
		</yd-flexbox>
		<!-- 确认商品信息 -->
		<yd-popup v-model="infoShow" position="center" width="80%">
			<yd-flexbox>
				<div class="info-left-img">
					<img v-lazy="detailsData.logo_url" :onerror="errorImg" :alt="detailsData.brand_name">
				</div>
				<yd-flexbox-item>
					<div style="font-size: 15px;">{{detailsData.name}}</div>
					<div style="font-size: 12px; padding-bottom: .4rem;">{{detailsData.brand_name}}</div>
					<div class="details-header-price emphasis" v-if="detailsData.seckill.length!==0">
						<em>￥</em>{{detailsData.seckill.seckill_price}}
					</div>
					<div class="details-header-price emphasis" v-else><em>￥</em>{{detailsData.sale_price}}</div>
				</yd-flexbox-item>
			</yd-flexbox>
			<yd-cell-group class="info-group">
				<yd-cell-item>
					<span slot="left">购买数量</span>
					<yd-spinner slot="right" v-if="detailsData.seckill.length!==0" :max="detailsData.seckill.restrictions_num" min="1" v-model="amount" />
					<yd-spinner slot="right" v-else :max="detailsData.stock" min="1" v-model="amount" />
				</yd-cell-item>
			</yd-cell-group>
			<div style="text-align: right; font-size: 13px;">库存: {{detailsData.stock}}件</div>
			<yd-button size="large" type="danger" @click.native="orderGo" class="no-radius">确认</yd-button>
		</yd-popup>
		<!-- 客服列表 -->
		<yd-popup v-model="showKefu" position="bottom" class="kefu-list" >
			<div class="kefu-list-inner">
				<div class="kefu-title">
					商家联系电话
				</div>
				<a :href="'tel:'+item+''" class="kefu-list-item" v-for="(item, key) in supplierPhone"  :key="key" @click="callPhone(key)" >
					<yd-icon name="phone2"></yd-icon>
					<span class="kefu-phone-number">{{item}}</span>
				</a>

			</div>
		</yd-popup>
	</section>
</template>

<script>
	import Sa from "../../../tool/wechatshare";
	import {
		logout
	} from "../../../tool/login";
	import Qs from "qs";
	import {locationinfo} from "../../../tool/location"
	export default {
		name: "newDetail",
		components: {},
		data() {
			return {
				infoShow: false,
				showKefu: false,
                isVideo: false,
				detailsData: {
					attribute: [],
					seckill: [],
					introduction: "",
					brand_description: "",
					category_description: ""
				},
				amount: 1,
				shareData: "",
				shoppingNum: "0",
				cartNum: 1,
				supplierInfo: {
					company_introduction: "",
				},
                supplierPhone:'',
				errorImg: 'this.src="' + require("../../assets/img/err.jpg") + '"'
			};
		},
		created() {
            locationinfo();
            // this.setResult();
            // this.share();
        },
		methods: {
			promote(e) {
				let _this = this;
				_this.$router.push("/promote?id=" + e);
			},
			backGo() {
				window.history.length > 1 ?
					this.$router.go(-1) :
					this.$router.push('/')
			},
			detailDataGet() {
				let _data = Qs.stringify({
					id: this.$route.query.id,
					u_id: localStorage.getItem("userId")
				});
				return this.$http.post("/api/v1/Product/detail", _data);
			},
			cartListGet() {
				let _data = Qs.stringify({
					user_id: localStorage.getItem("userId")
				});
				return this.$http.post("/api/v1/Cart/getNum", _data);
			},
			supplierGet() {
				return this.$http.post("/api/v1/Home/supplier");
			},
			setResult() {
				let _this = this;
				_this.$dialog.loading.open("很快加载好了");
				_this.$http
					.all([_this.detailDataGet(), _this.cartListGet(), _this.supplierGet()])
					.then(
						_this.$http.spread(function(d, c, b) {
							if (d.data.errno === "0") {
								_this.detailsData = d.data.result;
								_this.shareData = d.data.result;
                                _this.isVideo = true;
							}
							if (c.data.errno === "0") {
								_this.shoppingNum = c.data.result;
							} else {
								_this.$router.push("/user");
								_this.$dialog.toast({
									mes: c.data.errmsg,
									timeout: 1500,
									icon: "error"
								});
							}
							if (b.data.errno === "0") {
								_this.supplierInfo = b.data.result;
                                _this.supplierPhone = b.data.result.customer_tel;
							}
							_this.$nextTick(function() {
								_this.$dialog.loading.close();
							});
						})
					);
			},
			addCartGo() {
				let _this = this,
					_data = Qs.stringify({
						user_id: localStorage.getItem("userId")
					});
				_this.$dialog.loading.open("很快加载好了");
				_this.$http
					.post("/api/v1/User/isLogin", _data)
					.then(function(response) {
						if (response.data.errno === "0") {
							// 添加到购物车
							let cartData = Qs.stringify({
								user_id: localStorage.getItem("userId"),
								product_id: _this.$route.query.id,
								num: _this.cartNum
							});
							_this.$http
								.post("/api/v1/Cart/addCart", cartData)
								.then(function(response) {
									if (response.data.errno === "0") {
										_this.setResult();
										_this.$nextTick(function() {
											_this.$dialog.loading.close();
											_this.$dialog.toast({
												mes: "添加成功",
												timeout: 1500,
												icon: "success"
											});
										});
									} else {
										_this.$dialog.loading.close();
										_this.$dialog.toast({
											mes: response.data.errmsg,
											timeout: 1500,
											icon: "success"
										});
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
						} else {
							logout();
							_this.$dialog.loading.close();
							_this.$dialog.confirm({
								title: "提示",
								mes: "登录失效,请重新登录",
								opts: () => {
									_this.$router.push("/login");
								}
							});
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
			orderGo() {
				var _this = this;
				var max = _this.detailsData.stock;
				if (_this.detailsData.seckill.length !== 0) {
					if (_this.detailsData.seckill.is_restrictions === "2") {
						max = _this.detailsData.seckill.restrictions_num;
					}
				}
				if (_this.amount > max) {
					_this.$dialog.toast({
						mes: "库存不足",
						timeout: 500,
						icon: "error"
					});
					return;
				}
				_this.$router.push({
					name: "Order",
					query: {
						product_id: _this.detailsData.id,
						num: _this.amount
					}
				});
			},
			collectGo(isLike) {
				let _data,
					_this = this;
				if (!localStorage.getItem("userId")) {
					_this.$dialog.toast({
						mes: "请先登陆再收藏本商品",
						timeout: 1500,
						icon: "error"
					});
					_this.$router.push("/login");
					return;
				}
				if (isLike === "0") {
					_data = Qs.stringify({
						user_id: localStorage.getItem("userId"),
						product_id: _this.detailsData.id,
						type: 1
					});
					_this.$set(_this.detailsData, "is_like", "1");
					_this.detailsData.collect_num++;
				} else {
					_data = Qs.stringify({
						user_id: localStorage.getItem("userId"),
						product_id: _this.detailsData.id,
						type: 2
					});
					_this.$set(_this.detailsData, "is_like", "0");
					_this.detailsData.collect_num--;
				}
				_this.$http
					.post("/api/v1/User/userConcern", _data)
					.then(function(response) {
						if (response.data.errno === "0") {
							if (isLike === "0") {
								_this.$dialog.toast({
									mes: "收藏",
									timeout: 1500,
									icon: "success"
								});
							} else {
								_this.$dialog.toast({
									mes: "取消收藏",
									timeout: 1500,
									icon: "success"
								});
							}
						} else {
							_this.$dialog.loading.close();
							_this.$dialog.toast({
								mes: response.data.errmsg,
								timeout: 1500,
								icon: "success"
							});
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
			payVerify() {
				this.infoShow = true;
			},
			share() {
				let _this = this,
					_data = Qs.stringify({
						url: encodeURIComponent(location.href.split("#")[0])
					});
				_this.$http
					.post("/api/v1/weixin/getSingJsSign", _data)
					.then(function(response) {
						if (response.data.errno === "0") {
							Sa.weChat(
								_this.shareData.product_name,
								_this.shareData.describe,
								response.data.result.url,
								_this.shareData.logo_url,
								response.data.result.appId,
								response.data.result.timestamp,
								response.data.result.nonceStr,
								response.data.result.signature
							);
						} else {
							_this.$dialog.loading.close();
							//_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
						}
					})
					.catch(function(error) {
						_this.$dialog.loading.close();
						//_this.$dialog.toast({ mes: error, timeout: 1500, icon: 'error' });
					});
			},
			callPhone(phone) {
				this.showKefu = false;
			},
			playMedia(url) {
				this.$router.push(`/play?url=${url}&path=${this.$route.fullPath}`);
			},
            showCertificate() {
                window.location.href = this.$JD;
            }

		}
	};
</script>

<style>

</style>
