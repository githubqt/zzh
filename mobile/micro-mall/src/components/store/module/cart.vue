<template>
	<yd-layout class="cart-list">
		<!-- 头部 start -->
		<yd-navbar title="购物车" slot="navbar">
			<div @click="backGo" slot="left">
				<yd-navbar-back-icon></yd-navbar-back-icon>
			</div>
		</yd-navbar>
		<!-- 头部 end -->
		<!-- 购物车列表 start -->
		<yd-infinitescroll ref="infinitescrollDemo" class="scroll-box">
			<!-- 商品列表项  start-->
			<div class="merchant-list-item" slot="list" v-for="(item, index) in cartList.list" :key="index">
				<merchant :propMerchant="item" :propAll="isCheckAll" v-on:HandleCheckNum="HandleCheckNum" v-on:isReload="isReload" v-on:handleBuyNum="handleBuyNum"></merchant>
			</div>
			<span slot="doneTip">没有更多记录啦</span>
		</yd-infinitescroll>
		<!-- 购物车列表 end  -->
		<!-- 底部按钮 -->
		<yd-flexbox class="cart-button">
			<yd-flexbox-item class="cart-button-left">
				<yd-checkbox shape="circle" color="#dc2821" @click.native="checkAll" v-model="isCheckSelf">
					全选 
				</yd-checkbox>
			</yd-flexbox-item>
			<yd-flexbox-item  style="flex:2">
				<div class="text-center sale-price" >
					合计：￥{{totalPrice}}
				</div>
			</yd-flexbox-item>
			<yd-flexbox-item class="cart-button-right">
				<yd-button size="large" :type="!checkNum?'disabled':'danger'" class="no-radius" @click.native="payGo()">
					结算({{checkNum}})
				</yd-button>
			</yd-flexbox-item>
		</yd-flexbox>
	</yd-layout>
</template>

<script>
	import Qs from "qs";
	import {
		sortBy,
		forEach,
		remove
	} from "lodash";
	import {
		Auth
	} from "../../../mixins/auth"
	import CartMerchant from "@/components/store/module/merchant"
	export default {
		name: "Cart",
		mixins: [Auth],
		components: {
			'merchant': CartMerchant
		},
		data() {
			return {
				cartList: '',
				cartIds: [],
				isCheckAll: false,
				isCheckSelf: false,
				cartIds: [],
				checkNum: 0,
				mData: {},
				buyNum: {},
				totalPrice: 0
			};
		},
		methods: {
			afterLoginOK() {
				this.cartListGet();
			},
			backGo() {
				window.history.length > 1 ?
					this.$router.go(-1) :
					this.$router.replace('/')
			},
			/**
			 * 获取购物车商品
			 */
			cartListGet() {
				let _this = this;
				_this.$dialog.loading.open("很快加载好了");
				_this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.reInit');
				_this.$http
					.post("/api/v1/Cart/cartList")
					.then(function(response) {
						if (response.data.errno === "0") {
							_this.cartList = response.data.result;
							_this.sortBy();
							_this.calcPrice();
							_this.$nextTick(function() {
								_this.$dialog.loading.close();
							});
						} else {
							_this.$dialog.loading.close();
						}
						_this.$refs.infinitescrollDemo.$emit('ydui.infinitescroll.finishLoad');
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
			payGo() {
				let _this = this;
				this.$router.push({
					name: "OrderInfo",
					query: {
						product_id: this.cartIds.join(',')
					}
				});
			},
			// 判断是否可选
			canCheck(item) {
				return this.isProductValid(item) && this.hasStock(item);
			},
			//商品是否有效（下架）
			isProductValid(item) {
				switch (item.product_from) {
					case "自营":
						if (item.on_status == 2) {
							return true;
						}
						break;
					case "供应":
						if (item.channel_on_status == 2) {
							return true;
						}
						break;
					default:
						return false;
						break;
				}
				return false;
			},
			//是否有库存
			hasStock(item) {
				return item.stock > 0;
			},
			// 排序  不可选的排到后面
			sortBy() {
				let self = this;
				let list = sortBy(this.cartList.list, function(item) {
					sortBy(item.productData, function(info) {
						return !self.canCheck(info);
					})
				});
				this.cartList.list = list;
			},
			checkAll() {
				this.isCheckAll = !this.isCheckSelf;
			},
			editNum(param) {
				var _this = this,
					_data = Qs.stringify({
						product_id: param.product_id,
						num: param.num
					});
				_this.$http
					.post("/api/v1/Cart/editNum", _data)
					.then(function(response) {
						if (response.data.errno === "0") {
							_this.$dialog.toast({
								mes: "修改成功",
								timeout: 500,
								icon: "success"
							});
						} else {
							_this.$dialog.toast({
								mes: response.data.errmsg,
								timeout: 1500,
								icon: "error"
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
			// 更新购物车
			isReload(item) {
				this.cartIds = remove(this.cartIds,(v)=>{
					return v != item.cart_id
				});
				let list = remove(this.cartList.list,(v)=>{
					v.productData = remove(v.productData,(vv)=>{
						return vv.cart_id != item.cart_id
					});
					return v.productData.length;
				});
				this.cartList.list = list;
				this.cartList.total--;
				this.calcPrice();
			},
			// 更新购物车数量
			handleBuyNum(item) {
				this.buyNum[item[0]] = item[1];
				this.calcPrice();
			},
			// 处理选中项
			HandleCheckNum(item) {
				this.mData[item[0]] = item[1];
				let total = 0, // 每组总数
					checked = 0, // 每组选中数
					ids = []; // 每组商品ID
				for (let i in this.mData) {
					total += this.mData[i].total;
					checked += this.mData[i].check;
					if (this.mData[i].ids.length) {
						this.mData[i].ids.forEach((v) => {
							ids.push(v);
						});
					}
				}
				this.checkNum = checked;
				this.cartIds = ids;
				if (this.checkNum == total && parseInt(this.cartList.total) == total) {
					if(!this.isCheckSelf){
						this.isCheckSelf = true;
					}
				}else{
					if(this.isCheckSelf){
						this.isCheckSelf = false;
					}
				}
				this.calcPrice();
			},
			calcPrice() {
				this.totalPrice = 0;
				if (this.cartIds.length && this.cartList.total > 0) {
					this.cartIds.forEach((v) => {
						this.totalPrice += parseFloat(this.buyNum[v].total);
					});
				}else{
					this.checkNum = 0;
				}
				this.totalPrice =  this.totalPrice.toFixed(2);
			}
		}
	};
</script>

<style>

</style>
