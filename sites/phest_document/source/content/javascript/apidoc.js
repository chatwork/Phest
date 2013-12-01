$(function (){
    var headerHeight = $('#_header').height();
    $(window).scroll(function() {
        if ($(this).scrollTop() > headerHeight) {
            $('#_sideContent').css({
                position:'fixed',
                top:'0px'
            });
            $('#_mainContent').css({
                marginLeft:'230px'
            });
        } else {
            $('#_sideContent').css({
                position:'static',
                top:false
            });
            $('#_mainContent').css({
                marginLeft:'0px'
            });
        }
    });
    
    $('.mainContentInner').find('a').attr('target','_blank');
});