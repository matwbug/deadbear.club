$(function(){
    $(document).on('click', '[link]', function(){
      location.href = BASE+$(this).attr('link')
    })
    $('[animation="click"]').click(function(){
        $(this).find('button').toggleClass('click');
    })
    
    $('[mode="registro"]').find('input').blur(function(){
       var Email = $('[mode="registro"]').find('input').val()
       validateEmail(Email)
    })
    $('.slidebar').find('.content').find('.img').hover(function(){
      $(this).find('img').css('opacity','10%')
      $(this).find('.userOnline').css('opacity','10%')
      $(this).find('.photo').css('display','block')
    },
    function(){
      $(this).find('img').css('opacity','100%')
      $(this).find('.userOnline').css('opacity','100%')
      $(this).find('.photo').css('display','none')
    }
    )
    /* click product */
    $(document).on('click', '[js="clickProduto"]', function(){
      slug = $(this).attr('slug');
      location.href = BASE+'item/'+slug
    })
    /* */
    $('.logo').click(function(){
      location.href = BASE
    })

    
})
function validateEmail(email) {
    $('.alert-div-login').remove()
    const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if(re.test(email)){
        return true;
    }else{
        $('[mode="registro"]').before('<div class="alert-div-login"> E-mail inválido <span class="svg-error"></span></div>')
    }

}
function validateNumb(evt) {
    var theEvent = evt || window.event;
  
    // Handle paste
    if (theEvent.type === 'paste') {
        key = event.clipboardData.getData('text/plain');
    } else {
    // Handle key press
        var key = theEvent.keyCode || theEvent.which;
        key = String.fromCharCode(key);
    }
    var regex = /[0-9]|\./;
    if( !regex.test(key) ) {
      theEvent.returnValue = false;
      if(theEvent.preventDefault) theEvent.preventDefault();
    }
}
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays));
  var expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}
function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }
function alertar(texto,redirect){
  width = $(window).width()
  $('.message-alert-box').animate({'opacity':'0'}).css('left','-5000px')
  $('.content-Alert').text('')
  $('.content-Alert').text(texto)
  if(width <= 500){
    $('.message-alert-box').animate({'opacity':'1'}).removeClass('error').css('left','0px')
  }else{
    $('.message-alert-box').animate({'opacity':'1'}).removeClass('error').css('left','20px')
  }
  setTimeout(function(){
      $('.message-alert-box').animate({'opacity':'0'}).css('left','-5000px')
      $('.content-Alert').text('')
      if(redirect != '' && redirect.length > 0 && redirect != undefined){location.href=redirect}
  }, 6000)
}
function modal(texto){
  if(texto != '' || texto == undefined || texto == null){
    $('.modal-box').css('display','block').css('opacity','1').text(texto)
  }else{
    $('.modal-box').css('display','block').css('opacity','1')
  }
}
function redirectHttps(){
  url = location.href;
  penis = url
  url = url.split('/')
  http = url[0];
  if(http == 'http:'){
    location.href = penis.replace('http:','https:')
  }
}
window.onload = function() {
  //redirectHttps();
  //activity();

};
function redirect(url){
  location.href = url
}

setInterval(function(){
  activity();
},10000)

function activity(){
  $.ajax({
      url:BASE+'ajax/ajaxUser.php',
      method:'post',
      data:{'type-action':'updateActivity','token':sessionStorage.getItem('token')}
  })
}

window.$_GET = new URLSearchParams(location.search);