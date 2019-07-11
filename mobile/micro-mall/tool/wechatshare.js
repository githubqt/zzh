/***
 *   微信分享js
 *
 *
 */

const weixin = require('weixin-js-sdk')

module.exports = {
    weChat: function (title, desc, url, img, Id, Time, Nonce, Signa) {


        wx.config({
            // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            debug: false,
            // 必填，公众号的唯一标识
            appId: Id,
            // 必填，生成签名的时间戳
            timestamp: "" + Time,
            // 必填，生成签名的随机串
            nonceStr: Nonce,
            // 必填，签名
            signature: Signa,
            // 必填，需要使用的JS接口列表，所有JS接口列表
            jsApiList: [
                'onMenuShareAppMessage',
                'onMenuShareQZone',
                'onMenuShareTimeline',
                'onMenuShareQQ',
                'onMenuShareWeibo']
        });

        wx.ready(function () {

            console.log("调用成功");
            //  “分享到QQ空间”
            wx.onMenuShareQZone({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: url, // 分享链接
                imgUrl: img, // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    console.log("qq空间分享成功");
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    console.log("qq空间分享失败");
                }
            });

            //分享到朋友圈
            wx.onMenuShareTimeline({
                title: title, // 分享标题
                link: url, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: img, // 分享图标
                success: function () {
                    // 用户点击了分享后执行的回调函数
                    console.log("分享到朋友圈成功");
                },

            });

            //分享到qq
            wx.onMenuShareQQ({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: url, // 分享链接
                imgUrl: img, // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    console.log("qq朋友分享成功");
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    console.log("qq朋友分享失败");
                }
            });

            //分享给朋友
            wx.onMenuShareAppMessage({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: url, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                imgUrl: img, // 分享图标
                type: '', // 分享类型,music、video或link，不填默认为link
                dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
                success: function (res) {
                    // 用户确认分享后执行的回调函数
                    console.log("分享给朋友成功返回的信息为:" + res);
                },
                cancel: function (res) {
                    // 用户取消分享后执行的回调函数
                    console.log("取消分享给朋友返回的信息为:" + res);
                }
            });

            //分享到微博
            wx.onMenuShareWeibo({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: url, // 分享链接
                imgUrl: img, // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                    console.log("微博成功");
                    // this.$router.push(location.href.split('#')[0]);
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                    console.log("微博取消");
                    // this.$router.push(location.href.split('#')[0]);
                }
            });

        });

    }

}