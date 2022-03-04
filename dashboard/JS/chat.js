notif = new Audio(BASE+'data/Audio/notif.mp3');
$(function(){
    $('textarea').keyup(function(e){
        var code = e.Keycode || e.which;
        if(code == 13){
            insertChat();
        }
        
     })
     $('.sendMessage').find('form').submit(function(e){
        e.preventDefault();
        insertChat();
    }) 

    /* tabs controls */
    $(document).on('click', '.enviar-email', function(){
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'tabEnviarEmail','perm':'admin'}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                $('.open-tab').empty().append(data.response)
            }else{
                alertar('Aconteceu algum erro.','')
            }
        })
    })
    $(document).on('click', '.gerar-pix', function(){
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'tabGerarPix','perm':'admin'}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                $('.open-tab').empty().append(data.response)
            }else{
                alertar('Aconteceu algum erro.','')
            }
        }) 
    })
    $(document).on('click', '.tab-gerar-pix', function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        nome = $('[name="nometransacao"]').val()
        method = $('[name="method"]:checked').val()
        valor = $('[name="valor"]').val()
        if(method == null || method.length <= 0 || method != 'PIX' && method != 'PICPAY'){
            return alertar('Insira um método de pagamento','')
        }
        $('#fountainG').css('display','block').animate({'opacity':'1'})
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'GerarPix','perm':'admin','id':id,'method':method,'nome':nome,'valor':valor}
        }).done(function(data){
            $('#fountainG').css('display','none').animate({'opacity':'0'})
            data = JSON.parse(data)
            alertar(data.msg,'')
            if(data.sucesso){
                $('.open-tab').empty().append(data.response)
            }
        })
    })
    $(document).on('click', '.descongelar-pedido', function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        obj = $(this)
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'descongelarPedido','perm':'admin','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none').animate({'opacity':'0'})
            data = JSON.parse(data)
            alertar(data.msg,'')
            getControls()
        })
    })
    //congelar pedido
    $(document).on('click','.congelar-pedido',function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        obj = $(this)
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'congelarPedido','perm':'admin','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none').animate({'opacity':'0'})
            data = JSON.parse(data)
            alertar(data.msg,'')
            getControls();
        })
    })

    /* começo controls */
    $(document).on('click','.fechar-pedido',function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        $('#fountainG').css('display','block').animate({'opacity':'1'})
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'fecharPedido','perm':'admin','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none').animate({'opacity':'0'})
            data = JSON.parse(data)
            alertar(data.msg,'')
            getControls()
        })
    })
    //abrir pedido
    $(document).on('click','.abrir-pedido',function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        $('#fountainG').css('display','block').animate({'opacity':'1'})
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'abrirPedido','perm':'admin','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none').animate({'opacity':'0'})
            data = JSON.parse(data)
            alertar(data.msg,'');
            getControls()
        })
    })
    
    //enviar email
    $(document).on('click', '.tab-enviar-email', function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        var assunto = $('[name="assunto"]').val()
        var texto = $('[name="texto"]').val()
        $('#fountainG').css('display','block').animate({'opacity':'1'})
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'enviarEmail','perm':'admin','id':id,'assunto':assunto,'texto':texto}
        }).done(function(data){
            $('#fountainG').css('display','none').animate({'opacity':'0'})
            data = JSON.parse(data)
            if(data.sucesso){
            }
            alertar(data.msg,'')
        })
    })
    
    //banir usuário
    $(document).on('click', '.banir-usuario', function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'banirUsuario','perm':'admin','ticketid':id}
        }).done(function(data){
            data = JSON.parse(data)
            alertar(data.msg,'');
            if(data.sucesso){
                getControls()
            }
        })
        
    })

    $(document).on('click', '.garantia-tab', function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'garantia-tab','perm':'admin','ticketid':id}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                $('.open-tab').empty().append(data.response)
            }
        })
    })

    /* fim controls*/


    $(document).on('click', '.estornar-dinheiro', function(){
        var ticketid = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'tab-estornarPix','perm':'admin','ticketid':ticketid}
        }).done(function(data){
            data = JSON.parse(data);
            if(data.sucesso){
                $('.open-tab').empty().append(data.response)
            }else{
                alertar('Aconteceu algum erro.','')
            }
        })
    })

    $(document).on('click', '.estornarpix-confirm', function(){
        var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

        var code = $('[name="code"]').val()
        $('#fountainG').css('display','block')
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'estornarPix','perm':'admin','ticketid':id,'code':code}
        }).done(function(data){
            data = JSON.parse(data);
            $('#fountainG').css('display','none')
            if(data.sucesso){
                alertar(data.msg,'')
            }else{
                alertar('Aconteceu algum erro.','')
            }
        })


    })
    
})
function ticketStatus(){
    var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

    $.ajax({
        url:BASE+'ajax/Ticket.php',
        method:'post',
        data:{'acao':'ticketStatus','perm':'admin','id':id}
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            if(data.status == 'fechado'){
                $('.messageStatus').remove()
                $('.sendMessage').prepend('<div class="messageStatus" style="width:100%; text-align:center; padding:10px; background: #a58fdc;"><i class="fas fa-exclamation-triangle"></i> Este ticket foi fechado, o usuário não responderá e nem poderá visualizar as mensagens.</div>')
            }else if(data.status == 'pausado'){
                $('.messageStatus').remove()
                $('.sendMessage').prepend('<div class="messageStatus" style="width:100%; text-align:center; padding:10px; background: #a58fdc;"><i class="fas fa-exclamation-triangle"></i> Este ticket está congelado, o usuário não enviará mensagens, mas ainda tem acesso ao chat.</div>')
            }else{
                $('.messageStatus').remove()
            }
        }
    })
}
function insertChat(){
    var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

    var msg = $('textarea').val();
    var validMsg = function(msg){
        // starts with "WHISPER" or "SHOUT"
        if(/^(?:whisper|shout)/i.test(msg) || /^(?:\[[a-z]{1,2}\])?[a-z_]+:?.*$/i.test(msg) || msg.length >= 3) return true;
        return false;
    }
    if(!validMsg(msg)){
        return alertar('Digite uma mensagem válida.','')
    }
        //var image = document.getElementById('sendImageTicket').src;
        $.ajax({
            url:BASE+'ajax/chat.php',
            method:'post',  
            data:{'acao':'enviarMensagem','msg':msg,'perm':'admin','id':id}
        }).done(function(data){
            data = JSON.parse(data)
            if(data.sucesso){
                $('textarea').val('');
            }
            $('.body').append(data.msg)
            $('.body').scrollTop($('.body')[0].scrollHeight);
        })
}
function getMessages(){
    var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data:{'acao':'getMessages','perm':'admin','id':id}
    }).done(function(data){
        data = JSON.parse(data)
        $('.chat-box').find('.ajax-loading').css('display','none')
        $('.head').append(data.infoUser)
        if(data.sucesso){
            if(data.haveMessages){
                $('.chat-box .body').append(data.msg).scrollTop($('.chat-box .body')[0].scrollHeight);
            }
        }
    })
}
function getOrder(){
    var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data:{'acao':'getOrder','perm':'admin','id':id}
    }).done(function(data){
        data = JSON.parse(data)
        orders = data.msg.split('||')
        for(i=0; i<orders.length; i++){
            $('.insert').append(orders[i]);
        }
        $('.orders-box').append(data.total);
        $('.chat-box').find('.ajax-load').css('display','none');
    })
}
function recuperarMensagens(){
    var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data:{'acao':'recuperarMensagem','perm':'admin','id':id}
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            if(data.haveMessages){
                notif.play()
                msgs = data.msg.split('||')
                for(i=0; i<msgs.length; i++){
                    $('.body').append(msgs[i]);
                }
                $('.body').scrollTop($('.ticket-body')[0].scrollHeight);
            }
        }
    })
}
function recuperarTickets(){
    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data:{'acao':'recuperarTickets','perm':'admin'}
    }).done(function(data){
        data = JSON.parse(data)
        if(data.haveTicket){
            notif.play()
            $('.alert-div-login').remove()
            tickets = data.tickets.split('||')
            for(i=0;i<tickets.length; i++){
                $('.contentChats').append(tickets[i])
            }
            $('.container-contentWrapper').scrollTop($('.contentChats')[0].scrollHeight);
        }
    })
} 
function getControls(){
    var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

    $.ajax({
        url:BASE+'ajax/Ticket.php',
        method:'post',
        data:{'acao':'getControls','perm':'admin','id':id}
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            $('.controls').empty()
            $('.controls').find('.ajax-loading').css('display','none');
            $('.controls').append(data.response)
            
        }
    })
}

function Useronline(){
    var id = location.href.split('/')[2] == 'localhost' ? location.href.split('/')[6] : location.href.split('/')[5];

    $.ajax({
        url:BASE+'ajax/Ticket.php',
        method:'post',
        data:{'acao':'userOnline','perm':'admin','ticketid':id}
    }).done(function(data){
        data = JSON.parse(data)
        if(data.online){
            if($('.chat-box').find('userOnline').text() <= 0){
                $('.chat-box').find('.status').empty().append('<div style="top:70%;left:46%;position:absolute;" title="Online agora" class="userOnline ticket"></div>')
            }
        }else{
            if($('.chat-box').find('.status span').length <= 0){
                $('.chat-box').find('.status').empty().append('<span style="position: absolute;top: 70%;left: 100%;font-size: 10px;font-weight: 300;">OFFLINE</span>')
            } 
        }
        
    })
}

str = location.href.split('/')
window.addEventListener("load", function() {
    if(str[6] != ''){
        getMessages();
        getOrder();
        getControls();
        $('.chat-box .body').scrollTop($('.chat-box .body')[0].scrollHeight);
    }
});
setInterval(function(){
    Useronline();
},10000);
setInterval(function(){
    if(str[6]){
        recuperarMensagens();
        ticketStatus();
    }else{
        recuperarTickets();
    }
},3000)

setInterval(function(){
    Useronline();
},10000)
function sendImg(){
    id = $('.chat-box').attr('id')
    $('#fountainG').css('display','block')
    var image = $('[name="img"]').prop('files')[0]
    var formData = new FormData();
    formData.append('id','id')
    formData.append('image',image)
    formData.append('acao','enviarFoto');
    formData.append('perm','admin')
    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',  
        contentType:false,
        cache:false,
        processData:false,
        data:formData
    }).done(function(data){
        $('#fountainG').css('display','none')
        data = JSON.parse(data)
        if(data.alert != undefined){
            alertar(data.alert,'')
        }else{
            $('.chat-box .body').append(data.msg).scrollTop($('.chat-box .body')[0].scrollHeight);
        }
        
    })
}

