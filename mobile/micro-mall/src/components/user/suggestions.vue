<template>
    <!-- header -->
    <yd-layout title="投诉建议" class="suggestions" link="/user">
        <imgUpload v-bind:housingImg="housing_img" v-bind:fileType="fileType" v-on:picUrlSet="picUrlSet"></imgUpload>
        <yd-cell-group class="">
            <h3 class="title">反馈内容</h3>
            <yd-cell-item>
                <yd-textarea slot="right" placeholder="请填写内容" maxlength="100" v-model="proposal"></yd-textarea>
            </yd-cell-item>
        </yd-cell-group>
        <div class="box" v-on:click="submitInfo()">
            <yd-button size="large" type="danger">提交</yd-button>
        </div>
    </yd-layout>
</template>

<script>
    import imgUpload from './../pawn/module/imgUpload';
    import Qs from 'qs';
    import {
        Auth
    } from "../../mixins/auth"
    export default {
        components: {
            imgUpload
        },
        mixins: [Auth],
        data() {
            return {
                user_id: '',
                proposal: '',
                fileType: '2',
                housing_img: [],
            }
        },
        mounted: function() {
            this.$nextTick(function() {
                this.user_id = localStorage.getItem("userId")
            })
        },
        methods: {
            picUrlSet: function(housingImg) {
                this.housing_img = housingImg
            },
            submitInfo: function() {
                let that = this;
                if (!that.proposal) {
                    that.$dialog.toast({
                        mes: '请输入投诉建议',
                        timeout: 500,
                        icon: 'error'
                    })
                    return
                }
                that.$dialog.loading.open('提交中')
                var housingImg = []
                that.housing_img.forEach(function(ele, index) {
                    housingImg.push(ele.url)
                })
                let data = Qs.stringify({
                    'user_id': that.user_id,
                    'proposal': that.proposal,
                    'img_url': housingImg
                });
                that.$http({
                    url: '/api/v1/User/proposal',
                    method: 'POST',
                    data: data,
                }).then(function(res) {
                    that.$dialog.loading.close();
                    if (res.data.errno == '0') {
                        that.$dialog.toast({
                            mes: '提交成功',
                            timeout: 500,
                            icon: 'success'
                        });
                        setTimeout(function() {
                            that.$router.push('/sgSucceed')
                        }, 500)
                    } else if (res.data.errno == '50006') {
                        that.$dialog.confirm({
                            title: '系统提示',
                            mes: '登录状态失效，重新登录？',
                            opts: () => {
                                that.$router.push('/login')
                            }
                        })
                    } else {
                        //that.$dialog.toast({ mes: res.data.errmsg, timeout: 1500, icon: 'error' });
                        that.$dialog.confirm({
                            title: '系统提示',
                            mes: '登录状态失效，重新登录？',
                            opts: () => {
                                that.$router.push('/login')
                            }
                        })
                    }
                }).catch(function(err) {
                    that.$dialog.loading.close();
                    that.$dialog.toast({
                        mes: err,
                        timeout: 1500,
                        icon: 'error'
                    });
                })
            }
        }
    }
</script>

<style scoped>

</style>
