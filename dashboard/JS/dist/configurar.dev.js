"use strict";

$(function () {
  var _this = this;

  $(document).on('click', '.closeTab', function () {
    $('.tabopened').animate({
      'opacity': '0'
    }).css('display', 'none').remove();
    $('.cat-actions').find('button').removeClass('active');
  });
  $(document).on('click', '[js="tab-adicionarNovoAdmin"]', function () {
    $('#fountainG').css('display', 'block');
    $.ajax({
      url: BASE + 'ajax/Admin.php',
      method: 'post',
      data: {
        'type-action': 'tab-adicionarAdmin'
      }
    }).done(function (data) {
      $('#fountainG').css('display', 'none');
      data = JSON.parse(data);
      $('.tabopened').remove();

      if (data.sucesso) {
        $('.admin-list').parent().before(data.response);
        elem = document.getElementById('tabopened');

        if (!checkVisible(elem)) {
          $('html, body').animate({
            scrollTop: $(".tabopened").offset().top
          }, 500);
        }
      }
    });
  });
  $(document).on('click', '[js="confirm-adicionarNovoAdmin"]', function () {
    var user = $(_this).find('input[name="username"]').val();
    var email = $(_this).find('[name="email"]').val();
    var chavemestra = $(_this).find('[name="code"]').val();

    if (chavemestra.length <= 0 || email.length <= 0 || user.length <= 0) {
      return alertar('Você precisa preencher todos os dados.', '');
    }

    $('#fountainG').css('display', 'block');
    $.ajax({
      url: BASE + 'ajax/Admin.php',
      method: 'post',
      data: {
        'type-action': 'adicionarAdmin',
        'username': user,
        'email': email,
        'chavemestra': chavemestra
      }
    }).done(function (data) {
      $('#fountainG').css('display', 'none');
      data = JSON.parse(data);
      alertar(data.msg, '');

      if (data.sucesso) {
        $('.admin-list').parent().before(data.response);
        elem = document.getElementById('tabopened');

        if (!checkVisible(elem)) {
          $('html, body').animate({
            scrollTop: $(".tabopened").offset().top
          }, 500);
        }
      } else {
        $('.tabopened').remove();
      }
    });
  });
  $(document).on('click', '[js="changeCodeMaster"]', function () {
    $('#fountainG').css('display', 'block');
    $.ajax({
      url: BASE + 'ajax/Admin.php',
      method: 'post',
      data: {
        'type-action': 'tab-changeCodeMaster'
      }
    }).done(function (data) {
      data = JSON.parse(data);
      $('#fountainG').css('display', 'none');
      $('.tabopened').remove();

      if (data.sucesso) {
        $('.passwordmaster').parent().after(data.response);
        elem = document.getElementById('tabopened');

        if (!checkVisible(elem)) {
          $('html, body').animate({
            scrollTop: $(".tabopened").offset().top
          }, 500);
        }
      }
    });
  });
  $(document).on('click', '[js="confirm-changeCodeMaster"]', function () {
    var codeantigo = $(this).parent().find('[name="codeant"]').val();
    var codenovo = $(this).parent().find('[name="codenovo"]').val();
    $('#fountainG').css('display', 'block');
    $.ajax({
      url: BASE + 'ajax/Admin.php',
      method: 'post',
      data: {
        'type-action': 'changeCodeMaster',
        'codeantigo': codeantigo,
        'codenovo': codenovo
      }
    }).done(function (data) {
      data = JSON.parse(data);
      $('#fountainG').css('display', 'none');
      alertar(data.msg, '');

      if (data.sucesso) {
        $('.tabopened').remove();
      }
    });
  });
});

function getAdmins() {}