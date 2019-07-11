/**
 * 登录
 * @param {*} data
 */
function login(data) {
    localStorage.setItem('$app_login_state', JSON.stringify(data));
    setUserId(data.user_id);
}

/**
 * 获取登录状态
 */
function getState() {
    var data = localStorage.getItem('$app_login_state');
    if (data) {
        return JSON.parse(data);
    } else {
        return {
            "user_id": '',
            "token": ""
        };
    }
}

/**
 * 退出
 */
function logout() {
    localStorage.setItem('$app_login_state', JSON.stringify({
        "user_id": '',
        "token": ""
    }));

    setUserId('');
}

function setUserId(user_id) {
    localStorage.setItem('userId', user_id);
}


/**
 * 商户登录
 * @param {*} data
 */
function adminLogin(data) {
    localStorage.setItem('$app_admin_login_state', JSON.stringify(data));
}

/**
 * 获取商户登录状态
 */
function getAdminState() {
    var data = localStorage.getItem('$app_admin_login_state');
    if (data) {
        return JSON.parse(data);
    } else {
        return {
            "admin": {},
            "token": ""
        };
    }
}

/**
 * 商户退出
 */
function adminLogout() {
    localStorage.setItem('$app_admin_login_state', JSON.stringify({
        "admin": {},
        "token": ""
    }));
}


module.exports = {
    login: login,
    logout: logout,
    login_state: getState,
    adminLogin: adminLogin,
    getAdminState: getAdminState,
    adminLogout: adminLogout,
}