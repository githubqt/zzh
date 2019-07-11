<template>
    <yd-layout class="recycling-container">
        <!-- 头部 start -->
        <yd-navbar :title="shopName" slot="navbar">
            <span slot="right" @click="logout">退出</span>
        </yd-navbar>
        <!-- 头部 end -->
        <!-- 回收的规则以及广告位 start -->
        <yd-slider autoplay="3000" v-show="isLoaded && slides.length">
            <yd-slider-item v-for="(item,i) in slides" :key="i">
                <a href="#">
                    <img v-lazy="item.cover">
                </a>
            </yd-slider-item>
        </yd-slider>
        <!-- 回收的规则以及广告位 end -->
        <!-- 回收类别 start -->
        <yd-infinitescroll :callback="loadList" ref="infinitescrollList" v-show="isLoaded && types.length">
            <div class="category-list" slot="list">
                <div class="category-item" v-for="(item,i) in types" :key="i" @click="createOrder(item)">
                    <img v-lazy="item.cover" alt="" class="category-img">
                    <div class="category-name">
                        <h5 class="chinese-name">{{item.name}}</h5>
                        <span class="english-name">{{item.english}}</span>
                    </div>
                </div>
            </div>
            <!-- 数据全部加载完毕显示 -->
            <span slot="doneTip">没有更多 数据啦~~</span>
        </yd-infinitescroll>
        <!-- 回收类别 end -->
        <recycling-menu></recycling-menu>
    </yd-layout>
</template>
<script>
    import RecyclingMenu from '@/components/recycling/common/recyclingMenu';
    import {
        adminLogin,
        adminLogout,
        getAdminState
    } from "../../../tool/login";
    import Api from "../../../tool/supplier";
    export default {
        name: "Recycling",
        data() {
            return {
                shopName: '',
                slides: [],
                types: [],
                listCount: 5,
                loginState: {},
                page: 1,
                isLoaded: false,
            };
        },
        components: {
            "recycling-menu": RecyclingMenu
        },
        mounted() {
            this.loginState = getAdminState();
            if (!this.loginState.token) {
                this.$router.replace('/recyclingLogin');
            } else {
                this.shopName = this.loginState.admin.supplier.shop_name;
                this.$dialog.loading.open("正在加载...");
                setTimeout(()=>{
                    this.mainLoad();
                },100)
            }
        },
        methods: {
            loadList() {
                const _this = this;
                // 上拉加载...
            },
            createOrder(item) {
                this.$router.push(`/createRecycling?pid=${item.id}`);
            },
            logout() {
                adminLogout();
                this.$router.replace('/recyclingLogin');
            },
            getSlide() {
                return this.$http.post(Api.home.slide);
            },
            getTypeList() {
                return this.$http.post(Api.home.type);
            },
            mainLoad() {
                let _this = this;
                _this.$http
                    .all([
                        _this.getSlide(),
                        _this.getTypeList(),
                    ])
                    .then(
                        _this.$http.spread(function(slide, type) {
                            try {
                                if (slide.data.errno === '0') {
                                    _this.slides = slide.data.result;
                                } else {
                                    throw "服务暂不可用";
                                }
                                if (type.data.errno === '0') {
                                    _this.types = type.data.result;
                                } else {
                                    throw "服务暂不可用";
                                }
                                _this.page++;
                                _this.isLoaded = true;
                                _this.$nextTick(function() {
                                    _this.$dialog.loading.close();
                                });
                            } catch (err) {
                                _this.$dialog.loading.close();
                                _this.$dialog.toast({
                                    mes: '服务暂不可用',
                                    timeout: 1500,
                                    icon: 'error'
                                });
                            }
                        })
                    ).catch((err) => {
						console.log("​mainLoad -> err", err)
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({
                            mes: '服务暂不可用',
                            timeout: 1500,
                            icon: 'error'
                        });
                    });
            }
        }
    }
</script>

