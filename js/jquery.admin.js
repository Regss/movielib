$(document).ready(function() {
    $('#mode_0').click(function(){
        $('.xbmc').attr('disabled', 'disabled');
        $('.xbmc').attr('class', 'xbmc disabled');
    });
    $('#mode_1').click(function(){
        $('.xbmc').removeAttr('disabled');
        $('.xbmc').attr('class', 'xbmc');
    });
});