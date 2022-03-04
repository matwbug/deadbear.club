"use strict";

$(function () {
  $(document).on('focus', '[datamask="preco"]', function () {
    $(this).maskMoney({
      prefix: 'R$ ',
      allowNegative: true,
      thousands: '.',
      decimal: ',',
      affixesStay: false
    });
  });
  $(document).on('focus', '[datamask="preco"]', function () {
    $(this).maskMoney({
      prefix: 'R$ ',
      allowNegative: true,
      thousands: '.',
      decimal: ',',
      affixesStay: false
    });
  });
  $(document).on('focus', '[datamask="cpf"]', function () {
    $(this).mask('000.000.000-00', {
      reverse: true
    });
  });
  $(document).on('focus', '[datamask="telefone"]', function () {
    $(this).mask('+00 (00) 00000-0000');
  }); //$('[mask="date"]').mask('00/00/0000');

  $(document).on('click', '.tab-cong', function () {
    $('.cat-chat').removeClass('active');
    $(this).toggleClass('active');
    $('.ajax-loading').css('display', 'block');
    $.ajax({
      url: BASE + 'ajax/Ticket.php',
      method: 'post',
      data: {
        'acao': 'ticketsCongelados',
        'perm': 'admin'
      }
    }).done(function (data) {
      $('.ajax-loading').css('display', 'none');
      $('.contentChats').empty();
      data = JSON.parse(data);

      if (data.sucesso) {
        tickets = data.msg.split('||');

        for (i = 0; i < tickets.length; i++) {
          $('.contentChats').append(tickets[i]);
        }
      }
    });
  });
  $(document).on('click', '.menu-btn', function () {
    width = $(window).width();

    if (width <= 1000) {
      $('html, body').animate({
        scrollTop: $(".menu").offset().top
      }, 500);
    } else {
      if ($('.menu').css('left') == '0px') {
        //ta aberto e com tamanho de pc
        $('header').animate({
          'left': '0'
        }).css('width', '100%');
        $('.container-contentWrapper').animate({
          'left': '0'
        }).css('width', '100%');
        $('.menu').animate({
          'left': '-300px'
        });
      } else {
        //nao esta abertto e com tamanho de pc
        $('header').animate({
          'left': '300'
        }).css('width', 'calc(100% - 300px)');
        $('.container-contentWrapper').animate({
          'left': '300'
        }).css('width', 'calc(100% - 300px)');
        $('.menu').animate({
          'left': '0'
        });
      }
    }
  });
  $('#logout').click(function () {
    // deslogar
    //vai deslogar :c
    document.cookie = "admin_loginToken=; expires=Thu, 01 Jan 1900 00:00:00 UTC; path=/;";
    location.href = BASE + 'dashboard';
  });
  $('#edit').click(function () {
    // clicar pra editar perfil
    location.href = BASE + 'dashboard/perfil';
  });
  $('#img-edit').click(function () {});
  $('.infoPerfil').find('.img').hover(function () {
    $(this).find('img').css('opacity', '10%');
    $(this).find('.photo').css('display', 'block');
  }, function () {
    $(this).find('img').css('opacity', '100%');
    $(this).find('.photo').css('display', 'none');
  });
  openAvatar = false;
  $('.avatarArea').click(function () {
    if (openAvatar) {
      $('.menuHoverAvatar').css('right', '-9999px').css('top', '40px').animate({
        'opacity': '0'
      });
      $('.avatarArea').removeClass('hover');
      openAvatar = false;
    } else {
      $('.menuHoverAvatar').css('right', '15px').css('top', '40px').animate({
        'opacity': '1'
      });
      $('.avatarArea').addClass('hover');
      openAvatar = true;
    }
  });
});

function alertar(texto, redirect) {
  $('.content-Alert').text(texto);
  $('.message-alert-box').animate({
    'opacity': '1'
  }).removeClass('error').css('right', '50px');
  setTimeout(function () {
    $('.message-alert-box').animate({
      'opacity': '0'
    }).css('right', '-400px');
    $('.content-Alert').text('');

    if (redirect != '' || redirect.length > 0) {
      location.href = redirect;
    }
  }, 5000);
}

function modal(texto) {
  $('.modal-box').text(texto).css('display', 'block').css('opacity', '1');
}

function changeImage() {
  var imgUrl = $('[name="photo"]').prop('files')[0];
  var formData = new FormData();
  formData.append('file', imgUrl);
  formData.append('type-action', 'changeImage');
  $.ajax({
    url: BASE + 'ajax/Admin.php',
    method: 'post',
    contentType: false,
    cache: false,
    processData: false,
    data: formData
  }).done(function (data) {
    data = JSON.parse(data);
    alertar(data.msg, '');
    $('.img').find('img').prop('src', data.newImage);
  });
}

function redirectHttps() {
  url = location.href;
  penis = url;
  url = url.split('/');
  http = url[0];

  if (http == 'http:') {
    location.href = penis.replace('http:', 'https:');
  }
}

window.onload = function () {//redirectHttps();
  //activity();
};

function checkVisible(elm) {
  var rect = elm.getBoundingClientRect();
  var viewHeight = Math.max(document.documentElement.clientHeight, window.innerHeight);
  return !(rect.bottom < 0 || rect.top - viewHeight >= 0);
}

function activity() {
  $.ajax({
    url: BASE + 'ajax/Admin.php',
    method: 'post',
    data: {
      'type-action': 'updateActivity'
    }
  });
}

setInterval(function () {
  activity();
}, 10000);