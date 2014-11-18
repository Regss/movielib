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
        theme = set['theme'];
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
        if ($('.panel_top_item').length > 0) {
            $('.panel_top_item').cycle({
                timeout: +timeout
            });
        }
    });

    // view menu
    $('#view_menu').mouseenter(function () {
        $('#views').show();
    });
    $('#view_menu').mouseleave(function () {
        $('#views').hide();
    });
    $('#watch_menu').mouseenter(function () {
        $('#watch').show();
    });
    $('#watch_menu').mouseleave(function () {
        $('#watch').hide();
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
    $(document).on('keyup click', '.search', function() {
        clearTimeout(wait);
        wait = setTimeout(function() {
            var video = $('.search').attr('id');
            var search = $('.search').val();
            if (search.length > 0) {
                var url = "function.js.php?option=search&search="+search+"&video="+video;
                $.get(url, function(data) {
                    $('#panel_live_search').empty();
                    $('#panel_live_search').append(data);
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
                url: 'function.js.php?option=fexist&id='+movie_id,
                dataType: 'json',
                success: function(data){
                    if (data['fexist'] == 'exist') {
                        $('#background').fadeOut(500, function(){
                            $(this).delay(100).attr('src', 'cache/'+movie_id+'_f.jpg');
                            $(this).fadeIn(500);
                        });
                    }
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
    
    // extra thumb
    $(document).on('mouseenter', '.ex_thumbs img', function(){
        $(this).animate({'opacity': '.7'}).dequeue();
    });
    $(document).on('mouseleave', '.ex_thumbs img', function(){
        $(this).animate({'opacity': '1'}).dequeue();
    });
    $(document).on('click', '.ex_thumbs img', function(){
        var link = $(this).attr('src').slice(0, -5);
        $('body').append('<div class="ex_thumb_con"><img id="opened" class="ex_thumb" src="' + link + '.jpg"></div>');
        $("img#opened").load(function() {
            var b = 20;                             // border size
            var img_h = $(this).height();           // get image height
            var img_w = $(this).width();            // get image width
            var win_h = $(window).height();         // get window height
            var win_w = $(window).width();          // get window width
            if (img_h > win_h - 100) {              // if image height is greather than windows height
                var aspect = img_w / img_h;         // calculate aspect ratio
                var r_h = img_h - win_h + 100;      // get resize value
                img_h = img_h - r_h;                // set new image height
                img_w = img_w - (r_h * aspect);     // set new image width
            }
            if (img_w > win_w - 100) {              // same for width is the above
                var aspect = img_h / img_w;
                var r_w = img_w - win_w + 100;
                img_w = img_w - r_w;
                img_h = img_h - (r_w * aspect);
            }
            var pos_x = (win_w-img_w)/2;            // set position X
            var pos_y = (win_h-img_h)/2;            // set position Y
            $('.ex_thumb_con').css({'left': '0px', 'top': '0px', 'right': '0px', 'bottom': '0px', 'position': 'fixed'});
            $('.ex_thumb').css({'position': 'fixed', 'display': 'none', 'height': img_h+'px', 'width': img_w+'px', 'top':pos_y-b+'px', 'left': pos_x-b+'px', 'border': b+'px solid #fff'});
            $('.ex_thumb').fadeIn(500);
        });
    });
    $(document).on('click', '.ex_thumb, .ex_thumb_con', function(){
        $('.ex_thumb').fadeOut(500, function(){
            $('.ex_thumb, .ex_thumb_con').remove();
        });
    });
    
    // episode plot toggle
    $('.plot').mouseenter(function(){
        var e_id = $(this).parent().attr('id');
        $(this).parent().mousemove(function(event) {
            var posX = event.pageX;
            var posY = event.pageY;
            $('#plot_'+e_id).css({'top': posY+10, 'left': posX-100});
        });
        $('#plot_'+e_id).delay(500).show(0);
    });
    $('.plot').mouseleave(function(){
        $('.episode_plot').dequeue().hide();
    });
    
    // control remote - check connection and change logo
    $.ajax({url: 'function.js.php?option=remote&f=check', dataType: 'json', success: function(data){
        if ('result' in data) {
            $('#r_right img').attr('src', 'templates/'+theme+'/img/xbmc_v.png');
        } else {
            $('#r_right img').attr('src', 'templates/'+theme+'/img/xbmc_vd.png');
        }
    }});
    
    // show remote and now playing
    $('#panel_remote').on('mouseenter click', function(){
        $.ajax({url: 'function.js.php?option=remote&f=check', dataType: 'json', success: function(data){
            if ('result' in data) {
                $('#r_right img').attr('src', 'templates/'+theme+'/img/xbmc_v.png');
            } else {
                $('#r_right img').attr('src', 'templates/'+theme+'/img/xbmc_vd.png');
            }
        }});
        $('#panel_remote').animate({marginLeft: '10px'}, {queue: false, duration: 500, complete: function(){
            $.ajax({url: 'function.js.php?option=remote&f=playing', dataType: 'json', success: function(data){
                if ('type' in data) {
                    $('#np_details').html(data['details']);
                    var width = parseInt($('#bar').css('width'));
                    var w = (width * (parseInt(data['percentage']) / 100));
                    $('#prog').css('width', w+'px');
                    $('#now_playing').animate({marginLeft: '10px'}, {queue: false, duration: 500});
                }
            }});
        }});
    });
    
    // hide remote and now playing
    $('#panel_remote, #now_playing').mouseleave(function(){
        $('#panel_remote').animate({marginLeft: '-70px'}, {queue: false, duration: 500, complete: function(){
            $('#now_playing').animate({marginLeft: '-500px'}, {queue: false, duration: 500});
        }});
    });
    
    // hide now playing on stop button
    $('#stop').click(function(){
        $('#now_playing').animate({marginLeft: '-500px'}, {queue: false, duration: 500});
    });
    
    // button remote action
    $('#panel_remote img').click(function(){
        var act = $(this).attr('id');
        $.ajax({url: 'function.js.php?option=remote&f='+act});
    });
    
    // panel desc
    $('.movie').mouseenter(function(){
        $(this).children('.xbmc_hide').animate({opacity: 1}, {queue: false, duration: 300});
    });
    $('.movie').mouseleave(function(){
        $(this).children('.xbmc_hide').animate({opacity: .3}, {queue: false, duration: 300});
    });
    
    // create list.m3u
    $('.list').mouseenter(function(){
        var file = $(this).parent().attr('id');
        var id = $(this).parent().parent().attr('id');
        $.ajax({url: 'function.js.php?option=remote&f=list&id='+id+'&file='+file});
    });
    
    // play movie in xbmc
    $('.play').click(function(){
        var id = $(this).parent().attr('id');
        var video = $('#panel_list').attr('class');
        $.ajax({url: 'function.js.php?option=remote&f=play&id='+id+'&video='+video});
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
    
    // test XBMC conn
    $('#xbmc_test').click(function(){
        var xbmc_host = $('#xbmc_host').val();
        var xbmc_port = $('#xbmc_port').val();
        var xbmc_login = $('#xbmc_login').val();
        var xbmc_pass = $('#xbmc_pass').val();
        $.ajax({url: 'function.js.php?option=remote&f=xbmc_test&xbmc_host='+xbmc_host+'&xbmc_port='+xbmc_port+'&xbmc_login='+xbmc_login+'&xbmc_pass='+xbmc_pass,
        dataType: 'json',
        success: function(data){
            if ('result' in data) {
                $('#xbmc_test div').html('<img src="admin/img/exist.png">');
                $('#xbmc_test').css({'border': '2px solid #0FE800'});
                $('#xbmc_test img').css({'display': 'block', 'position': 'absolute', 'margin-left': '120px'});
            } else {
                $('#xbmc_test div').html('<img src="admin/img/delete.png">');
                $('#xbmc_test').css('border', '2px solid #FF0000');
                $('#xbmc_test img').css({'display': 'block', 'position': 'absolute', 'margin-left': '120px'});
            }
        }});
    });
});
