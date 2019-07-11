<template>
    <div class="dialog" id="dialogs" v-show="showMask">
        <div class="dialog-container">
            <div v-if="type != 'confirm'" class="default-btn" @click="closeBtn">
                <img style="width.5rem;height:.5rem;float:right;margin-top: 1.1rem;margin-right: 0.5rem;" src="./../../../static/imgs/close.jpg" alt=""></div>
        </div>
        <div class="content" v-html="content"></div>
        <div class="close-btn" @click="closeMask"><i class="iconfont icon-close"></i></div>
    </div>
</template>

<script>
    export default {
        props: {
            value: {},
            // 类型包括 defalut 默认， danger 危险， confirm 确认，
            type: {
                type: String,
                default: 'default'
            },
            content: {
                type: String,
                default: ''
            },
            title: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                showMask: false,
            }
        },
        methods: {
            closeMask() {
                this.showMask = false;
                location.reload();
            },
            closeBtn() {
                this.$emit('cancel');
                this.closeMask();
            },
            dangerBtn() {
                this.$emit('danger');
                this.closeMask();
            },
            confirmBtn() {
                this.$emit('confirm');
                this.closeMask();
            }
        },
        mounted() {
            this.showMask = this.value;
        },
        watch: {
            value(newVal, oldVal) {
                this.showMask = newVal;
            },
            showMask(val) {
                this.$emit('input', val);
            }
        },
    }
</script>
<style>
</style>
