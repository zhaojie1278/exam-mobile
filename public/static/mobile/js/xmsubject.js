$(function(){

    // 计时器
    if ($("#remainder_time_seconds").length > 0) {
        timedown('reminder_timer', $("#remainder_time_seconds").text());
    }

    // 单选控制
    $("input[sub_id]").click(
        function(){
            var sub_id = $(this).attr('sub_id');
            var sub_val = $(this).val();
            var u_id = $(this).attr('u_id');

            $.ajax({
                type: 'POST',
                url: '/api/xmsubject/dosub',
                data: {sub_id: sub_id, answer: sub_val, u_id: u_id},
                success: function(data){

                    // 登录跳转
                    if (undefined != data.data.rs_login_url && data.data.rs_login_url != '') {
                        tologin(data.data.rs_login_url, data.message);
                        return;
                    }
                    // --

                    // 已交卷操作
                    if (undefined != data.data.rs_subdone_url && data.data.rs_subdone_url != '') {
                        to_subdone(data.data.rs_subdone_url, data.message);
                        return;
                    }
                    // --

                    if (data.status == 0) {
                        if (undefined != data.message && data.message) {
                            $.alert(data.message);
                        } else {
                            $.alert('做题失败，请联系管理员或稍后重试');
                        }
                    } else {
                        // 做题成功
                    }
                },
                error: function(data) {
                    alert('系统异常，请联系管理员或稍后重试');
                    return;
                },
                dataType: 'json'
              });
        }
    );

    // 翻页控制
    $('.pagination a').click(
        function() {
            // leave_page($(this));
            // var checked_radio = $("input[type='radio']:checked", ".weui-form").val();
            // console.log('checked_radio::', checked_radio);
            var a_href = $(this).attr('href');
            if ($(this).hasClass('commitsub')) {
                var check = check_done_subject(true);
                // 后台验证
                if (check) {
                    dosubcommit();
                }
            } else {
                var check = check_done_subject(false);
                if (check) {
                    window.location.href=a_href;
                }
            }
            return false;
        }
    )

    // 标注
    $("span[id^='mark_sub_id_']").click(function(){
        var sub_id = $(this).attr('for_mark_sub_id');
        console.log(sub_id);
        domark(sub_id);
    });
})

// 本页题目是否已做完[网页js验证]
function check_done_subject(is_commitsub) {
    var check_rs = true;
    if (is_commitsub) {
        // 是否交卷按钮
    } else {
        $("input:hidden[id^='hidden_sub_id_']").each(
            function (index) {
                var rd_name = $(this).attr('for_radio_name');
                var rd_for_sub_id = $(this).attr('for_radio_sub_id');
                if (is_commitsub) {
                    // 是否交卷按钮
                    /* if (undefined == $("input:radio[name='" + rd_name + "']:checked", ".weui-form").val()
                        || null == $("input:radio[name='" + rd_name + "']:checked", ".weui-form").val()) {
                        $.alert("请做完本页题，再提交试卷");
                        check_rs = false;
                        return check_rs;
                    } else {
                        return true;
                    } */
                } else {
                    if ((undefined == $("input:radio[name='" + rd_name + "']:checked", ".weui-form").val()
                        || null == $("input:radio[name='" + rd_name + "']:checked", ".weui-form").val())
                        && $("#mark_sub_id_" + rd_for_sub_id).attr('for_mark_stu') == 0
                    ) {
                        $.alert("请做完本页题，再继续下页题目，如果有不确定的，您可以先[<span style='color: #1aad19;'>标注一下</span>]，标注的可以后续再答题");
                        check_rs = false;
                        return check_rs;
                    } else {
                        return true;
                    }
                }
            }
        );
    }
    return check_rs;
}

// 提交试卷
function dosubcommit() {
    $.ajax({
        type: 'POST',
        url: '/api/xmsubject/dosubcommitbefore',
        data: {},
        success: function (data) {
            console.log(data)
            if (data.status == 0) {

                // 登录跳转
                if (undefined != data.data.rs_login_url && data.data.rs_login_url != '') {
                    tologin(data.data.rs_login_url, data.message);
                    return;
                }
                // --

                // 已交卷操作
                if (undefined != data.data.rs_subdone_url && data.data.rs_subdone_url != '') {
                    to_subdone(data.data.rs_subdone_url, data.message);
                    return;
                }
                // --
                
                if (undefined != data.data.isprompt && data.data.isprompt) {
                    $.confirm(data.message, function () {
                        //点击确认后的回调函数
                        dosubcommit_redirect();
                    }, function () {
                        //点击取消后的回调函数
                        return;
                    });
                } else {
                    if (undefined != data.message && data.message) {
                        $.alert(data.message);
                    } else {
                        $.alert('提交失败，请稍后重试');
                    }
                }
                return;
            } else {
                $.confirm('您确认交卷吗？', function () {
                    //点击确认后的回调函数
                    dosubcommit_redirect();
                }, function () {
                    //点击取消后的回调函数
                    return;
                });
            }
        },
        error: function (data) {
            alert('系统异常，请联系管理员或稍后重试');
            return;
        },
        dataType: 'json'
    });
}

// 提交试卷-直接
function dosubcommit_redirect() {
    $.ajax({
        type: 'POST',
        url: '/api/xmsubject/dosubcommit',
        data: {},
        success: function (data) {

            // 登录跳转
            if (undefined != data.data.rs_login_url && data.data.rs_login_url != '') {
                tologin(data.data.rs_login_url, data.message);
                return;
            }
            // --
            
            // 已交卷操作
            if (undefined != data.data.rs_subdone_url && data.data.rs_subdone_url != '') {
                to_subdone(data.data.rs_subdone_url, data.message);
                return;
            }
            // --

            if (data.status == 0) {
                if (undefined != data.message && data.message) {
                    $.alert(data.message);
                } else {
                    $.alert('提交失败，请联系管理员或稍后重试');
                }
                return;
            } else {
                // console.log( data.data.a_href);
                window.location.href = data.data.a_href;
            }
        },
        error: function (data) {
            alert('系统异常，请联系管理员或稍后重试');
            return;
        },
        dataType: 'json'
    });
}

// 标注
function domark(sub_id) {
    var _mark = $("#mark_sub_id_"+sub_id);
    var is_mark = _mark.attr('for_mark_stu');
    $.ajax({
        type: 'POST',
        url: '/api/xmsubject/domark',
        data: {sub_id: sub_id, is_mark: is_mark},
        success: function(data){

            // 登录跳转
            if (undefined != data.data.rs_login_url && data.data.rs_login_url != '') {
                tologin(data.data.rs_login_url, data.message);
                return;
            }
            // --
            
            // 已交卷操作
            if (undefined != data.data.rs_subdone_url && data.data.rs_subdone_url != '') {
                to_subdone(data.data.rs_subdone_url, data.message);
                return;
            }
            // --

            if (data.status == 0) {
                if (undefined != data.message && data.message) {
                    $.alert(data.message);
                } else {
                    $.alert('标注失败，请联系管理员或稍后重试');
                }
            } else {
                // 标注成功
                var mark_do_msg = is_mark == 1 ? '已取消' : '已标注';
                $.toast(mark_do_msg, 1000, function(){
                    if (is_mark == 1) {
                        _mark.html('标注一下');
                        // _mark.removeClass('weui-btn_default');
                        // _mark.addClass('weui-btn_primary');
                        _mark.css('color', '#1aad19');
                        _mark.attr('for_mark_stu', 0);
                    } else {
                        _mark.html('取消标注');
                        _mark.css('color', '#666666');
                        // _mark.removeClass('weui-btn_primary');
                        // _mark.addClass('weui-btn_default');
                        _mark.attr('for_mark_stu', 1);
                    }
                });
            }
        },
        error: function(data) {
            alert('标注-系统异常，请联系管理员或稍后重试');
            return;
        },
        dataType: 'json'
    });
}

// 跳至登录页
function tologin(rs_login_url, msg) {
    var alert_msg = '您尚未登录，点击确定将为您跳至登录页';
    if (undefined != msg) {
        alert_msg = msg + '，点击确定将为您跳至登录页';
    }
    $.alert(alert_msg, function () {
        window.location.href = rs_login_url;
    })
}

// 跳至登录页
function to_subdone(rs_login_url, msg) {
    var alert_msg = '您已交卷，点击确定将为您跳至登录页';
    if (undefined != msg) {
        alert_msg = msg + '，点击确定将为您跳转';
    }
    $.alert(alert_msg, function () {
        window.location.href = rs_login_url;
    })
}
