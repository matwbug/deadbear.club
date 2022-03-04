<?php 
    namespace Models;

        use MySql;
        use Site;
        use Email;

    class Pagamento{
            public static function getInfoPayment($txid){
                $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.txid` WHERE `txid` = ?");
                $sql->execute(array($txid));
                if($sql->rowCount() >= 1){
                    $info = $sql->fetch()['id.pedido'];
                    $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `id` = ?");
                    $sql->execute(array($info));
                    $info = $sql->fetchAll();
                    return $info;
                }else{
                    return false;
                }
            }
            public static function getInfoOrder($id){
                $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
                $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `id` = ? AND `user.id` = ? AND `status` = 1");
                $sql->execute(array($id,$userid));
                if($sql->rowCount() >= 1){
                    $info = $sql->fetch();
                    return $info;
                }else{
                    return false;
                }
            }
            public static function paymentConfirmedSendEmail($pedidoid){
                $pedidoInfo = Site::getInfoDB('pedido.cart.users','`id`='.$pedidoid);
                $userInfo = Site::getUserInfoByID($pedidoInfo['user.id']);
                $paymentInfo = Site::getInfoDB('pedido.txid','`id.pedido`='.$pedidoid);
                $mail = new Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
                $mail->enviarPara($userInfo['email'],'Caro usuário '.ucfirst($userInfo['username']));
                $mail->formatarEmail(array('Assunto'=>'Seu pagamento foi confirmado','Corpo'=>
                '
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Deadbear</title>
                <link rel="preconnect" href="https://fonts.googleapis.com/%22%3E
                <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,500;1,700&display=swap" rel="stylesheet">
                <style>
                    *{
                        font-family: "Roboto", sans-serif;
                        color:white;
                    }
                    .email-wrap{
                        text-align:center;
                        margin: 20px 0;
                        padding:10px;
                        width:100%;
                    }
                    button{
                        padding: 10px;
                        border:none;
                        background-color:#512da8;
                        color:white;
                        cursor:pointer;
                        margin-top:10px; 
                        border-radius:3px;
                    }
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
                        padding: 10px;
                        margin:5px;
                        background: #2596be;
                        position: sticky;
                        top: 0;
                        right: 0;
                    }
                </style>
                </head>
                <body>
                <div class="email-wrap">
                    <div style="background: #4717f6; width:100%; min-height:300px; margin:10px 0; padding: 10px;">
                        <img style="width: 90px; height:90px; border-radius:50%;" src="'."https://deadbear.club/".'data/images/logo.webp">
                        <h4>O pagamento do pedido <b>'.$paymentInfo['txid'].'</b> foi aprovado!</h4>
                        <p>Seu pagamento foi aprovado, clique abaixo para ir direto ao atendimento ao cliente, recomendamos que faça isso o mais rápido possível!</p>
                        <a href="'.BASE.'chat><button>Ir ao chat</button></a>
                    </div>
                </div>
                </div>
                </body>
                </html>'));
                if($mail->enviarEmail()){
                    return true;
                }
                return false;
            }
            public static function pagamentoConfirmado($pedidoid){
                Site::createTicket(Site::getUserInfo($_COOKIE['loginToken'])['id'], $pedidoid);
                $sql = MySql::conectar()->prepare("UPDATE `pedido.txid` SET `status` = 'pago' WHERE `id.pedido` = $pedidoid OR `id.ticket` = $pedidoid"); $sql->execute();
                if(Site::getRowCountDB('pedido.txid','`id.pedido`='.$pedidoid.' AND `id.pedido`!=NULL') >= 1){
                    //$pedidotxidinfo = Site::getInfoDB('pedido.txid','`id.pedido`='.$pedidoid);
                    $sql = MySql::conectar()->prepare("UPDATE `pedido.cart.users` SET `status` = 0 WHERE `id` = ?");
                    $sql->execute(array($pedidoid));
                    $carts = explode(',', Site::getInfoDB('pedido.cart.users','`id`='.$pedidoid));
                    foreach($carts as $key => $value){
                        $infoCart = Site::getInfoDB('cart.users','`id`='.$value);
                        if($infoCart['cupom.id'] != 0){// NAO TEM CUPOM
                            $sql = MySql::conectar()->prepare("UPDATE `usuarios.cupom.usados` SET `stats` = 'concluido' WHERE `cart.id` = ?");
                            $sql->execute(array($value['id']));
                            if(Site::getRowCountDB('produtos.default.cupons','`id`='.$infoCart['cupom.id']) >= 1)
                            {
                                $refferencer = Site::getInfoDB('produtos.default.cupons','`id`='.$infoCart['cupom.id'])['creator.id'];
                            }
                            if($refferencer != '' || isset($refferencer) || $refferencer){
                                $sql = MySql::conectar()->prepare("INSERT INTO `reffers` VALUES(NULL,?,?,?)");
                                $sql->execute(array($pedidoid,$infoCart['cupom.id'],$refferencer));
                            }
                        }
                    }
                }
                self::paymentConfirmedSendEmail($pedidoid);
                return true;
                
            }

            public static function sendEmailPayment($pedidoid){
                $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
                $paymentInfo = Site::getInfoDB('pedido.txid','`id.pedido`='.$pedidoid);
                $pedidoInfo = Site::getInfoDB('pedido.cart.users','`id`='.$pedidoid);
                $mail = new Email('deadbear.club','admin@deadbear.club','+na2GlEm1T*V','Deadbear');
                $mail->enviarPara($userInfo['email'],'Caro usuário '.ucfirst($userInfo['username']));
                $mail->formatarEmail(array('Assunto'=>'Seu pagamento foi gerado','Corpo'=>
                '
                <html>
                <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <title>Deadbear</title>
                <link rel="preconnect" href="https://fonts.googleapis.com/%22%3E
                <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,500;1,700&display=swap" rel="stylesheet">
                <style>
                    *{
                        font-family: "Roboto", sans-serif;
                        color:white;
                    }
                    .email-wrap{
                        text-align:center;
                        margin: 20px 0;
                        padding:10px;
                        width:100%;
                    }
                    button{
                        padding: 10px;
                        border:none;
                        background-color:#512da8;
                        color:white;
                        cursor:pointer;
                        margin-top:10px; 
                        border-radius:3px;
                    }
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
                        padding: 10px;
                        margin:5px;
                        background: #2596be;
                        position: sticky;
                        top: 0;
                        right: 0;
                    }
                </style>
                </head>
                <body>
                <div class="email-wrap">
                    <div style="background: #4717f6; width:100%; min-height:300px; margin:10px 0; padding: 10px;">
                        <img style="width: 90px; height:90px; border-radius:50%;" src="'."https://deadbear.club/".'data/images/logo.webp">
                        <h4>Seu pagamento foi gerado!</h4>
                        <p>Seu link de pagamento para o pedido <b>'.$paymentInfo['txid'].'</b> foi gerado com sucesso, clique no botão abaixo para visualizar.</p>
                        <a href="'.BASE.'pagamento?id='.$pedidoid.'"><button>Ver pedido</button></a>
                        <hr style="width:100%; background:#ccc; margin: 10px 0;">
                        <p>Ou se preferir pague por este QRCODE</p>
                        <div style="width:100%; text-align:center; margin: 0 auto;" >
                            <h2>Total a pagar: <b style="color:#2596be;">R$'.number_format($pedidoInfo['valortotal'], 2,',','.').'</b></h2>
                            <span>Utilize o código copia e cola para fazer a transferência</span>
                            <div class="code" style="margin: 0 auto;">
                                <div id="copyTarget">
                                    <p>'.$paymentInfo['qrcode'].'</p>
                                </div>
                            </div>
                            <span style="margin: 10px 0;">Ou leia esse código QR</span>
                            <img style="width:300px; max-width:100%" src="'.$paymentInfo['base64'].'">  
                            <h3 style="margin-top: 10px;font-weight: 500;font-size: 15px;">Após fazer o pagamento e ele for confirmado aguarde até 10 minutos nessa página caso não aconteça nada contate um administrador.</h3>
                        </div>
                    </div>
                    </div>
                </div>
                </body>
                </html>'));
                if($mail->enviarEmail()){
                    return true;
                }
                return false;
            }

            public static function insertDbPaymentRefund($idpedido = null, $idticket = null,$cancellationID){
                $idpedido = $idpedido == null ? null : $idpedido;
                $idticket = $idticket == null ? null : $idticket;
                if(Site::getRowCountDB('pedido.txid.canceled','`id.pedido`='.$idpedido.' OR `id.ticket`='.$idticket) >= 1);
                    return false;
                $sql = MySql::conectar()->prepare("INSERT INTO `pedido.txid.canceled` VALUES(null,$idpedido,$idticket,$cancellationID,?)");
                $sql->execute(array(date('Y-m-d H:i:s')));
            }
        }
?>