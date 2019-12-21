// const shuoshuo_api_url = '/api/';

$(function () {

    // 右侧悬浮
    if ($(".right-absolute-outer").length > 0) {
        $(".right-absolute-outer").click(function () {
            console.log($(this));
            $(this).hide();
            $(".right-absolute-inner").css('display', 'flex');
        });
    }

    if ($(".close-right-ab-inner").length > 0) {
        $(".close-right-ab-inner").click(function () {
            $('.right-absolute-inner').hide();
            $(".right-absolute-outer").css('display', 'flex');
        });
    }

    // 标注试题
    if ($(".right-absolute-inner #to_mark_subjects").length > 0) {
        // $(".right-absolute-inner #to_mark_subjects").click(function () {
            
        // })
    }

    // 继续做题
    if ($(".right-absolute-inner #to_undo_subjects").length > 0) {
        // $(".right-absolute-inner #to_mark_subjects").click(function () {

        // })
    }

    // 已做试题
    if ($(".right-absolute-inner #to_done_subjects").length > 0) {
        // $(".right-absolute-inner #to_mark_subjects").click(function () {

        // })
    }
});