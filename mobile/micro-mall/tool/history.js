/**
 * 历史
 * @param {*} data
 */


function preious(url, remove) {
    var key = '$history-preious';
    if (url) {
        var host = window.location.host;
        var protocol = window.location.protocol;
        var fullurl = protocol + '//' + (host + url).replace('//', '/');
        // console.log('前页面地址:', fullurl);
        localStorage.setItem(key, encodeURIComponent(fullurl));
    } else {
        var url = localStorage.getItem(key) || false;
        if (remove) {
            localStorage.removeItem(key);
        }
        return url ? decodeURIComponent(url) : false;
    }
}

module.exports = {
    preious
}