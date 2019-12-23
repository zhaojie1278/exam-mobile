$(function () {
    var $input = $('.js_input');
    $input.on('input', function () {
        if ($input.val()) {
            $('#login_btn').removeClass('weui-btn_disabled weui-btn_default').addClass('weui-btn_primary');
        } else {
            $('#login_btn').removeClass('weui-btn_primary').addClass('weui-btn_disabled weui-btn_default');
        }
    });

    // 登录
    $('#login_btn').on('click', function () {
        if ($(this).hasClass('weui-btn_disabled')) return;

        var cid = $("select[name='cid']").val();
        var class_no = $("#class_no").val();
        var real_name = $("#real_name").val();
        var phone = $("#phone").val();
        if (cid == '0') {
            $.alert('请选择科目')
            return;
        }
        if (class_no == '') {
            $.alert('请填写学号信息')
            return;
        }
        if (real_name == '') {
            $.alert('请填写姓名')
            return;
        }
        if (phone == '') {
            $.alert('请填写手机号')
            return;
        }

        // 点击控制
        $('#login_btn').removeClass('weui-btn_primary').addClass('weui-btn_disabled weui-btn_default');
        
        $.ajax({
            type: 'POST',
            url: '/api/login/dologin',
            data: {cid: cid, class_no: class_no, real_name: real_name, phone: phone},
            success: function(data){

                // 点击控制
                $('#login_btn').removeClass('weui-btn_disabled weui-btn_default').addClass('weui-btn_primary');

                if (data.status == 0) {
                    if (undefined != data.message && data.message) {
                        $.alert(data.message, function(){
                            if (data.data.re_href) {
                                window.location.href = data.data.re_href;
                            }
                        });
                    } else {
                        $.alert('登录失败，请联系管理员或稍后重试');
                    }
                } else {
                    // 登录成功
                    window.location.href = data.data.re_href;
                }
            },
            error: function(data) {
                // 点击控制
                $('#login_btn').removeClass('weui-btn_disabled weui-btn_default').addClass('weui-btn_primary');

                alert('登录系统异常，请联系管理员或稍后重试');
                return;
            },
            dataType: 'json'
          });
    });
});

