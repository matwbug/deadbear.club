<?php

use Models\AdminModels;
use Models\ProductsDefault;

session_start();
$action = $_POST['type-action'];
error_reporting(E_ALL ^ E_NOTICE);

$token = $_POST['token'];
$data['redirect'] = '';

/*

if(!\Admin::validar_token($token)){
    $data['msg'] = 'Aconteceu algum erro, contate administração';
    $data['sucesso'] = false;
    die(json_encode($data));
}*/

if(isset($action)){
    $data['sucesso'] = true;
    $data['msg'] = '';
    require('../vendor/autoload.php');
    include('../Classes/MySql.php');
    include('../config.php');
    include('../Classes/Admin.php');
    include('../Classes/Site.php');
    include('../Models/ProductsDefault.php');
    include('../Classes/Usuario.php');
    if($action == 'login'){
        $user = trim($_POST['username']);
        $password = trim($_POST['password']);
        if(Admin::validar_token($token)){
            $sql = MySql::conectar()->prepare("SELECT * FROM `admin` WHERE `username` = ? AND `password` = ?");
            $sql->execute(array($user,$password));
            if($sql->rowCount() == 1){
                $token = uniqid();
                $info = $sql->fetch();
                $sql = MySql::conectar()->prepare('INSERT INTO `sessionadminlogin.token` VALUES(null,?,?,?)');
                if($sql->execute(array($token,Admin::getIpUser(),$info['id']))){
                    setcookie('admin_loginToken',$token,time() + (60*60*24*7*30), '/');
                    $data['msg'] = 'Login aprovado, seja bem vindo '.$user;
                    $data['redirect'] = BASE.'dashboard';
                }else{
                    $data['msg'] = 'Algum erro aconteceu, contate a administração.';
                    $data['sucesso'] = false;
                }
            }else{
                $data['msg'] = 'Usuário ou senha inexistente.';
                $data['sucesso'] = false;
            }
        }else{
            $data['sucesso'] = false;
            $data['msg'] = 'Algum erro aconteceu, contate a administração.';
        }
        die(json_encode($data));
    }
    if(!Admin::logado()){
        $data['msg'] = 'Percebemos que você ainda não fez login, redirecionando.';
        $data['redirect'] = ADMIN;
        die(json_encode($data));
    }
    if($action == 'changeImage'){
        include('../dashboard/Models/PerfilModels.php');
        $img = $_FILES['file'];
        if(\Models\PerfilModels::validarProfileImage($img)){
            if($return = \Models\PerfilModels::uploadProfileImage($img)){
                $data['msg'] = 'Foto de perfil atualizada com sucesso';
                $data['newImage'] = BASE.'data/images/upload/'.$return.'.webp';
            }else{
                $data['sucesso'] = false;
                $data['msg'] = 'Imagem inválida tente utilizar outra.';
            }
        }else{
            $data['sucesso'] = false;
            $data['msg'] = 'A imagem tem um formato inválido ou é menor que 500 pixeis.';
        }
    }else if($action == 'getAnuncios'){
        $data['msg'] = '<div class="cat-actions flex-center" style="flex-direction:row; width:100%;">
                            <button class="add-anuncio">Adicionar anúncios</button>
                            <button js="add-categorias">Adicionar categorias</button>
                            <button js="man-categorias">Gerenciar categorias</button>
                            <button js="an-desativados">Anúncios desativados <b style="margin-left:3px;">OFF</b></button>
                        </div>';
        $itensporpagina = isset($_POST['porpagina']) ? trim(strip_tags($_POST['porpagina'])) : 4;
        $pagina = isset($_POST['page']) ? trim(strip_tags($_POST['page'])) : 1;
        $startsfrom = ($pagina - 1) * $itensporpagina;
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `status` = 1 ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom");
        $sql->execute(); $quantAnuncios = $sql->rowCount();
        if($quantAnuncios >= 1){
            $anuncios = $sql->fetchAll();
            foreach($anuncios as $key => $value){
                $photo = \Models\ProductsDefault::getImageProductFromID($value['id']) == null ? 'nada.webp' : 'upload/'.\Models\ProductsDefault::getImageProductFromID($value['id'])[0]['name'];
                $data['msg'] .= '
                                <div class="single-an" id="'.$value['id'].'">
                                    <div class="body">
                                        <p>'.$value['nome'].'</p>
                                        <div class="img">
                                            <img src="'.BASE.'data/images/'.$photo.'">
                                        </div>
                                        <button class="edit-an"><i class="fas fa-edit"></i> Editar</button>
                                        <button class="remove-an"><i class="fas fa-trash"></i> Excluir</button>
                                    </div>
                                </div>
                                ';
            }
            $data['msg'] .= '</div>';

            $data['msg'] .= '<div class="paginator">';
            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `status` = 1"); $sql->execute(); $totalAn = $sql->rowCount();
            $totalPages = ceil($totalAn/$itensporpagina);
            if($totalPages != 1){
                for($i=0; $i < $totalPages; $i++){
                    if($i == ($pagina - 1)){
                        $data['msg'] .= '<button class="paginator-anuncios active" page="'.($i+1).'">'.($i+1).'</button>';
                    }else{
                        $data['msg'] .= '<button class="paginator-anuncios" page="'.($i+1).'">'.($i+1).'</button>';
                    }
                }
            }
        }else{
            $data['msg'] .= '<div class="w100 flex-center" style="background:#a58fdc; padding:10px;"> <span style="cursor:pointer;" onclick="tabAdicionarAnuncios();">Nenhum anúncio ativo, tente adicionar anúncios.</span></div>';
        }
        
    }else if($action == 'addAnuncio'){
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `status` = 1");
        $sql->execute();
        $cat = $sql->fetchAll();
        $categorias = '<select name="cat">';
        foreach($cat as $key => $value){
            $categorias .= '<option value="'.$value['id'].'">'.$value['nome'].'</option>';
        }
        $categorias .= '</select>';
        $data['msg'] .= '<div class="container-content tabopened flex-center" id="tabopened" style="flex-direction:column;">
                            <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                            <div class="add-an">
                                <div class="box">
                                    <h3>Adicionando um anúncio</h3>
                                    <div class="img">
                                        <img data-name="" src="'.BASE.'data/images/nada.webp">
                                        <label class="noimage">
                                                <i class="fas fa-file-upload"></i>
                                                <input onchange="changeImageAn();" style="visibility:hidden; opacity:0; display:none;" type="file" name="photo" accept="image/png, image/gif, image/jpeg">
                                        </label>
                                    </div>
                                </div>
                                <div class="box">
                                    <p>Categoria</p>
                                    '.$categorias.'
                                </div>
                                <div class="box">
                                    <p>Adicione um título</p>
                                    <div class="textar"><input type="text" name="title" maxlength="30"></div>
                                    <p>Adicione uma descrição</p>
                                    <div class="textar"><textarea id="editor" name="desc" maxlength="255"></textarea></div>
                                    <p>Adicione o complemento do produto</p>
                                    <div class="textar"><textarea id="editor" name="complement" maxlength="30"></textarea></div>
                                    <p>Valor</p>
                                    <div class="textar maskMoney"><input type="text" datamask=preco name="preco"></div>
                                </div>
                                <div class="box">
                                    <button type="submit" jsAction="confirm-AddAn" class="button-add">Adicionar <span class="svg-Send"></span></button>
                                </div>
                            </div>
                        </div>';
    }else if($action == 'addImage'){
        $img = $_FILES['file'];
        include('../dashboard/Models/AdminModels.php');
        if(\Models\AdminModels::validarImage($img)){
            if($return = \Models\AdminModels::uploadImage($img)){
                $data['msg'] = 'Imagem adicionada com sucesso.';
                $data['newImage'] = BASE.'data/images/upload/'.$return.'.webp';
            }else{
                $data['msg'] = 'Aconteceu algum erro.';
            }
        }else{
            $data['msg'] = 'Imagem de formato inválido ou inferior a 500 pixeis.';
        }
        
    }else if($action == 'editAnuncioAddImage'){
        $img = $_FILES['file'];
        $idanuncio = $_POST['id'];
        include('../dashboard/Models/AdminModels.php');
        if(\Models\AdminModels::validarImage($img)){
            if($return = \Models\AdminModels::uploadImage($img)){
                $sql = MySql::conectar()->prepare("INSERT INTO `produtos.imagens` VALUES (NULL,?,?)");
                if($sql->execute(array($idanuncio,$return.'.webp'))){
                    $data['msg'] = 'Imagem adicionada com sucesso.';
                    $data['newImage'] = BASE.'data/images/upload/'.$return.'.webp';
                }else{
                    $data['msg'] = 'Aconteceu algum erro.';
                    $data['sucesso'] = false;
                }
                
            }else{
                $data['msg'] = 'Aconteceu algum erro.';
                $data['sucesso'] = false;
            }
        }else{
            $data['msg'] = 'Imagem de formato inválido ou inferior a 500 pixeis.';
            $data['sucesso'] = false;
        }
        
    }else if($action == 'manageUsers'){
        $itensporpagina = isset($_POST['porpagina']) ? trim(strip_tags($_POST['porpagina'])) : 3;
        $pagina = isset($_POST['page']) ? trim(strip_tags($_POST['page'])) : 1;
        $startsfrom = ($pagina - 1) * $itensporpagina;
        $busca = @$_POST['search'] != 'false'  ? $_POST['search'] : false;

        $users = $busca ? Site::getInfoDBAll('usuarios', "`username` LIKE '%$busca%' OR `email` LIKE '%$busca%' ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom") : Site::getInfoDBAll('usuarios',"1=1 ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom");
        if($users){
            $query = $busca ? "`username` LIKE '%$busca%' OR `email` LIKE '%$busca%' LIMIT $itensporpagina OFFSET $startsfrom" : "1=1 ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom" ;
            if($busca){
                $data['msg'] .= '<div class="man-user">
                                <div class="search">
                                        <input autocomplete="off" type="text" name="search" placeholder="Procure pelo username ou e-mail">
                                        <button type="submit"><i class="fas fa-search"></i></button>
                                </div>
                                <div class="header">
                                    <span style="margin-left:2px"></span>
                                    <div><span>Username</span></div>
                                    <div><span>Tickets</span></div>
                                    <div><span>Referidos</span></div>
                                    <div><span>Criação</span></div>
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                                <div class="box-us">';
            }
            foreach($users as $key => $value){
                $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `creator_id` = ?"); $sql->execute(array($value['id']));
                $ticketab = $sql->rowCount();
                if(Usuario::checkHasUserBannedByID($value['id'])){
                    $data['msg'] .= '<div class="body banned" id='.$value['id'].'>
                        <div class="edit-actions"><i class="fas fa-ellipsis-v"></i></div>
                        <div><i class="fas fa-ban" aria-hidden="true"></i><img src="'.BASE.'data/images/upload/'.Site::getImageUser($value['id']).'"><span>'.substr($value['username'],0,8).'<span></div>
                        <div><span>'.$ticketab.'</span></div>
                        <div><span>2</span></div>
                        <div><span>'.date('d/m/Y', strtotime($value['data-criacao'])).'</span></div>
                        <div><button title="Desanir usuário" class="tab-ban-us"><i class="fas fa-unlock"></i></button></div>
                        <div><button title="Editar usuário" class="tab-edit-us"><i class="far fa-edit"></i></button></div>
                    </div>'; 
                }else{
                    $data['msg'] .= '<div class="body" id='.$value['id'].'>
                        <div class="edit-actions"><i class="fas fa-ellipsis-v"></i></div>
                        <div><img src="'.BASE.'data/images/upload/'.Site::getImageUser($value['id']).'"><span>'.substr($value['username'],0,8).'<span></div>
                        <div><span>'.$ticketab.'</span></div>
                        <div><span>2</span></div>
                        <div><span>'.date('d/m/Y', strtotime($value['data-criacao'])).'</span></div>
                        <div><button title="Banir usuário" class="tab-ban-us"><i class="fas fa-ban"></i></button></div>
                        <div><button title="Editar usuário" class="tab-edit-us"><i class="far fa-edit"></i></button></div>
                    </div>'; 
                }
            }

            if($busca){$data['msg'] .= '</div></div>';}

            $totalPages = ceil(Site::getRowCountDB('usuarios',$query)/$itensporpagina);
            if($totalPages != 1){
                $data['msg'] .= '<div class="paginator">';
                for($i=0; $i < $totalPages; $i++){
                    if($i == ($pagina - 1)){
                        $data['msg'] .= '<button class="paginator-users active" page="'.($i+1).'">'.($i+1).'</button>';
                    }else{
                        $data['msg'] .= '<button class="paginator-users" page="'.($i+1).'">'.($i+1).'</button>';
                    }
                }
                $data['msg'] .= '</div>';
            }
        }else{
            $data['msg'] = '<div class="w100 flex-center" style="padding:10px; background: #7c4dff;"><p>Não foi encontrado nenhum resultado</p></div>';
        }
        
    }else if($action == 'manageFeedback'){
        $itensporpagina = isset($_POST['porpagina']) ? trim(strip_tags($_POST['porpagina'])) : 3;
        $pagina = isset($_POST['page']) ? trim(strip_tags($_POST['page'])) : 1;
        $startsfrom = ($pagina - 1) * $itensporpagina;

        $data['response'] = '<div class="cat-actions flex-center" style="flex-direction:row; width:100%;">
                                    <button js="aprov-feedbacks">Feedbacks em análise</button>
                                    <button js="order-feedbacks">Configurar feedbacks <b style="margin-left:5px;"> OFF</b></button>
                                </div>
                                <div class="flex-center" style="align-items: unset;">';
        $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `aprovado` = 1 ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom"); $sql->execute();
        if($sql->rowCount() >= 1){
            $feedbacks = $sql->fetchAll();
            
            foreach($feedbacks as $key => $value){
                $infoUser = Site::getUserInfoByID($value['id.usuario']);
                $destacado = $value['destacado'] == 1 ? 'destacado': '';
                if($destacado == 'destacado'){
                    $buttondestacado = '<button class="destacar-feedback"><span><i class="far fa-star"></i> Tirar destaque</span></button>';
                }else{
                    $buttondestacado = '<button class="destacar-feedback"><span><i class="fas fa-star"></i> Destacar</span></button>';
                }
                $imagem = $value['img'] != null ? 'upload/'.$value['img'] : 'nada.webp';

                $data['response'] .= ' 
                <div class="single-feedback flex-center '.$destacado.'" style="flex-direction:row;" id="'.$value['id'].'">
                    <div class="user flex-center">
                        <img class="avatarPhoto" src="'.BASE.'data/images/upload/'.\Site::getImageUser($infoUser['id']).'">
                        <p><b>'.$infoUser['username'].'</b></p>
                    </div>
                    <div style="margin: 5px 0;"><img src="'.BASE.'data/images/'.$imagem.'"></div>
                    <div style="margin: 5px 0;"><span style="margin: 5px 0;">'.substr($value['text'],0,40).'</span></div>
                    '.$buttondestacado.'
                    <button class="remove-feedback"><span><i class="fas fa-trash"></i> Excluir</span></button>
                </div>';
                
            }
            $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `aprovado` = 1"); $sql->execute(); $total = $sql->rowCount();
            $totalPages = ceil($total/$itensporpagina);
            if($totalPages != 1){
                $data['response'] .= '<div class="paginator">';
                for($i=0; $i < $totalPages; $i++){
                    if($i == ($pagina - 1)){
                        $data['response'] .= '<button class="paginator-feedbacks active" page="'.($i+1).'">'.($i+1).'</button>';
                    }else{
                        $data['response'] .= '<button class="paginator-feedbacks" page="'.($i+1).'">'.($i+1).'</button>';
                    }
                }
                $data['response'] .= '</div>';
            }
        }else{
            $data['response'] .= '<div class="flex-center w100" style="background: #a58fdc; padding:10px;"> <span><i class="fa fa-times"></i> Não foi encontrado nenhum feedback, aprove alguns feedbacks.</span></div>';
        }
        
    }else if($action == 'tab-excluirFeedback'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `id` = ?"); $sql->execute(array($id));
        $feedbackInfo = $sql->fetch();
        $userInfo = Site::getUserInfoByID($feedbackInfo['id.usuario']);
        $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened"  style="flex-direction:column;" feedbackid="'.$feedbackInfo['id'].'">
                                <p>Deseja apagar esse o feedback de <b>'.$userInfo['username'].'</b>?</p>
                                <div class="feedback-preview">
                                    <img style="width:20px; height:20px; border-radius:50%;" src="'.BASE.'data/images/upload/'.Site::getImageUser($userInfo['id']).'">
                                    <span><b>'.$userInfo['username'].'</b></span>:
                                    <span>'.$feedbackInfo['text'].'</span>
                                </div>
                                <button js="confirmExcluirFeedback" style="width:100%; max-width:100px;">Sim, excluir</button>
                                <button class="closeTab"><i class="fas fa-times-circle"></i></button>
                            </div> ';
    }else if($action == 'aprovFeedbacks'){
        $itensporpagina = isset($_POST['porpagina']) ? trim(strip_tags($_POST['porpagina'])) : 3;
        $pagina = isset($_POST['page']) ? trim(strip_tags($_POST['page'])) : 1;
        $startsfrom = ($pagina - 1) * $itensporpagina;
        $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened"  style="flex-direction:row;"><button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button><h4 style="width:100%; text-align:center; margin:10px 0; border-bottom:1px solid; padding:5px;">Feedbacks não aprovados</h4> ';
        $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `aprovado` = 0 ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom"); $sql->execute();
        if($sql->rowCount() >= 1){
            $feedbacks = $sql->fetchAll();
            foreach($feedbacks as $key => $value){
                $infoUser = Site::getUserInfoByID($value['id.usuario']);
                $imagem = $value['img'] != null ? 'upload/'.$value['img'] : 'nada.webp';
                $data['response'] .= ' 
                                        <div class="single-feedback flex-center style="flex-direction:row;" id="'.$value['id'].'">
                                            <div class="user flex-center">
                                                <img class="avatarPhoto" src="'.BASE.'data/images/upload/'.\Site::getImageUser($infoUser['id']).'">
                                                <p><b>'.$infoUser['username'].'</b></p>
                                            </div>
                                            <div style="margin: 5px 0;"><img src="'.BASE.'data/images/'.$imagem.'"></div>
                                            <div style="margin: 5px 0;"><span style="margin: 5px 0;">'.substr($value['text'],0,60).'</span></div>
                                            <button js="aprovar-feedback"><span><i class="far fa-check-circle"></i> Aprovar</span></button>
                                            <button class="remove-feedback"><span><i class="fas fa-trash"></i> Excluir</span></button>
                                        </div>';
            }
            $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `aprovado` = 0"); $sql->execute(); $total = $sql->rowCount();
            $totalPages = ceil($total/$itensporpagina);
            if($totalPages != 1){
                $data['response'] .= '<div class="paginator">';
                for($i=0; $i < $totalPages; $i++){
                    if($i == ($pagina - 1)){
                        $data['response'] .= '<button class="paginator-feedbacks-aprov active" page="'.($i+1).'">'.($i+1).'</button>';
                    }else{
                        $data['response'] .= '<button class="paginator-feedbacks-aprov" page="'.($i+1).'">'.($i+1).'</button>';
                    }
                }
                $data['response'] .= '</div>';
            }
        }else{
            $data['response'] .= '<div class="flex-center w100" style="background: #a58fdc; padding:10px;"> <span><i class="fa fa-times"></i> Não foi encontrado nenhum feedback para aprovar.</span></div>';
        }
        $data['response'] .= '</div>';
        
        
    }else if($action == 'confirm-aprovarFeedback'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("UPDATE `feedbacks` SET `aprovado` = 1 WHERE `id` = ?");
        if($sql->execute(array($id))){
            $data['msg'] = 'Feedback aprovado com sucesso, já está disponível para usuários!';
        }else{
            $data['sucesso'] = false;
            $data['msg'] = 'Não foi possível aprovar o feedback';
        }
    }else if($action == 'confirmExcluirFeedback'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("DELETE FROM `feedbacks` WHERE `id`= ?"); 
        if($sql->execute(array($id))){
            $data['msg'] = 'Feedback excluído com sucesso.';
        }else{
            $data['msg'] = 'Aconteceu algum erro ao excluir o feedback';
            $data['sucesso'] = false;
        }
    }else if($action == 'destacarFeedback'){
        $id = $_POST['id'];

        $sql = MySql::conectar()->prepare("SELECT * FROM `feedbacks` WHERE `id` = ?"); $sql->execute(array($id));
        $feedbackInfo = $sql->fetch();

        if($feedbackInfo['destacado'] == 1){
            // ja esta destacado tem q tirar destaque
            $sql = MySql::conectar()->prepare("UPDATE `feedbacks` SET `destacado` = 0 WHERE `id` = ?");
            if($sql->execute(array($feedbackInfo['id']))){
                $data['msg'] = 'Feedback retirado dos destaques com sucesso!';
            }else{
                $data['msg'] = 'Aconteceu algum erro ao retirar dos destaques!';
                $data['sucesso'] = false;
            }
        }else{
            $sql = MySql::conectar()->prepare("UPDATE `feedbacks` SET `destacado` = 1 WHERE `id` = ?");
            if($sql->execute(array($feedbackInfo['id']))){
                $data['msg'] = 'Feedback destacado com sucesso!';
            }else{
                $data['msg'] = 'Aconteceu algum erro ao destacar o feedback!';
                $data['sucesso'] = false;
            }
        }
    }elseif($action == 'tab-banirUser'){
        $infoUser = Site::getUserInfoByID($_POST['id']);
        if(Usuario::checkHasUserBannedByID($infoUser['id'])){
            $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened"  style="flex-direction:column;" iduser="'.$infoUser['id'].'">
                                <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                <p style="margin-bottom:10px;">Deseja remover o banimento de <b>'.$infoUser['username'].'</b></p>
                                <div class="perfil flex-center" style="flex-direction:column;">
                                    <div class="disp flex-center" style="flex-direction:row; justify-content: space-evenly;">
                                        <img src="'.BASE.'data/images/upload/'.Site::getImageUser($infoUser['id']).'">
                                        <span style="margin: 0 5px;">'.$infoUser['username'].'</span>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Entrou em</b></span><br>
                                            <span>'.date('d/m/Y',strtotime($infoUser['data-criacao'])).'</span>
                                        </div>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Pedidos</b></span>
                                            <span>'.Usuario::getNumbersOfOrderByID($infoUser['id']).'</span>
                                        </div>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Referidos</b></span>
                                            <span>'.Usuario::getNumbersOfRefsByID($infoUser['id']).'</span>
                                        </div>
                                    </div>
                                    
                                </div>
                                <button class="ban-us" style="background: #a58fdc; width:100%; max-width:400px;"><span><i style="margin:0 3px;" class="fas fa-unlock"></i> Desbanir</span></button>                           
                            </div>';
        }else{
            $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened"  style="flex-direction:column;" iduser="'.$infoUser['id'].'">
                                <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                <p style="margin-bottom:10px;">Especifique o motivo do banimento de <b>'.$infoUser['username'].'</b></p>
                                <div class="perfil flex-center" style="flex-direction:column;">
                                    <div class="disp flex-center" style="flex-direction:row; justify-content: space-evenly;">
                                        <img src="'.BASE.'data/images/upload/'.Site::getImageUser($infoUser['id']).'">
                                        <span style="margin: 0 5px;">'.$infoUser['username'].'</span>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Entrou em</b></span><br>
                                            <span>'.date('d/m/Y',strtotime($infoUser['data-criacao'])).'</span>
                                        </div>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Pedidos</b></span>
                                            <span>'.Usuario::getNumbersOfOrderByID($infoUser['id']).'</span>
                                        </div>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Referidos</b></span>
                                            <span>'.Usuario::getNumbersOfRefsByID($infoUser['id']).'</span>
                                        </div>
                                    </div>
                                    
                                </div>
                                <textarea style="margin:10px 0; min-height:40px" placeholder="Motivo do banimento"></textarea> 
                                <button class="ban-us" style="background: #7c0029; width:100%; max-width:400px;"><span><i style="margin:0 3px;" class="fas fa-ban"></i> Banir</span></button>                            
                            </div>';
        }
        
    }else if($action == 'banirUser'){
        $id = $_POST['id'];
        if(Usuario::checkHasUserBannedByID($id)){
            if(Usuario::UnbanFromId($id)){
                $data['msg'] = 'Usuário desbanido com sucesso.';
            }else{
                $data['msg'] = 'Aconteceu algum erro ao desbanir este usuário, talvez já esteja desbanido.';
                $data['sucesso'] = false;
            }
        }else{
            $reason = !isset($_POST['motivo']) ? 'Não especificado' : $_POST['motivo'];
            if(Usuario::banFromId($id,$reason)){
                $data['msg'] = 'Banimento aplicado com sucesso';
            }else{
                $data['msg'] = 'Aconteceu algum erro ao banir este usuário, talvez já esteja banido.';
                $data['sucesso'] = false;
            }
        }   
        
    }else if($action == 'tab-editarUser'){
        $infoUser = Site::getUserInfoByID($_POST['id']);
        $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened"  style="flex-direction:column;" iduser="'.$infoUser['id'].'">
                            <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                            <p style="margin-bottom:10px;">Editando usuário <b>'.$infoUser['username'].'</b></p>
                            <div class="perfil flex-center" style="flex-direction:column;">
                                <div class="disp flex-center" style="flex-direction:row; justify-content: space-evenly;">
                                    <img src="'.BASE.'data/images/upload/'.Site::getImageUser($infoUser['id']).'">
                                    <span style="margin: 0 5px;">'.$infoUser['username'].'</span>
                                </div>
                                <div class="infos flex-center">
                                    <div>
                                        <span><b>Username</b></span><br>
                                        <input type="text" name="username" value="'.$infoUser['username'].'">
                                    </div>
                                </div>
                                <div class="infos flex-center">
                                    <div>
                                        <span><b>Email</b></span><br>
                                        <input type="text" name="email" value="'.$infoUser['email'].'">
                                    </div>
                                </div>
                                <div class="infos flex-center">
                                    <div>
                                        <span><b>Nome</b></span><br>
                                        <input type="text" name="nome" value="'.$infoUser['nome'].'">
                                    </div>
                                </div>
                                <div class="infos flex-center">
                                    <div>
                                        <span><b>Sobrenome</b></span><br>
                                        <input type="text" name="sobrenome" value="'.$infoUser['sobrenome'].'">
                                    </div>
                                </div>
                                <div class="infos flex-center">
                                    <div>
                                        <span><b>CPF</b></span><br>
                                        <input type="text" datamask="cpf" name="cpf" value="'.$infoUser['cpf'].'">
                                    </div>
                                </div>
                                <div class="infos flex-center">
                                    <div>
                                        <span><b>Telefone</b></span><br>
                                        <input type="text" datamask="telefone" name="telefone" value="'.$infoUser['telefone'].'">
                                    </div>
                                </div>
                                
                            </div>
                            <button class="edit-us" style="background: rgb(40 157 17); width:100%; max-width:400px;"><span><i style="margin:0 3px;" class="fas fa-send"></i> Confirmar</span></button>                            
                        </div>';
    }else if($action == 'editarUser'){
        $id = $_POST['id'];
        $username = @$_POST['username'];
        $email = @$_POST['email'];
        $nome = @$_POST['nome'];
        $sobrenome = @$_POST['sobrenome'];
        $cpf = @$_POST['cpf'];
        $telefone = @$_POST['telefone'];

        $cpf = preg_replace("/[^0-9]/", "",$cpf);
        $telefone = preg_replace("/[^0-9]/", "",$telefone);

        $query = '';
        if($username != ''){
            $query .= "`username`='$username' |";
        }
        if($email != ''){
            $query .= "`email`='$email' |";
        }
        if($nome != ''){
            $query .= "`nome`='$nome' |";
        }
        if($sobrenome != ''){
            $query .= "`sobrenome`='$sobrenome' |";
        }
        if($cpf != ''){
            $query .= "`cpf`=$cpf |";
        }
        if($telefone != ''){
            $query .= "`telefone`=$telefone |";
        }
        $query = substr(str_replace('|', ',', $query),0,-1);
        if($query != ''){
            $sql = MySql::conectar()->prepare("UPDATE `usuarios` SET $query WHERE `id` = ?"); 
            if($sql->execute(array($id))){
                $data['msg'] = 'Dados do usuário editados com sucesso.';
            }else{
                $data['msg'] = 'Aconteceu algum erro ao editar o usuário.';
                $data['sucesso'] = false;
            }
        }else{
            $data['msg'] = 'Não foi encontrado nada para editar.';
        }
        

    }else if($action == 'insertAnuncio'){
        $img = $_POST['img'];
        $title = $_POST['title'];
        $cat = $_POST['cat'];
        $desc = $_POST['desc'];
        $comp = $_POST['complement'];
        $preco = $_POST['preco'];
        $slug = Site::gerarSlug($title);
        $date = date('Y-m-d H:i:s');
        foreach($_POST as $key => $value){
            if($value == '' || $value == null || !isset($value)){
                $data['msg'] = 'Você precisa preencher todos as informações sobre o anúncio';
                $data['sucesso'] = false;
                die(json_encode($data,true));
            }
        }
        $preco = str_replace(',','.',$preco);
        $sql = MySql::conectar()->prepare("INSERT INTO `produtos.default` VALUES(null,?,?,?,?,?,?,?,1)");
        if($sql->execute(array($title,$slug,$comp,$cat,$desc,$preco,$date))){
            $idanuncio = MySql::conectar()->lastInsertId();
            $sql = MySql::conectar()->prepare("INSERT INTO `produtos.imagens` VALUES(null,?,?)");
            if($sql->execute(array($idanuncio,$img))){
                $data['msg'] = 'Anúncio inserido com sucesso';
            }else{$data['msg'] = 'Aconteceu algum erro';}
        }else{
            $data['msg'] = 'Aconteceu algum erro';
        }
    }else if($action == 'editAnuncio'){
        $id = $_POST['id'];
        $title = @$_POST['title'];
        $cat = @$_POST['cat'];
        $desc = @$_POST['desc'];
        $comp = @$_POST['complement'];
        $preco = @floatval(str_replace(',','.',str_replace('.','',$_POST['preco'])));
        $slug = Site::gerarSlug($title);
        $query = '';
        $infoAnuncio = Site::getInfoDB('produtos.default','`id`='.$id);
        if($title != '' && $infoAnuncio['nome'] != $title){
            $query .= '`nome`="'.$title.'" |';
        }
        if($cat != '' && $infoAnuncio['categoria_id'] != $cat){
            $query .= "`categoria_id`=$cat |";
        }
        if($desc != '' && $infoAnuncio['desc'] != $desc){
            $query .= '`desc`="'.$desc.'" |';
        }
        /*
        if($comp != '' && $infoAnuncio['complement'] != $comp){
            $query .= '`complement`="'.$comp.'" |';
        }
        */
        if($preco != '' && $infoAnuncio['preco'] != $preco){
            $query .= "`preco`=$preco |";
        }
        if($slug != '' && $infoAnuncio['slug'] != $slug){
            $query .= '`slug`="'.$slug.'" |';
        }
        $query = substr(str_replace('|', ',', $query),0,-1);
        if($query != ''){
            $sql = MySql::conectar()->prepare("UPDATE `produtos.default` SET $query WHERE `id` = ?"); 
            if($sql->execute(array($id))){
                $data['msg'] = 'Anúncio editado com sucesso';
            }else{
                $data['msg'] = 'Aconteceu algum erro.';
                $data['sucesso'] = false;
            }
        }else{
            $data['msg'] = 'Não foi encontrado nada para editar.';
        }
        

    }else if($action == 'tab-editarAnuncio'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?"); $sql->execute(array($id));
        $infoProduto = $sql->fetch();
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias`");
        $sql->execute();
        $cat = $sql->fetchAll();
        $categorias = '<select name="cat" selectedid='.$infoProduto['categoria_id'].'>';
        foreach($cat as $key => $value){
            $categorias .= '<option value="'.$value['id'].'">'.$value['nome'].'</option>';
        }
        $categorias .= '</select>';
        $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened" style="flex-direction:column;" idann="'.$infoProduto['id'].'">
                                <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                <p>Editando anúncio <b>'.$infoProduto['nome'].'</b></p>
                                <div class="tabEditAn flex-center" style="flex-direction:row;">
                                <div class="add-img">
                                    <label for="file">
                                        <div class="svg-addphoto position-center" style="top:40%;"></div>
                                        <span class="position-center">Adicionar</span>
                                        <input type="file" name="photo" id="file" onchange="changeImageAn('.'`edit`'.')" style="display:none">
                                    </label>
                                </div>';
    $imgs = \Models\ProductsDefault::getImageProductFromID($id);
    if($imgs != ''){
        foreach($imgs as $key => $value){
            $data['response'] .= '<div class="img" idimg="'.$value['id'].'">
                                    <img src="'.BASE.'data/images/upload/'.$value['name'].'">
                                    <button js="removeimage"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                </div>';
        }
    }
    
    $data['response'] .='</div>
                        <div class="add-an" style="width:100%;max-width:400px;">
                            <div class="box">
                                <p>Categoria</p>
                                '.$categorias.'
                            </div>
                            <div class="box">
                                <div class="textar"><input value="'.$infoProduto['nome'].'" type="text" name="title" maxlength="30" placeholder="Nome do anúncio"></div>
                                <div class="textar"><textarea id="editor" name="desc" maxlength="255" placeholder="Descrição do anúncio">'.$infoProduto['complement'].'</textarea></div>
                                <div class="textar"><textarea id="editor" name="complement" maxlength="30" placeholder="Complemento do anúncio">'.$infoProduto['desc'].'</textarea></div>
                                <div class="textar maskMoney"><input value="'.$infoProduto['preco'].'"type="text" datamask=preco name="preco" placeholder="Valor do anúncio"></div>
                            </div>
                            <div class="box">
                                <button jsAction="confirm-EditAn" class="button-add">Confirmar <span class="svg-Send"></span></button>
                            </div>
                        </div>
                    </div>';

    }else if($action == 'tab-removerAnuncio'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?"); $sql->execute(array($id)); $info = $sql->fetch();
        $data['response'] = '<div class="tabopened container-content flex-center" id="tabopened" style="flex-direction:column; position:relative;" dataid="'.$info['id'].'">
                                <p>Deseja excluir o anúncio <b>'.$info['nome'].'</b>?</p>
                                <button js="excluirAnuncio" style="width:100%; max-width:100px;">Sim, excluir</button>
                                <button class="closeTab"><i class="fas fa-times-circle"></i></button>
                            </div>';
    }else if($action == 'removerAnuncio'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("UPDATE `produtos.default` SET `status` = 0 WHERE `id` = ?"); 
        if($sql->execute(array($id))){
            $data['msg'] = 'Anúncio desativado com sucesso.';
        }
    }else if($action == 'removerImagem'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.imagens` WHERE `id` = ?"); $sql->execute(array($id));
        $info = $sql->fetch();
        $sql = MySql::conectar()->prepare("DELETE FROM `produtos.imagens` WHERE `id` = ?");
        if($sql->execute(array($id))){
            if(unlink("../data/images/upload/".$info['name'])){
                $data['msg'] = 'Imagem excluída com sucesso.';
            }
        }else{
            $data['msg'] = 'Não foi possível excluir a imagem';
            $data['sucesso'] = false;
        }
    }else if($action == 'edit-actions'){
        $id = $_POST['id'];
        $data['response'] = '<div id="'.$id.'"class="body mobile-actions" style="justify-content:center; border-bottom:1px solid;">
                                <div><button class="closeEditAction"><i class="fas fa-backspace"></i></button></div>
                                <div><button title="Banir usuário" class="tab-ban-us"><i class="fas fa-ban"></i></button></div>
                                <div><button title="Editar usuário" class="tab-edit-us"><i class="far fa-edit"></i></button></div>
                                <div><button title="Excluir usuário" class="tab-remove-us"><i class="fas fa-trash"></i></button></div>
                            </div>';
    }else if($action == 'addCategorias'){
        $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened" style="flex-direction:column;">
                                <button class="closeTab"><i class="fas fa-backspace"></i></button>
                                <h2 style="width:100%; text-align:center; margin-bottom:20px;">Adicionar categoria</h2>
                                <div class="add-an">
                                    <div class="box">
                                        <p>Imagem da categoria</p>
                                        <div class="img">
                                            <img data-name="" src="'.BASE.'data/images/nada.webp">
                                            <label class="noimage">
                                                    <i class="fas fa-file-upload"></i>
                                                    <input onchange="changeImageAn();" style="visibility:hidden; opacity:0; display:none;" type="file" name="photo" accept="image/png, image/gif, image/jpeg">
                                            </label>
                                        </div>
                                        <p>Nome da categoria</p>
                                        <div class="textar"><input type="text" name="nomecategoria"></div>
                                        <p>Descrição da categoria</p>
                                        <div class="textar"><textarea name="desc"></textarea></div>
                                    </div>
                                    <div class="box">
                                        <button type="submit" jsAction="confirm-AddCat" class="button-add">Adicionar <span class="svg-Send"></span></button>
                                    </div>
                                </div>
                            </div>';
    }else if($action == 'confirmAddCat'){
        $img = @$_POST['img'];
        $nome = strip_tags($_POST['nome']);
        $desc = strip_tags($_POST['desc']);
        foreach($_POST as $key => $value){
            if($value == '' || !isset($value) || $value == null){
                $data['msg'] = 'Preencha todos os dados antes de continuar.';
                $data['sucesso'] = false;
                die(json_encode($data));
            }
        }
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `nome` = ?"); $sql->execute($nome);
        if($sql->rowCount() >= 1){
            $data['msg'] = 'Já existe uma categoria com esse nome';
            $data['sucesso'] = false;
        }else{
            $slug = Site::gerarSlug($nome);
            $sql = MySql::conectar()->prepare("INSERT INTO `produtos.default.categorias` VALUES(null,?,?,?,?,1,?)");
            if($sql->execute(array($nome,$desc,$slug,$img,date('Y-m-d H:i:s')))){
                $data['msg'] = 'Categoria criada com sucesso.';
            }else{
                $data['msg'] = 'Aconteceu algum erro.';
                $data['sucesso'] = false;
            }
        }
    }else if($action == 'manageCategorias'){
        $itensporpagina = isset($_POST['porpagina']) ? trim(strip_tags($_POST['porpagina'])) : 3;
        $pagina = isset($_POST['page']) ? trim(strip_tags($_POST['page'])) : 1;
        $startsfrom = ($pagina - 1) * $itensporpagina;
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `status` = 1 ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom"); $sql->execute();
        $categorias = $sql->fetchAll();
        $data['response'] = '<div class="container-content cats tabopened flex-center" id="tabopened" style="flex-direction:row;"> <h2 style="width:100%; text-align:center; margin-bottom:20px;">Gerenciar categorias</h2>';
        foreach($categorias as $key => $value){
            $photo = \Models\ProductsDefault::getImageCategoriaFromID($value['id']) == null ? 'nada.webp' : 'upload/'.\Models\ProductsDefault::getImageCategoriaFromID($value['id']);
            $data['response'] .= '  
                                    <button class="closeTab"><i class="fas fa-backspace"></i></button>
                                    <div class="single-an" id="'.$value['id'].'">
                                        <div class="body">
                                            <p>'.$value['nome'].'</p>
                                            <div class="img">
                                                <img src="'.BASE.'data/images/'.$photo.'">
                                            </div>
                                            <button class="tab-edit-cat"><i class="fas fa-edit"></i> Editar</button>
                                            <button class="tab-remove-cat"><i class="fas fa-trash"></i> Excluir</button>
                                        </div>
                                    </div>
                                ';
        }
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `status` = 1"); $sql->execute(); $totalCat = $sql->rowCount();
        $totalPages = ceil($totalCat/$itensporpagina);
        if($totalPages != 1){
            $data['response'] .= '<div class="paginator">';
            for($i=0; $i < $totalPages; $i++){
                if($i == ($pagina - 1)){
                    $data['response'] .= '<button class="paginator-cats active" page="'.($i+1).'">'.($i+1).'</button>';
                }else{
                    $data['response'] .= '<button class="paginator-cats" page="'.($i+1).'">'.($i+1).'</button>';
                }
            }
            $data['response'] .= '</div>';
        }
        $data['response'] .= '</div>';
        
    }else if($action == 'tab-editarCategoria'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `id` = ?");$sql->execute(array($id));
        $categoriaInfo = $sql->fetch();
        $imagem = ProductsDefault::getImageCategoriaFromID($categoriaInfo['id'])  ? 'upload/'.ProductsDefault::getImageCategoriaFromID($categoriaInfo['id']) : 'nada.webp';
        $data['response'] = '<div class="container-content tabopened flex-center sub-tabopened" id="tabopened" style="flex-direction:column;" editid="'.$categoriaInfo['id'].'"> 
                                <p style="width:100%; text-align:center; margin-bottom:20px;">Editando categoria <b>'.$categoriaInfo['nome'].'</b></p>
                                <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                <div class="perfil flex-center" style="flex-direction:column;">
                                    <div class="disp flex-center" style="flex-direction:row; justify-content: space-evenly;">
                                        <img src="'.BASE.'data/images/'.$imagem.'">
                                        <span style="margin: 0 5px;">'.$categoriaInfo['nome'].'</span>
                                    </div>
                                    <div class="infos flex-center">
                                        <div class="img">
                                            <img data-name="" src="'.BASE.'data/images/'.$imagem.'">
                                            <label class="noimage">
                                                    <i style="top:80%" class="fas fa-file-upload"><span style="font-size:10px;">Alterar</span></i>
                                                    <input onchange="changeImageAn();" style="visibility:hidden; opacity:0; display:none;" type="file" name="photo" accept="image/png, image/gif, image/jpeg">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Nome</b></span><br>
                                            <input type="text" name="nome" value="'.$categoriaInfo['nome'].'">
                                        </div>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Slug (link)</b></span><br>
                                            <input type="text" name="slug" value="'.$categoriaInfo['slug'].'">
                                        </div>
                                    </div>
                                    <div class="infos flex-center">
                                        <div>
                                            <span><b>Descrição</b></span><br>
                                            <textarea style="border: 1px solid;" name="desc">'.$categoriaInfo['desc'].'</textarea>
                                        </div>
                                    </div>
                                </div>
                                <button class="w100 confirm-editCategoria" style="max-width:400px;">Confirmar</button>
                            </div>';
    }else if($action == 'editarCategoria'){
        $id = $_POST['id'];
        $img = @$_POST['img'];
        $nome = @$_POST['nome'];
        $slug = @$_POST['slug'];
        $desc = @$_POST['desc'];

        $query = '';
        if($img != ''){
            $query .= "`img`='$img' |";
        }
        if($nome != ''){
            $query .= "`nome`='$nome' |";
        }
        if($desc != ''){
            $query .= "`desc`='$desc' |";
        }
        if($slug != ''){
            $query .= "`slug`='$slug' |";
        }
        $query = substr(str_replace('|', ',', $query),0,-1);
        if($query != ''){
            $sql = MySql::conectar()->prepare("UPDATE `produtos.default.categorias` SET $query WHERE `id` = ?"); 
            if($sql->execute(array($id))){
                $data['msg'] = 'Categoria atualizada com sucesso';
            }else{
                $data['msg'] = 'Aconteceu algum erro ao atualizar a categoria';
                $data['sucesso'] = false;
            }
        }else{
            $data['msg'] = 'Não foi encontrado nada para editar.';
        }
    }else if($action == 'tab-excluirCategoria'){
        $id = $_POST['id'];
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `id` = ?");$sql->execute(array($id));
        $categoriaInfo = $sql->fetch();
        $imagem = ProductsDefault::getImageCategoriaFromID($categoriaInfo['id'])  ? 'upload/'.ProductsDefault::getImageCategoriaFromID($categoriaInfo['id']) : 'nada.webp';

        $data['response'] = '<div class="container-content tabopened flex-center sub-tabopened" id="tabopened" style="flex-direction:column;" editid="'.$categoriaInfo['id'].'"> 
        <p style="width:100%; text-align:center; margin-bottom:20px;">Deseja excluir a categoria <b>'.$categoriaInfo['nome'].'</b>?</p>
        <div class="perfil flex-center" style="flex-direction:column;">
            <div class="disp flex-center" style="flex-direction:row; justify-content: space-evenly;">
                <img src="'.BASE.'data/images/'.$imagem.'">
                <span style="margin: 0 5px;">'.$categoriaInfo['nome'].'</span>
            </div>
        </div>
        <button class="w100 confirm-excluirCategoria" style="max-width:400px;">Sim, excluir</button>
    </div>';
    }else if($action == 'excluirCategoria'){
        $id = $_POST['id'];
        
        $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.categorias` WHERE `id` = ?"); $sql->execute(array($id));
        $categoriaInfo = $sql->fetch();

        if($categoriaInfo['status'] == 1){
            $sql = MySql::conectar()->prepare("UPDATE `produtos.default.categorias` SET `status` = 0 WHERE `id` = ?");
            if($sql->execute(array($categoriaInfo['id']))){
                $data['msg'] = 'Categoria desativada com sucesso!';
            }else{
                $data['Aconteceu algum erro.'];
                $data['sucesso'] = false;
            }
        }else{
            $sql = MySql::conectar()->prepare("UPDATE `produtos.default.categorias` SET `status` = 1 WHERE `id` = ?");
            if($sql->execute(array($categoriaInfo['id']))){
                $data['msg'] = 'Categoria ativada com sucesso!';
            }else{
                $data['Aconteceu algum erro.'];
                $data['sucesso'] = false;
            }
        }
    }else if($action == 'StatusSite'){
        $json = file_get_contents('../config.json');
        $json = json_decode($json, true);
        if($json['manutencao'] == 1){
            $status = '<i class="fas fa-user-slash"></i> A manutenção está ativada, os usuários não tem acesso ao site.';
            $checked = '';
        }else{
            $status = '<i class="far fa-check-circle"></i> Site disponível para os usuários.';
            $checked = 'checked';
        }
        $data['response'] = '<div class="container-contentWrapper">
                                <div class="container-content flex-center" style="flex-direction:column">
                                    <h2>Site no ar</h2>
                                    <div class="switch__container flex-center">
                                        <input '.$checked.' id="switch-shadow" class="switch switch--shadow" type="checkbox" onchange="changeStatusSite();">
                                        <label for="switch-shadow"></label>
                                    </div>
                                    <span class="status">'.$status.'</span>
                                </div>
                            </div>';

    }else if($action == 'Manutencao'){
        $json = file_get_contents('../config.json');
        $json = json_decode($json, true);
        if($json['manutencao'] == 1){
            $json['manutencao'] = 0;
            $json = json_encode($json);
            file_put_contents('../config.json', $json);
            $data['response'] = '<i class="far fa-check-circle"></i> Site disponível para os usuários.';
        }else{
            $json['manutencao'] = 1;
            $json = json_encode($json);
            file_put_contents('../config.json', $json);
            $data['response'] = '<i class="fas fa-user-slash"></i> A manutenção está ativada, os usuários não tem acesso ao site.';
        }

    }else if($action == 'tab-adicionarAdmin'){
        $data['response'] = '<div class="container-content tabopened info-content" style="justify-content: center;">
                                <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                <div style="flex-direction:column; margin:10px 0;" class="perfil flex-center w100">
                                    <p style="margin: 5px 0;">Insira os dados do novo administrador</p>
                                    <label for="username" class="infos">
                                        <span>Usuário</span><br>
                                        <input type="text" id="username" name="username">
                                    </label>
                                    <label for="email" class="infos">
                                        <span>E-mail</span><br>
                                        <input type="text" id="email" name="email"><br><br>
                                        <span style="font-size:10px; margin: 3px 0;">Será enviado um e-mail para verificar o administrador.</span>
                                    </label>

                                    <label for="code" class="infos">
                                        <span>Chave mestra</span><br>
                                        <input type="text" autocomplete="off" id="code" name="code"><br><br>
                                    </label>
                                    <button js="confirm-adicionarNovoAdmin" style="margin:0; margin-top:5px;" class="w100">Confirmar</button>
                                </div>
                            </div>';
    }else if($action == 'adicionarAdmin'){
        $username = $_POST['username'];
        $email = $_POST['email'];
        $chavemestra = $_POST['chavemestra'];

        foreach($_POST as $key => $value){
            if($value == '' || $value == null || !$value){
                $data['msg'] = 'Você preencher todos os dados.';$data['sucesso'] = false;die(json_encode($data));
            }
        }
        if(!voku\helper\EmailCheck::isValid($email)){
            $data['msg'] = 'Este e-mail não é valido';
            $data['sucesso'] = false;
            die(json_encode($data));
        }

        $json = json_decode(file_get_contents('../config.json'),true);
        if(/*md5($chavemestra) == $json['chaveMestra']*/ 1==1){
            $sql = MySql::conectar()->prepare("SELECT * FROM `admin` WHERE `username` = ? OR `email` = ?"); $sql->execute(array($username,$email));
            if($sql->rowCount() >= 1){
                $data['msg'] = 'Já existe algum administrador com esse usuário ou e-mail'; $data['sucesso'] = false;
            }else{
                include('../Classes/Email.php');
                include('../dashboard/Models/AdminModels.php');
                if(\Models\AdminModels::adicionarAdministrador($email,$username)){
                    $data['msg'] = 'Código para confirmar a adição do administrador enviado.';
                }else{
                    $data['msg'] = 'Aconteceu algum erro ao adicionar esse administrador.';
                    $data['sucesso'] = false;
                }
            }
        }else{
            $data['sucesso'] = false;
            $data['msg'] = 'Chave de segurança incorreta, você tem mais 3 tentativas antes de ter seu ip bloqueado.';
        }
    }else if($action == 'tab-changeCodeMaster'){
        $data['response'] = '<div class="container-content tabopened info-content" style="justify-content: center;">
                                <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                <div style="flex-direction:column; margin:10px 0;" class="perfil flex-center w100">
                                    <p style="margin: 5px 0;">Insira os seguintes dados para alterar a chave mestra</p>
                                    <label for="codeant" class="infos">
                                        <span>Chave mestra <b>(Antiga)</b></span><br>
                                        <input type="text" autocomplete="off" id="codeant" name="codeant"><br><br>
                                    </label>
                                    <label for="codenovo" class="infos">
                                        <span>Chave mestra <b>(Atual)</b></span><br>
                                        <input type="text" autocomplete="off" id="codenovo" name="codenovo"><br><br>
                                    </label>
                                    <button js="confirm-changeCodeMaster" style="margin:0; margin-top:5px;" class="w100">Confirmar</button>
                                </div>
                            </div>';
    }else if($action == 'changeCodeMaster'){
        $codeantigo = $_POST['codeantigo'];
        $codenovo = $_POST['codenovo'];

        $json = json_decode(file_get_contents('../config.json'),true);

        if(Admin::checkCodeMaster($codeantigo)){ // codigo valido pode alterar!!
            $json['chaveMestra'] = md5($codenovo);
            file_put_contents('../config.json',json_encode($json));
            $data['msg'] = 'Codigo mestre alterado com sucesso';
        }else{
            $data['sucesso'] = false;
            $data['msg'] = 'Chave de segurança incorreta, você tem mais 3 tentativas antes de ter seu ip bloqueado.';
        }
    }else if($action == 'updateActivity'){
        $userInfo = Admin::getUserInfo($_COOKIE['admin_loginToken']);

        if(Site::getRowCountDB("admin.online", "`id.admin`='".$userInfo['id']."'") == 0){
            $sql = MySql::conectar()->prepare("INSERT INTO `admin.online` VALUES(null,?,?)");
            $sql->execute(array($userInfo['id'], date('Y-m-d H:i:s')));
        }else{
            $sql = MySql::conectar()->prepare("UPDATE `admin.online` SET `lastactivity` = ? WHERE `id.admin` = ?");
            $sql->execute(array(date('Y-m-d H:i:s'), $userInfo['id']));
        }
    }else if($action == 'tab-manageEmail'){
        $data['response'] =     '<div class="cat-actions flex-center" style="flex-direction:row; width:100%;">
                                    <button js="enviar-email" class="active">Enviar e-mail</button>
                                    <button js="manage-emails">Gerenciar Emails</button>
                                </div>
                                <div class="w100 flex-center" style="align-items: unset; position:relative;">
                                    <div class="search w100 flex-center" style="flex-direction:row; flex-wrap:nowrap; position:relative;">
                                        <input class="w100" type="search" name="buscausuario" placeholder="Remetente">
                                        <button><div class="fa fa-search"></div></button>
                                    </div>
                                    <div class="w100 p-auto-complete">
                                        
                                    </div>
                                    <div class="w100 flex-center">
                                    <form class="ajax w100" method="post" action="'.BASE.'ajax/Admin.php">
                                        <input type="hidden" name="type-action" value="sendMailConfirm">
                                        <textarea name="textemail" class="w100" tinymce="true"></textarea>
                                        <button type="submit" class="w100" style="margin: 10px 0;">Enviar e-mail</button>
                                    </form>
                                    </div>

                                    
                                </div>';
        

    }else if($action == 'buscaUsuario'){
        $busca = $_POST['search'] != '' ? $_POST['search'] : '';
        $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios` WHERE `username` LIKE '%$busca%' OR `email` LIKE '%$busca%'");$sql->execute();
        if($sql->rowCount() >= 1){
            $response = $sql->fetchAll();
            $data['response'] = '';
            foreach($response as $key => $value){
                $data['response'] .= '<div userid="'.$value['id'].'" class="w100 c-auto-complete flex-center" style="flex-direction:row; position:relative;">
                                        <img class="imgminimum borderradius50" src="'.BASE.'data/images/upload/'.Site::getImageUser($value['id']).'">
                                        <p>'.$value['username'].'</p>
                                        <p>'.$value['email'].'</p>
                                        <p>'.date('d/m/Y', strtotime($value['data-criacao'])).'</p>
                                    </div>';
            }
            $data['response'] .= '<div userid="all" class="w100 c-auto-complete flex-center allusers" style="flex-direction:row;">
                                    <p style="margin-top:10px; margin-bottom:5px;">Para todos</p>
                                </div>';
        }
    }else if($action == 'selectUserBusca'){
        $from = $_POST['from'] != '' ? $_POST['from'] : false;
        if($from){
            if($from == 'all'){

            }else{
                $userInfo = Site::getUserInfoByID($from);
                $data['userid'] = $userInfo['id'];
                $data['response'] = '<div userid="all" class="selecteduser posabsolute flex-center" style="flex-direction:row;">
                                        <img class="imgminimum borderradius50" src="'.BASE.'data/images/upload/'.Site::getImageUser($userInfo['id']).'">
                                        <p style="margin: 0 3px;">'.$userInfo['username'].'</p>
                                        <p style="margin: 0 3px;">'.$userInfo['email'].'</p>
                                        <p style="margin: 0 3px;">'.date('d/m/Y', strtotime($userInfo['data-criacao'])).'</p>
                                    </div>';
            }
        }
    }else if($action == 'sendMailConfirm'){
        $textmail = $_POST['textemail'];
        $from = $_POST['from'];
        if(!isset($textmail) || $textmail == '' || !isset($from) || $from == ''){
            $data['msg'] = 'Você precisa preencher todos os campos';
            $data['sucesso'] = false;
            die(json_encode($data));
        }
        if($from == 'all' || Site::getRowCountDB('usuarios','`id`="'.$from.'"') >= 1){
            \Models\AdminModels::enviarEmail($textmail,$from);
            $sql = Mysql::conectar()->prepare("INSERT INTO `emails.enviados` VALUES(null,?,?,?,?)");
            $sql->execute(array(Admin::getUserInfo('admin_loginToken')['id']), $textmail, $from, date('Y-m-d H:i:s'));
        }else{
            $data['msg'] = 'Usuário selecionado não encontrado';
            $data['sucesso'] = false;
        }
    }else if($action == 'manageTransacoes'){
        $itensporpagina = isset($_POST['porpagina']) ? trim(strip_tags($_POST['porpagina'])) : 10;
        $pagina = isset($_POST['page']) ? trim(strip_tags($_POST['page'])) : 1;
        $startsfrom = ($pagina - 1) * $itensporpagina;

        $busca = isset($_POST['searchtransation']) ? @$_POST['searchtransation'] : '';
        $transacoes = !isset($_POST['searchtransation']) || $_POST['searchtransation'] == ''
        ? Site::getInfoDBAll('pedido.txid'," 1=1 ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom")
        : Site::getInfoDBAll('pedido.txid',"`txid` LIKE '%$busca%' OR `id.pedido` LIKE '%$busca%' OR `id.ticket` LIKE '%$busca%' ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom");
        if($transacoes){
            //print_r($transacoes);
            $data['response'] = '';
            if($_POST['type'] == 'tab'){
                $data['response'] .= '<div class="search flex-center w100" style="flex-direction:row;">
                                            <input style="margin:5px 0;" autocomplete="off" type="text" name="searchtransation" placeholder="Procure pelo ID do pedido ou procure pelo ID da transação">
                                    </div>
                                    <div class="w100 flex-center" style="align-items: unset; position:relative;">
                                        <div class="w100 flex-center content" style="flex-direction:row;">';
            }
            foreach($transacoes as $key => $value){
                $pedidoInfo = Site::getInfoDB('pedido.cart.users','`id`='.$value['id.pedido']);
                $produtoInfo = Site::getInfoDB('produtos.default','`id`='.Site::getInfoDB('cart.users','`id`='.explode(',',$pedidoInfo['carts.id'])[0])['id.item']);
                $id = $value['id.pedido'] == NULL ? 'TICKET #'.$value['id.ticket'] : 'PEDIDO #'.$value['id.pedido'];
                $data['response'] .= '<div transationid="'.$value['id'].'" class="single-transation flex-center w100" style="flex-direction:row;">
                                        <div class="flex-center"><p>'.$id.'</p></div>
                                        <div class="flex-center"><p>'.$value['txid'].'</p></div>
                                        <div class="flex-center"><p>'.$value['method'].'</p></div>
                                        <div class="flex-center"><p>'.ucfirst($value['status']).'</p></div>
                                        <div class="flex-center"><p>R$'.$pedidoInfo['valortotal'].'</p></div>
                                    </div>';
            }
            $totalPages = ceil(Site::getRowCountDB('pedido.txid')/$itensporpagina);
            if($totalPages != 1){
                $data['response'] .= '<div class="paginator">';
                for($i=0; $i < $totalPages; $i++){
                    if($i == ($pagina - 1)){
                        $data['response'] .= '<button class="paginator-transation active" page="'.($i+1).'">'.($i+1).'</button>';
                    }else{
                        $data['response'] .= '<button class="paginator-transation" page="'.($i+1).'">'.($i+1).'</button>';
                    }
                }
                $data['response'] .= '</div>';
            }
            $data['response'] .= '</div></div>';
        }else{
            $data['response'] = '<div class="w100 flex-center" style="background:#a58fdc; padding:10px;"><p>Não foi encontrado nenhuma transação 😞</p></div>';
        }

        
    }else if($action == 'manageTransacao'){
        $idtransation = $_POST['id'];
        $infotransation = Site::getInfoDB('pedido.txid','`id`='.$idtransation);
        $userInfo = $infotransation['id.pedido'] != NULL 
        ? Site::getUserInfoByID(Site::getInfoDB('pedido.cart.users','`id`='.$infotransation['id.pedido'])['user.id'])
        : Site::getUserInfoByID(Site::getInfoDB('tickets','`id`='.$infotransation['id.pedido'])['creator_id']);

        if($infotransation){
            $data['response'] = '<div class="container-content tabopened flex-center" id="tabopened"  style="flex-direction:column;" transationid="'.$infotransation['id'].'">
                                    <button class="closeTab"><i class="fas fa-times-circle" aria-hidden="true"></i></button>
                                    <p style="margin-bottom:10px;">Visualizando transação <b>'.strtoupper($infotransation['txid']).'</b></p>
                                    <div class="perfil flex-center" style="flex-direction:column;">
                                        <div class="disp flex-center" style="flex-direction:row; justify-content: space-evenly;">
                                            <img src="'.BASE.'data/images/upload/'.Site::getImageUser($userInfo['id']).'">
                                            <span style="margin: 0 5px;">'.$userInfo['username'].'</span>
                                        </div>
                                        <div class="infos flex-center">
                                            <div><p>Valor total da transação</p></div>
                                            <div><p><b>R$'.$infotransation['valortotal'].'</b></p></div>
                                        </div>
                                        
                                        
                                    </div>
                                </div>';
        }else{
            $data['sucesso'] = false;
            $data['msg'] = 'Não foi encontrado nenhuma transação com esse ID';
        }

    }
    die(json_encode($data));
}else{
    Site::redirecionar(BASE);
}