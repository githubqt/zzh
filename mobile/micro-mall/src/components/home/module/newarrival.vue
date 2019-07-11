<template>
	<section class="newarrival-container">
		<yd-cell-group class="newarrival-cell-group">
			<yd-cell-item arrow type="link" :href="{name:'Store',query:{sort:'now_at',order: 'DESC'}}">
				<div slot="left">
					<h3 class="newarrival-item-title">最新上架</h3>
					<span style="color: #999999;">New arrival</span>
				</div>
				<span slot="right">更多</span>
			</yd-cell-item>
		</yd-cell-group>
		<yd-list theme="2" slot="list">
			<yd-list-item v-for="(item, key) in newarrivalList"    :key="key" :id="item.id" type="link" :href={name:'Details',query:{id:item.id}}>
				<img slot="img" v-lazy="item.logo_url" >
				<span slot="title">{{item.name}}</span>


				<yd-list-other slot="other" class="newarrival-item">
					<yd-flexbox-item>
						<span class="market-price">公价: <em>¥</em>{{item.market_price}}</span>
					</yd-flexbox-item>
				</yd-list-other>

				<yd-list-other slot="other"  class="newarrival-item">
					<yd-flexbox-item class="sale-price">
						<span>
							<em>¥</em>{{item.sale_price}}
						</span>
					</yd-flexbox-item>
				</yd-list-other>

				<yd-list-other slot="other"  class="newarrival-item">
					<yd-flexbox-item>
						<div class="favor-num">
							收藏量 {{item.collect_num}}
						</div>
					</yd-flexbox-item>
					<yd-flexbox-item>
						<div class="view-num">浏览量 {{item.browse_num}}</div>
					</yd-flexbox-item>
				</yd-list-other>
			</yd-list-item>
		</yd-list>
	</section>
</template>

<script>
	export default {
		name: 'Newarrival',
		props: ['newarrival'],
		data() {
			return {
				newarrivalList: [],
			}
		},
		watch: {
			newarrival(val, oldVal) {
				this.newarrivalList = val
			}
		}
	}
</script>

<style>
	@import "../../../assets/css/components/home/module/newarrival";
</style>