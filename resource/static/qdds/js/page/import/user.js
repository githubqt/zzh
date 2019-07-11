
    // 下一步处理
    $('#next-btn').click(function () {
        $.ajax({
            url: location.href+"&action=comparse",
            type: 'get',
            data: {
                file_url: $('#file_url').val()
            },
            beforeSend: function () {
                $(this).attr('disable',true);
            },
            success: function (res) {
                $(this).attr('disable',false);
                $.messager.alert('提示', res.msg);
                location.reload();
            }
        });
        //同步定时任务查询进度
        //progressTimer = setTimeout('progress(1)', 1000);
    });

    //校验进度查询/导入进度查询
    function progress(step) {
        $.ajax({
            url: location.href + '&action=progress',
            type: 'get',
            success: function (res) {
                if (res.step == step) {
                    location.reload();
                    clearInterval(progressTimer);
                } else {
                    if (res.step == 1 && step == 2) {
                        $('#progress-bar').css('width', res.valid_progress_100 + "%");
                        $('#progress-text').text(res.valid_progress_100 + "%");
                    } else if (res.step == 3 && step == 4) {
                        $('#progress-bar').css('width', res.progress_100 + "%");
                        $('#progress-text').text(res.progress_100 + "%");
                        $('#success').text(res.success);
                        $('#fail').text(res.fail);
                    }
                }
            }
        })
    }

    //查看校验结果
    function valid_detail() {
        $('#dialog_html').dialog({
            title: '查看校验结果',
            top: 5,
            width: 1000,
            height: 500,
            closed: false,
            cache: false,
            href: '/index.php?m=Import&c=Import&a=user&action=validDetail',
            modal: true
        });
    }

    //重新上传
    function modify() {
        $.ajax({
            url: location.href + '&action=modify',
            type: 'get',
            success: function (res) {
                location.reload();
            }
        })
    }

    // 确定导入
    function submitimport() {
        $.ajax({
            url: location.href + '&action=import',
            type: 'get',
            success: function (res) {
                location.reload();
            }
        });
        //同步定时任务查询进度
        progressTimer = setTimeout('progress(4)', 1000);
    }