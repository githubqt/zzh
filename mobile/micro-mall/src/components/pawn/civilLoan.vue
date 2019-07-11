<template>
    <section class="civilLoan">

        <yd-navbar title="民品抵押贷款" class="fixed-header">
            <router-link slot="left" to="/pawn">
                <yd-navbar-back-icon></yd-navbar-back-icon>
            </router-link>
        </yd-navbar>

        <yd-layout title="" style="padding-top: 1rem;">

            <yd-cell-group class="">
                <yd-cell-item>
                    <span slot="left">联系方式：</span>
                    <yd-input slot="right" type="tel" placeholder="请输入联系方式" v-model="mobile" ref="mobile" regex="mobile"></yd-input>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">民品种类：</span>
                    <input slot="right" type="text" placeholder="请选择民品种类" v-model="categoryText" readonly v-on:click="showPopup('Category')">
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">民品品牌：</span>
                    <input slot="right" type="text" placeholder="请选择民品品牌" v-model="brandText" readonly v-on:click="showPopup('Brand')">
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">商品描述：</span>
                    <input slot="right" type="text" placeholder="请输入商品描述" v-model="product_note">
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">商品配件：</span>
                    <yd-checkbox-group v-model="parts_note" slot="left">
                        <yd-checkbox val="1">发票</yd-checkbox>
                        <yd-checkbox val="2">证书</yd-checkbox>
                        <yd-checkbox val="3">包装</yd-checkbox>
                    </yd-checkbox-group>
                </yd-cell-item>

                <yd-cell-item>
                    <span slot="left">购买价格：</span>
                    <input slot="right" type="text" placeholder="请输入购买价格" v-model="purchase_price">
                    <span slot="right">元</span>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">借贷金额：</span>
                    <input slot="right" type="text" placeholder="请输入借贷金额" v-model="loan_price">
                    <span slot="right">元</span>
                </yd-cell-item>
            </yd-cell-group>

            <imgUpload v-bind:housingImg="housing_img" v-bind:fileType="fileType" v-on:picUrlSet="picUrlSet"></imgUpload>

            <div class="box" v-on:click="submitInfo()" style="margin:0 .24rem">
                <yd-button size="large" type="danger" >提交</yd-button>
            </div>

        </yd-layout>

        <screen v-bind:isScreenShow="isScreenShow" v-bind:screenType="screenType" v-on:setScreenId="setScreenId"></screen>

    </section>
</template>

<script>
import imgUpload from './module/imgUpload';
import screen from './module/screen'
import Qs from 'qs';

export default {
    components: {
        imgUpload,
        screen
    },
    data() {
        return {
            user_id: '',
            isAdressShow: false,
            mobile: '',
            brandId: '',
            brandText: '',
            categoryId: '',
            categoryText: '',
            product_note: '',
            parts_note: [],
            purchase_price: '',
            loan_price: '',
            housing_img: [],
            fileType: '3',
            screenType: 'Category',
            isScreenShow: false,
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            this.user_id = localStorage.getItem("userId")
        })
    },
    methods: {
        showPopup: function (type) {
            this.screenType = type
            this.isScreenShow = true
        },
        picUrlSet: function(housingImg) {
            this.housing_img = housingImg
        },
        setScreenId: function (data) {
            if (data.type == 'Category') {
                this.categoryText = data.name
                this.categoryId = data.id
            }
            if (data.type == 'Brand') {
                this.brandId =  data.id
                this.brandText = data.name
            }
            this.isScreenShow = false
        },
        submitInfo: function() {
            let that = this;
            let isVerify = that.$refs.mobile.valid
            if (!that.mobile) {
                that.$dialog.toast({
                    mes: '请输入联系方式',
                    timeout: 500,
                    icon: 'error'
                })
                return
            } else {
                if (!isVerify) {
                    that.$dialog.toast({
                        mes: '请输入正确的手机号',
                        timeout: 500,
                        icon: 'error'
                    })
                    return
                }
            }
            if (!that.categoryId) {
                that.$dialog.toast({
                    mes: '请输入民品种类',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.brandId) {
                that.$dialog.toast({
                    mes: '请输入民品品牌',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.product_note) {
                that.$dialog.toast({
                    mes: '请输入商品描述',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.parts_note) {
                that.$dialog.toast({
                    mes: '请选择商品配件',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.purchase_price) {
                that.$dialog.toast({
                    mes: '请输入购买价格',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.loan_price) {
                that.$dialog.toast({
                    mes: '请输入借贷金额',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (that.housing_img.length == '0') {
                that.$dialog.toast({
                    mes: '请上传图片',
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
                'name': that.categoryId,
                'housing_area': that.brandId,
                'product_note': that.product_note,
                'parts_note': that.parts_note,
                'purchase_price': that.purchase_price,
                'loan_price': that.loan_price,
                'mobile': that.mobile,
                'housing_img': housingImg
            });
            that.$http({
                url: '/api/v1/Onlinepawn/civilMortgage',
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
                        that.$router.push('/pawn')
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
                    that.$dialog.toast({ mes: res.data.errmsg, timeout: 1500, icon: 'error' });
                }
            }).catch(function(err) {
                that.$dialog.loading.close();
                that.$dialog.toast({ mes: err, timeout: 1500, icon: 'error' });
            })
        }
    }
}
</script>

<style scoped>
</style>
