import {
    login,
    logout,
    login_state
} from "../../tool/login";
import {
    preious
} from '../../tool/history';
export const Auth = {
    data() {
        return {
            isLogined: false, // 是否登录？,
            uid: 0,
            _success: null, // 登录成功回调callback
            _fail: null, // 登录失败回调callback
        }
    },
    created() {
        // 登录成功
        if (this.afterLoginOK && typeof this.afterLoginOK === 'function') {
            this._success = this.afterLoginOK;
        }

        // 登录失败
        if (this.afterLoginFail && typeof this.afterLoginFail === 'function') {
            this._fail = this.afterLoginFail;
        }

        this.checkLogin();
    },
    mounted() {},
    methods: {
        /**
         * 检测登录状态
         */
        checkLogin() {
            let _this = this;
            _this.$http.post('/api/v1/User/isLogin').then(function(response) {
                if (parseInt(response.data.errno) === 0) {
                    _this.isLogined = true;
                    _this.uid = response.data.result.user_id;
                    _this.goUser();
                    if (_this._success && typeof _this._success === 'function') {
                        _this._success(response.data.result);
                    }
                } else {
                    logout();
                    if (_this._fail && typeof _this._fail === 'function') {
                        _this._fail(response.data.result);
                    } else {
                        _this.goLogin();
                    }

                }
            }).catch(function(error) {
                logout();
                if (_this._fail && typeof _this._fail === 'function') {
                    _this._fail(error);
                } else {
                    _this.goLogin();
                }

            });
        },
        /**
         * 去登录页面
         */
        goLogin() {
            this.$dialog.loading.close()
            if (this.$route.path !== '/login' && this.$route.path !== '/user') {
                this.$router.replace('/login');
            }
        },
        goUser() {
            if (this.$route.path === '/login' && this.isLogined) {
                // 登录后跳转
                var url = preious(false, true);
                if (url) {
                    window.location.href = url;
                } else {
                    this.$router.replace("/user");
                }
            }
        }
    },
};