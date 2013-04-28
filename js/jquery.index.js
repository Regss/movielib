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
    
    var i = 1;
    var e = 3000;
    for (var i = 1; i < 4; i++) {
    $('#rec_'+i).fadeIn(1000+e).delay(1000).fadeOut(1000);
    e+3000;
    }

});