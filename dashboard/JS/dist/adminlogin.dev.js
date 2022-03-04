"use strict";

$(function () {
  $('.ajax').submit(function (e) {
    e.preventDefault();
    var form = $(this);
    var url = form.attr('action');
    $('#fountainG').css('display', 'block');
    $.ajax({
      type: "POST",
      url: url,
      data: form.serialize(),
      success: function success(data) {
        data = JSON.parse(data);
        alertar(data.msg, data.redirect);
        $('#fountainG').css('display', 'none');
      },
      error: function error(_error) {
        $('#fountainG').css('display', 'none');
      }
    });
  });
});