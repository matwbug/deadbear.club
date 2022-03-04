notif = new Audio(BASE+'data/Audio/notif.mp3');
$(function(){
    $('.ticket-body').scrollTop($('.ticket-body')[0].scrollHeight);
    $('textarea').keyup(function(e){
        var code = e.Keycode || e.which;
        if(code == 13){
            insertChat();
        }        
    })
    $('.chat-form').submit(function(e){
        e.preventDefault();
        insertChat();
    })
    $(document).on('mouseover', '.ticket-message', function() {
        $(this).find('.escondido').css('display','block')
    });
    $(document).on('mouseout', '.ticket-message', function() {
        $(this).find('.escondido').css('display','none')
    });
    
    setInterval(function(){
        ticketStatus();
    },4000)
    setInterval(function(){
        Adminonline();
    },10000)
    window.onload = function() {
        checkTicket();
    };

    setTimeout(function(){
        if($('.ticket-message').length == 0){
            $.ajax({
                url:BASE+'ajax/chat.php',
                method:'post',  
                data: {
                    'token' : sessionStorage.getItem('token'),
                    'acao':'botmessage',
                    'msg': "Aguarde um momento em breve algum atendente irá prosseguir com seu pedido.",
                    'perm':'user'
                    }
            }).done(function(data){
                data = JSON.parse(data);
                $('.ticket-body').append(data.msg).scrollTop($('.ticket-body')[0].scrollHeight);
            })
        }
    },6000)
    setTimeout(function(){
        if(!$('.ticket-head .admin')){
            $.ajax({
                url:BASE+'ajax/chat.php',
                method:'post',  
                data: {
                    'token' : sessionStorage.getItem('token'),
                    'acao':'botmessage',
                    'demora' : true,
                    'msg': "Está demorando mais que o normal, parece que não há atendentes disponíveis no momento 🤔",
                    'perm':'user'
                    }
            }).done(function(data){
                data = JSON.parse(data);
                $('.ticket-body').append(data.msg).scrollTop($('.ticket-body')[0].scrollHeight);
            })
        }
    },6000)
    
    
})
function insertChat(){
    var msg = $('textarea').val();
    var validMsg = function(msg){
        if(/^(?:whisper|shout)/i.test(msg) || /^(?:\[[a-z]{1,2}\])?[a-z_]+:?.*$/i.test(msg) || msg.length >= 3) return true;
        return false;
    }
    if(!validMsg(msg)){
        warn('<div class="erroenviarmensagem"><i class="fas fa-exclamation-circle"></i><span>Digite uma mensagem válida.</span></div>','erroenviarmensagem')
    }else{
        //var image = document.getElementById('sendImageTicket').src;
        $.ajax({
            url:BASE+'ajax/chat.php',
            method:'post',  
            data: {
                'token' : sessionStorage.getItem('token'),
                'acao':'enviarMensagem',
                'msg':msg,
                'perm':'user'
                }
        }).done(function(data){
            data = JSON.parse(data)
            if(data.cooldown){
                return false;                
            }else if(data.sucesso){
                $('textarea').val('');
            }
            $('.ticket-body').append(data.msg).scrollTop($('.ticket-body')[0].scrollHeight);
        })
    }
}
function recuperarMensagens(){
    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data: {
            'token' : sessionStorage.getItem('token'),
            'acao':'recuperarMensagem',
            'perm':'user'
        },
    }).done(function(data){            
        data = JSON.parse(data)
        if(data.sucesso){
            if(data.haveMessages){
                notif.play();
                $('.ticket-body').append(data.msg).scrollTop($('.ticket-body')[0].scrollHeight);
            }else{
                return;
            }
        }else{
            return;
        }
    })
}
function getMessages(){
    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data: {
            'token' : sessionStorage.getItem('token'),
            'acao':'getMessages',
            'perm':'user'
        }
    }).done(function(data){
        $('.ajax-loading').css('display','none')
        data = JSON.parse(data)
        if(data.sucesso){
            if(data.haveMessages){
                msgs = data.msg.split('||')
                for(i=0; i<msgs.length; i++){
                    $('.ticket-body').append(msgs[i]);
                    $('.ticket-body').scrollTop($('.ticket-body')[0].scrollHeight);
                }
                
            }else{
                return;
            }
        }else{
            return;
        }
    })
}
function ticketStatus(){
    var $_GET = location.search.substr(1).split("&").reduce((o,i)=>(u=decodeURIComponent,[k,v]=i.split("="),o[u(k)]=v&&u(v),o),{});
    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data: {
            'token' : sessionStorage.getItem('token'),
            'acao':'ticketStatus',
            'perm':'user',
            'idpedido': $_GET['paymentConfirmed']
        }
    }).done(function(data){
        data = JSON.parse(data)
        if(data.fechado){
            if(!$('.ticket-body').find('.closedTicket').text()){
                $('.ticket-body').append(data.msg);
                $('textarea').prop('disabled', true)
            }   
            setTimeout(function(){
                location.href = data.redirect;
            },5000)
        }else if(data.status == 'atendido'){
            if(!$('.admin').length){
                $('.ticket-head').empty().prepend(data.infAtendido);
                $('.ajax-loading').remove()
            }
            recuperarMensagens();

        }else if(data.status == 'aguardando'){
            $('.ticket-head').empty().prepend(data.infAtendido);
        }

        $('.bot.nenhumadmin').remove() //remover aviso sem admin
        if(data.nenhumadminon){ //caso ainda nao tiver admin on colocar denovo o aviso  
            $('.ticket-body').append(data.text);
            $('.ticket-body').scrollTop($('.ticket-body')[0].scrollHeight);
        }
    })
}
function Adminonline(){
    $.ajax({
        url:BASE+'ajax/Ticket.php',
        method:'post',
        data:{
            'token' : sessionStorage.getItem('token'),
            'acao':'userOnline',
            'perm':'admin'
        }
    }).done(function(data){
        data = JSON.parse(data)
        if(data.online){
            if($('.ticket-head').find('userOnline.ticket').text() <= 0){
                $('.ticket-head').find('.img .status').empty().append('<div style="top:70%;left:46%;position:absolute;" title="Online agora" class="userOnline ticket"></div>')
            }
        }else{
            $('.ticket-head').find('.userOnline').remove()
        }
        
    })
}
function checkTicket(){
    $.ajax({
        url:BASE+'ajax/chat.php',
        method:'post',
        data:{
            'token' : sessionStorage.getItem('token'),
            'acao':'checkTicket',
            'perm':'user'
        }
    }).done(function(data){
        data = JSON.parse(data)
        if(data.msg.length > 0 && data.redirect.length > 0 ){alertar(data.msg,data.redirect)}
        if(data.reivindicado){
            $('.ticket-head').empty().prepend(data.infAtendido);
            getMessages();
        }
        
    })
}
function warn(msg,nomediv){
    nomediv = '.'+nomediv;
    if($(nomediv).length <= 0){
        $('.warn').prepend(msg).animate({'opacity':'1'})
    }
    $('textarea').prop('disabled',true)
    $('[type="submit"]').prop('disabled',true)
    setTimeout(function(){
        $('.warn').animate({'opacity':'0'}).empty()
        $('textarea').prop('disabled',false)
        $('[type="submit"]').prop('disabled',false)
    },2000)
}
function sendImg(){
    id = $('.chat-box').attr('id')
    var image = $('[name="img"]').prop('files')[0]
    console.log(image);
    var formData = new FormData();
    formData.append('id','id')
    formData.append('image',image)
    formData.append('acao','enviarFoto');
    formData.append('perm','user')
    formData.append('token',sessionStorage.getItem('token'));
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
            $('.ticket-body').append(data.msg)
            setTimeout(function(){
                $('.ticket-body').scrollTop($('.ticket-body')[0].scrollHeight);
            },2000)
        }
        
    })
}
