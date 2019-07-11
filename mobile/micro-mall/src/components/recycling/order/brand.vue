<template>
    <yd-layout>
        <yd-navbar slot="navbar" title="选择品牌">
            <yd-navbar-back-icon slot="left" @click.native="goBack"></yd-navbar-back-icon>
        </yd-navbar>

        <yd-search v-model="searchContent" placeholder="请输入品牌名称" :on-cancel="submitHandler" :on-submit="submitHandler"
                   cancel-text="搜索"></yd-search>

        <yd-accordion v-show="Commonlize.isCommon">
            <yd-accordion-item title="常用" open>
                <yd-cell-group>
                    <yd-cell-item v-for="(item,i) in CommonList" :key="i" v-if="i < 9" type="radio">
                        <span slot="left">{{item.name}} <small style="color:#999">{{item.en_name}}</small></span>
                        <input slot="right" type="radio" :value="item" v-model="picked" @click="chooseItem(item)"/>
                    </yd-cell-item>
                </yd-cell-group>
            </yd-accordion-item>
        </yd-accordion>

        <!--<yd-accordion v-show="initialize.isLoaded">-->
        <!--<yd-accordion-item title="全部" open>-->
        <!--<yd-cell-group >-->
        <!--<yd-cell-item v-for="(item,i) in brandList" :key="i" type="radio">-->
        <!--<span slot="left">{{item.name}} <small style="color:#999">{{item.en_name}}</small></span>-->
        <!--<input slot="right" type="radio" :value="item" v-model="picked" @click="chooseItem(item)"/>-->
        <!--</yd-cell-item>-->
        <!--</yd-cell-group>-->
        <!--</yd-accordion-item>-->
        <!--</yd-accordion>-->

        <yd-cell-group v-show="initialize.isLoaded">
            <yd-cell-item type="radio">
                <span slot="left">全部</span>
            </yd-cell-item>
            <yd-cell-item v-for="(item,i) in brandList" :key="i" type="radio">
                <span slot="left">{{item.name}} <small style="color:#999">{{item.en_name}}</small></span>
                <input slot="right" type="radio" :value="item" v-model="picked" @click="chooseItem(item)"/>
            </yd-cell-item>
        </yd-cell-group>

        <yd-cell-group v-show="Searchlize.isSearch">
            <yd-cell-item v-for="(item,i) in SearchList" :key="i" type="radio">
                <span slot="left">{{item.name}} <small style="color:#999">{{item.en_name}}</small></span>
                <input slot="right" type="radio" :value="item" v-model="picked" @click="chooseItem(item)"/>
            </yd-cell-item>
        </yd-cell-group>

    </yd-layout>
</template>
<script>
    import Api from "../../../../tool/supplier";
    import RecyclingForm from "../../../../tool/recyclingForm";
    import Qs from 'qs'

    export default {
        name: "RecyclingBrand",
        data() {
            return {
                brandList: [],
                CommonList: [],
                picked: {},
                initialize: {
                    isLoaded: false
                },
                searchContent: '',
                Commonlize: {
                    isCommon: false
                },
                Searchlize: {
                    isSearch: false
                },
                SearchList: [],
            }
        },
        mounted() {
            this.getBrandList();
            this.getCommonList();
        },
        methods: {
            getBrandList() {
                const _this = this;
                _this.$dialog.loading.open('正在加载...');
                _this.$http.get(`${Api.brand}`).then(res => {
                    const json = res.data;
                    _this.$dialog.loading.close();
                    if (parseInt(json.errno) === 0) {
                        _this.brandList = json.result;
                        _this.initialize.isLoaded = true;
                    } else {
                        _this.$dialog.toast({
                            mes: '服务暂不可用',
                            timeout: 1500,
                            icon: 'error'
                        });
                    }
                }).catch(err => {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: '服务暂不可用',
                        timeout: 1500,
                        icon: 'error'
                    });
                });
            },
            getCommonList() {
                let _this = this;
                _this.$dialog.loading.open('正在加载...');
                this.$http.get(`${Api.common}`).then(res => {
                    const json = res.data;
                    _this.$dialog.loading.close();
                    if (parseInt(json.errno) === 0) {
                        _this.CommonList = json.result;
                        _this.Commonlize.isCommon = true;
                    } else {
                        _this.$dialog.loading.close();
                    }
                }).catch(err => {
                    _this.$dialog.loading.close();

                });
            },
            chooseItem(item) {
                RecyclingForm.setStorage('brand_id', item.id);
                RecyclingForm.setStorage('brand_name', `${item.name} ${item.en_name}`);
                this.$router.go(-1);
            },
            goBack() {
                this.$router.go(-1);
            },
            submitHandler() {
                if (this.searchContent) {
                    this.getSelectBrand(this.searchContent);

                } else {
                    this.$dialog.toast({
                        mes: '请输入品牌名称',
                        timeout: 1500,
                    });
                }

            },
            getSelectBrand(value) {
                let _this = this,
                    _data = Qs.stringify({
                        name: value,
                    });
                _this.$dialog.loading.open('正在加载...');
                _this.$http.post(`${Api.search}`, _data).then(function (response) {
                    _this.$dialog.loading.close();
                    if (response.data.errno == '0') {
                        _this.initialize.isLoaded = false;
                        _this.Commonlize.isCommon = false;
                        _this.Searchlize.isSearch = true;
                        _this.SearchList = response.data.result;

                    } else if (response.data.errno == '-2') {
                        _this.$dialog.toast({
                            mes: '没有这个品牌！',
                            timeout: 1500,
                            icon: 'error'
                        });
                    } else {
                        _this.$dialog.toast({
                            mes: '服务不可用！',
                            timeout: 1500,
                            icon: 'error'
                        });
                    }
                }).catch(function (error) {
                    _this.$dialog.toast({mes: error, timeout: 1500, icon: 'error'});
                });
            }
        }
    }
</script>

