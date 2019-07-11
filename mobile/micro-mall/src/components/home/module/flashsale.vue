<template>
	<section class="flashsale-container">
		<yd-cell-group class="flashsale-cell-group">
			<yd-cell-item arrow type="link" href="snapUp">
				<div slot="left">
					<h3 class="home-item-title">限时抢购</h3>
					<span style="color: #999999;">Flash sale</span>
				</div>
				<div slot="left" class="flashsale-time-box">
					<yd-icon name="time" color="#e93b3a" size=".3rem"></yd-icon> {{flashSale.starttime}}
				</div>
				<span slot="right">更多</span>
			</yd-cell-item>
		</yd-cell-group>
		<router-link to="/snapUp" class="flashsale-content-box" :id="flashSale.id">
			<img v-lazy="flashSale.logo_url" :onerror="errorImg" :alt="flashSale.product_name">
		</router-link>
	</section>
</template>
<script>
export default {
	name: 'Flashsale',
	props: ['flashsale'],
	data() {
		return { flashSale: {}, errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"' }
	},
	watch: {
		flashsale(val, oldVal) { this.flashSale = val }
	}
}

</script>
<style>
.flashsale-time-box {
	color: #e93b3a;
	font-size: .3rem;
	padding-left: .24rem;
}
.flashsale-cell-group {
	margin: .12rem 0 0 0;
}
.flashsale-content-box {
	display: block;
	width: 100%;
	height: 3rem;
	overflow: hidden;
	text-align: center;
	vertical-align: middle;
}
.flashsale-content-box img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

</style>
