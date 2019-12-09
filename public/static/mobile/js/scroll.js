/*
 **	Anderson Ferminiano
 **	contato@andersonferminiano.com -- feel free to contact me for bugs or new implementations.
 **	jQuery ScrollPagination
 **	2019 use
 **	http://andersonferminiano.com/jqueryscrollpagination/
 **	You may use this script for free, but keep my credits.
 **	Thank you.
 */
var last_p = 1;
(function($){
    $.fn.scrollPagination = function(options) {
        var opts = $.extend($.fn.scrollPagination.defaults, options);
        var target = opts.scrollTarget;
        if (target == null){
            target = obj;
        }
        opts.scrollTarget = target;

        return this.each(function() {
            $.fn.scrollPagination.init($(this), opts);
        });

    };

    $.fn.stopScrollPagination = function(){
        return this.each(function() {
            $(this).attr('scrollPagination', 'disabled');
        });

    };

    $.fn.scrollPagination.loadContent = function(obj, opts){
        if(opts.end == 1) return;
        var obj = $(opts.target_obj);
        var target = opts.scrollTarget;
        var mayLoadContent = $(target).scrollTop()+opts.heightOffset >= $(document).height() - $(target).height();
        if (mayLoadContent){
            if (opts.beforeLoad != null){
                opts.beforeLoad();
            }
            $(obj).children().attr('rel', 'loaded');
            var params = $(obj).data();
            delete params.page;
            delete params.hasend;
            delete params.path;
            params.page_index = opts.page;
            if(opts.page != last_p){
                last_p = opts.page;
                $.ajax({
                    type: 'GET',
                    url: opts.contentPage,
                    data: params,
                    success: function(data){
                        var parse_data = JSON.parse(data);
                        console.log('parse_data::', parse_data);
                        var end = 0;
                        if (parse_data != 0){
                            opts.page = opts.page + 1;
                            $(obj).append(parse_data);

                        }else{
                            end = 1;
                        }
                        var objectsRendered = $(obj).children('[rel!=loaded]');

                        if (opts.afterLoad != null){
                            opts.afterLoad(objectsRendered, end);
                        }
                    },
                    dataType: 'html'
                });
            }
        }

    };

    $.fn.scrollPagination.init = function(obj, opts){
        var target = opts.scrollTarget;
        $(obj).attr('scrollPagination', 'enabled');

        $(target).scroll(function(event){
            if ($(obj).attr('scrollPagination') == 'enabled'){
                $.fn.scrollPagination.loadContent(obj, opts);
            }
            else {
                event.stopPropagation();
            }
        });

        $.fn.scrollPagination.loadContent(obj, opts);

    };

    $.fn.scrollPagination.defaults = {
        'contentPage' : null,
        'contentData' : {},
        'beforeLoad': null,
        'afterLoad': null	,
        'scrollTarget': null,
        'heightOffset': 0,
    };
})( jQuery );
/**
 * 页面上拉加载更多控制
 */
function scrollList(obj){
    $('.page_loading').html('正在加载...');
    $('.page_loading').fadeOut();
    $('.page_loading').removeAttr('end');
    var url =  $(obj).attr("data-path");
    var page =  $(obj).attr("data-page");
    page = Number(page);
    var hasend =  $(obj).data("hasend");
    last_p = page-1;
    $(obj).scrollPagination({
        'contentPage': url,
        'contentData': {},
        'scrollTarget': $(window),
        'heightOffset': 3,
        'page':page,
        'target_obj':obj,
        'beforeLoad': function(){
            if(hasend!=1){
                $('.page_loading').fadeIn();
            }
        },
        'afterLoad': function(elementsLoaded, end){
            page = page + 1;
            $(obj).attr("data-page", page);
            $(obj).next('.page_loading').fadeOut();
            var i = 0;
            $(elementsLoaded).fadeInWithDelay();
            if (end == 1){
                hasend = 1;
                $('.page_loading').fadeOut();
                $('.page_loading').html("已经最后一页了~~");
                $('.page_loading').attr("end", 1);
                $('.page_loading').hide();
                $(obj).stopScrollPagination();

            }
        }
    });
}
$(document).ready(function(){
    if($("#content").length > 0){
        scrollList("#content");
    }
    $.fn.fadeInWithDelay = function(){
        var delay = 0;
        return this.each(function(){
            $(this).delay(delay).animate({opacity:1}, 200);
            delay += 100;
        });
    };
});