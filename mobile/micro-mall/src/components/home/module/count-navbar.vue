<template>
	<yd-flexbox class="count-navbar">
		<yd-flexbox-item v-if="showFullpage == false">
			<yd-flexbox>
				<yd-flexbox-item>
					<yd-icon custom name="liulan"></yd-icon>
					<div class="count-navbar-num">{{views || 0}}</div>
				</yd-flexbox-item>
				<yd-flexbox-item>
					<yd-icon name="like"></yd-icon>
					<div class="count-navbar-num"> {{likes || 0}}</div>
				</yd-flexbox-item>
			</yd-flexbox>
		</yd-flexbox-item>
		<yd-flexbox-item v-if="showFullpage == false">
			<yd-flexbox>
				<yd-flexbox-item class="count-navbar-link" @click.native="contact()">
					<yd-icon custom name="ke_fu"></yd-icon>
					<div>联系我们</div>
				</yd-flexbox-item>
				<yd-flexbox-item class="count-navbar-link" @click.native="showSearch()">
					<yd-icon custom name="sousuo"></yd-icon>
					<div>搜索</div>
				</yd-flexbox-item>
				<yd-flexbox-item class="count-navbar-link" @click.native="company()">
					<yd-icon name="home-outline"></yd-icon>
					<div>公司介绍</div>
				</yd-flexbox-item>
			</yd-flexbox>
		</yd-flexbox-item>
		<yd-flexbox-item v-if="showFullpage" style="background-color:#efeff4;">
			<yd-flexbox>
				<yd-flexbox-item>
					<yd-search cancel-text="搜索" v-model="kw" fullpage :on-submit="submitHandler" :on-cancel="submitHandler"></yd-search>
				</yd-flexbox-item>
				<div>
					<yd-button @click.native="cancelHandler()" type="hollow">取消</yd-button>
				</div>
			</yd-flexbox>
		</yd-flexbox-item>
	</yd-flexbox>
</template>
<script>
	export default {
		name: "CountNavbar",
		props: ["logo", "title", "views", "likes"],
		data() {
			return {
				showFullpage: false,
				kw: ''
			};
		},
		methods: {
			contact() {
				this.$router.push("/contact");
			},
			company() {
				this.$router.push("/about");
			},
			showSearch() {
				this.showFullpage = true;
			},
			cancelHandler(value) {
				this.showFullpage = false;
			},
			submitHandler(value) {
				this.$router.push({
					name: 'Store',
					query: {
						name: value || this.kw
					}
				})
			}
		}
	};
</script>
<style scoped>
	@import "../../../assets/css/components/home/module/countnavbar";
</style>
