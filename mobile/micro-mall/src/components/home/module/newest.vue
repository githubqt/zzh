<template>
	<div class="home-newest">
		<yd-cell-group>
			<yd-cell-item arrow type="link" :href="{name:'Store',query:{sort:'now_at',order: 'DESC'}}">
				<div slot="left">
					<h3>最新上架</h3>
				</div>
				<span slot="right"></span>
			</yd-cell-item>
		</yd-cell-group >
		<div class="products" >
			<div class="item clearfix" v-for="(item, key) in newarrivalList" :key="key" v-if="key<5" :id="item.id" @click="examine(item.id)">
				<img slot="img" v-lazy="item.logo_url" >
				<div class="right">
					<h3 class="title">{{item.name}}</h3>
					<div class="price">
						<span class="new"><em>¥</em>{{item.sale_price}}</span>
						<span class="old"><em>¥</em>{{item.market_price}}</span>
					</div>
				</div>
			</div>

		</div>
	</div>
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
        },
        methods: {
            examine(id){
                this.$router.push("/details?id="+id);
			}
		}
	}
</script>

<style scoped>
</style>