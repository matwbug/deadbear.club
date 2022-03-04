$(function(){
    
})

function changeStatusSite(){
    $('span.status').parent().parent().animate({'opacity':'0.10'})
    $('#fountainG').css('display','block')
    $.ajax({
        url:BASE+'ajax/Admin.php',
        method:'post',
        data:{'type-action':'Manutencao'}
    }).done(function(data){
        data = JSON.parse(data)
        $('span.status').parent().parent().animate({'opacity':'1'})
        $('#fountainG').css('display','none')
        if(data.sucesso){
            $('span.status').empty().append(data.response)
        }
    })
}

