$(function(){
    $('.ajax').submit(function(e){
        e.preventDefault();

        var form = $(this);
        var url = form.attr('action');
        $('#fountainG').css('display','block')
        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(),
            success:function(data){
                data = JSON.parse(data)
                alertar(data.msg,data.redirect)
                $('#fountainG').css('display','none')
            },
            error:function(error){
                $('#fountainG').css('display','none')
                console.log(error)
            }
        })

    })

    $(document).on('click', '[js="reenviaremail"]', function(){
        $.ajax({
            url:BASE+'ajax/ajaxUser.php',
            method:'post',
            data:{'type-action':'reenviarCode','token':sessionStorage.getItem('token')}
        }).done(function(data){
            data = JSON.parse(data)
            alertar(data.msg,data.redirect)
        })
    })
    
})