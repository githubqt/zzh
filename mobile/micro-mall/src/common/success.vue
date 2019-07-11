<template>
    <section class="success-container">
        <yd-flexbox direction="vertical">
            <div class="success-iocn">
                <yd-icon name="checkoff" color="#dab461" size="1.4rem" class="success-iocn-radius"></yd-icon>
            </div>
            <yd-flexbox-item class="button-box">
                <yd-button size="large" bgcolor="#dab461" color="#fff" @click.native="backGo">返回</yd-button>
            </yd-flexbox-item>
        </yd-flexbox>
    </section>
</template>
<script>
import Qs from 'qs'
export default {
    name: 'Success',
    data() {
        return {}
    },
    created() {},
    methods: {
        backGo() { this.$router.push('/user') }
    }
}

</script>
<style>
.success-container {
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.success-iocn {
    width: 100%;
    padding: .5rem .24rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.success-iocn-radius {
    width: 1.8rem;
    height: 1.8rem;
    text-align: center;
    line-height: 1.8rem;
    border-radius: 50%;
    border: 2px solid #dab461;
}
.button-box {
    width: 100%;
    padding: .24rem;
}
.button-box button {
    margin: 0;
}

</style>
