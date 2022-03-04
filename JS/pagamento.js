
id = location.href; id = id.split('?id=')[1];
url = location.href; url = url.split('?')[0]; url = url.split('/'); var base = url[3] == 'db' ? 'local' : 'online';
key = base == 'local' ? 3 : 2 ;
if(url[key] != 'chat'){
    setInterval(function(){
        if(getCookie('chktic') != true){
             checkPayment(id)
        }
    },5000)
}
function checkPayment(id){
    if(id.length <= 0 || id == undefined || id == null){ id = ''}
    $.ajax({
        url:BASE+'ajax/Pagamento.php',
        method:'post',
        data:{'token' : sessionStorage.getItem('token'),'acao':'checkPayment','id':id},
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            if(data.pago){
                alertar(data.msg,data.redirect)
                setCookie('chktic',true, 20000)
            }
        }
    })
}
function checkPaymentChat(id){
    if(id.length <= 0 || id == undefined || id == null){ id = ''}
    $.ajax({
        url:BASE+'ajax/Pagamento.php',
        method:'post',
        data:{'token' : sessionStorage.getItem('token'),'acao':'checkPayment','id':id, 'chat':''},
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            if(data.pago){
                alertar(data.msg,data.redirect)
                setCookie('chktic',true, 20000)
            }
        }
    })
}

