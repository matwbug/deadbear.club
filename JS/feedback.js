$(function(){
    $(document).on('click', '.fas.fa-star', function(){
        var rating = parseInt($(this).attr('data-rating'))
        $('div.fas.fa-star').addClass('die').removeClass('selected')
        for(var i = 1; i <= rating; i++){
            $('div.fas.fa-star.die:nth-of-type('+i+')').removeClass('die');
        }
        $(this).toggleClass('selected')
    })

    $(document).on('click', '.sugest-feedback', function(){
        var text = $(this).text()
        $(this).parent().parent().find('textarea').empty().val(text)
    })

    $(document).on('click','[js="submitFeedback"]', function(){
        if($('label[for="image"]').find('img').prop('src') == location.href){return alertar('Anexe uma imagem ao seu feedback!','')}
        if($('.tab-opened').find('textarea').val().length < 40){return alertar('Você precisa escrever um feedback com ao menos 40 caractéres','')}
        var img = $('label[for="image"]').find('img').prop('src'); img = img.split('/')[7];
        var text = $('.tab-opened').find('textarea').val()
        var rating = $('div.fas.fa-star.selected').attr('data-rating')
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Feedback.php',
            method:'post',
            data:{
                'acao':'insertFeedback',
                'ticketid':$_GET.get('ticketid'),
                'token' : sessionStorage.getItem('token'),
                'img':img,
                'texto': text,
                'rating':rating
            }
        }).done((data) =>{
            $('#fountainG').css('display','none')
            data = JSON.parse(data)
            if(typeof data.msg !== 'undefined'){
                alertar(data.msg,data.redirect)
            }
            if(data.sucesso){
                $('.tab-opened').remove()
            }
        })
        
    })
})

function addImage(){
    var imgUrl = $('input[type="file"]').prop('files')[0];
    var formData = new FormData();
    formData.append('file',imgUrl)
    formData.append('tokenValidar',sessionStorage.getItem('token'))
    formData.append('acao','addImage');
    $('#fountainG').css('display','block')
    $.ajax({
        url:BASE+'ajax/Feedback.php',
        method:'post',
        contentType:false,
        cache:false,
        processData:false,
        data:formData
    }).done(function(data){
        $('#fountainG').css('display','none')
        data = JSON.parse(data)
        if(typeof data.msg !== 'undefined'){
            alertar(data.msg,'')
        }
        if(data.sucesso){
            $('label[for="image"]').find('img').prop('src',data.image).css('display','block');
            $('label[for="image"]').find('.svg-addphoto').css('display','none')
        }
    })
}