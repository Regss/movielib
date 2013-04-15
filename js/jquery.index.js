$(function() {
    $('.recently_img').css({
        'opacity' : '.7'
    });
    $('.recently_img').mouseover(function(){
        $(this).animate({
            opacity: '1'
        }, {
            queue:false,
            duration:300
        });
    });
    $('.recently_img').mouseleave(function(){
        $(this).animate({
            opacity: '.7'
        }, {
            queue:false,
            duration:300
        });
    });
});