<template>
    <yd-layout title="投诉建议" link="/user" class="suggestions">
        <div style="padding: 1rem 0.24rem;margin-top: 1rem;text-align:justify;font-size: 20px;color: #EE9495">
            
            尊敬的用户你好，我们已经收到您的反馈与建议，感谢您对< <em>{{supplierData.company}}</em> >的支持，如有需要我们将与您联系，谢谢!
        </div>
        <div class="box" v-on:click="submitInfo()">
            <yd-button size="large" type="danger" >点击返回</yd-button>
        </div>
    </yd-layout>
</template>

<script>
    export default {
        name: 'sgSucceed',
        data() {
            return {
                supplierData: '',
            }
        },
        created() {
            this.supplierGet();
        },
        methods: {
            submitInfo() {
                this.$router.push("/suggest");
            },
            supplierGet() {
                let _this = this;
                _this.$http.post('/api/v1/Home/supplier').then(function(response) {
                    if (response.data.errno === '0') {
                        _this.supplierData = response.data.result;
                        console.log(this.supplierData);
                    } else {
                        _this.$dialog.loading.close();
                    }
                }).catch(function(error) {
                    _this.$dialog.loading.close();
                });
            }
        }
    }
</script>
