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
    $('#panel_info').fadeIn(5000).delay(3000).fadeOut(5000);
    
    
    $(function() {
        $("#panel_recently").cycle({
            timeout: 4000
        });
    });
    $(function() {
        $("#panel_random").cycle({
            timeout: 4000
        });
    });
    $(function() {
        $("#panel_premiere").cycle({
            timeout: 4000
        });
    });
});