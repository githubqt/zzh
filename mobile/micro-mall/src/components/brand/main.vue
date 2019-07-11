<template>
    <section class="brand-container">
    	<!-- header -->
    	<yd-search v-model="searchContent" placeholder="请输入商品名称" cancel-text="搜索" :on-submit="submitHandler" :on-cancel="submitHandler"/>
    	<!-- tab -->
    	<yd-tab active-color="#1a191e" class="brand-tab-container">
    		<!-- 品类 -->
	        <yd-tab-panel label="品类" class="brand-panel-box">
	        	<sty-category></sty-category>
	        </yd-tab-panel>
	        <!-- 品牌 -->
	        <yd-tab-panel label="品牌" class="brand-panel-box">
	        	<sty-brand></sty-brand>
	        </yd-tab-panel>
	    </yd-tab>

		<sty-menu></sty-menu>
    </section>
</template>

<script>
import mBrand from '@/components/brand/module/brand'
import Category from '@/components/brand/module/category'
import MenuBar from "@/common/menu";
export default {
	name: 'Brand',
	components: { 'sty-brand': mBrand, 'sty-category': Category,"sty-menu": MenuBar, },
	data() {
        return { searchContent: '' }
    },
    mounted() {},
    methods: {
    	submitHandler(value) { this.$router.push({name:'Store',query:{name: value}}) }
    }
}
</script>

<style>
@import "../../assets/css/components/brand/main";
</style>
