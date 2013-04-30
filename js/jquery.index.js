$(function() {
    $('#panel_recently, #panel_random, #panel_last_played').mouseover(function(){
        $(this).animate({
            opacity: '.7'
        }, {
            queue:false,
            duration:300
        });
    });
    $('#panel_recently, #panel_random, #panel_last_played').mouseleave(function(){
        $(this).animate({
            opacity: '1'
        }, {
            queue:false,
            duration:300
        });
    });
    
    $('.movie').mouseover(function(){
        var id = $(this).attr('id');
        $('#poster_'+id).animate({
            opacity: '.7',
            width: '144px',
            height: '202px'
        }, {
            queue:false,
            duration:300
        });
    });
    $('.movie').mouseleave(function(){
        var id = $(this).attr('id');
        $('#poster_'+id).animate({
            opacity: '1'
        }, {
            queue:false,
            duration:300
        });
    });
    
    $('#panel_info').fadeIn(5000).delay(3000).fadeOut(5000);
    
    $('#panel_recently, #panel_random, #panel_last_played, #panel_recently_title, #panel_random_title, #panel_last_played_title').hide().fadeIn(3000);
    
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
        $("#panel_last_played").cycle({
            timeout: 4000
        });
    });
});