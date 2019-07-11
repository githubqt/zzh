<template>
	<section class="mbrand-flex-box">
		<div class="mbrand-grids-box">
			<yd-grids-group :rows="3" item-height="1.2rem">
				<yd-grids-item v-for="(item, index) in recommendList" :key="index" :id="item.id" @click.native="storeBrandGo(item.id)">
					<div slot="else" class="mbrand-image-box">
						<img v-lazy="item.logo_url" :onerror="errorImg" class="mbrand-content-image">
					</div>
				</yd-grids-item>
			</yd-grids-group>
			<div class="mbrand-brand-title">
				<span style="white-space:pre;"></span><span class="line"></span>
				<span style="white-space:pre;"></span><span class="txt">全部品牌</span>
				<span style="white-space:pre;"></span><span class="line"></span>
			</div>
			<yd-cell-group class="mbrand-cell-group" v-for="(value, key) in mbrandContent" :key="key" :id="'anchor-'+key">
				<div class="mbrand-cell-header">{{key=='null'?'0':key}}</div>
				<yd-cell-item v-for="(item, key) in value" :key="key" type="link" :href="{name:'Store',query:{brand_id:item.id}}" v-if="item.product_num>0">
					<span slot="left">{{item.name}} ({{item.product_num}})</span>
				</yd-cell-item>
			</yd-cell-group>
		</div>
		<ul class="mbrand-flex-menu">
			<li v-for="(value, key) in mbrandContent" :key="key">
				<a :href="'#anchor-'+key">{{key=='null'?'0':key}}</a>
			</li>
		</ul>
	</section>
</template>

<script>
	import Qs from 'qs'
	export default {
		name: 'mBrand',
		data() {
			return {
				recommendList: [],
				letterList: [],
				mbrandContent: [],
				errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"'
			}
		},
		created() {
			this.recommendListGet();
			this.mbrandContentGet();
		},
		mounted() {},
		methods: {
			recommendListGet() {
				let _this = this,
					_data = Qs.stringify({
						is_hit: 2
					});
				_this.$http.post('/api/v1/Brand/list', _data).then(function(response) {
					if (response.data.errno === '0') {
						for (let i in response.data.result) {
							if (i < 6) _this.recommendList.push(response.data.result[i]);
						}
					} else {
						_this.$dialog.toast({
							mes: response.data.errmsg,
							timeout: 1500,
							icon: 'error'
						});
					}
				}).catch(function(error) {
					_this.$dialog.toast({
						mes: error,
						timeout: 1500,
						icon: 'error'
					});
				});
			},
			mbrandContentGet() {
				let _this = this,
					_data = Qs.stringify({
						is_hit: 0
					}),
					_resultData = localStorage.getItem('loanListData');
				_this.$http.post('/api/v1/Brand/list', _data).then(function(response) {
					if (response.data.errno === '0') {
						let _arr = {};
						response.data.result.forEach(function(item, index) {
							let array = _arr[item['first_letter']] || [];
							array.push(item);
							_arr[item['first_letter']] = array;
						});
						_this.mbrandContent = _arr;
					} else {
						_this.$dialog.toast({
							mes: response.data.errmsg,
							timeout: 1500,
							icon: 'error'
						});
					}
				}).catch(function(error) {
					_this.$dialog.toast({
						mes: error,
						timeout: 1500,
						icon: 'error'
					});
				});
			},
			storeBrandGo(id) {
				this.$router.push({
					name: 'Store',
					query: {
						brand_id: id
					}
				});
			}
		}
	}
</script>
<style>
@import "../../../assets/css/components/brand/module/brand";
</style>
