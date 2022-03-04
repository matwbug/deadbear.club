$(function(){
$('[mask="date"]').mask('00/00/0000');
$('[mask="telefone"]').mask('+00 (00) 00000-0000');
$('[mask="cpf"]').mask('000.000.000-00', {reverse: true});
    
  $(document).on('click', '.my-orders-tab', function(){
    tabOrderUser();
  })
  $(document).on('click', '.my-data-user',function(){
    tabDataUser();
  })  
  $(document).on('click', '.my-ref-tab', function(){
    tabRefTab();
  })
  $(document).on('click', '.paginator-orders', function(){
    if($(this).hasClass('active')){return}
    page = $(this).attr('page')
    $('.content-myaccount').find('.body').css('opacity','0.10')
    $.ajax({
        url:BASE+'ajax/myaccount.php',
        method:'post',
        data:{
            'token' : sessionStorage.getItem('token'),
            'acao':'getOrders','page':page,'perm':'user'
        }
    }).done(function(data){
        data = JSON.parse(data)
        if(data.sucesso){
            $('.content-myaccount').empty()
            orders = data.orders.split('||'); orders.reverse()
            for(i=0; i<orders.length; i++){
                $('.content-myaccount').append(orders[i]);
            }
        } 

    })
  })
  $(document).on('click', '.reivindicarRefCode', function(){
      if(!$('#boxrevcode').length){
        $('.refferal-box').append('<div id="boxrevcode" style=" display: flex; flex-direction: column; align-content: center; justify-content: center; border-top: 1px solid; margin-top: 10px; "><span>Digite o código que você irá usar para divulgar</span> <input name="codeForRef" type="text" style=" background: rgb(13 26 59); border: none; padding: 10px; margin: 10px 0 5px 0; "> <button name="subcodeForRef" style=" border: none; background: #2596be; padding: 10px; width: fit-content; margin: 0 auto; ">Utilizar</button> </div>')
      }
  })
  $(document).on('keyup', '[name="codeForRef"]', function(){
      obj = $(this)
      var code = obj.val()
      if(!$('.avis').length){
        $.ajax({
            url:BASE+'ajax/myaccount.php',
            method:'post',
            data:{
                'token' : sessionStorage.getItem('token'),
                'acao':'checkCode','perm':'user','code':code
            }
        }).done(function(data){
            data = JSON.parse(data)
            if(!data.sucesso){
                obj.before('<div class="avis red"><span>'+data.msg+'</span> </div>')
                setTimeout(function(){
                    $('.avis').remove()
                },3000)
            }
        })
        
      }
  })
  $(document).on('focus', '.edit', function(){
      $('.save').slideUp();
      if($(this).parent().parent().find('.save').css('display') == 'none'){
        $(this).parent().parent().find('.save').slideDown();
      }
  })
  $(document).on('click', '.save', function(){
        var name = $(this).parent().find('input').attr('name'); nameVal = $(this).parent().find('input').val();
        if(nameVal == '' || nameVal == null){return alertar('O campo não pode ficar vázio.','')}
        $.ajax({
            url:BASE+'ajax/myaccount.php',
            method:'post',
            data:{
                'token' : sessionStorage.getItem('token'),
                'acao':'alterarInfo','perm':'user','name':name,'nameVal':nameVal
            }
        }).done(function(data){
            data = JSON.parse(data);
            alertar(data.msg,data.redirect)
        })
  })

    $(document).on('click', '[name="subcodeForRef"]', function(){
        var code = $('[name="codeForRef"]').val()
        $.ajax({
            url:BASE+'ajax/myaccount.php',
            method:'post',
            data:{
                'token' : sessionStorage.getItem('token'),
                'acao':'insertCode',
                'perm':'user',
                'code':code
            }
        }).done(function(data){
            data = JSON.parse(data)
            alertar(data.msg,data.redirect)
        })
    })
    $(document).on('click', '.closetab', function(){
        $('.open-tab').remove()
    })
    $(document).on('click', '.openOrder', function(){
        openOrder($(this))
    })
    $(document).on('click', '.content-myaccount .body .single', function(){
        openOrder($(this).find('.openOrder'))
    })
    $(document).on('click', '.copy', function(){
        copyToClipboard(document.getElementById("code"));
    })

    $(document).on('click', '.single-step span.flex-center-notresize', function(){
        if($(this).parent().parent().find('.escondido').css('display') == 'flex'){
            $(this).parent().parent().find('.escondido').css('display','none');
        }else{$(this).parent().parent().find('.escondido').css('display','flex');}
    })
})
function copyToClipboard(elem) {
    // create hidden text element, if it doesn't already exist
  var targetId = "_hiddenCopyText_";
  var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
  var origSelectionStart, origSelectionEnd;
  if (isInput) {
      // can just use the original source element for the selection and copy
      target = elem;
      origSelectionStart = elem.selectionStart;
      origSelectionEnd = elem.selectionEnd;
  } else {
      // must use a temporary form element for the selection and copy
      target = document.getElementById(targetId);
      if (!target) {
          var target = document.createElement("textarea");
          target.style.position = "absolute";
          target.style.left = "-9999px";
          target.style.top = "0";
          target.id = targetId;
          document.body.appendChild(target);
      }
      target.textContent = elem.textContent;
  }
  // select the content
  var currentFocus = document.activeElement;
  target.focus();
  target.setSelectionRange(0, target.value.length);
  
  // copy the selection
  var succeed;
  try {
        succeed = document.execCommand("copy");
  } catch(e) {
      succeed = false;
  }
  // restore original focus
  if (currentFocus && typeof currentFocus.focus === "function") {
      currentFocus.focus();
  }
  
  if (isInput) {
      // restore prior selection
      elem.setSelectionRange(origSelectionStart, origSelectionEnd);
  } else {
      // clear temporary content
      target.textContent = "";
  }
  alertar('Código copiado com sucesso.','')
  return succeed;
}
function tabDataUser(){
    obj = $('.my-data-user')
    if(!obj.hasClass('active')){ // checka se ja está na guia
        $('.buttons-area').find('.active').removeClass('active').prop('disabled',true)
        obj.prop('disabled',true)
        $('#fountainG').css('display','block')
        $('.content-myaccount').css('opacity','10%')
        $.ajax({
            url:BASE+'ajax/myaccount.php',
            method:'post',
            data:{
                'token' : sessionStorage.getItem('token'),
                'acao':'getDados','perm':'user'
            }
        }).done(function(data){
            $('.buttons-area').find('button').prop('disabled',false)
            $('.my-data-user').prop('disabled',false).toggleClass('active')
            $('#fountainG').css('display','none')
            $('.content-myaccount').css('opacity','100%')
            data = JSON.parse(data)
            if(data.sucesso){
                $('.content-myaccount').empty().append(data.response)
                
            } 
        })
    }
}
function tabOrderUser(){
    obj = $('.my-orders-tab')
    if(!obj.hasClass('active')){ // checka se ja está na guia
        $('.buttons-area').find('.active').removeClass('active').prop('disabled',true)
        obj.prop('disabled',true)
        $('#fountainG').css('display','block')
        $('.content-myaccount').css('opacity','10%')
        $.ajax({
            url:BASE+'ajax/myaccount.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'getOrders','perm':'user'}
        }).done(function(data){
            $('.buttons-area').find('button').prop('disabled',false)
            $('.my-orders-tab').prop('disabled',false).toggleClass('active')
            $('#fountainG').css('display','none')
            $('.content-myaccount').css('opacity','100%')
            data = JSON.parse(data)
            if(data.sucesso){
                $('.content-myaccount').empty()
                orders = data.orders.split('||')
                for(i=0; i<orders.length; i++){
                    $('.content-myaccount').append(orders[i]);
                }
            } 
        })
      }
}
function tabRefTab(){
    obj = $('.my-ref-tab')
     if(!obj.hasClass('active')){ // checka se ja está na guia
        $('.buttons-area').find('.active').removeClass('active').prop('disabled',true)
        obj.prop('disabled',true)
        $('#fountainG').css('display','block')
        $('.content-myaccount').css('opacity','10%')
        $.ajax({
            url:BASE+'ajax/myaccount.php',
            method:'post',
            data:{'token' : sessionStorage.getItem('token'),'acao':'getReivindicado','perm':'user'}
        }).done(function(data){
            $('.buttons-area').find('button').prop('disabled',false)
            $('.my-ref-tab').prop('disabled',false).toggleClass('active')
            $('#fountainG').css('display','none')
            $('.content-myaccount').css('opacity','100%')
            data = JSON.parse(data)
            if(data.sucesso){
                $('.content-myaccount').empty()
                orders = data.orders.split('||')
                for(i=0; i<orders.length; i++){
                    $('.content-myaccount').append(orders[i]);
                }
            } 
        })
    }
}

function openOrder(elem){
    id = elem.parent().parent().attr('dataid')
    $.ajax({
        url:BASE+'ajax/myaccount.php',
        method:'post',
        data:{'token' : sessionStorage.getItem('token'),'acao':'openOrder','id':id,'perm':'user'}
    }).done(function(data){
        data = JSON.parse(data)
        $('.open-tab').remove()
        $(elem).parent().parent().parent().parent().prepend(data.response)
    })
}
    