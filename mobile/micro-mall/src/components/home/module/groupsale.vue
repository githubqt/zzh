<template>
	<section class="groupsale-container">
		<yd-cell-group class="groupsale-cell-group">
			<yd-cell-item arrow type="link" href="groupUp">
				<div slot="left">
					<h3 class="home-item-title">多人拼团</h3>
					<span style="color: #999999;">Group sale</span>
				</div>
				<span slot="right">更多</span>
	        </yd-cell-item>
	    </yd-cell-group>
	    <div class="groupsale-slide-box">
	        <router-link class="groupsale-slide-item" v-for="(item, index) in groupsaleList" :key="index" :id="item.id" 
	           :to="{name:'GroupDetails',query:{id:item.id}}">
	        	<div class="groupsale-slide-item-image">
	        		<img v-lazy="item.logo_url" :onerror="errorImg" :alt="item.product_name">
	        	</div>
	        	<h3 class="groupsale-slide-title">{{item.product_name}}</h3>
	        	<span class="groupsale-slide-market">原价: <em>¥</em>{{item.sale_price}}</span>
	        	<span class="groupsale-slide-sale">拼团价：<em>¥</em>{{item.group_price}}</span>
	        </router-link>
	    </div>
	</section>
</template>

<script>
export default {
	name: 'groupsale',
	props: ['groupsale'],
	data() {
        return { groupsaleList: [], errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"'}
    },
	watch: {
        groupsale(val, oldVal) { this.groupsaleList = val }
    }
}
</script>

<style>
.groupsale-cell-group {
	margin: .12rem 0 0 0;
}
.groupsale-slide-box{
    display: -webkit-box;
    overflow-x: scroll;
    overflow-y: hidden;
    -webkit-overflow-scrolling:touch;
    background: #ffffff;
}
.groupsale-slide-title {
	font-size: .3rem;
	padding: .12rem 0 0 0;
	overflow: hidden;
	text-overflow:ellipsis;
	white-space: nowrap; 
}
.groupsale-slide-item{
	position: relative;
	display: block;
	width: 2.8rem;
	padding: .24rem;
}
.groupsale-slide-item:before {
	content: '';
	position: absolute;
	right: 0;
	bottom: 0;
	left: 0;
	height: 1px;
	background: #b2b2b2;
	transform: scale3d(1, .5, 1);
}
.groupsale-slide-item:after {
	content: '';
	position: absolute;
	top: .12rem;
	right: 0;
	bottom: .12rem;
	width: 1px;
	background: #b2b2b2;
	transform: scale3d(.5, 1, 1);
}
.groupsale-slide-item:last-child:after {
	display: none;
}
.groupsale-slide-item-image {
	width: 100%;
	height: 2rem;
	margin-top: .12rem;
	overflow: hidden;
    text-align: right;
	vertical-align: middle;
}
.groupsale-slide-item-image img {
	width: 100%;
	height: 100%;
	object-fit:cover;
}
.groupsale-slide-market {
	position: relative;
    color: #1a191e;
    font-size: .26rem;
    width: 100%;
    padding: .1rem 0;
}
.groupsale-slide-market:after {
	position: absolute;
	content: '';
	top: 50%;
	right: 0;
	left: 0;
	height: 1px;
	background-color: #1a191e;
	transform: scale3d(1, .5, 1);
}
.groupsale-slide-sale {
	display: inline-block;
    color: #ea3d39;
    font-size: .3rem;
    width: 100%;
}
</style>