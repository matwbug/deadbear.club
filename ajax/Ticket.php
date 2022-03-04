<?php

use Models\ProductsDefault;

include('../config.php');
    $data['msg'] = '';
    $data['sucesso'] = true;
    $type = $_POST['perm'];
    $action = $_POST['acao'];
    include('../Classes/Site.php');
    include('../Classes/MySql.php');
    include('../Classes/Admin.php');
    require('../vendor/autoload.php');
    include('../Classes/Usuario.php');

    if(isset($action)){
        if(!Admin::logado()){
            $data['msg'] = 'Percebemos que você ainda não fez login, redirecionando.';
            $data['redirect'] = ADMIN;
            die(json_encode($data));
        }
        if($type == 'admin')
        {
            if($action == 'reivindicarTicket'){
                $ticketid = $_POST['id'];
                $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?");
                $sql->execute(array($ticketid));
                $ticketinfo = $sql->fetch();
                if($ticketinfo['reivindicado'] == true){
                    $data['msg'] = 'Este ticket já foi reivindicado por outra pessoa';
                    $data['sucesso'] = false;
                }else{
                    $userid = Admin::getUserInfo($_COOKIE['admin_loginToken'])['id'];
                    $sql = MySql::conectar()->prepare("UPDATE `tickets` SET `reivindicado` = ?, `reivindicado_id` = ? WHERE `id` = ?");
                    $sql->execute(array(1,$userid,$ticketid));
                    $data['msg'] = 'Ticket reivindicado com sucesso, boa venda!';
                    $data['redirect'] = BASE.'dashboard/chat/'.$ticketid;
                }
            }else if($action == 'ticketsAbertos'){
                $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `closed` = 0"); $sql->execute();
                if($sql->rowCount() >= 1){
                    $tickets = $sql->fetchAll();
                    foreach($tickets as $key => $value){
                        $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `id` = ?"); $sql->execute(array($value['pedido.id']));
                        $pedidoInfo = $sql->fetch();
                        $cartsid = explode(',',$pedidoInfo['carts.id']);
                        $itens = '';
                        foreach($cartsid as $key => $val){
                            $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `id` = ?"); $sql->execute(array($val));
                            $cartInfo = $sql->fetch();
                            $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?"); $sql->execute(array($cartInfo['id.item']));
                            $produtoInfo = $sql->fetch();
                            $itens .= '<div class="sing flex-center" >
                                            <img style="width:25px; height:25px; margin: 0 3px;" src="'.BASE.'data/images/upload/'.Site::getImageAnuncio($produtoInfo['id']).'">
                                            <span>'.substr($produtoInfo['nome'],0,10).'</span>
                                            <span><b>R$'.$cartInfo['valor.un'].'</b></span><br>
                                            <span><b>'.intval($cartInfo['quantidade']).'x</b></span>
                                            </div>';
                            }
                            if($value['reivindicado'] == 0){
                                $data['msg'] =  '<div class="box-chatSingleWrapper" >
                                        <div class="box-chatSingle" id="'.$value['id'].'">
                                            <div class="head">
                                                <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                                <img src="'.BASE.'data/images/upload/'.Site::getImageUser($value['creator_id']).'">
                                            </div>
                                            <div class="body">
                                                <div class="info">
                                                    <h4>Informações</h4>
                                                    <hr style="margin:3px 0; width:100%;">
                                                    <div class="sing">
                                                        <span>Valor do pedido</span>
                                                        <span><b>R$'.($cartInfo['valor.un'] * $cartInfo['quantidade']).'</b></span>
                                                    </div>
                                                    <div class="sing">
                                                        <span>Status</span>
                                                        <span><b>'.ucfirst($value['status']).'</b></span>
                                                    </div>
                                                    <div class="sing">
                                                        <span>Itens do pedido</span>
                                                        <hr style="margin:3px 0;">
                                                        '.$itens.'
                                                    </div>
                                                </div>
                                                <button class="reivindicarTicket"> <i class="far fa-hand-paper"></i> Reivindicar</button>
                                            </div>
                                        </div>
                                    </div>';
                            }else{
                                $data['msg'] = '<div class="box-chatSingleWrapper" >
                                        <div class="box-chatSingle" id="'.$value['id'].'">
                                            <div class="head">
                                                <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                                <img src="'.BASE.'data/images/upload/'.Site::getImageUser(Site::getUserInfoByID($value['creator_id'])['id']).'">
                                            </div>
                                            <div class="body">
                                                <div class="info">
                                                    <h4>Informações</h4>
                                                    <hr style="margin:3px 0; width:100%;">
                                                    <div class="sing">
                                                        <span>Valor do pedido</span>
                                                        <span><b>R$233,99</b></span>
                                                    </div>
                                                    <div class="sing">
                                                        <span>Status</span>
                                                        <span><b>'.ucfirst($value['status']).'</b></span>
                                                    </div>
                                                    <div class="sing" style="border: 1px solid">
                                                        <span>Itens do pedido</span>
                                                        <hr style="margin:3px 0;">
                                                        '.$itens.'
                                                    </div>
                                                </div>
                                                <button class="chat-vis-btn"><i class="fas fa-eye"></i> Visualizar</button>
                                            </div>
                                        </div>
                                    </div>';
                            }
                            $_SESSION['lastIdTickets'] = $value['id'];  
                        }
                        
                    }else{
                        $data['sucesso'] = false;
                        $data['response'] = '<div class="w100 flex-center" style="background: #a58fdc; padding:10px; border-radius:3px; height:100%"><span>Não foi encontrado nenhum ticket de atendimento.</span></div>';
                    }
                }else if($action == 'ticketsFechados'){
                    $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `closed` = 1"); $sql->execute();
                    if($sql->rowCount() >= 1){
                        $tickets = $sql->fetchAll();
                        foreach($tickets as $key => $value){
                                $data['msg'] .= '<div class="box-chatSingleWrapper" >
                                                        <div class="box-chatSingle" id="'.$value['id'].'">
                                                            <div class="head">
                                                                <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                                                <img src="'.BASE.'data/images/upload/'.Site::getImageUser($value['creator_id']).'">
                                                            </div>
                                                            <div class="body">
                                                                <div class="info">
                                                                    <h4>Informações</h4>
                                                                    <hr style="margin:3px 0; width:100%;">
                                                                    <div class="sing">
                                                                        <span>Valor do pedido</span>
                                                                        <span><b>R$233,99</b></span>
                                                                    </div>
                                                                    <div class="sing">
                                                                        <span>Itens do pedido</span>
                                                                        <hr style="margin:3px 0;">
                                                                        '.$itens.'
                                                                    </div>
                                                                </div>
                                                                <button class="chat-vis-btn"><span><i class="fas fa-eye"></i> Visualizar</span></button>
                                                            </div>
                                                        </div>
                                                </div> ⠀ ';
                        } 
                    }else{
                        $data['sucesso'] = false;
                        $data['response'] = '<div class="w100 flex-center" style="background: #a58fdc; padding:10px; border-radius:3px; height:100%"><span>Não foi encontrado nenhum ticket de atendimento.</span></div>';
                    }
                }else if($action == 'ticketsCongelados'){
                    $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `status` = 'pausado'");
                    $sql->execute();
                    if($sql->rowCount() >= 1){
                        $tickets = $sql->fetchAll();
                        foreach($tickets as $key => $value){
                                $data['msg'] .= '<div class="box-chatSingleWrapper" >
                                                    <a href="'.BASE.'dashboard/chat/'.$value['id'].'">
                                                        <div class="box-chatSingle" id="'.$value['id'].'">
                                                            <div class="head">
                                                                <h2><i class="fas fa-comments"></i> Ticket #'.$value['id'].'</h2>
                                                                <img src="'.BASE.'data/images/5-10anos.webp">
                                                            </div>
                                                            <div class="body">
                                                                <button class="chat-vis-btn"><i class="fas fa-eye"></i> Visualizar</button>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div> ⠀ ';
                        } 
                    }else{
                        $data['sucesso'] = false;
                    }
                }else if($action == 'ticketStatus'){
                    $id = $_POST['id'];
                    $sql = MySql::conectar()->prepare("SELECT `status` FROM `tickets` WHERE `id` = ?");
                    $sql->execute(array($id));
                    $info = $sql->fetch()['status'];
                    $data['status'] = $info;
                }else if($action == 'getControls'){
                    $id = $_POST['id'];
                    $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?"); $sql->execute(array($id));
                    $infoTicket = $sql->fetch();
                    $data['response'] = '';
                    if($infoTicket['closed'] == 1){
                        $data['response'] .= '<button title="O usuário terá novamente acesso ao ticket e mensagens." class="abrir-pedido"><i class="fas fa-store-slash"></i> Abrir pedido</button>⠀';
                    }else{
                        $data['response'] .= '<button title="O usuário não terá acesso ao ticket e mensagens." class="fechar-pedido"><i class="fas fa-store-slash"></i> Fechar pedido</button>⠀';
                    }
                    if($infoTicket['status'] == 'pausado') { //esta congelado
                        $data['response'] .= '<button title="Descongela o ticket" class="descongelar-pedido"><i class="fas fa-wind"></i> Descongelar ticket</button>⠀';
                    }else{
                        $data['response'] .= '<button title="Impossibilita o usuário de enviar mensagens" class="congelar-pedido"><i class="fas fa-wind"></i> Congelar ticket</button>⠀';
                    }
                    $data['response'] .= '<button title="Envia um e-mail ao usuário para que entre no site por uma mensagem." class="enviar-email"><i class="far fa-envelope"></i> Enviar e-mail</button>⠀';
                    $data['response'] .= '<button title="Gerar pix com qualquer valor caso precisar"class="gerar-pix"><i class="fas fa-dollar-sign"></i> Gerar pix </button> ⠀';
                    if(Usuario::checkHasUserBannedByID($infoTicket['creator_id'])){
                        $data['response'] .= '<button title="Desbane o usuário permanentemente do site." class="banir-usuario"><i class="fas fa-ban"></i> Desbanir usuário</button>⠀';

                    }else{
                        $data['response'] .= '<button title="Bane o usuário permanentemente do site." class="banir-usuario"><i class="fas fa-ban"></i> Banir usuário</button>⠀';

                    }
                    $data['response'] .= '<button title="Ativar garantia do usuário" class="garantia-tab"><i class="fas fa-ban"></i> Garantia</button>⠀';
                    $data['response'] .= '<button title="Estornar dinheiro" class="estornar-dinheiro"><i class="fas fa-ban"></i> Estornar dinheiro</button>⠀';
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
                }
                else if($action == 'enviarEmail'){
                    $id = $_POST['id'];
                    $assunto = $_POST['assunto'];
                    $texto = $_POST['texto'];
                    foreach($_POST as $key => $value){
                        if($value == ''){
                            $data['msg'] = 'Preencha todos os dados do e-mail.';
                            $data['sucesso'] = false;
                        }
                    }
                    if($data['sucesso']){
                        $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?");
                        $sql->execute(array($id));
                        $creatorid = $sql->fetch()['creator_id'];
                        $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios` WHERE `id` = ?");
                        $sql->execute(array($creatorid));
                        $info = $sql->fetch();
                        include('../Classes/Email.php');
                        include('../dashboard/Models/ChatModels.php');
                        require('../vendor/autoload.php');
            
                        if(\Models\ChatModels::notificarEmail($info['email'],$info['username'],$assunto,$texto)){
                            $data['msg'] = 'O cliente foi notificado no e-mail com sucesso';
                        }else{
                            $data['msg'] = 'Aconteceu algum erro ao enviar o e-mail.';
                            $data['sucesso'] = false;
                        }
                    }
                    
                }else if($action == 'congelarPedido'){
                    $id = $_POST['id'];
                    $sql = MySql::conectar()->prepare("UPDATE `tickets` SET `status` = 'pausado' WHERE `id` = ?");
                    if($sql->execute(array($id))){
                        $data['msg'] = 'Ticket congelado com sucesso';
                    }else{
                        $data['msg'] = 'Aconteceu algum erro.';
                        $data['sucesso'] = false;
                    }
                }else if($action == 'descongelarPedido'){
                    $id = $_POST['id'];
                    $sql = MySql::conectar()->prepare("UPDATE `tickets` SET `status` = 'aberto' WHERE `id` = ?");
                    if($sql->execute(array($id))){
                        $data['msg'] = 'Ticket descongelado com sucesso';
                    }else{
                        $data['msg'] = 'Aconteceu algum erro.';
                        $data['sucesso'] = false;
                    }
                }else if($action == 'tabEnviarEmail'){
                    $data['response'] = '<div class="cabeca">
                                            <h3>Informe o que será dito no e-mail enviado.</h3>
                                        </div>
                                        <div class="corpo">
                                            <label for="assunto">
                                                <div style="min-width:100px"><span>Assunto</span></div>
                                                <input type="text" name="assunto" id="assunto">
                                            </label>
                                            <label for="texto">
                                                <div style="min-width:100px"><span>Texto</span></div>
                                                <textarea type="text" id="texto" name="texto" id="assunto"></textarea>
                                            </label>
                                            <button class="tab-enviar-email">Enviar</button>
                                        </div>';
                }else if($action == 'tabGerarPix'){
                    $data['response'] = '<div class="cabeca">
                                            <h3>Informe seguintes dados pra gerar o pix</h3>
                                        </div>
                                        <div class="corpo">
                                            <h3 style="margin-top:10px;">Método de pagamento</h3>
                                            <label for="picpay" style="flex-direction:row; margin: 5px 0;">
                                                <input type="radio" id="picpay" name="method" value="PICPAY">
                                                <div style="min-width:100px"><span>Picpay</span></div>
                                        </label>
                                        <label for="pix" style="flex-direction:row; margin: 5px 0;">
                                            <input type="radio" id="pix" name="method" value="PIX">
                                            <div style="min-width:100px"><span>Pix</span></div>
                                        </label>
                                        <label for="nometransacao">
                                            <div style="min-width:100px"><span>Nome da transação</span></div>
                                            <input type="text" name="nometransacao" id="nometransacao">
                                        </label>
                                        <label for="valor">
                                            <div style="min-width:100px"><span>Valor</span></div>
                                            <input type="text" name="valor" datamask="preco" id="valor">
                                        </label>
                                        <button class="tab-gerar-pix">Enviar</button>
                                    </div>';
            }else if($action == 'GerarPix'){
                $ticketid = $_POST['id'];
                $valor = $_POST['valor'];
                $nome = @$_POST['nome'];
                $method = $_POST['method'];
                //print_r($_POST);
                if($method == 'PIX'){
                    include('../Classes/Pix.php');
                    $pix = new Pix('apk_46355677-otSYjLIKjAydfCmsOOHpblHuBrrGQCnE','89XNRLXYZLBXI3WQFBRW09W16Q7USMI0WGOO1LM17XYQ');
                    if($response = $pix->criarCobSemPedido($ticketid,$nome,$valor)){
                        $data['msg'] = 'A requisição da transferência foi criada informe os seguintes dados para o cliente.';
                        $data['response'] = '<div class="corpo">
                                                <h3>Link pagamento direto</h3>
                                                <div class="code">
                                                    <div id="copyTarget">
                                                        <p>'.$response['pix_create_request']['pix_code']['qrcode_image_url'].'</p>
                                                    </div>
                                                    <button class="copyCode"><i class="fas fa-clone"></i></button>
                                                </div>
                                                <h3>Código copia e cola</h3>
                                                <div class="code">
                                                    <div id="copyTarget">
                                                        <p>'.$response['pix_create_request']['pix_code']['emv'].'</p>
                                                    </div>
                                                    <button class="copyCode"><i class="fas fa-clone"></i></button>
                                                </div>
                                            </div>';
                    }
                }else if($method == 'PICPAY'){
                    include('../Classes/PicPay.php');
                    $picpay = new PicPay('fa2a854c-7118-4c4b-87e3-78b1c6cba33c','a1edf350-3cc8-45e6-9ad2-8bae44d3d85a');
                    if($response = $picpay->criarCobPixSemPedido($ticketid,$valor)){
                        $data['msg'] = 'A requisição da transferência foi criada informe os seguintes dados para o cliente.';
                        $data['response'] = '<div class="corpo">
                                                <h3>Link pagamento direto</h3>
                                                <div class="code">
                                                    <div id="copyTarget">
                                                        <p>'.$response->paymentUrl.'</p>
                                                    </div>
                                                    <button class="copyCode"><i class="fas fa-clone"></i></button>
                                                </div>
                                                <h3>Código copia e cola (só funciona picpay)</h3>
                                                <div class="code">
                                                    <div id="copyTarget">
                                                        <p>'.$response->qrcode->content.'</p>
                                                    </div>
                                                    <button class="copyCode"><i class="fas fa-clone"></i></button>
                                                </div>
                                            </div>';
                    }else{
                        $data['sucesso'] = false;
                        $data['msg'] = 'Aconteceu algum erro.';
                    }
                }
            }else if($action == 'banirUsuario'){
                $idticket = $_POST['ticketid'];
                $sql = MySql::conectar()->prepare("SELECT * FROM `tickets` WHERE `id` = ?");
                $sql->execute(array($idticket));
                $ticketinfo = $sql->fetch();
                if(Usuario::checkHasUserBannedByID($ticketinfo['creator_id'])){
                    if(Usuario::UnbanFromId($ticketinfo['creator_id'])){
                        $data['msg'] = 'Usuário desbanido com sucesso.';
                    }else{
                        $data['msg'] = 'Aconteceu algum erro ao desbanir este usuário, talvez já esteja desbanido.';
                        $data['sucesso'] = false;
                    }
                }else{
                    $reason = !isset($_POST['motivo']) ? 'Não especificado' : $_POST['motivo'];
                    if(Usuario::banFromId($ticketinfo['creator_id'],$reason)){
                        $data['msg'] = 'Banimento aplicado com sucesso';
                    }else{
                        $data['msg'] = 'Aconteceu algum erro ao banir este usuário, talvez já esteja banido.';
                        $data['sucesso'] = false;
                    }
                } 
            }else if($action == 'garantia-tab'){
                $data['response'] = '<div class="cabeca">
                                        <h3>Garantia</h3>
                                    </div>
                                    <div class="corpo">
                                        <label for="assunto">
                                            <div style="min-width:100px"><span>Tempo de garantia (em horas)</span></div>
                                            <input style="margin:10px 0;" type="text" name="assunto" id="assunto">
                                        </label>
                                        <button class="confirm-garantia">Iniciar</button>
                                    </div>';
            }else if($action == 'userOnline'){
                $info = Site::getInfoDB('tickets','`closed` = 0');
                if(Admin::isOnline($info['reivindicado_id'])){
                    $data['online'] = true;
                }else{
                    $data['online'] = false;
                }
            }else if($action == 'tab-estornarPix'){
                $data['response'] = '<div class="cabeca">
                                        <h3>Para cancelar a transação digite a senha mestra e clique em confirmar</h3>
                                    </div>
                                    <div class="corpo">
                                        <input type="password" style="max-width: 200px;" name="code" placeholder="Código Mestre">
                                        <button class="estornarp$ix-confirm">Confirmar</button>
                                    </div>';

            }else if($action == 'estornarPix'){
                $ticketid = $_POST['ticketid'];
                if(Site::getInfoDB('tickets','`id`='.$ticketid) || Admin::checkCodeMaster($code)){
                    $ticketInfo = Site::getInfoDB('tickets','`id`='.$ticketid); 
                    $transationInfo = Site::getInfoDB('pedido.txid','`id.pedido`='.$ticketInfo['pedido.id']);
                    if($transationInfo['method'] == 'PICPAY'){
                        include('../Classes/PicPay.php');
                        $picpay = new PicPay('fa2a854c-7118-4c4b-87e3-78b1c6cba33c','a1edf350-3cc8-45e6-9ad2-8bae44d3d85a');
                        if($response = $picpay->estornarTransacao($transationInfo['id'])){
                            $data['msg'] = 'Transação estornada com sucesso!';
                        }else{
                            $data['msg'] = 'Aconteceu algum erro ao estornar a transação!';
                            $data['sucesso'] = false;
                        }
                        print_r($response);
                    }else if($transationInfo['method'] == 'PIX'){
                        include('../Classes/Pix.php');
                        $pix = new Pix('apk_46355677-otSYjLIKjAydfCmsOOHpblHuBrrGQCnE','89XNRLXYZLBXI3WQFBRW09W16Q7USMI0WGOO1LM17XYQ');
                        if($response = $pix->estornarTransacao($transationInfo['id'])){
                            $data['msg'] = 'Transação estornada com sucesso!';
                        }else{

                            $data['msg'] = 'Aconteceu algum erro ao estornar a transação!';
                            $data['sucesso'] = false;
                        }
                    }
                    
                }else{
                    $data['sucesso'] = false;
                    $data['msg'] = 'Código mestre inválido ou ticket não encontrado.';
                }
            }
        }
        die(json_encode($data));
    }else{
        die(Site::redirecionar(BASE));
    }

?>