/**
 * 获取当前运行环境
 * 小程序 => mini | 微信 => wechat  |  其他 => wap
 */
export const Env = {
    data() {
        return {
            env: '' // 小程序 => mini | 微信 => wechat  |  其他 => wap
        }
    },
    created() {
        this.checkEnv();
        console.warn(`当前运行环境：${this.env}`);
    },
    methods: {
        /**
         * 检测当前环境
         */
        checkEnv() {
            let _this = this;
            let ua = window.navigator.userAgent.toLowerCase();
            if (window.__wxjs_environment == "miniprogram") {
                _this.env = 'mini';
            } else if (ua.match(/MicroMessenger/i) == "micromessenger") {
                _this.env = 'wechat';
            } else {
                _this.env = 'wap';
            }
        }
    },
};