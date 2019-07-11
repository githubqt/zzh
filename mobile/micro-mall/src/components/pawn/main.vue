<template>
    <section class="pawn">
        <!-- header -->
        <yd-navbar class="fixed-header" title="在线售卖"></yd-navbar>

        <div class="loan-item" style="margin-top: 1rem;" v-on:click="pageJump('/housingLoan')">
            <img src="./../../../static/imgs/house.png" alt="">
            <div class="content">
                <h3>房屋借款</h3>
                <p>Housing loan</p>
            </div>
        </div>
        <div class="loan-item" v-on:click="pageJump('/carLoan')">
            <img src="./../../../static/imgs/car.png" alt="">
            <div class="content">
                <h3>汽车借款</h3>
                <p>Car loan</p>
            </div>
        </div>
        <div class="loan-item" v-on:click="pageJump('/civilLoan')">
            <img src="./../../../static/imgs/civil.png" alt="">
            <div class="content">
                <h3>民品借款</h3>
                <p>Civil goods loan</p>
            </div>
        </div>

        <router-link to="/contact" class="customer-service"></router-link>

    </section>
</template>

<script>
export default {
    data() {
        return {
            user_id: ''
        }
    },
    mounted: function() {
        this.$nextTick(function() {
            this.user_id = localStorage.getItem("userId");
        })
    },
    methods: {
        pageJump: function(url) {
            var that = this
            if (that.user_id) {
                that.$router.push(url)
            } else {
                that.$router.push('/login')
            }

        }
    }
}
</script>
<style scoped>
@import "../../assets/css/components/pawn/main";
</style>
