$(function(){
    $('.tabsHeader').find('.tab').click(function(){
        linktab = $(this).find('a').attr('href')
        location.href = linktab
    })
    $('.box-produtos-single').click(function(){
        slug = $(this).attr('slug')
        location.href = BASE+slug
    })
    var open = false;
    $('.tabsMobile').click(function(){
        if(!open){
            $('.container-login').css('width','auto')
            $('.menu').animate({'left':'0'})
            $('.black-fade').animate({'opacity':'60%'}).css('left','0')
            //$('.contentSite').animate({'left':'250','width':'calc(100% - 250px)'});
            open = true
        }else if(open){
            $('.container-login').css('width','500px')
            $('.menu').animate({'left':'-250px'})
            $('.black-fade').animate({'opacity':'0'}).css('left','-4444px')
            //$('.contentSite').animate({'left':'0','width':'100%'});
            open = false;
        }
    })
    openAvatar = false;
    $('.avatarArea').find('.avatar').click(function(){
        if(openAvatar){
            $('.menuHoverAvatar').css('right','-9999px').css('top','50px').animate({'opacity':'0'})
            $('.avatarArea').removeClass('hover')
            openAvatar = false;
        }else{
            $('.menuHoverAvatar').css('right','20px').css('top','50px').animate({'opacity':'1'})
            $('.avatarArea').addClass('hover')
            openAvatar = true;
        }
    })
    OpenCart = false;
    $('[js="openCart"]').click(function(){
        width = $(window).width()
        if(OpenCart){
            $('.cartinside').css('right','-4000px').animate({'opacity':'0'}).css('display','none')
            OpenCart = false;
        }else{
            if(width < 600){
                right = '0px'
                $('.cartinside').css('width',width)
            }else{
                right='185px'
            }
            $('.cartinside').css('right',right).animate({'opacity':'1'}).css('display','flex')
            OpenCart = true;
            $('.cartinside').find('.ajax-loading').css('display','block');

            $.ajax({
                url:BASE+'ajax/Carrinho.php',
                method:'post',
                data:{'token' : sessionStorage.getItem('token'),'action':'getCarrinhoTab'}
            }).done(function(data){
                data = JSON.parse(data)
                if(data.sucesso){
                $('.cartinside').empty()
                    if(data.haveCart){
                        carts = data.carts.split('||');
                        carts.reverse()
                        for(i=0;i<carts.length;i++){
                            $('.cartinside').prepend(carts[i])
                        }
                    }else{
                        $('.cartinside').prepend('<div class="flex-center" style="justify-conten:center; height:150px;"><span>Não foi encontrado nada no seu carrinho</span></div>')
                    }
                    $('.cartinside').find('.ajax-loading').css('display','none');
                }else{
                    if(data.msg.length > 0 && data.redirect.length > 0){
                        alertar(data.msg,data.redirect)
                    }else{
                        alertar('Aconteceu algum erro, contate o administrador.','')
                    }
                }
                
            })
        }
        
        
    })
$(document).on('click', '[js="removeitemcart"]', function(){
    var id = $(this).parent().attr('id');
    obj = $(this)
    $.ajax({
        url:BASE+'ajax/Carrinho.php',
        method:'post',
        data:{'token' : sessionStorage.getItem('token'),'action':'excluirItem','id':id}
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            $('.cartinside').empty()
            if(data.haveCart){
                carts = data.carts.split('||');
                carts.reverse()
                for(i=0;i<carts.length;i++){
                    $('.cartinside').prepend(carts[i])
                }
            }else{
                $('.cartinside').prepend('<div class="flex-center" style="justify-conten:center; height:150px;"><span>Não foi encontrado nada no seu carrinho</span></div>')
            }
            
        }
        alertar(data.msg,'');
    })
})
    
})