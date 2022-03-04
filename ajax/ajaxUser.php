<?php

    use Models\RegisterModels;
    session_start();
    $action = $_POST['type-action'];
    include('../Classes/Site.php');
    require('../vendor/autoload.php');
    error_reporting(E_ALL ^ E_NOTICE);

    $token = @$_POST['token'];
    if(!\Site::validar_token($token)){
        $data['msg'] = 'Aconteceu algum erro, contate administração';
        $data['sucesso'] = false;
        die(json_encode($data));
    }
    if(isset($action)){
        $data['sucesso'] = true;
        $data['msg'] = '';
        $data['redirect'] = '';
        include('../Models/RegisterModels.php');
        include('../Classes/MySql.php');
        include('../config.php');
        include('../Classes/Email.php');
        if($action == 'registrar'){
            $token = md5(uniqid());
            $username = preg_replace("/[^A-Za-z0-9.!? ]/","",strip_tags($_POST['username']));
            $password = md5($_POST['password']);
            $email = strip_tags($_POST['email']);
            if(voku\helper\EmailCheck::isValid($email)){
                if(\Models\RegisterModels::sessionToken($token,$email,$username,$password)){
                    setcookie('tokenSessionReg',$token,time() + (60*60*6), '/');
                    $data['msg'] = 'Sua conta foi criada com sucesso, aguarde!';
                    $data['redirect'] = BASE.'registrar/verificar-email/';
                }else{
                    $data['sucesso'] = false;
                    $data['msg'] = 'E-mail, usuário ou senha inválidos ou já usados.';
                }
            }else{
                $data['sucesso'] = false;
                $data['msg'] = 'Seu e-mail não é válido, veja se digitou-o corretamente ou tente utilizar outro.';
            }
        }else if($action == 'reenviarCode'){
            $info = \Models\RegisterModels::reenviarEmail($_COOKIE['tokenSessionReg']);
            echo $info;
            if(!$info){
                $data['msg'] = 'Aconteceu algum erro ao validar o registro da sua conta, tente novamente!';
                $data['sucesso'] = false; 
            }
            else if($info){
                $data['msg'] = 'Seu código de confirmação foi reenviado, aguarde 5 minutos para reenviar novamente!'; 
            }
            else if($info == 'colldown'){
                $data['msg'] = 'Você deve esperar 5 minutos para pedir pra reenviar seu e-mail novamente!'; 
                $data['sucesso'] = false; 
            }
        }else if($action == 'confirmationEmail'){
            $codeconfirmation = trim(strip_tags($_POST['code']));
            $token = $_COOKIE['tokenSessionReg'];
            if(Site::getRowCountDB('sessionregister.token','`token` = "'.$token.'" AND `status` = 1') <= 0){
                $data['msg'] = 'Aconteceu algum erro ao validar o registro da sua conta, tente novamente!';
                $data['sucesso'] = false; 
                die(json_encode($data));
            }
            $info = Site::getInfoDB('sessionregister.token','`token` = "'.$token.'" AND `status` = 1');
            if($codeconfirmation == $info['code']){
                $data['msg'] = 'Sua conta foi verificada com sucesso!';
                \Models\RegisterModels::registerUser($info['username'],$info['password'],$info['email']);
                $data['redirect'] = BASE.'login/';
            }else{
                $data['msg'] = 'Código inválido, talvez esse código já tenha sido utilizado.';
                $data['sucesso'] = false; 
            }
        }
        else if($action == 'updateActivity'){
            if(!Site::logado()){
                die(json_encode($data));
            }
            $userInfo = Site::getUserInfo($_COOKIE['loginToken']);

            if(Site::getRowCountDB("usuarios.online", "`id.usuario`='".$userInfo['id']."'") == 0){
                $sql = MySql::conectar()->prepare("INSERT INTO `usuarios.online` VALUES(null,?,?)");
                $sql->execute(array($userInfo['id'], date('Y-m-d H:i:s')));
            }else{
                $sql = MySql::conectar()->prepare("UPDATE `usuarios.online` SET `lastactivity` = ? WHERE `id.usuario` = ?");
                $sql->execute(array(date('Y-m-d H:i:s'), $userInfo['id']));
            }
        }else if($action == 'login'){
            $user = trim($_POST['username']);
            $password = md5($_POST['password']);
            $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios` WHERE `username` = ? AND `password` = ?");
            $sql->execute(array($user,$password));
            $info = $sql->fetch();
            if($sql->rowCount() == 1){
                if(RegisterModels::successLogin($info['id'])){
                    \Models\RegisterModels::notificarLogin();
                    $data['msg'] = 'Usuário e senha encontrado, aguarde.';
                    $data['redirect'] = BASE;
                }else{
                    $data['msg'] = 'Aconteceu algum erro, contate administração';
                    $data['sucesso'] = false;
                    die(json_encode($data)); 
                }
            }else{
                $data['msg'] = 'Usuário ou senha inválido.';
                $data['sucesso'] = false;
            }
        }else if($action == 'resetPassword'){
            $email = trim($_POST['email']);
            if(!\voku\helper\EmailCheck::isValid($email)){
                $data['msg'] = 'Insira um e-mail válido.';
                $data['sucesso'] = false;
                die(json_encode($data));
            }
            if(!\Models\RegisterModels::verifyHasRequestResetPass($email)){
                $info = Site::getRowCountDB('usuarios','`email`="'.$email.'"');
                if($info == 1){
                    $code = \Models\RegisterModels::generateRandomString();
                    \Models\RegisterModels::insertDBResetPass($code,$email,date('Y-m-d H:i:s'));
                    \Models\RegisterModels::resetPassword($email,$code);
                }
                $data['msg'] = 'Se existir uma conta registrada com o email '.$email.', enviámos instruções sobre como redefinir a sua senha.';
            }else{
                $data['msg'] = 'Você já solicitou um código para alteração da senha recentemente, aguarde para pedir denovo!';
            }
        }else if($action == 'resetPassCode'){
            $code = trim($_POST['code']);
            $info = Site::getInfoDB('resetpassword.token','`token`='.$code);

            if(Site::getRowCountDB('resetpassword.token','`token`='.$code) == 1){
                //code existe
                if(strtotime(date('Y-m-d H:i:s', strtotime('+3 days')) <= strtotime($info['data-criacao']))){
                    $data['msg'] = 'Código inválido ou inexistente.'; 
                    $data['sucesso'] = false;
                }else{
                    if($info['status'] == '1'){
                        $data['msg'] = 'Esse código já foi utilizado.';
                        $data['sucesso'] = false;
                    }else{
                        \Models\RegisterModels::generateTokenResetPass($info['email']);
                        $data['msg'] = 'Código validado, aguarde enquanto te redirecionamos.';
                        $data['redirect'] = BASE.'login/resetar-senha?certifiedToken='.md5(uniqid());
                    }              
                }  
            }else{
                $data['msg'] = 'Código inválido ou inexistente.';
                $data['sucesso'] = false;
            }
        }else if($action == 'changePass'){
            if(!isset($_COOKIE['tokenIDReset'])){
                $data['msg'] = 'Aconteceu algum erro, tente novamemente alterar sua senha.';
                $data['sucesso'] = false;
                die(json_encode($data));
            }
            $email = Site::getInfoDB('resetpassword.cookietoken','`tokenID`='.$_COOKIE['tokenIDReset'])['email'];
            $senha = md5($_POST['password']);
            if(\Models\RegisterModels::changePassword($email,$senha)){
                $data['msg'] = 'Senha alterada com sucesso!';
                $data['redirect'] = BASE.'login';
            }else{
                $data['msg'] = 'Aconteceu algum erro, contate administração!';
                $data['sucesso'] = false;
             }
        }else if($action == 'getInfoOrder'){
            $ticketid = $_POST['id'];
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ? AND `creator_id` = ?");
            $sql->execute(array($ticketid,$userid));
            if($sql->rowCount() >= 1){
                $infoTicket = $sql->fetch();
                $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.garantia` WHERE `ticket.id` = ?");
                $sql->execute(array($ticketid));
                if($sql->rowCount() >= 1){
                    $garantiaInfo = $sql->fetch();
                    $data['garantia'] = ' <div class="garantia">
                                            <p>Status da sua garantia</p>
                                            <div class="status statusgarantia"><span class="aberto">Em andamento</span></div>
                                            <div class="timerest"><p><b> A garantia termina:</b></p> 
                                            <span>'.date('H:i d/m/Y', strtotime($garantiaInfo['datafinal'])).'</span></div>
                                        </div>';
                }else{
                    $data['garantia'] = '<span class="pausado">Sua garantia ainda não foi iniciada<span>';
                }
                
                
                if($infoTicket['status'] == 'aberto'){
                    $data['statusticket'] = ' <p>Status do ticket</p>
                                            <div class="status statusticket"><span class="finalizado">Aberto</span></div>
                                            <a href="'.BASE.'chat"><button class="btn-hover">Entrar no chat</button></a>';
                }else if($infoTicket['status'] == 'fechado'){
                    $data['statusticket'] = '<p>Status do ticket</p>
                    <div class="status statusticket"><span class="finalizado">Finalizado</span></div>
                    <span>Seu ticket de atendimento foi finalizado, faça outro pedido para atendimento.</span>';
                }else if($infoTicket['status'] == 'pausado'){
                    $data['statusticket'] = '<p>Status do ticket</p>
                    <div class="status statusticket"><span class="pausado">Pausado</span></div>
                    <span>Seu ticket foi congelado, aguarde e ele será descongelado.</span>
                ';
                }
                //$data['timerest']
                $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `ticket.id` = ?");
                $sql->execute(array($ticketid));
                if($sql->rowCount() >= 1){
                    $feedback = $sql->fetch(); 
                    $text = $feedback['text'];
                    $feedback = $feedback['stars'];
                    if($feedback == 1){
                        $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                    }else if($feedback == 2){
                        $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                    }else if($feedback == 3){
                        $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star die"></i><i class="fas fa-star die"></i></div>';
                    }else if($feedback == 4){
                        $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star die"></i></div>';
                    }else if($feedback == 5){
                        $feedback =  '<div class="stars" style="width: unset;height: unset;border: none;"><i class="fas fa-star"></i><i class="fas fa-star"></i></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>';
                    }
                    $ins = '"<span>'.substr($text,0, 15).'..</span>"'.$feedback;
                }else{
                    $ins = '<span>Você ainda não avaliou o atendimento</span> <button class="jsAvaliar">Avaliar</button>';

                }
                $sql = MySql::conectar()->prepare("SELECT * FROM `admin` WHERE `id` = ?"); $sql->execute(array($infoTicket['reivindicado_id'])); $infoAdmin = $sql->fetch();
                $data['feedback'] = '<p>Seu feedback para o atendimento de <b>'.$infoAdmin['username'].'</b></p>
                    <div class="cabesona">
                        <img src="'.BASE.'data/images/mtw.png">
                    </div>
                    <div class="body">
                        '.$ins.'
                    </div>';
            }else{
                $data['sucesso'] = false;
                $data['msg'] = 'Você não tem acesso a esse pedido';
            }
            
        }else if($action == 'changeImage'){
            include('../Models/ContaModels.php');
            require('../vendor/autoload.php');
            $img = $_FILES['file'];
            if(\Models\ContaModels::validarProfileImage($img)){
                if($return = \Models\ContaModels::uploadProfileImage($img)){
                    $data['msg'] = 'Foto de perfil atualizada com sucesso';
                    $data['redirect'] = BASE.'minhaconta';
                    $data['newImage'] = BASE.'data/images/upload/'.$return.'.webp';
                }else{
                    $data['sucesso'] = false;
                    $data['msg'] = 'Imagem inválida tente utilizar outra.';
                }
            }else{
                $data['sucesso'] = false;
                $data['msg'] = 'A imagem tem um formato inválido ou é menor que 500 pixeis.';
            }
        }
        die(json_encode($data));
    }else{
        include('../Classes/Site.php');
        Site::redirecionar(BASE.'?error');
    }
?>