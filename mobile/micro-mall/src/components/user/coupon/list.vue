<template>
	<section class="coupon-panel">
		<yd-infinitescroll :callback="getCouponsList" ref="infinitescrollDemo" class="coupons-loadList-box">
			<yd-list slot="list">
				<div class="coupon-item" v-for="(item, key ) in couponList" :key="key">
					<yd-flexbox class="coupon-yd-flexbox" @click.native="showDetail(item)">
						<yd-flexbox-item class="coupon-item-left">
							<div class="coupon-price" v-if="item.pre_type == '1'"><span class="price-type">￥</span>{{ parseInt(item.pre_value)}} </div>
							<div class="coupon-price" v-if="item.pre_type == '2'">{{item.pre_value}} <span class="price-type">折</span></div>
							<div class="coupon-range">{{item.sill_txt | sillFilter}}</div>
						</yd-flexbox-item>
						<yd-flexbox-item class="coupon-item-right">
							<yd-flexbox>
								<yd-flexbox-item>
									<div class="coupon-type">
										<yd-badge type="danger" shape="square">{{item.use_type_txt | typeFilter}}</yd-badge>
										<span class="text-ellipsis">{{item.c_name}}</span>
									</div>
									<!-- <span class="status" v-if="status != '1'">{{item.status_txt}}</span> -->
								</yd-flexbox-item>
							</yd-flexbox>
							<yd-flexbox>
								<yd-flexbox-item>
									<div v-if="item.c_status == 4" class="coupon-expire">{{item.c_status_txt}}</div>
									<div  v-else class="coupon-expire">{{item.time_txt}}</div>
								</yd-flexbox-item>
								<yd-flexbox-item class="text-center">
									<yd-button type="danger" shape="circle" v-if="status == '1'" v-on:click="pageJump(item)">立刻使用</yd-button>
								</yd-flexbox-item>
							</yd-flexbox>
						</yd-flexbox-item>
					</yd-flexbox>
					<!-- 适用说明 -->
					<div class="coupon-product" v-if="status == '1'">
						<div class="coupon-product-list">
							<div class="product-list-title" :ref="'plTitle_'+key" v-on:click="showall(key)" v-if="item.product.total != 0">
								<span class="be-suitable">适用范围: 部分商品</span>
								<img src="../../../assets/img/unfold.png" alt="" :ref="'child_img_'+key" class="bottom-img" style="display:block">
								<img src="../../../assets/img/fold.png" alt="" :ref="'child_img_un_'+key" class="bottom-img" style="display:none">
							</div>
							<div class="product-list-title" v-else>
								<span class="be-suitable">适用范围: 全店通用</span>
							</div>

							<div v-show="isShow" ref="child" :data-coupon-key="key" class="product-list-item">
								<yd-flexbox v-for="(product, k) in item.product.list" :key="k">
									<yd-flexbox-item>
										<span class="item-name">【{{product.name}}】</span>
									</yd-flexbox-item>
									<yd-flexbox-item>
										<div class="click-use" v-on:click="productJump(product.id)">点击使用</div>
									</yd-flexbox-item>
								</yd-flexbox>
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
	 import {
        Auth
    } from "../../../mixins/auth"
	export default {
		name: "CouponList",
		mixins:[Auth],
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
			this.getCouponsList();
		},
		methods: {
			showall: function(index) {
				let iconImg = 'child_img_' + index;
				let iconImgUn = 'child_img_un_' + index;
				let plTitle = 'plTitle_' + index;
				if (this.$refs.child[index].style.display === "none") {
					this.$refs.child[index].style.display = "block";
					this.$refs[iconImg][0].style.display = "none";
					this.$refs[iconImgUn][0].style.display = "block";
					this.$refs[plTitle][0].classList.add('show-border-bottom')
				} else {
					this.$refs.child[index].style.display = "none";
					this.$refs[iconImg][0].style.display = "block";
					this.$refs[iconImgUn][0].style.display = "none";
					this.$refs[plTitle][0].classList.remove('show-border-bottom')
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
                that.$dialog.loading.open('很快加载好了');
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
                            console.log(that.couponList);
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
                            that.$dialog.loading.close();
						} else if (res.data.errno == "50006") {
                            that.$dialog.loading.close();
							that.$dialog.confirm({
								title: "系统提示",
								mes: "登录状态失效，重新登录？",
								opts: () => {
									that.$router.replace("/login");
								}
							});
						} else {
                            that.$dialog.loading.close();
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
			},
			/**
			 * 显示商品优惠券、店铺优惠券详细
			 */
			showDetail: function(item) {
				// console.log('TCL: item', item);
				// console.log('TCL: item.use_type', item.use_type);
				if(item.c_status == 3 || item.c_status == 4){
                    this.$dialog.toast({
                        mes: '优惠卷已过期！',
                        timeout: 1500,
                        icon: "error"
                    });
                    return;
				}

				if (parseInt(item.use_type) === 2) {
					this.$router.push("/couponShop?coupon_id=" + item.coupan_id+"&status="+item.status);
				} else {
					this.$router.push("/couponCommodity?coupon_id=" + item.coupan_id+"&status="+item.status);
				}
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
					//return val + "使用";
                    return val ;
				}
			}
		}
	};
</script>


<style scoped>

</style>
