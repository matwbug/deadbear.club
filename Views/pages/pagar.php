 <?php

use Models\Pagamento;

if($info = Pagamento::getInfoOrder($_GET['id'])){
?>
<style>
    .code{
        background: mintcream;
        width: 100%;
        max-width: 400px;
        height: 60px;
        margin: 5px 0;
        padding: 5px;
        display: flex;
        flex-direction: row;
        align-content: center;
        align-items: center;
        overflow-y: hidden;
        overflow-x: scroll;
        position: relative;

    }
    .code ::-webkit-scrollbar{
        width: 5px;
    }
    .code p{
        color:black;
        white-space: nowrap;
    }
    .code button{
        float: right;
        border:none;
        padding: 5px;
        margin:5px;
        background: #2596be;
        position: sticky;
        top: 0;
        right: 0;
        font-size:20px;
    }

</style>
<?php 
    Site::loadJs('user','user',
    array('pagamento'), // paginas que o chat sera carregado
    'pagamento' 
    );
    $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.txid` WHERE `id.pedido` = ?");
    $sql->execute(array($_GET['id'])); 
    if($sql->rowCount() >= 1){
        $response = $sql->fetch();
        $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `id` = ?"); $sql->execute(array($_GET['id'])); 
        $pedidoInfo = $sql->fetch();
        echo '<div class="container-main">
                <div class="center" style="text-align:center;display: flex;flex-direction: column;align-content: center;justify-content: center;align-items: center;" >
                    <h2>Total a pagar: <b style="color:#2596be;">R$'.number_format($pedidoInfo['valortotal'], 2,'.','.').'</b></h2>
                    <hr style="width:100%; background:#ccc; margin: 10px 0;">
                    <span>Utilize o código copia e cola para fazer a transferência</span>
                    <div class="code">
                        <div id="copyTarget">
                            <p>'.$response['qrcode'].'</p>
                        </div>
                        <button class="copyCode"><span class="material-icons">content_copy</span></button>
                    </div>
                    <span style="margin: 10px 0;">Ou leia esse código QR</span>
                    <img style="width:300px; max-width:100%" src="'.$response['base64'].'">  
                    <h3 style="margin-top: 10px;font-weight: 500;font-size: 15px;">Após fazer o pagamento e ele for confirmado aguarde até 10 minutos nessa página caso não aconteça nada contate um administrador.</h3>
                </div>
            </div>';
    }else{
        $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
        if($userInfo['nome'] == null || $userInfo['sobrenome'] == null || $userInfo['cpf'] == null || $userInfo['data.nascimento'] == null){
            Site::scriptJS('alertar(`Você não preencheu todos seus dados, precisamos deles para finalizar o pagamento.`,`'.BASE.'minhaconta?tab=meusdados&pagamentopedido='.$_GET['id'].'`)');
        }else{
            $id = $_GET['id'];
            Site::scriptJS('$(function(){
                $("#fountainG").css("display","block")
                $.ajax({
                    url: BASE+"ajax/Pagamento.php",
                    method:"post",
                    data:{"acao":"getPaymentTab","pedidoID":'.$id.', "token":sessionStorage.getItem("token")}
                }).done(function(data){
                    $("#fountainG").css("display","none")
                    data = JSON.parse(data);
                    if(data.picpay){
                        return alertar(data.msg,data.redirect)  
                    }else{
                        $(".ajax-loading").remove();
                        $(".contentSite").prepend(data.response)
                    }
                    //alertar(data.msg,"");   

                    
                })
            })');  
        }
    }
?>



<?php }else{ ?>
        <div style="margin: 20px 0;width:100%; background:#ce0c45; padding:10px; text-align:center;"><h3> <i class="fas fa-times"></i> Este pedido não está mais disponível ou você não tem acesso.</h3></div>
<?php } ?>
<script>
    $(document).on('click','.copyCode', function(){
        copyToClipboard(document.getElementById("copyTarget"));
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
</script>