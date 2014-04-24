// get settings
var show_fanart;
var fadeout_fanart;
var panel_top_time;
$.ajax({
    dataType: "json",
    url: "function.js.php?option=settings",
    async: false,
    success: function(set){
        show_fanart = set['show_fanart'];
        fadeout_fanart = set['fadeout_fanart'];
        panel_top_time = set['panel_top_time'];
    }
});

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
        var timeout = panel_top_time * 1000;
        $('.panel_top_item').cycle({
            timeout: +timeout
        });
    });

    // view menu
    $('#view_menu').mouseenter(function () {
        $('#views').show();
    });
    $('#view_menu').mouseleave(function () {
        $('#views').hide();
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
    
    // live search movie
    var wait;
    $(document).on('keyup click', '#search_movies', function() {
        clearTimeout(wait);
        wait = setTimeout(function() {
            var search = $('#search_movies').val();
            if (search.length > 0) {
                $.getJSON("function.js.php?option=searchmovie&search="+search, function(data){
                    $('#panel_live_search').empty();
                    for(var m in data) {
                        var movie = data[m];
                        $('#panel_live_search').append('\
                        <a href="index.php?video=movies&id='+movie['id']+'">\
                            <div class="live_search_box" title="'+movie['title']+'">\
                                <img class="img_live_search" src="' + movie['poster'] + '">\
                                <div class="live_search_title">'+movie['title']+'</div>\
                                <div class="live_search_orig_title">'+movie['originaltitle']+'</div>\
                                '+movie['year']+' | '+movie['rating']+' | '+movie['runtime']+' min. | '+movie['genre']+' | '+movie['country']+' | '+movie['director']+'\
                            </div>\
                        </a>');
                    }
                });
            } else {
                $('#panel_live_search').empty();
            }
            $(document).click(function(){
                $('#panel_live_search').empty();
            });
        }, 500);
    });
    $(document).on('mouseenter', '.live_search_box', function(){
        $(this).addClass('live_hover');
    });
    $(document).on('mouseleave', '.live_search_box', function(){
        $(this).removeClass('live_hover');
    });
    
    // live search tvshow
    var wait;
    $(document).on('keyup click', '#search_tvshows', function() {
        clearTimeout(wait);
        wait = setTimeout(function() {
            var search = $('#search_tvshows').val();
            if (search.length > 0) {
                $.getJSON("function.js.php?option=searchtvshow&search="+search, function(data){
                    $('#panel_live_search').empty();
                    for(var m in data) {
                        var movie = data[m];
                        $('#panel_live_search').append('\
                        <a href="index.php?video=tvshows&id='+movie['id']+'">\
                            <div class="live_search_box" title="'+movie['title']+'">\
                                <img class="img_live_search" src="' + movie['poster'] + '">\
                                <div class="live_search_title">'+movie['title']+'</div>\
                                <div class="live_search_orig_title">'+movie['originaltitle']+'</div>\
                                '+movie['rating']+' | '+movie['genre']+'\
                            </div>\
                        </a>');
                    }
                });
            } else {
                $('#panel_live_search').empty();
            }
            $(document).click(function(){
                $('#panel_live_search').empty();
            });
        }, 500);
    });
    $(document).on('mouseenter', '.live_search_box', function(){
        $(this).addClass('live_hover');
    });
    $(document).on('mouseleave', '.live_search_box', function(){
        $(this).removeClass('live_hover');
    });
    
    // change background
    if (show_fanart == '1') {
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
            if (fadeout_fanart == '1') {
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
            }
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
    $('.animate').mouseenter(function(){
        $(this).css('opacity', '.7');
    });
    $('.animate').mouseleave(function(){
        $(this).css('opacity', '1');
    });
    
    // animate trailer button
    $('.trailer_img').mouseenter(function(){
        $(this).css('opacity', '1');
    });
    $('.trailer_img').mouseleave(function(){
        $(this).css('opacity', '.8');
    });
    
    // delete movie
    $('.delete_row').click(function(){
        var id = $(this).parent().parent().attr('id');
        var video = $(this).parent().parent().parent().parent().attr('id');
        $('#'+id).hide();
        $.ajax({url: 'function.js.php?option=delete'+video+'&id='+id});
    });
        
    // delete all
    $('#delete_all').click(function(){
        var c = $('#delete_all').html();
        return confirm(c);
    });
    
    // actor thumbnail
    $('.actor_img').mouseenter(function(){
        $(this).mousemove(function(event) {
            var posX = event.pageX;
            var posY = event.pageY;
            $(this).children('.actor_thumb').css({'top': posY-110, 'left': posX+10});
        });
        $(this).children('.actor_thumb').delay(500).show(0);
    });
    $('.actor_img').mouseleave(function(){
        $('.actor_thumb').dequeue().hide();
    });
    
    // episode plot toggle
    $('.episode').mouseenter(function(){
        var e_id = $(this).attr('id');
        $(this).mousemove(function(event) {
            var posX = event.pageX;
            var posY = event.pageY;
            $('#plot_'+e_id).css({'top': posY+10, 'left': posX-100});
        });
        $('#plot_'+e_id).delay(500).show(0);
    });
    $('.episode').mouseleave(function(){
        $('.episode_plot').dequeue().hide();
    });
    
    // control remote
    $('#panel_remote span').click(function(){
        var act = $(this).attr('id');
        $.ajax({url: 'function.js.php?option=remote&f='+act});
    });
    
    // admin visible - hidden
    $(document).on('click', '.visible', function(){
        var id = $(this).parent().parent().attr('id');
        var video = $(this).parent().parent().parent().parent().attr('id');
        $(this).attr('src', 'admin/img/hidden.png');
        $(this).addClass('hidden');
        $(this).removeClass('visible');
        $.ajax({url: 'function.js.php?option=hide'+video+'&id='+id});
    });
    $(document).on('click', '.hidden', function(){
        var id = $(this).parent().parent().attr('id');
        var video = $(this).parent().parent().parent().parent().attr('id');
        $(this).attr('src', 'admin/img/visible.png');
        $(this).addClass('visible');
        $(this).removeClass('hidden');
        $.ajax({url: 'function.js.php?option=visible'+video+'&id='+id});
    });
    
    // admin banner
    $(document).on('keyup', '.ban', function() {
        var b = [];
        var f = false;
        $('.ban').each(function(){
            var key = $(this).attr('id');
            var val = $(this).val();
            b.push(key+':'+val);
            if (f == false) {
                if (!$.isNumeric(val)) {
                    if (!val.match(/[0-9abcdefABCDEF]{6}/)) {
                        f = true;
                    }
                }
            }
        });
        banner = b.join(';');
        if (f == false) {
            $.ajax({
                url: 'function.js.php?option=banner&banner='+banner,
                success: function(){
                    $('#banner').attr('src', 'cache/banner_v.jpg?'+Math.random());
                }
            });
        }
    });
});
