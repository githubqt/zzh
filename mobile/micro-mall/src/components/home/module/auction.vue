<template>
	<section class="auction-container">
		<yd-cell-group class="auction-cell-group">
	        <yd-cell-item arrow type="link" href="bidding">
	            <div slot="left">
	            	<h3 class="auction-item-title">在线拍卖</h3>
	            	<span style="color: #999999;">Online auction</span>
	            	
	            </div>
	              <span slot="right">更多</span>
	        </yd-cell-item>
	    </yd-cell-group>
	   
	    <div class="auction-slide-box">
	        <router-link class="auction-slide-item" v-for="(item, index) in auction" :key="index" :id="item.id" 
	           :to="{name:'Product',query:{id:item.id,product_id:item.product_id}}">
	        	<div class="auction-slide-item-image">
	        		<img v-lazy="item.logo_url" :onerror="errorImg" :alt="item.name">
	        	</div>
	        	<h3 class="auction-slide-title">{{item.product_name}}</h3>
	        	<span class="auction-slide-market">起拍价: <em>¥</em>{{item.start_price}}</span>
	        	<span class="auction-slide-sale">当前价: <em>¥</em>{{item.total_price}}</span>
	        </router-link>
	    </div>
	    
	</section>
</template>

<script>
export default { 
name: 'Auction',
 props: ['auction'],
 data() {
		return { 
		auctionList: [],
		errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"' }
	},
	watch: {
		auctionList(val, oldVal) { 
		this.auction = val 
		}
	} 
}
</script>

<style>
.home-cell-group {
	margin: .12rem 0 0 0;
}
.home-grids-item {
	padding: .24rem;
}
.home-grids-item-content {
	display: flex;
	height: 2rem;
	align-items: flex-start;
	justify-content: center;
}
.home-grids-content-left {
	width: 40%;
	flex-grow: 0;
}
.home-grids-content-right {
	width: 60%;
	flex-grow: 0;
	align-self: flex-end;
}
.home-grids-content-right img {
	width: 100%;
	height: auto;
}
.auction-cell-group {
	margin: .12rem 0 0 0;
}

.auction-slide-box{
    display: -webkit-box;
    overflow-x: scroll;
    overflow-y: hidden;
    -webkit-overflow-scrolling:touch;
    background: #ffffff;
}
.auction-slide-title {
	font-size: .3rem;
	padding: .12rem 0 0 0;
	overflow: hidden;
	text-overflow:ellipsis;
	white-space: nowrap; 
}
.auction-slide-item{
	position: relative;
	display: block;
	width: 3.8rem;
	padding: .24rem;
}
.auction-slide-item:before {
	content: '';
	position: absolute;
	right: 0;
	bottom: 0;
	left: 0;
	height: 1px;
	background: #b2b2b2;
	transform: scale3d(1, .5, 1);
}
.auction-slide-item:after {
	content: '';
	position: absolute;
	top: .12rem;
	right: 0;
	bottom: .12rem;
	width: 1px;
	background: #b2b2b2;
	transform: scale3d(.5, 1, 1);
}
.auction-slide-item:last-child:after {
	display: none;
}
.auction-slide-item-image {
	width: 100%;
	height: 2rem;
	margin-top: .12rem;
	overflow: hidden;
    text-align: right;
	vertical-align: middle;
}
.auction-slide-item-image img {
	width: 100%;
	height: 100%;
	object-fit:cover;
}
.auction-slide-market {
    color: #1a191e;
    font-size: .26rem;
    width: 100%;
    padding: .1rem 0;
}
.auction-slide-market:after {
	content: '';
	top: 50%;
	right: 0;
	left: 0;
	height: 1px;
	background-color: #1a191e;
	transform: scale3d(1, .5, 1);
}
.auction-slide-sale {
	display: inline-block;
    color: #ea3d39;
    font-size: .3rem;
    width: 100%;
}

</style>
