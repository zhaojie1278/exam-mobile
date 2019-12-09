// 说说
/* 
// 列表数据集
var shuoshuo_list = {};

$(function(){
    getlist();
    console.log(shuoshuo_list);
});


// 获取说说列表
function getlist() {
    $.ajax({
        type: 'get',
        contentType: 'application/json;charset=UTF-8',
        url: shuoshuo_api_url+'index/list',
        data: {
            'version' : '1.0'
        },
        success: function(rs) {
            console.log(rs);
            shuoshuo_list = rs.data;
        },
        dataType: 'json'
    });
} */