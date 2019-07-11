<template>
    <section class="store-container">
        <yd-search class="store-search-container" v-model="name" placeholder="请输入商品名称" cancel-text="搜索" :on-submit="submitHandler" :on-cancel="submitHandler" />
        <sty-screen :brandId="brand_id" :categoryId="category_id" v-on:screenId="screenId" v-on:clearId="clearId" v-on:screenSort="screenSort" v-on:multipointId="multipointId" />
        <yd-infinitescroll :callback="loadList" ref="infinitescrollDemo" class="store-loadList-box">
            <yd-list theme="2" slot="list" style="margin-bottom:0.3rem;">
                <yd-list-item v-for="(item, key) in storeList" :key="key">
                    <img slot="img" v-lazy="item.logo_url" @click="showDetails(item.id)">
                    <yd-flexbox slot="title">
                        <yd-flexbox-item style="flex:2" class="list-title">
                            {{item.name}}
                        </yd-flexbox-item>
                        <div class="list-like">
                            <yd-icon :name="item.is_like==='0'?'like-outline':'like'" :color="item.is_like==='0'?'#000':'#ef4f4f'" size="0.36rem"></yd-icon>
                        </div>
                        <span>{{item.collect_num}}</span>
                    </yd-flexbox>
                    <yd-list-other slot="other" class="price-item">
                        <yd-flexbox-item class="sale-price">
                            ¥{{item.sale_price}}
                        </yd-flexbox-item>
                    </yd-list-other>
                    <yd-list-other slot="other" class="view-item">
                        <yd-flexbox-item class="market-price">
                            ￥{{item.market_price}}
                        </yd-flexbox-item>
                        <yd-flexbox-item>
                            <div class="view-num">{{item.browse_num}}人浏览</div>
                        </yd-flexbox-item>
                    </yd-list-other>
                    <!-- 分期 退货标识-->
                    <yd-list-other slot="other" class="badge-item">
                        <!--<yd-flexbox-item >-->
                        <!--<yd-badge shape="square" type="danger">支持分期</yd-badge>-->
                        <!--</yd-flexbox-item>-->
                        <yd-flexbox-item v-show="item.appraisal_status==2">
                            <yd-badge shape="square" type="warning">鉴定证书</yd-badge>
                        </yd-flexbox-item>
                        <yd-flexbox-item v-show="item.is_return == 1">
                            <yd-badge shape="square" type="warning">不支持退货</yd-badge>
                        </yd-flexbox-item>
                    </yd-list-other>
                    <!-- 位置-->
                    <yd-list-other slot="other" class="position-item " @click.native="showMap(item.multi_point_data)">
                        <yd-flexbox-item align="top" style="flex:1" v-show="item.multi_point_data != null">
                            <yd-icon custom name="weizhi" size="0.4rem"></yd-icon>
                        </yd-flexbox-item>
                        <yd-flexbox-item align="center" style="flex:5" class="text-ellipsis" v-if="item.multi_point_data != null  ">
                            <em v-if="item.multi_point_data!=null && item.multi_point_data.distance != null"> 距离{{item.multi_point_data.distance}}</em>（<em v-if="item.multi_point_data!=null && item.multi_point_data.address != null">{{item.multi_point_data.address}}</em>）
                        </yd-flexbox-item>
                        <yd-flexbox-item align="center" style="flex:5" class="text-ellipsis" v-else>
                            <em>暂只支持线上销售</em>
                        </yd-flexbox-item>
                    </yd-list-other>
                </yd-list-item>
            </yd-list>
            <!-- 数据全部加载完毕显示 -->
            <span slot="doneTip" v-if="category_id">
    	                        <span v-if="storeList.length == 0">本分类目前无商品</span>
            <span v-else>本分类没有更多商品啦</span>
            </span>
            <span slot="doneTip" v-else>
    	                        <span v-if="storeList.length == 0">本品牌目前无商品</span>
            <span v-else>本品牌没有更多商品啦</span>
            </span>
        </yd-infinitescroll>
        <!-- 悬浮按钮 -->
        <section class="shopping-cart-box">
            <router-link to="/cart" class="miconfont micon-shopping shopping-btn-icon">
                <i class="corner-mark" v-show="shopping_num!=='0'">{{shopping_num}}</i>
            </router-link>
        </section>
        <sty-menu></sty-menu>
    </section>
</template>

<script>
    import Qs from "qs";
    import Screen from "@/components/store/module/screen";
    import MenuBar from "@/common/menu";
    export default {
        name: "Store",
        components: {
            "sty-screen": Screen,
            "sty-menu": MenuBar
        },
        data() {
            return {
                searchVal: "",
                currentIndex: 3,
                page: 1,
                rows: 10,
                storeList: [],
                name: "",
                brand_id: "",
                category_id: "",
                coupan_id: "",
                order: "",
                sort: "",
                shopping_num: "0",
                multi_point_id: '',
                longitude: 0, //经度
                latitude: 0, //纬度
                city: '',
                meter: '',
                list: []
            };
        },
        created() {
        },
        mounted() {
            let _this = this;
            this.name = this.$route.query.name;
            this.category_id = this.$route.query.category_id;
            this.brand_id = this.$route.query.brand_id;
            this.coupan_id = this.$route.query.coupan_id;
            this.sort = this.$route.query.sort;
            this.order = this.$route.query.order;
            this.list = this.$route.query.data;
            try {
                if (!navigator.geolocation) {
                    throw "定位服务不可用";
                } else {
                    console.log('定位');
                    navigator.geolocation.getCurrentPosition(this.onPosSuccess, this.onPosError);
                }
            } catch (e) {
                this.$dialog.toast({
                    mes: e.toString(),
                    timeout: 1500,
                    icon: "error",
                    callback: () => {
                        setTimeout(function() {
                            _this.showPosition();
                        }, 1500);
                    }
                });
            }
        },
        methods: {
            submitHandler() {
                this.storeListGet(0);
            },
            screenId(data) {
                this.brand_id = data.brandId;
                this.category_id = data.categoryId;
                this.storeListGet(0);
            },
            screenSort(data) {
                this.order = data.order;
                this.sort = data.sort;
                this.storeListGet(0);
            },
            clearId(data) {
                this.brand_id = data.brandId;
                this.category_id = data.categoryId;
                this.storeListGet(0);
            },
            multipointId(data) {
                this.multi_point_id = data.multipointId;
                this.storeListGet(0);
            },
            showDetails(id) {
                this.$router.push("/details?id=" + id);
            },
            shopping_numGet() {
                let _this = this,
                    _data = (_data = Qs.stringify({
                        user_id: localStorage.getItem("userId")
                    }));
                _this.$http
                    .post("/api/v1/Cart/getNum", _data)
                    .then(function(response) {
                        if (response.data.errno === "0") {
                            _this.shopping_num = response.data.result;
                        } else {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: response.data.errmsg,
                                timeout: 1500,
                                icon: "error"
                            });
                        }
                    })
                    .catch(function(error) {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({
                            mes: '服务不可用',
                            timeout: 1500,
                            icon: "error"
                        });
                    });
            },
            storeListGet(type) {
                let _this = this;
                if (type === 0) {
                    _this.page = 1;
                    _this.storeList = [];
                    _this.$refs.infinitescrollDemo.$emit("ydui.infinitescroll.reInit");
                }
                let _data = Qs.stringify({
                    name: _this.name,
                    brand_id: _this.brand_id,
                    category_id: _this.category_id,
                    coupan_id: _this.coupan_id,
                    multi_point_id: _this.multi_point_id,
                    longitude: _this.longitude,
                    latitude: _this.latitude,
                    order: _this.order,
                    sort: _this.sort,
                    page: _this.page,
                    rows: _this.rows,
                    data: _this.list
                });
                _this.$dialog.loading.open("很快加载好了");
                _this.$http
                    .post("/api/v1/Product/list", _data)
                    .then(function(response) {
                        if (response.data.errno === "0") {
                            _this.storeList = [
                                ..._this.storeList,
                                ...response.data.result.list
                            ];
                            if (
                                response.data.result.list.length < _this.rows ||
                                response.data.result.total / _this.page === 0
                            ) {
                                _this.$refs.infinitescrollDemo.$emit(
                                    "ydui.infinitescroll.loadedDone"
                                );
                            } else {
                                _this.$refs.infinitescrollDemo.$emit(
                                    "ydui.infinitescroll.finishLoad"
                                );
                                _this.page++;
                            }
                            _this.$nextTick(function() {
                                _this.$dialog.loading.close();
                            });
                        } else {
                            _this.$dialog.loading.close();
                            _this.$dialog.toast({
                                mes: response.data.errmsg,
                                timeout: 1500,
                                icon: "error"
                            });
                        }
                    })
                    .catch(function(error) {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({
                            mes: '服务不可用',
                            timeout: 1500,
                            icon: "error"
                        });
                    });
            },
            loadList() {
                this.storeListGet();
            },
            showMap(data) {
                this.$router.push('/tencentmap?longitude=' + data.longitude + '&dimension=' + data.dimension + '&id=' + data.id);
            },
            showPosition(position) {
                let _this = this;
                console.log('pos:showPosition', position);
                if (position) {
                    _this.latitude = position.coords.latitude;
                    _this.longitude = position.coords.longitude;
                }
                // _this.latitude = 39.966596;
                // _this.longitude = 116.396027;
                // _this.city = position.city;
                _this.storeListGet();
                _this.shopping_numGet();
            },
            onPosSuccess(pos) {
                console.log('pos:onPosSuccess', pos);
                this.showPosition(pos);
            },
            onPosError(err) {
                let _this = this;
                console.log('pos:onPosError', err);
                this.$dialog.toast({
                    mes: '定位服务不可用',
                    timeout: 1500,
                    icon: "error"
                });
                setTimeout(function() {
                    _this.showPosition();
                }, 1500);
            }
        }
    };
</script>

<style>

</style>
