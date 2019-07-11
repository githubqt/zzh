<template>
    <section class="site-container">
        <!-- header -->
        <yd-navbar class="fixed-header" title="地址管理">
            <router-link to="/user" slot="left">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
        </yd-navbar>
        <!-- list -->
        <div class="m-t-1" style="padding: .12rem 0 1.12rem 0">
            <div class="site-list-box" v-for="(item, index) in siteList">
                <yd-flexbox class="site-list-after">
                    <yd-flexbox-item>
                        <span class="site-list-name">{{item.name}}</span>
                        <span class="site-list-content">
                                {{item.province}} {{item.city}} {{item.area}} {{item.street}} {{item.address}}
                            </span>
                    </yd-flexbox-item>
                    <span class="site-list-phone">{{item.mobile}}</span>
                </yd-flexbox>
                <yd-flexbox>
                    <yd-flexbox-item>
                        <yd-radio-group size="15" color="#dc2821" class="site-list-radio" v-model="siteRadio">
                            <yd-radio :val="item.id" @change.native="setDefault">默认地址</yd-radio>
                        </yd-radio-group>
                    </yd-flexbox-item>
                    <div class="site-list-btn">
                        <yd-button bgcolor="#fff" @click.native="editSiteGo(item.id)">编辑</yd-button>
                        <yd-button bgcolor="#fff" @click.native="siteDel(item.id)">删除</yd-button>
                    </div>
                </yd-flexbox>
            </div>
        </div>
        <!-- create -->
        <div class="create-site-bax">
            <yd-button size="large" type="danger" color="#fff" @click.native="createSiteGo">新增地址</yd-button>
        </div>
    </section>
</template>

<script>
    import Qs from 'qs'
    import {
        Auth
    } from "../../../mixins/auth"
    export default {
        name: 'Site',
        mixins: [Auth],
        data() {
            return {
                siteRadio: '',
                siteList: []
            }
        },
        mounted() {
            this.$nextTick(()=>{
this.siteListGet();
            });
            
        },
        methods: {
            siteListGet() {
                let _this = this,
                    _data = Qs.stringify({
                        user_id: localStorage.getItem('userId'),
                        page: 1,
                        rows: 15
                    });
                _this.$dialog.loading.open('很快加载好了');
                _this.$http.post('/api/v1/address/list', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.siteList = response.data.result.list;
                        response.data.result.list.forEach(item => {
                            if (item.is_default === '2') _this.siteRadio = item.id;
                        });
                        _this.$nextTick(function() {
                            _this.$dialog.loading.close()
                        });
                    } else {
                        _this.$dialog.loading.close();
                        _this.$dialog.toast({
                            mes: response.data.errmsg,
                            timeout: 1500,
                            icon: 'error'
                        });
                    }
                }).catch(function(error) {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: error,
                        timeout: 1500,
                        icon: 'error'
                    });
                });
            },
            setDefault() {
                let _this = this,
                    _data = Qs.stringify({
                        user_id: localStorage.getItem('userId'),
                        address_id: _this.siteRadio
                    });
                _this.$http.post('/api/v1/address/setDefault', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.$dialog.toast({
                            mes: '默认地址已设置',
                            timeout: 1500,
                            icon: 'success'
                        });
                    } else {
                        _this.$dialog.toast({
                            mes: response.data.errmsg,
                            timeout: 1500,
                            icon: 'error'
                        });
                    }
                }).catch(function(error) {
                    _this.$dialog.toast({
                        mes: error,
                        timeout: 1500,
                        icon: 'error'
                    });
                });
            },
            siteDel(siteId) {
                let _this = this,
                    _data = Qs.stringify({
                        user_id: localStorage.getItem('userId'),
                        address_id: siteId
                    });
                _this.$dialog.confirm({
                    title: '删除',
                    mes: '请确认是否删除地址',
                    opts: () => {
                        _this.$http.post('/api/v1/address/delele', _data).then(function(response) {
                            if (response.data.errno === '0') {
                                _this.siteListGet();
                                _this.$nextTick(function() {
                                    _this.$dialog.toast({
                                        mes: '已删除',
                                        timeout: 1500,
                                        icon: 'success'
                                    });
                                });
                            } else {
                                _this.$dialog.toast({
                                    mes: response.data.errmsg,
                                    timeout: 1500,
                                    icon: 'error'
                                });
                            }
                        }).catch(function(error) {
                            _this.$dialog.toast({
                                mes: error,
                                timeout: 1500,
                                icon: 'error'
                            });
                        });
                    }
                });
            },
            editSiteGo(id) {
                this.$router.push({
                    name: 'Create',
                    query: {
                        id: id
                    }
                })
            },
            createSiteGo() {
                this.$router.push('/create')
            }
        }
    }
</script>

<style>
    @import "../../../assets/css/components/user/module/site";
</style>