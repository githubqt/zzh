<template>
    <yd-layout>
        <yd-navbar slot="navbar" title="选择分类">
            <yd-navbar-back-icon slot="left" @click.native="goBack"></yd-navbar-back-icon>
        </yd-navbar>
        <yd-cell-group v-show="initialize.isLoaded">
            <yd-cell-item v-for="(item,i) in categoryList" :key="i" type="radio" @click.native="chooseItem(item)" arrow>
                <span slot="left">{{item.name}} <small style="color:#999">{{item.en_name}}</small></span>
            </yd-cell-item>
        </yd-cell-group>
    </yd-layout>
</template>
<script>
    import Api from "../../../../tool/supplier";
    import RecyclingForm from "../../../../tool/recyclingForm";
    export default {
        name: "RecyclingCategory",
        data() {
            return {
                categoryList: [],
                initialize: {
                    isLoaded: false,
                },
                req: {
                    pid: 0
                },
                reload: false
            }
        },
        created() {
            this.req.pid = this.$route.query.pid;
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
                this.$router.replace(`/recyclingCategoryThree?pid=${item.id}&previous=${this.req.pid}`);
            },
            goBack() {
                this.$router.go(-1);
            }
        }
    }
</script>

