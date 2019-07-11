const storageKey = '$supplier_create_order_form';
const storageEditKey = '$supplier_create_order_form_edit';
/**
 * 设置
 * @param {*} data
 */
function setStorage(key, value) {
    let data = getStorage() || {};
    data[key] = value;
    localStorage.setItem(getStorageKey(), JSON.stringify(data));
}

/**
 * 获取
 */
function getStorage() {
    var data = localStorage.getItem(getStorageKey());
    if (data === null) {
        return false;
    } else {
        const json = JSON.parse(data);
        if (arguments.length === 1) {
            if (json[arguments[0]]) {
                return json[arguments[0]];
            }
            return false;
        } else {
            return json;
        }
    }
}

/**
 * 是否编辑
 */
function isEdit() {
    var data = localStorage.getItem('recyclingEdit');
    return data === 'edit';
}
/**
 * 设置为编辑
 */
function setEdit() {
    localStorage.setItem('recyclingEdit', 'edit');
}
/**
 * 清除编辑
 */
function clearEdit() {
    localStorage.removeItem('recyclingEdit');
    localStorage.removeItem(storageEditKey);
}
/**
 * 清除
 */
function clearStorage() {
    localStorage.removeItem(getStorageKey());
}

function getStorageKey() {
    if (isEdit()) {
        return storageEditKey;
    } else {
        return storageKey;
    }
}



module.exports = {
    setStorage,
    getStorage,
    clearStorage,
    isEdit,
    setEdit,
    clearEdit
};