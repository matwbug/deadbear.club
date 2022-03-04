$(function(){
    $(document).on('click', '.moreinfo', function(){
        if($(this).find('.escondido').css('display') == 'none'){
            $(this).find('.escondido').slideDown();
            $(this).find('i').css('transform','rotate(180deg)')
        }else{
            $(this).find('.escondido').slideUp();
            $(this).find('i').css('transform','rotate(360deg)')
        }
    })
})