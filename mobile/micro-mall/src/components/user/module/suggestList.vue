<template>
    <section class="suggestList">
        <yd-cell-group class="suggestList-cell-group" v-for="(item, index) in suggestData" :key="index">
            <yd-cell-item style="background-color:rgba(220, 40, 33, 0.75); " v-show="item.status==1">
                <span slot="left" style="font-size: 18px;color:white; ">提交时间:{{item.time_txt}}</span>
                <span slot="right" style="font-size: 18px;color:white;">已提交</span>
            </yd-cell-item>
            <yd-cell-item style="background-color: #AEAEAE; " v-show="item.status!=1">
                <span slot="left" style="font-size: 18px;color:white; ">提交时间:{{item.time_txt}}</span>
                <span slot="right" style="font-size: 18px;color:white;">已处理</span>
            </yd-cell-item>
            <div>
                <span calss="" style="font-size: 16px;color: #EE9495;padding: .2rem;"> 投诉建议详细描述:</span>
                <div style="font-size: 14px;padding: .2rem;">{{item.proposal}}</div>
            </div>
            <hr/>
            <yd-lightbox :num="item.imgList.length">
                <yd-lightbox-img style="width: 2.5rem;height: 2.5rem;background-size:100% 100%;margin-top: 0.15rem;padding: .1rem;" v-for="(list,index) in item.imgList" :src="list.log_url" :original="list.log_url" :key="index"></yd-lightbox-img>
                <yd-lightbox-txt>
                    <h1 slot="top"></h1>
                    <div slot="content"> </div>
                </yd-lightbox-txt>
            </yd-lightbox>
            <div style="padding: .3rem;">
                <div style="padding: .2rem;border:1px solid #f1f1f1;background-color: #f2f2f2;" v-show="item.status!=1">
                    <span style="color: #199ED8; font-size: 16px"> 商家回复:</span>
                    <div style="color: #199ED8; font-size: 14px; margin-top: .2rem;">
                        {{item.note}}
                    </div>
                </div>
            </div>
        </yd-cell-group>
    </section>
</template>

<script>
    import Qs from 'qs'
    export default {
        name: 'suggestList',
        data() {
            return {
                suggestData: '',
                page: 1,
                rows: 10,
                errorImg: 'this.src="' + require('../../../assets/img/err.jpg') + '"'
            }
        },
        created() {
            this.suggestLogin();
        },
        methods: {
            suggestLogin() {
                let _this = this,
                    _data = Qs.stringify({
                        user_id: localStorage.getItem('userId'),
                        page: this.page,
                        rows: this.rows
                    });
                _this.$http.post('/api/v1/User/userProposal', _data).then(function(response) {
                    if (response.data.errno === '0') {
                        _this.suggestData = response.data.result;
                    } else {
                        _this.$dialog.loading.close();
                        //_this.$dialog.toast({ mes: response.data.errmsg, timeout: 1500, icon: 'error' });
                    }
                }).catch(function(error) {
                    _this.$dialog.loading.close();
                    _this.$dialog.toast({
                        mes: error,
                        timeout: 1500,
                        icon: 'error'
                    });
                });
            }
        }
    }
</script>

<style scoped>
    .suggestList {
        background-color: #f5f5f5;
    }
    .suggestList .suggestList-cell-group::after {
        content: '';
        height: 1rem;
        width: 100%;
        padding: 2rem;
    }
    .suggestList .yd-cell-box {
        margin-bottom: 0rem !important;
    }
</style>
