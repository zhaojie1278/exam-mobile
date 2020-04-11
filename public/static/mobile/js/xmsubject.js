// 防抖操作变量 --
var app_last_time = 0;
var app_last_time_old = 0;
var app_move_dis = 1000; // 1秒
var app_checked_radio = null;
var app_do_sub_timer = null;
var app_dosub_server_rs = 1; // 服务器返回后才可继续做题
// --

$(function(){

    // 考试时间计时器
    if ($("#remainder_time_seconds").length > 0) {
        timedown('reminder_timer', $("#remainder_time_seconds").text());
    }

    // 单选控制
    $("input[sub_id]").bind('click', 
        function() {

            // 防止点击过快
            app_checked_radio = $(this);
            app_last_time_old = app_last_time;
            app_last_time = (new Date()).valueOf();
            
            if(app_do_sub_timer !== null){
                clearTimeout(app_do_sub_timer);
            }
            
            // 200 毫秒后执行，防止点击过快
            app_do_sub_timer = setTimeout(function(){
                if(app_last_time-app_last_time_old < app_move_dis){
                    // console.log('app_last_time_old - app_last_time < app_move_dis')

                    $.alert('答题过快，请认真作答，“点击确定后请查看界面上有灰底的选项是否你要选的选项”，<font color="red">如果不一致，请重新选择后继续答题</font>');
                    return;
                } else {
                    // app_last_time_old = 0;
                    // app_last_time = 0;

                    // console.log('app_dosub_server_rs::',app_dosub_server_rs);
                    // 服务器返回后才可继续做题
                    if (app_dosub_server_rs != 1) {
                        $.alert('网络请求过慢，“点击确定后请查看界面上有灰底的选项是否你要选的选项”，<font color="red">如果不一致，请重新选择后继续答题</font>');
                        return;
                    } else {
                        app_dosub_server_rs = 0;
                    }
                    
                    do_sub(app_checked_radio);
                    app_checked_radio = null;
                    return;
                }
            },100);
            return;
        }
    );

    // 翻页控制
    $('.pagination a').click(
        function() {
            if ($(this).hasClass('weui-btn_disabled')) return false;

            // leave_page($(this));
            // var checked_radio = $("input[type='radio']:checked", ".weui-form").val();
            // console.log('checked_radio::', checked_radio);
            var a_href = $(this).attr('href');
            if ($(this).hasClass('commitsub')) {
                var check = check_done_subject(true);
                // 后台验证
                if (check) {
                    dosubcommit($(this));
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
        // console.log(sub_id);
        var _mark = $("#mark_sub_id_"+sub_id);
        if (_mark.hasClass('mark-disable')) {
            return;
        }
        domark(sub_id);
    });

    // 发送错题记录至邮箱
    $("#send_mail").click(function() {
        if ($(this).hasClass('send_disabled')) return;
        if ($(this).hasClass('send_sended')) {
            $.alert('邮件已发送，请去邮箱 '+$("#send_mail").attr('to_mail')+' 查收');
            return;
        }

        $.showLoading('发送中...');

        $("#send_mail").addClass('send_disabled');
        $.ajax({
        type: 'POST',
        url: '/api/xmsubject/exporttomail',
        data: {},
        success: function (data) {
            $.hideLoading();
            // 登录跳转
            if (undefined != data.data.rs_login_url && data.data.rs_login_url != '') {
                tologin(data.data.rs_login_url, data.message);
                return;
            }
            // --

            $("#send_mail").removeClass('send_disabled');
            if (data.status == 0) {
                if (undefined != data.message && data.message) {
                    $.alert(data.message);
                } else {
                    $.alert('发送失败，请联系管理员或稍后重试');
                }
            } else {
                $("#send_mail").addClass('send_sended');
                var mail = data.data.mail;
                $("#send_mail").attr('to_mail', mail);
                $.alert('邮件已发送，请去邮箱 ' + mail + ' 查收');
            }
        },
        error: function (data) {
            $.hideLoading();
            $("#send_mail").removeClass('send_disabled');
            $.alert('系统异常，请联系管理员或稍后重试');
            return;
        },
        dataType: 'json'
    });
    })
});

// 做题
function do_sub(checked_radio) {
    if (checked_radio == null) {
        $.alert('做题失败，请重新选择');
        return;
    }
    console.log('checked_radio:',checked_radio);
    if (checked_radio.parents('.weui-check__label').hasClass('dosub-lbl-checked')) {
        console.log('in do_sub: also checked');
        return;
    }
    console.log('in do_sub::', checked_radio);
    var sub_id = checked_radio.attr('sub_id');
    var sub_val = checked_radio.val();
    var u_id = checked_radio.attr('u_id');
    var this_id = checked_radio.attr('id');

    $.ajax({
        type: 'POST',
        url: '/api/xmsubject/dosub',
        data: { sub_id: sub_id, answer: sub_val, u_id: u_id },
        success: function (data) {

            // 上一题做完有正确返回才可做下一题，避免网络慢的情况
            app_dosub_server_rs = 1;

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

                // 单选框选项样式
                var do_lbl = $(".weui-check__label[for='" + this_id + "']");
                console.log(do_lbl.parent(".weui-cells_checkbox").attr('class'));
                do_lbl.parent(".weui-cells_checkbox").find(".dosub-lbl-checked").removeClass('dosub-lbl-checked');
                do_lbl.addClass('dosub-lbl-checked');

                // 单选框样式
                do_lbl.parent(".weui-cells_checkbox").find(".weui-icon-checkbox-diy-checked")
                    .removeClass('weui-icon-checkbox-diy-checked')
                    .addClass('weui-icon-checkbox-diy-uncheck');
                do_lbl.find(".weui-icon-checkbox-diy")
                    .removeClass('weui-icon-checkbox-diy-uncheck')
                    .addClass('weui-icon-checkbox-diy-checked');
            }
        },
        error: function (data) {
            // 上一题做完有正确返回才可做下一题，避免网络慢的情况
            app_dosub_server_rs = 1;
            alert('系统异常，请联系管理员或稍后重试');
            return;
        },
        dataType: 'json'
    });
}

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
                        $.alert("请做完本页题，再继续下页题目，如果有不确定的，你可以先[<span style='color: #1aad19;'>标注一下</span>]，标注的可以后续再答题");
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
function dosubcommit(subcommit_a) {
    // 点击控制
    $(this).removeClass('weui-btn_primary').addClass('weui-btn_disabled weui-btn_default');
    
    $.ajax({
        type: 'POST',
        url: '/api/xmsubject/dosubcommitbefore',
        data: {},
        success: function (data) {

            // 点击控制
            subcommit_a.removeClass('weui-btn_disabled weui-btn_default').addClass('weui-btn_primary');
            
            // console.log(data)
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
                $.confirm('你确认交卷吗？', function () {
                    //点击确认后的回调函数
                    dosubcommit_redirect();
                }, function () {
                    //点击取消后的回调函数
                    return;
                });
            }
        },
        error: function (data) {
            // 点击控制
            subcommit_a.removeClass('weui-btn_disabled weui-btn_default').addClass('weui-btn_primary');

            alert('系统异常，请联系管理员或稍后重试');
            return;
        },
        dataType: 'json'
    });
}

// 提交试卷-直接
function dosubcommit_redirect(is_auto = 0) {
    $.ajax({
        type: 'POST',
        url: '/api/xmsubject/dosubcommit',
        data: {auto: is_auto},
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
    var old_mark_html = _mark.html();
    _mark.addClass('mark-disable');
    _mark.html('处理中...');
    $.ajax({
        type: 'POST',
        url: '/api/xmsubject/domark',
        data: {sub_id: sub_id, is_mark: is_mark},
        success: function(data){

            _mark.removeClass('mark-disable');

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
            _mark.removeClass('mark-disable');
            alert('标注-系统异常，请联系管理员或稍后重试');
            _mark.html(old_mark_html);
            return;
        },
        dataType: 'json'
    });
}

// 跳至登录页
function tologin(rs_login_url, msg) {
    var alert_msg = '你尚未登录，点击确定将为你跳至登录页';
    if (undefined != msg) {
        alert_msg = msg + '，点击确定将为你跳至登录页';
    }
    $.alert(alert_msg, function () {
        window.location.href = rs_login_url;
    })
}

// 跳至登录页
function to_subdone(rs_login_url, msg) {
    var alert_msg = '你已交卷，点击确定将为你跳至登录页';
    if (undefined != msg) {
        alert_msg = msg + '，点击确定将为你跳转';
    }
    $.alert(alert_msg, function () {
        window.location.href = rs_login_url;
    })
}
