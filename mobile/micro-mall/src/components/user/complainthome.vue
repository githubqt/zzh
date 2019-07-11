<template>
    <yd-layout title="投诉建议" link="/user" class="suggestions">
        <img src="/../../static/imgs/suggest.png" style="display:block;margin: 1.5rem auto">
        <div class="box" v-on:click="submitInfo()">
            <yd-button type="danger" size="large">我要投诉</yd-button>
        </div>
    </yd-layout>
</template>

<script>
import {
        Auth
    } from "../../mixins/auth"
    export default {
        name: 'Complainthome',
        mixins: [Auth],
        data() {
            return {}
        },
        created() {},
        methods: {
            submitInfo() {
                this.$router.push("/suggestions");
            },
        }
    }
</script>
