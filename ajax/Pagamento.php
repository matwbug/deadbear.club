<?php

use Picpay\Buyer;
use Picpay\Exception\RequestException;
use Picpay\Payment;
use Picpay\Request\PaymentRequest;
use Picpay\Seller;

    include('../config.php');
    $data['msg'] = '';
    $data['sucesso'] = true;
    $action = $_POST['acao'];
    include('../Classes/Site.php');
    include('../Classes/MySql.php');
    include('../Classes/Admin.php');
    include('../Classes/Email.php');
    include('../Models/Pagamento.php');
    require('../vendor/autoload.php');

    //print_r($_POST);
    if(isset($action)){
        include('../Classes/PicPay.php'); //incluindo classe picpay
        include('../Classes/Pix.php'); //incluindo classe Pix

        if($action == 'getPaymentTab'){
            $pedidoID = $_POST['pedidoID'];
            $pedidoInfo = Site::getInfoDB('pedido.cart.users','`id`='.$pedidoID);
            if($pedidoInfo['metodopagamento'] == 'PIX'){
                $pix = new Pix('apk_46355677-otSYjLIKjAydfCmsOOHpblHuBrrGQCnE','89XNRLXYZLBXI3WQFBRW09W16Q7USMI0WGOO1LM17XYQ');
                $response = $pix->criarCobPix($pedidoID);
                try{
                    if($response['pix_create_request']['result'] != '' && $response['pix_create_request']['result'] == 'success'){
                        $data['response'] = '<div class="container-main">
                                                <div class="center" style="text-align:center;display: flex;flex-direction: column;align-content: center;justify-content: center;align-items: center;" >
                                                    <h2>Total a pagar: <b style="color:#2596be;">R$'.str_replace(',','.',$pedidoInfo['valortotal']).'</b></h2>
                                                    <hr style="width:100%; background:#ccc; margin: 10px 0;">
                                                    <span>Utilize o código copia e cola para fazer a transferência</span>
                                                    <div class="code">
                                                        <div id="copyTarget">
                                                            <p>'.$response['pix_create_request']['pix_code']['emv'].'</p>
                                                        </div>
                                                        <button class="copyCode"><span class="material-icons">content_copy</span></button>
                                                    </div>
                                                    <span style="margin: 10px 0;">Ou leia esse código QR</span>
                                                    <img style="width:300px; max-width:100%" src="'.$response['pix_create_request']['pix_code']['qrcode_image_url'].'">
                                                    <h3 style="margin-top: 10px;font-weight: 500;font-size: 15px;">Após fazer o pagamento e ele for confirmado aguarde até 10 minutos nessa página caso não aconteça nada contate um administrador.</h3>  
                                                </div>
                                            </div>';
                        \Models\Pagamento::sendEmailPayment($pedidoID);
                    }else{
                        $data['response'] = '<div style="width:100%; background: #ce0c45; padding:10px;>Aconteceu algum erro com o pagamento, contate a administração.</div>';
                        $data['sucesso'] = false;
                    }
                }catch(Exception $e){
                    $data['response'] = '<div style="width:100%; background: #ce0c45; padding:10px;>Aconteceu algum erro com o pagamento, contate a administração.</div>';
                    $data['sucesso'] = false;
                }
            }else if($pedidoInfo['metodopagamento'] == 'PICPAY'){
                $data['picpay'] = true;
                $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
                $picpay = new PicPay('fa2a854c-7118-4c4b-87e3-78b1c6cba33c','a1edf350-3cc8-45e6-9ad2-8bae44d3d85a');
                if($response = $picpay->criarCobPix($pedidoInfo['id'],$pedidoInfo['valortotal'])){
                    $data['redirect'] = $response->paymentUrl;
                    $data['msg'] = 'Seu link de pagamento foi criado, assim que fizer o pagamento você será redirecionado para o site novamente.';
                    \Models\Pagamento::sendEmailPayment($pedidoID);
                }else{
                    $data['sucesso'] = false;
                    $data['msg'] = 'Aconteceu algum erro, contate a administração informar erro com a plataforma de pagamento';
                } 
            }
        }else if($action == 'checkPayment'){
           //checkar se fez pagamento
            if($_POST['id'] != ''){
                $idpagamento = $_POST['id'];
                $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `id` = ?");
                $sql->execute(array($idpagamento));
                $pedidoInfo = $sql->fetch();
                $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.txid` WHERE `id.pedido` = ?");
                $sql->execute(array($pedidoInfo['id']));
                if($sql->rowCount() == 0){
                    die(json_encode($data));
                }
                $info = $sql->fetch();
                $pedido = array([
                    "id" => $pedidoInfo['id'],
                    "txid" => $info['txid'],
                    "method" => $pedidoInfo['metodopagamento']
                    ]);
            }
            else{
                $pedidoInfo = Site::getInfoDBAll('pedido.cart.users','`user.id`='.Site::getUserInfo($_COOKIE['loginToken'])['id']);
                foreach($pedidoInfo as $key => $value){
                    $info = Site::getInfoDB('pedido.txid','`id.pedido`='.$value['id']);
                    $pedido = array([
                        "id" => $value['id'],
                        "txid" => $info['txid'],
                        "method" => $value['metodopagamento']
                    ]);
                } 
            }
            foreach($pedido as $key => $value){
                if($value['method'] == 'PICPAY'){
                    $picpay = new PicPay('fa2a854c-7118-4c4b-87e3-78b1c6cba33c','a1edf350-3cc8-45e6-9ad2-8bae44d3d85a');
                    $response = $picpay->PagamentoStatus($value['txid']);
                    if($response->status == 'completed'){
                       if(\Models\Pagamento::pagamentoConfirmado($value['id'])){
                            $autID = $response->authorizationId;
                            $sql = MySql::conectar()->prepare("UPDATE `pedido.txid` SET `picpayAutorizationID` = ? WHERE `id.pedido` = ?"); 
                            if($sql->execute(array($autID,$value['id']))){
                                //$data['redirect'] = BASE.'chat/';
                                $data['msg'] = 'Seu pagamento foi confirmado, aguarde um momento.';
                                $data['pago'] = true;
                            }
                       }else{
                            $data['msg'] = 'Aconteceu algum erro, contate administração.';

                       }
                    }else{
                        if(isset($_POST['chat'])){
                            $data['msg'] = 'Seu pagamento ainda não foi confirmado, caso isso persista por muito tempo contate a administração.';
                            $data['pago'] = false;
                        }
                    }
                }else if($value['method'] == 'PIX'){
                    $pix = new Pix('apk_46355677-otSYjLIKjAydfCmsOOHpblHuBrrGQCnE','89XNRLXYZLBXI3WQFBRW09W16Q7USMI0WGOO1LM17XYQ');
                    $response = $pix->PagamentoStatus($value['txid']);
                    //print_r($response);
                    if($response['status_request']['status'] == 'paid'){
                        if(\Models\Pagamento::pagamentoConfirmado($value['id'])){
                            $data['redirect'] = BASE.'chat/';
                            $data['pago'] = true;
                            $data['msg'] = 'Seu pagamento foi confirmado, aguarde um momento.';
                       }else{
                            $data['msg'] = 'Já existe um ticket para esse pedido, contate um administrador.';
                            $data['redirect'] = BASE.'chat';

                       }
                    }else{
                        if(isset($_POST['chat'])){
                            $data['msg'] = 'Seu pagamento ainda não foi confirmado, caso isso persista por muito tempo contate a administração.';
                            $data['pago'] = false;
                        }
                    }
                } 
            }
        }
        die(json_encode($data));
    }else{
        die(Site::redirecionar(BASE));
    }

?>