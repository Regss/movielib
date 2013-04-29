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
    
    
    $('#rec_1').fadeIn().delay(500).fadeOut();
    
    $(document).ready(function() {

     var j = 0;
     var delay = 2000; //millisecond delay between cycles
     function cycleThru(){
             var jmax = $("#rec_5").length -1;
             $("#rec_:eq(" + j + ")")
                     .animate({"opacity" : "1"} ,400)
                     .animate({"opacity" : "1"}, delay)
                     .animate({"opacity" : "0"}, 400, function(){
                             (j == jmax) ? j=0 : j++;
                             cycleThru();
                     });
             };

     cycleThru();

});
});