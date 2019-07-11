// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import VueLazyLoad from 'vue-lazyload'

import Vuex from 'vuex'
import axios from 'axios'
import store from './store/index';

import YDUI from 'vue-ydui'
import 'vue-ydui/dist/ydui.rem.css'
import './assets/icon/iconfont.css'

//vue-croppa
import Croppa from 'vue-croppa';
import 'vue-croppa/dist/vue-croppa.css';
Vue.use(Croppa);

import { login_state, getAdminState } from "../tool/login"

import { preious } from '../tool/history'

Vue.use(YDUI, Vuex)

// import { VueTouch } from 'vue-touch'
const VueTouch = require('vue-touch')
Vue.use(VueTouch, { name: 'v-touch' })

axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded'
Vue.prototype.$API = process.env.API_ROOT
Vue.prototype.$FILE = process.env.FILE_ROOT
Vue.prototype.$JD = process.env.JD_ROOT
Vue.prototype.$http = axios

Vue.use(VueLazyLoad, {
    error: '../static/imgs/err.jpg',
    loading: '../static/imgs/loading.gif'
})

const WebDomain = {
    getDomain: function() {
        var pathName = window.document.location.pathname;
        var pathURL = window.location.host;
        var n = (pathURL.split('.')).length - 1;
        //获取后缀
        var projectName = pathName.substring(1, pathName.substr(1).indexOf('/') + 1);
        if (projectName != "" && projectName != "/" && projectName != "mobile" && n < 3) {
            return (projectName);
        } else {
            //获取前缀
            projectName = pathURL.substring(0, pathURL.substr(1).indexOf('.') + 1);
            var regPos = /^\d+$/; // 非负整数

            if (projectName != "" && projectName != "/" && !(regPos.test(projectName)) && projectName != "mobile" && n == 3) {
                return (projectName);
            }

            return "test";
        }
    }
}




axios.interceptors.request.use(
    config => {
        //let state = login_state(),
            //token = state.token;
        // if (config.url.indexOf('mobile_img.php') < 0 && config.url.indexOf('chunk_upload.php') < 0) { //过滤图片上传
        //     //兼任旧接口 不能去掉的
        //     config.data = config.data == "" || config.data == null ? "identif=" + WebDomain.getDomain() : config.data + "&identif=" + WebDomain.getDomain();
        //     config.headers['Domain'] = WebDomain.getDomain();
        // }
        //config.headers['X-Authorization-Token'] = token;

        //商户登录token
        //let adminState = getAdminState();
        //config.headers['X-Admin-Authorization-Token'] = adminState.token;
        // 请求超时
        //config.timeout = 1000 * 60 * 3;
        return config;
    },
    err => {
        return Promise.reject(err);
    });


Vue.prototype.DOMAIN = WebDomain.getDomain()
Vue.config.productionTip = false


/* eslint-disable no-new */
new Vue({
    el: '#app',
    router,
    store,
    render: h => h(App)
})


// 滚动行为：当切换到新路由时，保持原先的滚动位置
let scrollTop = 0;

router.beforeEach((route, redirect, next) => {
    let state = login_state();
    if (!state.token) {
        if (redirect.path !== '/login' && redirect.path !== '/writer') {
            preious(router.history.base + redirect.fullPath);
        }
    }
    let scrollView = document.getElementById('scrollView');
    if (scrollView !== null) {
        if (redirect.path === '/') {
            scrollTop = scrollView.scrollTop;
        }
    }
    next();
});

router.afterEach(route => {
    let scrollView = document.getElementById('scrollView');
    if (scrollView !== null) {
        if (route.path === '/') {
            Vue.nextTick(() => {
                scrollView.scrollTop = scrollTop;
            });
        } else {
            scrollView.scrollTop = 0;
        }
    }
});