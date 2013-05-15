$(document).ready(function() {
    // show panel header when site load
    $('#panel_recently, #panel_random, #panel_last_played, #panel_top_rated, #panel_recently_title, #panel_random_title, #panel_last_played_title, #panel_top_rated_title').hide().fadeIn(3000);
    $('#panel_recently, #panel_random, #panel_last_played, #panel_top_rated').mouseover(function(){
        $(this).animate({
            opacity: '.7'
        }, {
            queue:false,
            duration:300
        });
    });
    $('#panel_recently, #panel_random, #panel_last_played, #panel_top_rated').mouseleave(function(){
        $(this).animate({
            opacity: '1'
        }, {
            queue:false,
            duration:300
        });
    });

    // toggle box on left panel
    $('.panel_box_title').click(function(){
        var id = $(this).attr('id');
        $('#panel_'+id).slideToggle();
    });

    // hide info panel
    $('#panel_info').delay(4000).fadeOut(4000);

    // show panels in loop
    $(function() {
        var timeout = $('#panel_top').attr('class');
        $('#panel_recently, #panel_random, #panel_last_played, #panel_top_rated').cycle({
            timeout: +timeout
        });
    });

    // Default value for search input
    $('input').focus(function () {
	if ($(this).val() == $(this).attr('title')) {
		$(this).val('');
	}
    }).blur(function () {
	if ($(this).val() == '') {
		$(this).val($(this).attr('title'));
	}
    });  
    
    $('#mode_0').click(function(){
        $('.xbmc').attr('disabled', 'disabled');
        $('.xbmc').attr('class', 'xbmc disabled');
    });
    $('#mode_1').click(function(){
        $('.xbmc').removeAttr('disabled');
        $('.xbmc').attr('class', 'xbmc');
    });
    
    $('body').click(function(){
        $('#background').fadeOut(4000, function() {
css('background-image', 'url(\'img/bg2.jpg\')')
}).fadeIn(4000);
    });
    
    
});