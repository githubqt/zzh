<template>
    <section class="housingLoan">
        <yd-navbar title="房屋抵押贷款" class="fixed-header">
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
                    <span slot="left">小区名称：</span>
                    <input slot="right" type="text" placeholder="请输入小区名称" v-model="name">
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">建筑面积：</span>
                    <input slot="right" type="text" placeholder="请输入建筑面积" v-model="housing_area">
                    <span slot="right">平方米</span>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">房屋年限：</span>
                    <input slot="right" type="text" placeholder="请输入房屋年限" v-model="housing_year">
                    <span slot="right">年</span>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">购买价格：</span>
                    <input slot="right" type="text" placeholder="请输入购买价格" v-model="purchase_price">
                    <span slot="right">万元</span>
                </yd-cell-item>
                <yd-cell-item>
    	            <span slot="left" class="create-cell-babel">所在地区：</span>
    	            <styArea slot="right" v-bind:cityNames="cityNames" v-on:cityIds="cityIds"></styArea>
    	        </yd-cell-item>
                <yd-cell-item>
                    <yd-textarea slot="right" placeholder="详细地址" maxlength="50" v-model="address"></yd-textarea>
                </yd-cell-item>
                <yd-cell-item>
                    <span slot="left">借贷金额：</span>
                    <input slot="right" type="text" placeholder="请输入借贷金额" v-model="loan_price">
                    <span slot="right">万元</span>
                </yd-cell-item>
            </yd-cell-group>

            <imgUpload v-bind:housingImg="housing_img" v-bind:fileType="fileType" v-on:picUrlSet="picUrlSet"></imgUpload>

            <div class="box" v-on:click="submitInfo()" style="margin:0 .24rem">
                <yd-button size="large" type="danger" >提交</yd-button>
            </div>

        </yd-layout>

    </section>
</template>

<script>
import imgUpload from './module/imgUpload';
import styArea from '@/common/area'
import Qs from 'qs';

export default {
    components: {
        imgUpload,
        styArea
    },
    data() {
        return {
            user_id: '',
            isAdressShow: false,
            name: '',
            mobile: '',
            housing_area: '',
            housing_year: '',
            purchase_price: '',
            province_id: '',
            city_id: '',
            area_id: '',
            address: '',
            addressDetail: '',
            loan_price: '',
            housing_img: [],
            fileType: '3',
            cityNames: ''
        }
    },
    mounted: function () {
        this.$nextTick(function () {
            this.user_id = localStorage.getItem("userId")
        })
    },
    methods: {
        cityIds(data) {
    		this.province_id = data.provinceId;
    		this.city_id = data.cityId;
    		this.area_id = data.areaId;
    		this.street = data.streetId;
    	},
        picUrlSet: function (housingImg) {
            this.housing_img = housingImg
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
            if (!that.name) {
                that.$dialog.toast({
                    mes: '请输入小区名称',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.housing_area) {
                that.$dialog.toast({
                    mes: '请输入建筑面积',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.housing_year) {
                that.$dialog.toast({
                    mes: '请输入建筑年限',
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
            if (!that.province_id) {
                that.$dialog.toast({
                    mes: '请输入所在地区',
                    timeout: 500,
                    icon: 'error'
                })
                return
            }
            if (!that.address) {
                that.$dialog.toast({
                    mes: '请输入详细地区',
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
            that.housing_img.forEach(function(ele, index){
                housingImg.push(ele.url)
            })
            let data = Qs.stringify({
                'user_id': that.user_id,
                'name': that.name,
                'housing_area': that.housing_area,
                'housing_year': that.housing_year,
                'purchase_price': that.purchase_price,
                'province_id': that.province_id,
                'city_id': that.city_id,
                'area_id': that.area_id,
                'address': that.address,
                'loan_price': that.loan_price,
                'mobile': that.mobile,
                'housing_img': housingImg
            });
            that.$http({
                url: '/api/v1/Onlinepawn/addHousing',
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
                    setTimeout(function () {
                        that.$router.push('/pawn')
                    },500)
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
