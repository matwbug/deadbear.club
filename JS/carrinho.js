$(function(){
    $('.remove').click(function(){
        id = $(this).parent().attr('cid')
        pinto = $(this)
        $.ajax({
            url:BASE+'ajax/Carrinho.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'action':'excluirItem','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                pinto.parent().remove()
            }
            alertar(data.msg,'')
        })
    })
    $('.quantCart').blur(function(){
        cid = $(this).parent().parent().attr('cid')
        valueCart = $(this).val()
        $(this).parent().find('.quantCart').val(valueCart).animate({'opacity':'0.3'}).css('opacity','0.3')
        pinto = $(this)
        $.ajax({
            url:BASE+'ajax/Carrinho.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'action':'alterarCarrinho','id':cid,'quant':valueCart}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                pinto.parent().find('.quantCart').val(valueCart).animate({'opacity':'1'}).css('opacity','1')
                pinto.prop('disabled',false).animate({'opacity':'1'})
                pinto.parent().find('.cartRemove').prop('disabled',false).animate({'opacity':'1'})
                pinto.parent().parent().find('.price').empty().append(data.total)
                if(data.inf){pinto.parent().parent().find('.price').append(data.inf)}
                $('.info').find('[js="totalCart"]').empty().append(data.totalCarts)
            }
        })
    })
    $('.jsCloseOrder').click(function(e){
        e.preventDefault()
        $('#fountainG').css({'display':'block'})
        if($('[name="method"]:checked').length <= 0){$('#fountainG').css({'display':'none'}); return alertar('Escolha uma forma de pagamento antes de finalizar a compra!','')}
        method = $('[name="method"]:checked').val()
        if(method != 'PIX' && method != 'PICPAY'){$('#fountainG').css({'display':'none'}); return alertar('Selecione um método de pagamento válido.','')}
        if($('[name="terms"]:checked').length <= 0){$('#fountainG').css({'display':'none'}); return alertar('Você precisa concordar com os termos pra prosseguir com sua compra!','')}
        $.ajax({
            url:BASE+'ajax/Carrinho.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'action':'fecharPedido','method':method}
        }).done(function(data){
            data = JSON.parse(data)
            $('#fountainG').css({'display':'none'})
            if(data.msg){
                alertar(data.msg,data.redirect)
            }
        })
    })
    $(document).on('click', '.buy-action', function(){
        url = location.href; url = url.split('/'); var base = url[3] == 'db' ? 'local' : 'online';
        key = base == 'local' ? 5 : 4 ;
        $('#fountainG').css({'display':'block'})
        cupom = $('[name="cuppon"]').val()
        $.ajax({
            url:BASE+'ajax/Carrinho.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'action':'adicionarCarrinho','slug':url[key],'cupom':cupom}
        }).done(function(data){
            data = JSON.parse(data)
            $('#fountainG').css({'display':'none'})
            if(data.sucesso){
                $('.cartinside').empty()
                if($('.cartinside').css('display') == 'block'){
                    $('.cartinside').css('right','-4000px').animate({'opacity':'0'}).css('display','none')
                }else if($('.cartinside').css('display') == 'none'){
                    width = $(window).width() <= 800 ? '0px' : '185px';
                    $('.cartinside').css('right',width).animate({'opacity':'1'}).css('display','flex')
                    $('.cartinside').find('.ajax-loading').css('display','block');
                }
                if(data.haveCart){
                    carts = data.carts.split('||');
                    carts.reverse()
                    for(i=0;i<carts.length;i++){
                        $('.cartinside').prepend(carts[i])
                    }
                    $('.cartinside').find('.totalCart').append(data.total) 
                }else{
                    $('.cartinside').prepend('<div class="flex-center" style="justify-conten:center; height:150px;"><span>Não foi encontrado nada no seu carrinho</span></div>')
                }
                $('.cartinside').find('.ajax-loading').css('display','none');
            }else{
                if(data.msg.length > 0 || data.redirect.length > 0){
                    alertar(data.msg,data.redirect)
                }else{
                    alertar('Aconteceu algum erro, contate o administrador.','')
                }
            }
        })
    })
})