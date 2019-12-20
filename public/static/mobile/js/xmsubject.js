$(function(){

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
                    console.log(data)
                },
                error: function(data) {
                    alert('系统异常，请联系管理员或稍后重试');
                    return;
                },
                dataType: 'json'
              });
        }
    );
    $('.pager a').click(
        function() {
            leave_page($(this));
            var checked_radio = $("input[type='radio']:checked", ".weui-form").val();
            console.log('checked_radio::', checked_radio);
            return false;
        }
    )
})

// 点击分页时触发
function leave_page(pager) {
    console.log('pager::',$(pager).attr('href'));
    
}