<template>
	<yd-flexbox class="middle-nav">
		<yd-flexbox-item @click.native="snapUp()" class="yellow"  >
			<yd-icon custom name="miaosha"></yd-icon>
			<div>限时秒杀</div>
		</yd-flexbox-item>

		<yd-flexbox-item  @click.native="groupUp()" class="yellow"  >
			<yd-icon custom name="tuan"></yd-icon>
			<div>拼团</div>
		</yd-flexbox-item>

		<yd-flexbox-item @click.native="bidding()" class="yellow"  >
			<yd-icon custom name="paimaichui"></yd-icon>
			<div>竞价拍</div>
		</yd-flexbox-item>

		<yd-flexbox-item  @click.native="stages()" class="disabled-icon">
			<yd-icon custom name="fenqi" ></yd-icon>
			<div>分期</div>
		</yd-flexbox-item>

	</yd-flexbox>
</template>
<script>
	export default {
		name: "MiddleNav",
		props: [],
		data() {
			return {};
		},

        methods: {
            bidding() {
                this.$router.push("bidding");
            },
            groupUp() {
                this.$router.push("groupUp");
            },
            snapUp() {
                this.$router.push("snapUp");
            },
            stages() {
                this.$dialog.toast({ mes: "正在建设中...", timeout: 1500 });
			},
            groupsaleGet() {
                return this.$http.post("/api/v1/Group/list");
            },


        }
	};
</script>
<style scoped>
	@import "../../../assets/css/components/home/module/middlenav";
</style>
