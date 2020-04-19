
/*
时间倒计时插件
timedown.js
*/
function timedown(id, totalSeconds) {
    totalSeconds = Number(totalSeconds);
    // console.log('totalSeconds::',totalSeconds);
    if (totalSeconds < 0) {
        return;
    }
    //结束时间
    // var endDate = new Date(endDateStr);
    //当前时间
    // var nowDate = new Date();
    //相差的总秒数
    // var totalSeconds = parseInt((endDate - nowDate) / 1000);
    //天数
    var days = Math.floor(totalSeconds / (60 * 60 * 24));
    //取模（余数）
    var modulo = totalSeconds % (60 * 60 * 24);
    //小时数
    var hours = Math.floor(modulo / (60 * 60));
    modulo = modulo % (60 * 60);
    //分钟
    var minutes = Math.floor(modulo / 60);
    //秒
    var seconds = modulo % 60;
    
    //输出到页面
    var time_str = '';
    if (days > 0) {
        time_str = days + "天";
    }
    if (hours > 0) {
        time_str += hours + "小时";
    }
    if (minutes > 0) {
        time_str += minutes + "分钟";
    }
    if (seconds >= 0) {
        time_str += seconds + "秒";
    }

    document.getElementById(id).innerHTML = '剩余时间：' + time_str;

    //延迟一秒执行自己
    var timer_id = setTimeout(function () {
        totalSeconds = totalSeconds - 1;
        timedown(id, totalSeconds);
    }, 1000);
    if (totalSeconds <= 0) {

        clearTimeout(timer_id);

        $.modal({
            title: "提示",
            text: "考试已结束，3秒后，系统将自动为您交卷",
            buttons: [
              { text: '' },
            ]
          });
        var timer_id2 = setTimeout(function () {
            clearTimeout(timer_id2);
            dosubcommit_redirect(1);
        }, 3000);
        return;

        document.getElementById(id).innerHTML = '';
        
    }
}