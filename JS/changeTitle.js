$(document).ready(function(){
    if($('.titleProduct').val() != null){
        nomeProduto = $('.titleProduct').text()
        document.title = 'Deadbear | '+nomeProduto
    }
})