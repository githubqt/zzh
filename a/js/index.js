

$.ajax({
    type: "POST",
    async: true,  // 设置同步方式
    url: "http://api.zhahehe.com/v1/Machine/log",
    data: {'identif':'test'},
    dateType: "json",
    success: function (res) {
        console.log(res);
    }
});