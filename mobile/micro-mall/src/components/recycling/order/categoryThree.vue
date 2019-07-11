<template>
    <yd-layout>
        <yd-navbar slot="navbar" title="选择分类">
            <yd-navbar-back-icon slot="left" @click.native="goBack"></yd-navbar-back-icon>
        </yd-navbar>
        <yd-cell-group v-show="initialize.isLoaded">
            <yd-cell-item v-for="(item,i) in categoryList" :key="i" type="radio" @click.native="chooseItem(item)">
                <span slot="left">{{item.name}} <small style="color:#999">{{item.en_name}}</small></span>
                <input slot="right" type="radio" :value="item" v-model="picked" />
            </yd-cell-item>
        </yd-cell-group>
    </yd-layout>
</template>
<script>
    import Api from "../../../../tool/supplier";
    import RecyclingForm from "../../../../tool/recyclingForm";
    export default {
        name: "RecyclingCategoryThree",
        data() {
            return {
                categoryList: [],
                picked: {},
                initialize: {
                    isLoaded: false,
                },
                req: {
                    previous: 0,
                    pid: 0
                }
            }
        },
        created() {
            this.req = this.$route.query;
        },
        mounted() {
            this.getCategoryList();
        },
        methods: {
            getCategoryList() {
                const _this = this;
                _this.$dialog.loading.open('正在加载...');
                _this.$http.get(`${Api.category}?pid=${_this.req.pid}`).then(res => {
                    const json = res.data;
                    _this.$dialog.loading.close();
                    if (parseInt(json.errno) === 0) {
                        _this.categoryList = json.result;
                        // //默认选中第一个
                        // if (_this.categoryList.length) {
                        //     _this.picked = json.result[0];
                        // }
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
            chooseItem(item) {
                RecyclingForm.setStorage('category_id', item.id);
                RecyclingForm.setStorage('category_name', item.name);
                this.$router.replace(`/createRecycling?pid=${this.req.previous}`);
            },
            goBack() {
                this.$router.replace(`/recyclingCategory?pid=${this.req.previous}`);
            }
        }
    }
</script>

