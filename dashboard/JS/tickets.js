$(function(){
    $(document).on('click', '.reivindicarTicket', function(){
        $('#fountainG').css('display','block').animate({'opacity':'1'})
        id = $(this).parent().parent().attr('id')
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'reivindicarTicket','perm':'admin','id':id}
        }).done(function(data){
            $('#fountainG').css('display','none').animate({'opacity':'0'})
            data = JSON.parse(data)
            if(data.sucesso){
                alertar(data.msg,data.redirect)
            }else{
                alertar(data.msg,'')
            }
        })
    })
    //levar admin pro ticket
    $(document).on('click', '.box-chatSingle', function(){
        id = $(this).attr('id')
        location.href = BASE+'dashboard/chat/'+id
    })

    $(document).on('click', '.tab-abertos', function(){
        tabTicketsAbertos();
    })
    $(document).on('click', '.tab-fechados', function(){
        tabTicketsFechados();
    })
    
})
    
window.onload = function(){
    tabTicketsAbertos()
}
function tabTicketsAbertos(){
    if(!$('.tab-abertos').hasClass('active')){
        $('.header-box-chat').find('div').removeClass('active')
        $('#fountainG').css('display','block');
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'ticketsAbertos','perm':'admin'}
        }).done(function(data){
            $('#fountainG').css('display','none');
            $('.tab-abertos').toggleClass('active')
            $('.contentChats').empty()
            data = JSON.parse(data)
            if(data.sucesso){
                tickets = data.msg.split('||')
                for(i=0; i<tickets.length; i++){
                    $('.contentChats').append(tickets[i]);
                }
            }else{
                $('.contentChats').append(data.response);
            }
        })
    }
    
}
function tabTicketsFechados(){
    if(!$('.tab-fechados').hasClass('active')){
        $('.header-box-chat').find('div').removeClass('active')
        $('#fountainG').css('display','block');
        $.ajax({
            url:BASE+'ajax/Ticket.php',
            method:'post',
            data:{'acao':'ticketsFechados','perm':'admin'}
        }).done(function(data){
            $('#fountainG').css('display','none');
            $('.contentChats').empty()
            data = JSON.parse(data)
            $('.tab-fechados').toggleClass('active')
            if(data.sucesso){
                tickets = data.msg.split('||')
                for(i=0; i<tickets.length; i++){
                    $('.contentChats').append(tickets[i]);
                }
            }else{
                $('.contentChats').append(data.response);
            }
            
        })
    }
}