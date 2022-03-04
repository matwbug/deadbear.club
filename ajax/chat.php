<?php

include('../config.php');
include('../Classes/Site.php');
include('../Classes/MySql.php');
include('../Classes/Admin.php');
include('../Classes/Usuario.php');
include('../Models/ChatModels.php');
require('../vendor/autoload.php');
include('../Classes/Chat.php');
//error_reporting(E_ALL ^ E_NOTICE);

//session_start();


$action = $_POST['acao'];
$token = @$_POST['token'];

if(!\Site::validar_token($token)){
    $data['msg'] = 'Aconteceu algum erro, contate administração';
    $data['sucesso'] = false;
    die(json_encode($data));
}

if(isset($action)){
    $data['msg'] = '';
    $data['sucesso'] = true;
    $type = $_POST['perm'];
    if($type == 'user'){
        if(!Site::logado()){
            $data['msg'] = 'Percebemos que você ainda não fez login, redirecionando.';
            $data['redirect'] = BASE.'login/';
            die(json_encode($data));
        }
        $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
        if(!$userid){
            $data['msg'] = 'Aconteceu algum erro';
            $data['sucesso'] = false;
            die(json_encode($data));
        }
        $ticketInfo = Site::getInfoDB('tickets',"`creator_id` = '$userid' AND `closed` = 0");
        
        if($action == 'getMessages')
        {
            if(Site::getRowCountDB('tickets.msg','`ticket_id`='.$ticketInfo['id']) >= 1){
                $data['haveMessages'] = true;
                $mensagens = Site::getInfoDBAll('tickets.msg','`ticket_id`='.$ticketInfo['id']);
                foreach($mensagens as $key => $value){
                    $conteudo = $value['type'] == 'texto' ? $value['conteudo'] : "<span><img class='clickopen' src='".BASE.'data/users/msg/'.$value['conteudo']."'></span>"; 
                    if(Site::getRowCountDB('usuarios','`id`="'.$value['user_id'].'"') <= 0){
                        $user = Site::getInfoDB('admin','`id`='.$value['user_id']);
                        $data['msg'] .= '<div class="ticket-message flex-center-notresize direction-row w100 direction-row" style="justify-content:flex-start; flex-wrap:nowrap; align-items:flex-start;">
                                            <div class="flex-center direction-column"> 
                                                <img class="avatarPhoto" src='.Admin::getProfilePhotoId($user['id']).'>
                                            </div>
                                            <div class="flex-center-notresize" style="justify-content:flex-start; margin-left:10px;">
                                                <div class="w100 flex-center-notresize" style="justify-content: flex-start;">
                                                <p>'.$user['username'].' <b>VENDEDOR</b></p>
                                                    <span class="time flex-center-notresize">'.Chat::returnFormatedDate(($value['data-criacao'])).'</span>
                                                </div>
                                                <div class="flex-center">
                                                    <span class="content">'.$conteudo.'</span>
                                                </div>
                                            </div>
                                        </div>';
                    }else{
                        $user = Site::getInfoDB('usuarios','`id`="'.$value['user_id'].'"');
                        $data['msg'] .= '<div class="ticket-message bot flex-center-notresize direction-row w100 direction-row" style="justify-content:flex-start; flex-wrap:nowrap; align-items:flex-start;">
                                            <div class="flex-center direction-column"> 
                                                <img class="avatarPhoto" src="'.BASE."data/images/upload/".Site::getImageUser($user['id']).'">
                                            </div>
                                            <div class="flex-center-notresize" style="justify-content:flex-start; margin-left:10px;">
                                                <div class="w100 flex-center-notresize" style="justify-content: flex-start;">
                                                <p>'.$user['username'].'</p>
                                                    <span class="time flex-center-notresize">'.Chat::returnFormatedDate(($value['data-criacao'])).'</span>
                                                </div>
                                                <div class="flex-center">
                                                    <span class="content">'.$conteudo.'</span>
                                                </div>
                                            </div>
                                        </div>';
                    }
                    $_SESSION['lastId'] = $value['id']; 
                }
            }else{
                $data['sucesso'] = false;
                if(Site::getInfoDB('tickets.msg','1=1 ORDER BY `id` DESC LIMIT 1') >= 1){
                    $_SESSION['lastId'] = Site::getInfoDB('tickets.msg','1=1 ORDER BY `id` DESC LIMIT 1')['id']; 
                }else{
                    $_SESSION['lastId'] = 0; 
                }
                
            }
        }else if($action == 'ticketStatus'){
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            if(Site::getRowCountDB('tickets',"`creator_id` = '$userid' AND `closed` = 0") >= 1){
                $ticket = Site::getInfoDB('tickets',"`creator_id` = '$userid' AND `closed` = 0");
                $transacaoInfo = Site::getInfoDB('pedido.txid','`id.pedido`='.$ticket['pedido.id']);
                if($transacaoInfo['status'] == 'pendente'){
                    $data['status'] = 'aguardando';
                    $data['infAtendido'] = '<h2><img src="'.BASE.'data/images/loading.gif"></h2>';
                    $data['ticketbody'] = '<div class="flex-center direction-column" style="margin: 20px;">
                                                <img style="width:100px; max-width:100%;" src="'.BASE.'data/images/suspeito-emoji.gif">
                                                <h5 style="font-size:medium;">Estamos aguardando seu pagamento para o envio do produto ser efetuado, caso já tenha pago contate o <b><a href="<?php echo BASE ?>discord">discord</a></b></h5>
                                            </div>';
                }else if($ticket['reivindicado'] == true){
                    $data['status'] = 'atendido';
                    $data['infAtendido'] = '<div class="admin" title="Administrador">
                                                <div class="img">
                                                    <div title="Online agora" class="userOnline ticket"></div>
                                                    <img src="'.Admin::getProfilePhotoId($ticket['reivindicado_id']).'">
                                                </div>
                                                <span>'.Admin::getUserInfoId($ticket['reivindicado_id'])['username'].'</span>
                                            </div>';
                }else{
                    $data['status'] = 'aguardando';
                    $data['infAtendido'] = '<h2>Aguarde um vendedor já irá te atender, não se preocupe será notificado no seu e-mail quando seu chat for atendido. </h2>';
                }
                if($ticket['closed'] == true){
                    $data['fechado'] = true;
                }else if($ticket['status'] == 'pausado'){
                    $data['sucesso'] = false;
                    $data['msg'] = Chat::messageDeadbear('congelado','Seu ticket foi congelado, não é possível enviar mensagens.');
                }

                $data['nenhumadminon'] = false;
                foreach(Site::getInfoDBAll('admin') as $key => $value){
                    if(Admin::isOnline($value['id'], 60)){
                        die(json_encode($data));
                    }
                }
                $data['nenhumadminon'] = true;
                $data['text'] = Chat::messageDeadbear('nenhumadmin','No momento não há  nenhum vendedor online, <b>Horário de atendimento:</b> Segunda a Sexta: <b>12:00 até 22:00</b> <br> Sábado e Domingo: <b>14:00 até 20:00</b>');                
                
            }else{
                $data['fechado'] = true;
                $data['msg'] = Chat::messageDeadbear('ticketfinalizado','Seu ticket foi finalizado, você será redirecionado para avaliar o atendimento');
                $data['redirect'] = BASE.'feedbacks/?ticketid='.$ticket['id'].'&action=wfeedback';
                
            }
            
        }
        else if($action == 'enviarFoto'){
            $img = $_FILES['image'];
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $infoTicket = Site::getInfoDB('tickets',"`creator_id` = '$userid' AND `closed` = 0");
            if($infoTicket['status'] == 'pausado'){
                $data['sucesso'] = false;
                $data['msg'] = Chat::messageDeadbear('congelado','Seu ticket foi congelado, não é possível enviar mensagens.');
            }else{
                $ticketid = $infoTicket['id'];
                if(\Models\ChatModels::ImagemValida($img)){
                    $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
                    if($nameFile = \Models\ChatModels::uploadImage($img)){
                        $_SESSION['lastId'] = Chat::insertMessage($ticketid,'imagem',$nameFile,$userInfo['id']);
                        setcookie('cooldown', true, time() + 3, '/');
                        $data['msg'] .= '<div class="ticket-message flex-center-notresize direction-row w100 direction-row" style="justify-content:flex-start; flex-wrap:nowrap; align-items:flex-start;">
                                            <div class="flex-center direction-column"> 
                                                <img class="avatarPhoto" src='.BASE."data/images/upload/".Site::getImageUser($userInfo['id']).'>
                                            </div>
                                            <div class="flex-center-notresize" style="justify-content:flex-start; margin-left:10px;">
                                                <div class="w100 flex-center-notresize" style="justify-content: flex-start;">
                                                <p>'.$userInfo['username'].'</p>
                                                    <span class="time flex-center-notresize">'.Chat::returnFormatedDate().'</span>
                                                </div>
                                                <div class="flex-center">
                                                    <span class="content"><img src="'.BASE.'data/users/msg/'.$nameFile.'"></span>
                                                </div>
                                            </div>
                                        </div>';
                    }else{
                        $data['msg'] = Chat::messageDeadbear('erro','Aconteceu algum erro, contate a administração');
                    }
                }else{
                    $data['alert'] = 'Formato ou tamanho da Imagem inválido, não pode ser maior que 2000 pixeis.';
                }
            }
            
        }
        else if($action == 'enviarMensagem')
        {
            $cnt1 = @strip_tags($_POST['msg']);
            if($cnt1 == ''){
                $data['sucesso'] = false;
                $data['msg'] = 'A mensagem precisa conter algo.';
            }
            if($data['sucesso']){
                $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
                $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `creator_id` = ? AND `closed` = 0");
                $sql->execute(array($userid));
                $infoTicket = $sql->fetch();
                if($infoTicket['status'] == 'pausado'){
                    $data['sucesso'] = false;

                    $data['msg'] = Chat::messageDeadbear('congelado','Seu ticket foi congelado, não é possível enviar mensagens.');
                }else if($infoTicket['status'] == 'aberto'){
                    $ticketid = $infoTicket['id'];
                    $user = Site::getUserInfo($_COOKIE['loginToken']);
                    $userid = $user['id']; 
                    $username = $user['username'];
                    if(!isset($_COOKIE['cooldown'])){
                        $_SESSION['lastId'] = Chat::insertMessage($ticketid,'texto',$cnt1,$userid);
                        setcookie('cooldown', true, time() + 3, '/');
                        $data['msg'] .= '<div class="ticket-message flex-center-notresize direction-row w100 direction-row" style="justify-content:flex-start; flex-wrap:nowrap; align-items:flex-start;">
                                            <div class="flex-center direction-column"> 
                                                <img class="avatarPhoto" src='.BASE."data/images/upload/".Site::getImageUser($userid).'>
                                            </div>
                                            <div class="flex-center-notresize" style="justify-content:flex-start; margin-left:10px;">
                                                <div class="w100 flex-center-notresize" style="justify-content: flex-start;">
                                                <p>'.$username.'</p>
                                                    <span class="time flex-center-notresize">'.Chat::returnFormatedDate().'</span>
                                                </div>
                                                <div class="flex-center">
                                                    <span class="content">'.$cnt1.'</span>
                                                </div>
                                            </div>
                                        </div>';
                    }else{
                        $data['cooldown'] = true;
                    }
                }else{
                    $data['msg'] = "<div class='ticket-message'>
                                    <img class='avatarPhoto' src='".BASE."data/images/logo.webp'>
                                    <p>deadbear</p>
                                    <span>Seu ticket foi fechado, não é possível enviar mensagens.</span>
                                    <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate()."</span>
                                </div>";
                    $data['sucesso'] = false;
                }
            }else{
                    $data['msg'] = "<div class='ticket-message'>
                                    <img class='avatarPhoto' src='".BASE."data/images/logo.webp'>
                                    <p>deadbear</p>
                                    <span>Algum erro aconteceu, contate a administração.</span>
                                    <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate()."</span>
                                </div>";
                $data['sucesso'] = false;
            }
        }
        else if($action == 'recuperarMensagem')
        {
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $ticketid = Site::getInfoDB('tickets',"`creator_id` = '$userid' AND `closed` = 0")['id'];

            $lastId = $_SESSION['lastId'];
            if(Site::getRowCountDB('tickets.msg',"`ticket_id` = $ticketid AND `id` > $lastId") >= 1){
                $data['haveMessages'] = true;
                $mensagens = Site::getInfoDBAll('tickets.msg',"`ticket_id` = $ticketid AND `id` > $lastId");
                foreach($mensagens as $key => $value){
                    $conteudo = $value['type'] == 'texto' ? "<span>".$value['conteudo']."</span>" : "<span><img class='clickopen' src='".BASE.'data/users/msg/'.$value['conteudo']."'></span>";
                    if(Site::getRowCountDB('usuarios','`id`='.$value['user_id']) <= 0){ // mensagem de admin
                        $user = Site::getInfoDB('admin','`id`='.$value['user_id']);
                        $data['msg'] .= "<div class='ticket-message'>
                        <img class='avatarPhoto' src='".Admin::getProfilePhotoId($user['id'])."'>
                        <p>".$user['username']." <b style='font-weight: 100;font-size: 15px;color: #ff1744;'>VENDEDOR</b></p>
                        ".$conteudo."
                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate($value['data-criacao'])."</span>
                        </div> ⠀";
                    }else{
                        $user = Site::getInfoDB('usuarios','`id`='.$value['user_id']);
                        $data['msg'] .= "<div class='ticket-message'>
                        <img class='avatarPhoto' src='".BASE."data/images/upload/".Site::getImageUser($user['id'])."'>
                        <p>".$user['username']."</p>
                        ".$conteudo."
                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate($value['data-criacao'])."</span>
                        </div> ⠀";
                    }
                    $_SESSION['lastId'] = $value['id']; 
                }
            }else{
                $data['haveMessages'] = false;
                $data['sucesso'] = false;
            }
        }
        else if($action == 'checkTicket'){
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            if(Site::getRowCountDB('tickets','`creator_id` = "'.$userid.'" AND `closed` = 0') >= 1){
                $ticket = Site::getInfoDB('tickets','`creator_id` = "'.$userid.'" AND `closed` = 0');
                if($ticket['reivindicado'] == 0){
                    $data['reivindicado'] = false;
                }else{
                    $data['reivindicado'] = true;
                    $statususer = Admin::isOnline($ticket['reivindicado_id']) ? '<div style="top:70%;left:46%;position:absolute;" title="Online agora" class="userOnline ticket"></div>' : '';
                    $data['infAtendido'] = '
                    <div class="admin flex-center" title="Administrador">
                        <div class="img">
                            <img src="'.Admin::getProfilePhotoId($ticket['reivindicado_id']).'">
                                <div class="status">
                                    '.$statususer.'
                                </div>
                        </div>
                        <span>'.Admin::getUserInfoId($ticket['reivindicado_id'])['username'].'</span>
                    </div>';
                }
            }
            
        }else if($action == 'botmessage'){
            $msg = $_POST['msg'];
            $data['msg'] = Chat::messageDeadbear('message',$msg);
        }
    }else if($type == 'admin')
    {
        if(!Admin::logado()){
            $data['msg'] = 'Percebemos que você ainda não fez login, redirecionando.';
            $data['redirect'] = ADMIN;
            die(json_encode($data));
        }
        if($action == 'getMessages')
        {
            $ticketid = $_POST['id'];
            $infoTicket = Site::getInfoDB('tickets','`id`='.$ticketid);

            $infoUser = Site::getInfoDB('usuarios','`id`="'.$infoTicket['creator_id'].'"');
            $statususer = Usuario::isOnline($infoUser['id']) ? '<div style="top:70%;left:46%;position:absolute;" title="Online agora" class="userOnline ticket"></div>' : '';
            $data['infoUser'] = '<div class="img" style="position:relative;">
                                    <img src="'.BASE.'data/images/upload/'.Site::getImageUser($infoUser['id']).'">
                                    <div class="status">'.$statususer.'</div>
                                </div>
                                    <span>'.ucfirst($infoUser['username']).'</span>
                                ';
            if(Site::getRowCountDB('tickets.msg','`ticket_id`='.$ticketid) >= 1){
                $data['haveMessages'] = true;
                $mensagens = Site::getInfoDBAll('tickets.msg','`ticket_id`='.$ticketid);
                foreach($mensagens as $key => $value){
                    if($value['type'] == 'texto'){
                        $conteudo = "<span>".$value['conteudo']."</span>";
                    }else{
                        $conteudo = "<span><img class='clickopen' src='".BASE.'data/users/msg/'.$value['conteudo']."'></span>";
                    }
                    $username = MySql::conectar()->prepare("SELECT * FROM `usuarios` WHERE `id` = ?"); 
                    $username->execute(array($value['user_id']));
                    if($username->rowCount() <= 0 ){
                        $username = MySql::conectar()->prepare("SELECT * FROM `admin` WHERE `id` = ?"); 
                        $username->execute(array($value['user_id']));
                        $user = $username->fetch();
                        $data['msg'] .= "<div class='msg'>
                        <img class='avatarPhoto' src='".Admin::getProfilePhotoId($user['id'])."'>
                        <p>".$user['username']." <b style='font-weight: 100;font-size: 15px;color: #ff1744;'>VENDEDOR</b></p>
                        ".$conteudo."
                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate($value['data-criacao'])."</span>
                        </div> ⠀";
                    }else{
                        $user = $username->fetch();
                        $data['msg'] .= "<div class='msg'>
                        <img class='avatarPhoto' src='".BASE."data/images/upload/".Site::getImageUser($user['id'])."'>
                        <p>".$user['username']."</p>
                        ".$conteudo."
                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate($value['data-criacao'])."</span>
                        </div> ⠀";
                    }
                    $_SESSION['lastId_'.$ticketid] = $value['id']; 
                }
            }else{
                //não tem msg ou nao existe :/
                $data['sucesso'] = false;
                $sql = MySql::conectar()->prepare("SELECT * FROM `tickets.msg` ORDER BY `id` DESC LIMIT 1"); $sql->execute();
                if($sql->rowCount() >= 1){
                    $info = $sql->fetch();
                    $_SESSION['lastId_'.$ticketid] = $info['id']; 
                }else{
                    $_SESSION['lastId_'.$ticketid] = 0; 
                }
            }
        }else if($action == 'banirUsuario'){
            $idticket = $_POST['ticketid'];
            $motivo = $_POST['motivo'] == '' ? 'Não especificado' : 'Não especificado';
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?");
            $sql->execute(array($id));
            $iduser = $sql->fetch()['creator_id'];
            if(Usuario::banFromId($id, $motivo)){
                $data['msg'] = 'Usuário banido com sucesso.';
            }else{
                $data['sucesso'] = false;
                $data['msg'] = 'Aconteceu algum erro.';
            }
        }else if($action == 'recuperarTickets'){
            $lastTicket = $_SESSION['lastIdTickets'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `status` = 'aberto' AND `id` > ?");
            $sql->execute(array($lastTicket));
            if($sql->rowCount() >= 1){
                $data['haveTicket'] = true;
                $tickets = $sql->fetchAll();
                foreach($tickets as $key => $value){
                    if($value['reivindicado'] == false){
                        $data['tickets'] .= '<div class="box-chatSingleWrapper" >
                                <div class="box-chatSingle" id="'.$value['id'].'">
                                    <div class="head">
                                        <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                        <img src="'.BASE.'data/images/upload/'.Site::getImageUser(Site::getUserInfo($value['creator_id'])['id']).'">
                                    </div>
                                    <div class="body">
                                        <button class="reivindicarTicket"> <i class="far fa-hand-paper"></i> Reivindicar</button>
                                    </div>
                                </div>
                            </div> ⠀';
                    }else{
                        $data['tickets'] .= '<div class="box-chatSingleWrapper" >
                                <div class="box-chatSingle" id="'.$value['id'].'">
                                    <div class="head">
                                        <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                        <img src="'.BASE.'data/images/upload/'.Site::getImageUser(Site::getUserInfo($value['creator_id'])['id']).'">
                                    </div>
                                    <div class="body">
                                        <button class="chat-vis-btn"><i class="fas fa-eye"></i> Visualizar</button>
                                    </div>
                                </div>
                            </div> ⠀';
                    }
                    $_SESSION['lastIdTickets'] = $value['id']; 
                }
            }else{
                $data['haveTicket'] = false;
            }
            
        }
        else if($action == 'enviarFoto'){
            $id = $_POST['id'];
            $img = $_FILES['image'];
            $userInfo = Admin::getUserInfo($_COOKIE['admin_loginToken']);
            $infoTicket = Site::getInfoDB('tickets','`id`='.$id); $ticketid = $infoTicket['id'];
            if(\Models\ChatModels::ImagemValida($img)){
                if($nameFile = \Models\ChatModels::uploadImage($img)){
                    $_SESSION['lastId_'.$ticketid] = Chat::insertMessage($infoTicket['id'],'imagem',$nameFile,$userInfo['id']);

                    $data['msg'] = "<div class='msg'>
                                        <img class='avatarPhoto' src='".Admin::getProfilePhotoId($userInfo['id'])."'>
                                        <p>".$userInfo['username']."</p>
                                        <span><img src='".BASE.'data/users/msg/'.$nameFile."'></span>
                                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate()."</span>
                                    </div>";
                }else{
                    $data['msg'] = "<div class='msg'>
                                        <img class='avatarPhoto' src='".BASE."data/images/logo.webp'>
                                        <p>deadbear</p>
                                        <span>Aconteceu algum erro, contate administração.</span>
                                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate()."</span>
                                    </div>";
                }
            }else{
                $data['alert'] = 'Formato ou tamanho da Imagem inválido, não pode ser maior que 2000 pixeis.';
            }
        }else if($action == 'getOrder'){
            $ticketid = $_POST['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?");
            $sql->execute(array($ticketid));
            $pedidoid = $sql->fetch()['pedido.id'];
            //
            $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `id` = ?");
            $sql->execute(array($pedidoid));
            $cartsid = explode(',',$sql->fetch()['carts.id']);
            foreach($cartsid as $key => $val){
                $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `id` = ?");
                $sql->execute(array($val));
                if($sql->rowCount() >= 1){
                    $cartInfo = $sql->fetch();
                    if($cartInfo['cupom.preco.desconto']==null){$valor = ($cartInfo['valor.un'] * $cartInfo['quantidade']);}else{$valor = ($cartInfo['cupom.preco.desconto']* $cartInfo['quantidade']);}
                    $valor = str_replace('.',',',$valor);
                    $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?");
                    $sql->execute(array($cartInfo['id.item']));
                    $info = $sql->fetch();
                    $data['msg'] .= '
                                    <li><div class="item">
                                        <div class="w25"><img src="'.BASE.'data/images/upload/'.Site::getImageAnuncio($info['id']).'"></div>
                                        <div class="w25"><span>'.$info['nome'].'</span></div>
                                        <div class="w25"><span>'.$cartInfo['quantidade'].'x</span></div>
                                        <div class="w25"><span>R$'.$valor.'</span></div>
                                    </div></li> ⠀';
                }else{
                    $data['sucesso'] = false;
                }
            }
        }else if($action == 'enviarMensagem')
        {
            $cnt1 = @strip_tags($_POST['msg']); 
            $cnt1 .= ' ';
            if($cnt1 == '' || strlen($cnt1) <= 0){
                $data['sucesso'] = false;
                $data['msg'] = 'A mensagem precisa conter algo.';
            }
            if($data['sucesso']){
                //ja sabe o type da MSG
                $ticketid = $_POST['id'];
                //getinfo user
                $user = Admin::getUserInfo($_COOKIE['admin_loginToken']);
                $userid = $user['id']; $username = $user['username'];
                $msg = explode(' ',$cnt1);
                $msgs = '';
                for($i=0;$i < count($msg); $i++){
                    if(filter_var($msg[$i], FILTER_VALIDATE_URL)){
                        $msgs .= ' <a target="_blank" rel="noreferrer" href="'.$msg[$i].'">'.$msg[$i].'</a>';
                    }else{
                        $msgs .= ' '.$msg[$i];
                    }
                }
                if($data['sucesso']){
                    $_SESSION['lastId_'.$ticketid] = Chat::insertMessage($ticketid,'texto',$cnt1,$userid); 

                    $data['msg'] = "<div class='msg'>
                                        <img class='avatarPhoto' src='".Admin::getProfilePhotoId($user['id'])."'>
                                        <p>".$username." <b style='font-size: 15px;color: #ff1744;'>VENDEDOR</b></p>
                                        <span>".$msgs."</span>
                                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate()."</span>
                                    </div>";
                }else{
                    $data['msg'] = "<div class='ticket-message'>
                                        <img class='avatarPhoto' src='".BASE."data/images/logo.webp'>
                                        <p>deadbear</p>
                                        <span>Algum erro aconteceu, contate <b>@matthewbugado</b>.</span>
                                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate()."</span>
                                    </div>";
                    $data['sucesso'] = false;
                }
            }else{
                $data['msg'] = "<div class='ticket-message'>
                                        <img class='avatarPhoto' src='".BASE."data/images/logo.webp'>
                                        <p>deadbear</p>
                                        <span>Algum erro aconteceu, contate <b>@matthewbugado</b>.</span>
                                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate()."</span>
                                    </div>";
                $data['sucesso'] = false;
            }
        }else if($action == 'recuperarMensagem')
        {
            $ticketid = $_POST['id'];
            $lastId = @$_SESSION['lastId_'.$ticketid];
            $penis = MySql::conectar()->prepare("SELECT * FROM `tickets.msg`WHERE `ticket_id` = ? AND `id` > ?");
            $penis->execute(array($ticketid,$lastId));
            if(Site::getRowCountDB('tickets.msg'," `ticket_id` = $ticketid AND `id` > $lastId") >= 1){
                $data['haveMessages'] = true;
                $mensagens = Site::getInfoDBAll('tickets.msg'," `ticket_id` = $ticketid AND `id` > $lastId");
                foreach($mensagens as $key => $value){
                    $conteudo = $value['type'] == 'texto' ? "<span>".$value['conteudo']."</span>" : "<span><img class='clickopen' src='".BASE.'data/users/msg/'.$value['conteudo']."'></span>";
                    if(Site::getRowCountDB('usuarios','`id`="'.$value['user_id'].'"') <= 0 ){
                        $user = Site::getInfoDB('admin','`id`='.$value['user_id']);
                        $data['msg'] .= "<div class='msg'>
                        <img class='avatarPhoto' src='".Admin::getProfilePhotoId($user['id'])."'>
                        <p>".$user['username']."<b style='font-size: 15px;color: #ff1744;'>VENDEDOR</b></p>
                        ".$conteudo."
                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate($value['data-criacao'])."</span>
                        </div> ⠀";
                    }else{
                        $user = Site::getInfoDB('usuarios','`id`="'.$value['user_id'].'"');
                        $data['msg'] .= "<div class='msg'>
                        <img class='avatarPhoto' src='".BASE."data/images/upload/".Site::getImageUser($user['id'])."'>
                        <p>".$user['username']."</p>
                        ".$conteudo."
                        <span><i class='far fa-clock'></i> ".Chat::returnFormatedDate($value['data-criacao'])."</span>
                        </div> ⠀";
                    }
                    $_SESSION['lastId_'.$ticketid] = $value['id']; 
                }
            }else{
                $data['haveMessages'] = false;
                $data['sucesso'] = false;
            }  
        }else if($action == 'fecharPedido'){
            $id = $_POST['id'];
            $sql = MySql::conectar()->prepare("UPDATE `tickets` SET `closed` = 1 WHERE `id` = ?");
            if($sql->execute(array($id))){
                $data['msg'] = 'Ticket fechado com sucesso';
            }else{
                $data['msg'] = 'Aconteceu algum erro.';
                $data['sucesso'] = false;
            }
        }else if($action == 'abrirPedido'){
            $id = $_POST['id'];
            $sql = MySql::conectar()->prepare("UPDATE `tickets` SET `closed` = 0 WHERE `id` = ?");
            if($sql->execute(array($id))){
                $data['msg'] = 'Ticket aberto com sucesso';
            }else{
                $data['msg'] = 'Aconteceu algum erro.';
                $data['sucesso'] = false;
            }
        }else if($action == 'congelarPedido'){
            $id = $_POST['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?");
            $sql->execute(array($id));
            $status = $sql->fetch()['status'];
            if($status = 'pausado'){
                $data['status'] = 'pausado';
                $sql = MySql::conectar()->prepare("UPDATE `tickets` SET `status` = 'aberto' WHERE `id` = ?");
                if($sql->execute(array($id))){
                    $data['msg'] = 'Ticket descongelado com sucesso';
                }else{
                    $data['msg'] = 'Aconteceu algum erro.';
                    $data['sucesso'] = false;
                }
            }else{
                $data['status'] = 'aberto';
                $sql = MySql::conectar()->prepare("UPDATE `tickets` SET `status` = 'pausado' WHERE `id` = ?");
                if($sql->execute(array($id))){
                    $data['msg'] = 'Ticket congelado com sucesso';
                }else{
                    $data['msg'] = 'Aconteceu algum erro.';
                    $data['sucesso'] = false;
                }
            }
        }
    }
    die(json_encode($data));
}else{
    //SAFADINHO TENTANDO ENRTAR PELO LINK DIRETO RS KKKKKK SE FYUDE MLK FDP DO CARALHO
    die(Site::redirecionar(BASE));
}

?>