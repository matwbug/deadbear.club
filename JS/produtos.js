$(function(){
    $(document).on('click', 'img.smallimage', function(){
        if(!$(this).hasClass('active')){
            $('img.smallimage').removeClass('active')
            $('.parentimage').prop('src',$(this).prop('src'))
            $(this).toggleClass('active')
        }
    })

    $(document).on('click', '.single[slug]', function(){
        location.href = BASE+'item/'+$(this).attr('slug')
    })

    $(document).on('click', '.categoriapage .body .single-div[slug]', function(){
        location.href = BASE+'item/'+$(this).attr('slug')
    })
})

function getInfoProduct(){
    url = location.href; url = url.split('/'); var base = url[3] == 'db' ? 'local' : 'online';
    key = base == 'local' ? 5 : 4 ;
    $.ajax({
        url:BASE+'ajax/Produtos.php',
        method:'post',
        data:{'acao':'getProduto','slug':url[key],'token' : sessionStorage.getItem('token')}
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            $('.container-main').prepend(data.response)
            $('.ajax-load').remove()
        }
    })
}

window.onload = function(){
    getInfoProduct()
}