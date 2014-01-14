$(document).ready(function() {
    // show panel header when site load
    $('.panel_top_item, .panel_top_item_title').hide().fadeIn(3000);
    $('.panel_top_item').mouseover(function(){
        $(this).animate({
            opacity: '.7'
        }, {
            queue:false,
            duration:300
        });
    });
    $('.panel_top_item').mouseleave(function(){
        $(this).animate({
            opacity: '1'
        }, {
            queue:false,
            duration:300
        });
    });

    // hide info panel
    $('.panel_info').delay(4000).fadeOut(4000);
    
    // show panels in loop
    $(function() {
        var timeout = $('#panel_top').attr('class');
        $('.panel_top_item').cycle({
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
    
    // change background
    if ($('#background').attr('alt') == 1) {
        var bg = $('#background').attr('src');
        // mouse enter
        $('.movie').mouseenter(function(){
            var movie_id = $(this).attr('id');
            $.ajax({
                url: 'cache/'+movie_id+'_f.jpg',
                success: function(data){
                    $('#background').fadeOut(500, function(){
                        $(this).delay(100).attr('src', 'cache/'+movie_id+'_f.jpg');
                        $(this).fadeIn(500);
                    });
                }
            });
        });
        // mouse leave
        $('.movie').mouseleave(function(){
            var movie_id = $(this).attr('id');
            $.ajax({
                url: 'cache/'+movie_id+'_f.jpg',
                success: function(data){
                    $('#background').fadeOut(500, function(){
                        $(this).delay(100).attr('src', bg);
                        $(this).fadeIn(500);
                    });
                }
            });
        });
    }
    
    // toggle panel_box
    $('.panel_box').each(function(){
        var opt = $(this).attr('class').replace('panel_box ', '');
        if (opt == 2) {
            var id = $(this).attr('id');
            $('#'+id).hide();
        }
    });
    $('.panel_box_title').click(function(){
        var id = $(this).attr('id');
        $('#panel_'+id).slideToggle();
        var opt = $('#panel_'+id).attr('class').replace('panel_box ', '');
        if (opt == 1) {
            $('#panel_'+id).attr('alt', 'panel_box 2');
            $.ajax({url: 'function.js.php?option=panel&id=panel_'+id+'&opt=2'});
        }
        if (opt == 2) {
            $('#panel_'+id).attr('alt', 'panel_box 1');
            $.ajax({url: 'function.js.php?option=panel&id=panel_'+id+'&opt=1'});
        }
    });
    
    // animate delete button
    $('.delete_row').mouseenter(function(){
        $(this).css('opacity', '.7');
    });
    $('.delete_row').mouseleave(function(){
        $(this).css('opacity', '1');
    });
    
    // animate trailer button
    $('.img_trailer').mouseenter(function(){
        $(this).css('opacity', '1');
    });
    $('.img_trailer').mouseleave(function(){
        $(this).css('opacity', '.8');
    });
    
    // delete movie
    $('.delete_row').click(function(){
        var id = $(this).attr('id');
        $('#row_'+id).hide();
        $.ajax({url: 'function.js.php?option=delete&id='+id});
    });
});