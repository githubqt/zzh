<template>
    <div class="cart-merchant">
        <!-- 商户名称-网点名称 -->
        <div class="merchant-list-item-title" v-show="propMerchant.id != null">
            <yd-checkbox color="#dc2821" v-model="isCheckAll" :label="true" shape="circle" @click.native="checkAll" style="width:100%" class="text-ellipsis">
                <span class="merchant-name"><yd-icon custom name="dianpu"></yd-icon>{{propMerchant.company}}-{{propMerchant.name}}</span>
            </yd-checkbox>
        </div>
        <!-- 商户名称-网点名称 -->
        <yd-checklist color="#dc2821" ref="productList" v-model="checklist" :callback="handleChangefn">
            <yd-checklist-item v-for="(info, i) in propMerchant.productData" :disabled="!canCheck(info)" :key="i" :val="info.cart_id">
                <merchant-product :info="info" v-on:isReload="isReload" v-on:handleBuyNum="handleBuyNum"></merchant-product>
            </yd-checklist-item>
        </yd-checklist>
    </div>
</template>

<script>
    import Qs from "qs";
    import CartMerchantProduct from "@/components/store/module/merchantProduct"
    export default {
        name: 'CartMerchant',
        props: ['propMerchant', 'propAll'],
        components: {
            'merchant-product': CartMerchantProduct
        },
        data() {
            return {
                isCheckAll: false,
                checklist: [],
                checkNum: 0,
                totalNum: 0,
                cid: ''
            }
        },
        created() {
            this.totalNum = this.propMerchant.productData.length;
            //key name
            this.cid = this.propMerchant.id ? `m_${this.propMerchant.id}` : `p_${this.propMerchant.productData[0].cart_id}`
        },
        watch: {
            propAll(val, old) {
                this.isCheckAll = this.propAll;
                this.$refs.productList.$emit('ydui.checklist.checkall', this.isCheckAll);
            }
        },
        methods: {
            handleChangefn(val, isCheckAll) {
                let data = [];
                data.push(this.cid);
                data.push({
                    total: this.totalNum,
                    check: this.checklist.length,
                    ids: val
                });
                this.$emit('HandleCheckNum', data);
            },
            // 判断是否可选
            canCheck(item) {
                return this.isProductValid(item) && this.hasStock(item);
            },
            //商品是否有效（下架）
            isProductValid(item) {
                switch (item.product_from) {
                    case "自营":
                        if (item.on_status == 2) {
                            return true;
                        }
                        break;
                    case "供应":
                        if (item.channel_on_status == 2) {
                            return true;
                        }
                        break;
                    default:
                        return false;
                        break;
                }
                return false;
            },
            //是否有库存
            hasStock(item) {
                return item.stock > 0;
            },
            // 商品详细
            toDetail(item, param) {
                if (this.canCheck(item)) {
                    return param;
                }
                return "#";
            },
            /**
             * 选择网点商品
             */
            checkAll() {
                let _this = this;
                this.$refs.productList.$emit(
                    "ydui.checklist.checkall", !this.isCheckAll
                )
            },
            isReload(item) {
                this.$emit('isReload', item);
            },
            handleBuyNum(item) {
                this.$emit('handleBuyNum', item);
            }
        }
    }
</script>

<style scoped>

</style>