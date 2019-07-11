<template>
	<section class="recommend-container">
		<yd-cell-group class="recommend-cell-group">
	        <yd-cell-item>
	            <div slot="left">
	            	<h3 class="home-item-title">人气推荐</h3>
	            	<span style="color: #999999;">Popularity recommendation</span>
	            </div>
	        </yd-cell-item>
	    </yd-cell-group>
	    <div class="recommend-slide-box">
	        <router-link class="recommend-slide-item" v-for="(item, index) in recommendList" :key="index" :id="item.id" 
	           :to="{name:'Details',query:{id:item.id}}">
	        	<div class="recommend-slide-item-image">
	        		<img v-lazy="item.logo_url" :onerror="errorImg" :alt="item.name">
	        	</div>
	        	<h3 class="recommend-slide-title">{{item.name}}</h3>
	        	<span class="recommend-slide-market">公价: <em>¥</em>{{item.market_price}}</span>
	        	<span class="recommend-slide-sale"><em>¥</em>{{item.sale_price}}</span>
	        </router-link>
	    </div>
	</section>
</template>

<script>
export default {
	name: 'Recommend',
	props: ['recommend'],
	data() {
        return { recommendList: [], errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"'}
    },
	watch: {
        recommend(val, oldVal) { this.recommendList = val }
    }
}
</script>

<style>
.recommend-cell-group {
	margin: .12rem 0 0 0;
}
.recommend-slide-box{
    display: -webkit-box;
    overflow-x: scroll;
    overflow-y: hidden;
    -webkit-overflow-scrolling:touch;
    background: #ffffff;
}
.recommend-slide-title {
	font-size: .3rem;
	padding: .12rem 0 0 0;
	overflow: hidden;
	text-overflow:ellipsis;
	white-space: nowrap; 
}
.recommend-slide-item{
	position: relative;
	display: block;
	width: 2.8rem;
	padding: .24rem;
}
.recommend-slide-item:before {
	content: '';
	position: absolute;
	right: 0;
	bottom: 0;
	left: 0;
	height: 1px;
	background: #b2b2b2;
	transform: scale3d(1, .5, 1);
}
.recommend-slide-item:after {
	content: '';
	position: absolute;
	top: .12rem;
	right: 0;
	bottom: .12rem;
	width: 1px;
	background: #b2b2b2;
	transform: scale3d(.5, 1, 1);
}
.recommend-slide-item:last-child:after {
	display: none;
}
.recommend-slide-item-image {
	width: 100%;
	height: 2rem;
	margin-top: .12rem;
	overflow: hidden;
    text-align: right;
	vertical-align: middle;
}
.recommend-slide-item-image img {
	width: 100%;
	height: 100%;
	object-fit:cover;
}
.recommend-slide-market {
	position: relative;
    color: #1a191e;
    font-size: .26rem;
    width: 100%;
    padding: .1rem 0;
}
.recommend-slide-market:after {
	position: absolute;
	content: '';
	top: 50%;
	right: 0;
	left: 0;
	height: 1px;
	background-color: #1a191e;
	transform: scale3d(1, .5, 1);
}
.recommend-slide-sale {
	display: inline-block;
    color: #ea3d39;
    font-size: .3rem;
    width: 100%;
}
</style>