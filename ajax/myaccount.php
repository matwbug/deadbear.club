<?php

use WideImage\WideImage;

include('../config.php');
$data['sucesso'] = true;
$type = $_POST['perm'];
$action = $_POST['acao'];
include('../Classes/Site.php');
include('../Classes/MySql.php');
include('../Classes/Admin.php');
include('../Classes/Usuario.php');
include('../Models/ContaModels.php');
$date = date('Y-m-d H:i:s');

$token = @$_POST['token'];
if(!\Site::validar_token($token)){
    $data['msg'] = 'Aconteceu algum erro, contate administração';
    $data['sucesso'] = false;
    die(json_encode($data));
}
if(isset($action)){
    //error_reporting(E_ALL ^ E_NOTICE); 
    $userid = Site::getUserInfo($_COOKIE['loginToken'])['id']; // variavel global
    if($type == 'user'){
        if($action == 'getOrders'){
            $itensporpagina = 8;
            $pagina = isset($_POST['page']) ? trim(strip_tags($_POST['page'])) : 1;
            $startsfrom = ($pagina - 1) * $itensporpagina;
            $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `user.id` = ? ORDER BY `id` DESC LIMIT $itensporpagina OFFSET $startsfrom");
            $sql->execute(array($userid));
            $data['orders'] = '';
            if($sql->rowCount() >= 1){
                $pedidos = $sql->fetchAll();
                $data['orders'] .= '<div class="head">
                        <div><span class="material-icons">store</span> <span>Pedido</span></div>
                        <div><span class="material-icons">help_outline</span> <span>Status</span></div>
                        <div></div>
                        <div></div>
                    </div>';
                foreach($pedidos as $key => $value){
                    if(Site::getRowCountDB('tickets', '`pedido.id`='.$value['id']) <= 0){
                        $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.txid` WHERE `id.pedido` = ?");
                        $sql->execute(array($value['id']));
                        if(Site::getRowCountDB('pedido.txid', '`id.pedido`='.$value['id']) >= 1){
                            $pagInfo = Site::getInfoDB('pedido.txid', '`id.pedido`='.$value['id']);
                            if($pagInfo['status'] == 'pendente'){
                                $status = '<i class="dot" style="background:yellow;"></i><span title="Aguardando pagamento" class="pending"> Aguardando pagamento</span>';
                            }else if($pagInfo['status'] == 'pago'){
                                $status = '<i class="dot" style="background:green;"></i><span title="Pedido pago" class="aberto"> Pago</span>';
                            }
                        }else{
                            $status = '<i class="dot" style="background:red0000 ;"></i><span onclick="redirect(BASE+`pagamento?id='.$value['id'].'`)" title="Aconteceu algum erro contate a administração." class="finalizado"> Pagamento ainda não criado</span>';
                        }
                        
                    }else{
                        $ticketInfo = Site::getInfoDB('tickets', '`pedido.id`='.$value['id']);
                        if($ticketInfo['closed'] == 1){
                            $status = '<i class="dot" style="background:red;"></i><span title="Seu pedido foi finalizado." class="finalizado"> Finalizado</span>';
                        }else if($ticketInfo['status'] == 'pausado'){
                            $status = '<i class="dot" style="background-color:#A5F2F3;"></i><span class="pausado" title="Seu pedido foi congelado, aguarde."> Congelado 🥶</span>';
                        }else if($ticketInfo['status'] == 'fechado'){
                            $status = '<i class="material-icons">block</i><span class="finalizado" title="Pedido finalizado"> Fechado</span>';
                        }
                        else if($ticketInfo['status'] == 'aberto'){
                            $status = '<i class="dot" style="background:green;"></i><span class="aberto" title="Aberto, clique para ser atendido."> Aberto</span>';
                        }
                    }
                    if(isset($ticketInfo['reivindicado']) && $ticketInfo['reivindicado'] == 1){
                        $reivindicado = '<img src="'.Admin::getProfilePhotoId($ticketInfo['reivindicado_id']).'"> <span>'.Admin::getUserInfoId($ticketInfo['reivindicado_id'])['username'].'</span>';
                    }else{
                        $reivindicado = '<span class="aguardando"><img src="'.BASE.'data/images/loading.gif"> Aguardando atendimento</span>';
                    }
                    $data['orders'] .= '<div class="body">
                                            <div class="single" dataid="'.$value['id'].'"> 
                                                <div class="id"><i class="material-icons">reorder</i><span>Pedido #'.$value['id'].'</span></div>
                                                <div class="status">'.$status.'</div>
                                                <div class="order"><button class="openOrder"> <span>Ver pedido <i class="material-icons">open_in_new</i></span></button></div>
                                                <div class="avatar">'.$reivindicado.'</div>
                                            </div>
                                        </div>';
                }
                $data['orders'] .= '<div class="paginator">';
                $sql = MySql::conectar()->prepare("SELECT * FROM `pedido.cart.users` WHERE `user.id` = ?"); $sql->execute(array($userid));
                $totalPages = ceil($sql->rowCount()/$itensporpagina);
                for($i=0; $i < $totalPages; $i++){
                    if($i == ($pagina - 1)){
                        $data['orders'] .= '<button class="paginator-orders active" page="'.($i+1).'">'.($i+1).'</button>';
                    }else{
                        $data['orders'] .= '<button class="paginator-orders" page="'.($i+1).'">'.($i+1).'</button>';

                    }
                }
                $data['orders'] .= '</div>';
            }else{
                $data['orders'] .= '<div class="w100 center" style="background:var(--border-color); margin: 20px 0; border-radius:3px; text-align:center;"><i class="material-icons">sentiment_dissatisfied</i> Você não fez nenhum pedido ainda.</div>';
            }
        }else if($action == 'getDados'){
            $userInfo = Site::getUserInfo($_COOKIE['loginToken']);
            if($userInfo['data.nascimento'] == null){$userInfo['data.nascimento'] = date('Y-m-d');}//fazer validador de data de nasc
            $data['response'] = '<div class="data-user">
                                    <h3>Informações públicas</h3>
                                    <div>
                                        <label>
                                            <div title="Não editável"><span>Usuário</span></div>
                                            <input  type="text" disabled name="usuario" value="'.$userInfo['username'].'">
                                        </label>
                                    </div>
                                    <h3>Informações pessoais <i class="material-icons">person</i></h3>
                                    <div>
                                        <label for="Nome">
                                            <div><span>Nome</span></div>
                                            <input class="edit" type="text" name="nome" value="'.$userInfo['nome'].'">
                                        </label>
                                    <button class="save">Salvar</button>
                                    </div>
                                    <div>
                                        <label for="Sobrenome">
                                            <div><span>Sobrenome</span></div>
                                            <input class="edit" type="text" name="sobrenome" value="'.$userInfo['sobrenome'].'">
                                        </label>
                                        <button class="save">Salvar</button>
                                    </div>
                                    <div>
                                        <label for="cpf">
                                            <div><span>CPF<br></span><b style="font-size:10px">(apenas números)</b></div>
                                            <input onkeypress="validateNumb()" max-length="11" class="edit" type="text" mask="cpf" name="cpf" value="'.$userInfo['cpf'].'">
                                        </label>
                                        <button class="save">Salvar</button>
                                    </div>
                                    <div>
                                        <label for="tel">
                                            <div><span>Telefone</span></div>
                                            <input class="edit" type="text" placeholder="Ex: +55 (31) 99999-9999" mask="telefone" name="tel" value="'.$userInfo['telefone'].'">
                                        </label>
                                        <button class="save">Salvar</button>
                                    </div>
                                    <div>
                                        <label for="nasc">
                                            <div><span>Data de nascimento</span></div>
                                            <input style="PADDING: 10px 15px;" class="edit" type="date" mask="date" name="nasc" value="'.$userInfo['data.nascimento'].'">
                                        </label>
                                        <button class="save">Salvar</button>
                                    </div>
                                </div>';

        }else if($action == 'alterarInfo'){
            $userInfo = Site::getUserInfo();

            if(@$_POST['name'] == 'nome'){$query = 'nome';$val = $_POST['nameVal'];
            }else if(@$_POST['name'] == 'sobrenome'){$query = 'sobrenome'; $val = $_POST['nameVal'];
            }else if(@$_POST['name'] == 'cpf'){$query = 'cpf';$val = $_POST['nameVal'];
            }else if(@$_POST['name'] == 'nasc'){$query = 'data.nascimento';$val = $_POST['nameVal'];
            }else if(@$_POST['name'] == 'tel'){$query = 'telefone';$val = $_POST['nameVal'];}
            strip_tags($val);
            
            //validar nascimento
            if($query == 'data.nascimento'){
                $strSystemMaxDate = (date('Y') - 18).'/'.date('m/d');
                $date = explode('-',$val); $dia = $date[2]; $mes = $date[1]; $ano = $date[0];
                if ( !checkdate( $mes , $dia , $ano ) || $ano < 1900 || mktime( 0, 0, 0, $mes, $dia, $ano ) > time() || strtotime($val) > strtotime($strSystemMaxDate)){$validado = false; $erro = 'Há algum erro na sua data de nascimento, tente novamente.';}
                else{$validado = true; $val = date('Y-m-d', strtotime($val));}
                
            }
            //validar numero
            else if($query == 'telefone'){
                if (preg_match("/^(?:(?:\+|00)?(55)\s?)?(?:\(?([1-9][0-9])\)?\s?)?(?:((?:9\d|[2-9])\d{3})\-?(\d{4}))$/", $val)) {
                    $val = preg_replace("/[^0-9]/", "",$val);
                    $validado = true;
                    
                }else{
                    $validado = false; $erro = 'Há algum erro no seu número de telefone '.$val.', tente utilizar outro.';
                }
            }
            //validar cpf
            else if($query == 'cpf'){
                 function validaCpf($cpf){
                    for ($t = 9; $t < 11; $t++) {
                        for ($d = 0, $c = 0; $c < $t; $c++) {
                            $d += $cpf[$c] * (($t + 1) - $c);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ($cpf[$c] != $d) {
                            return false;
                        }
                        return true;
                    }
                }
                $val = (preg_replace("/[^0-9]/", "",$val));   
                if(strlen($val) == 11 && validaCpf($val)){
                    $validado = true;
                }else{
                    $erro = 'Há algum erro no cpf informado, tente digita-lo novamente.';
                    $validado = false;
                }
            }
            else if($query == 'nome'){
                if(preg_match("/^[a-zA-Z]+$/", $val)){
                    $validado = true;
                    $val = ucfirst($val);
                }else{
                    $validado = false;
                    $erro = 'Digite seu primeiro nome corretamente sem espaços, Ex: Carlos ';
                }
            }
            else if($query == 'sobrenome'){
               if(preg_match("/\b([A-Z]{1}[a-z]{1,30}[- ]{0,1}|[A-Z]{1}[- \']{1}[A-Z]{0,1}  
               [a-z]{1,30}[- ]{0,1}|[a-z]{1,2}[ -\']{1}[A-Z]{1}[a-z]{1,30}){2,5}/",$val)){
                   $validado = true;
               }else{
                   $validado = false;
                   $erro = 'Digite seu sobrenome corretamente sem espaços, Ex: Lopes Alpino';
               }
            }
            //
            if($val == $userInfo[$query]){$erro = 'Você não alterou nada =('; $validado = false;}
            if($validado){
                if($query == 'cpf'){$val = preg_replace("/[^0-9]/", "",$val);}// caso seja cpf remover as pontuaçoes
                $sql = MySql::conectar()->prepare("UPDATE `usuarios` SET `$query` = '$val' WHERE `id` = ?");
                if($sql->execute(array($userid))){
                    $data['sucesso'] = true;
                    $data['msg'] = str_replace('.',' ',ucfirst($query)).' foi alterado com sucesso.';
                }else{
                    $data['msg'] = 'Não é mais possível alterar '.ucfirst($_POST['name']).', contate a administração';
                    $data['sucesso'] = false;
                }
            }else{
                $data['sucesso'] = false;
                $data['msg'] = $erro;
            }
            $data['redirect'] = '';
            
        }else if($action == 'getReivindicado'){
            if(Site::getRowCountDB('produtos.default.cupons','`creator.id`="'.$userid.'"') >= 1){
                $refs = Site::getInfoDBAll('reffers','`refferencer.id`="'.$userid.'"');
                $code = Site::getInfoDB('produtos.default.cupons','`creator.id`="'.$userid.'"')['code'];
                $data['orders'] = '';
                $numeroRef = Site::getRowCountDB('reffers','`refferencer.id`="'.$userid.'"');
                if($numeroRef >= 1){
                    $refInfo = Site::getInfoDB('reffers','`refferencer.id`="'.$userid.'"');
                    $data['orders'] .='
                            <div class="refferal-box flex-center direction-row">
                                <div class="inside">
                                    <div class="uses flex-center direction-column">
                                        <span class="flex-center"> <i class="material-icons">ios_share</i> <b style="margin:0 5px;padding:0 2px; border-bottom: 1px solid var(--a-white);">'.$numeroRef.'</b> </span>
                                        <span >Usos em seu código </span>
                                    </div>
                                </div>
                                <div class="areaCode flex-center-notresize">
                                    <div class="flex-center-notresize direction-row w100">
                                        <div class="direction-row" style="border-bottom: 1px solid var(--a-white); max-width:80px; text-align:left; overflow-x:auto;"><span style="overflow-x:auto;" id="code">'.$code.'</span></div>
                                        <div><button class="copy"><span class="material-icons" style="font-size:15px;">content_copy</span></button></div>
                                    </div>
                                    <span class="text">Seu código</span>
                                </div>
                            </div>
                            <div class="head">
                                <div style="width:33.3%">Código</div>
                                <div style="width:33.3%">Quem usou</div>
                                <div style="width:33.3%">Usou em</div>
                            </div>
                    ';
                    foreach($refs as $key => $value){
                        $infoCode = Site::getInfoDB('produtos.default.cupons','`id`='.$value['code.id']);
                        $data['orders'] .= '<div class="body">
                                                <div class="single">
                                                    <div style="width:33.3%" class="code"><span> <b>'.$infoCode['code'].'</b></span> <span style="margin:0 2px;"> ('.$infoCode['porcentdesconto'].'%)</span></div>
                                                    <div style="width:33.3%" class="user"><img src="'.BASE.'data/images/upload/'.Site::getImageUser($value['refferencer.id']).'"><span>'.Site::getUserInfoByID($value['refferencer.id'])['username'].'</span></div>
                                                    <div style="width:33.3%" class="date"><span>22/11/2021 05:33</span></div>
                                                </div>
                                            </div>';
                    }
                }else{
                    $data['orders'] .= '<div class="refferal-box" style="flex-direction:row; justify-content:center;">
                                <div class="inside">
                                    <div class="uses">
                                        <span>0</span><br>
                                        <span>Usos em seu código</span>
                                    </div>
                                </div>
                                <div class="areaCode">
                                    <div>
                                        <div style="width:80%"><span id="code">'.$code.'</span></div>
                                        <div style="width:20%"><button class="copy"><i class="material-icons" >content_copy</i></button></div>
                                    </div>
                                    <span class="text">Seu código</span>
                                </div>
                            </div>
                            <div class="head">
                                <div style="width:33.3%">Código</div>
                                <div style="width:33.3%">Quem usou</div>
                                <div style="width:33.3%">Usou em</div>
                            </div>
                            <div style="text-align:center; margin: 10px 0; padding: 5px; background:var(--border-color);"><p><i class="material-icons">sentiment_dissatisfied</i> Ninguém usou seus códigos de convite ainda.</p></div>
                            ';
                }
            }else{
                $data['orders'] = '<div class="refferal-box direction-column" style="max-height:unset;">
                                        <div class="code-gen">
                                            <span>Você ainda não possui um código de referência.</span>
                                            <button class="reivindicarRefCode">Reivindicar meu código</button>
                                        </div>
                                    </div>';
            }
        }else if($action == 'checkCode'){
            $code = strip_tags($_POST['code']);
            if(preg_match("/^[a-zA-Z0-9]+$/", $code)){
                if(strlen($code) > 6){
                    if(Site::getRowCountDB('produtos.default.cupons', '`code`="'.$code.'"') >= 1){
                        $data['msg'] = 'Código inválido, já está em uso!';
                        $data['sucesso'] = false;
                    }
                }else{
                    $data['msg'] = 'Código inválido, o código precisa ser maior!';
                    $data['sucesso'] = false;
                }
            }else{
                $data['msg'] = 'Código inválido, tente outro';
                $data['sucesso'] = false;
            }
        }else if($action == 'insertCode'){
            $code = strip_tags($_POST['code']);
            if(preg_match("/^[a-zA-Z0-9]+$/", $code)){
                $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.cupons` WHERE `code` = ?");
                $sql->execute(array($code));
                if($sql->rowCount() >= 1){
                    $data['msg'] = 'Código inválido, já está em uso!';
                    $data['sucesso'] = false;
                }else{
                    $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.cupons` WHERE `creator.id` = ?");
                    $sql->execute(array($userid));
                    if($sql->rowCount() >= 1){
                        $data['msg'] = 'Você já possui um código de referência.';
                        $data['sucesso'] = false;
                    }else{
                        $sql = MySql::conectar()->prepare("INSERT INTO `produtos.default.cupons` VALUES(null,?,?,?)");
                        $sql->execute(array(5,$code,$userid));
                        $data['msg'] = 'Seu código foi criado com sucesso.';
                        $data['redirect'] = BASE.'minhaconta';
                    }
                    
                }
            }else{
                $data['msg'] = 'Código inválido';
                $data['sucesso'] = false;
            }
        }else if($action == 'openOrder'){
            include('../Models/ProductsDefault.php');
            $id = $_POST['id'];
            $info = Site::getInfoDB('pedido.cart.users','`id`='.$id);
            if($info['user.id'] != $userid){
                $data['sucesso'] = false;
                $data['response'] = '<div class="open-tab">
                                        <div class="w100" style="background: var(--border-color)"><h4>Você não tem acesso a esse pedido</h4></div>
                                    </div>';
                die(json_encode($data));
            }
            $pedidoInfo = \Models\ProductsDefault::getCartsFromPedidoId($id);
            $pedidos = '<div class="orders-single w100 flex-center-notresize">';
            $total = 0.00;
            foreach($pedidoInfo as $key => $value){
                $produtoInfo = Site::getInfoDB('produtos.default','`id`='.$value['id.item']);
                $transacaoInfo = Site::getInfoDB('pedido.txid','`id`='.$value['id']);
                $valortotal = number_format(($value['quantidade'] * floatval($value['valor.un'])),2,',','.');
                $total = number_format(($value['quantidade'] * floatval($value['valor.un'])) + $total,2,'.','.');
                $pedidos .= '<div class="body w100">
                                <div class="single w100">
                                    <div class="openorder" style="width:8%; margin: 0;" ><img src="'.BASE.'data/images/upload/'.Site::getImageAnuncio($produtoInfo['id']).'"></div>
                                    <div class="openorder" style="border:none!important; margin: 0;"><a target="_blank" href="'.BASE.'item/'.$produtoInfo['slug'].'"><p>'.substr(ucfirst($produtoInfo['nome']),0,15).'</p></a></div>
                                    <div class="openorder" style="border:none!important; margin: 0;"><p>'.$value['quantidade'].'x</p></div>
                                    <div class="openorder" style="border:none!important; margin: 0;"><p>R$ '.$valortotal.'</p></div>
                                </div>
                            </div>';
            }
            $pedidos .= '</div>';
            $payInfo = Site::getInfoDB('pedido.txid','`id.pedido`='.$id);
            $paymentCreated = $payInfo ? 'check_box' : 'check_box_outline_blank';
            $paymentConfirmed = $payInfo['status'] == 'pago' ? 'check_box' : 'check_box_outline_blank';
            $pagamentosteps =  '<div class="single-step flex-center direction-column w100">
                                    <div class="w100 flex-center-notresize direction-row" style="justify-content: left;">
                                        <span class="material-icons">'.$paymentConfirmed.'</span>
                                        <span class="flex-center-notresize">Pagamento <i class="material-icons">expand_more</i></span>
                                    </div>
                                    <div class="escondido flex-center direction-column w100" style="justify-content:left;">
                                        <div class="flex-center-notresize w100" style="justify-content:left;">
                                            <span class="material-icons">'.$paymentCreated.'</span>
                                            <span>Pagamento criado </span>
                                        </div>
                                        <div class="flex-center-notresize w100" style="justify-content:left;">
                                            <span class="material-icons">'.$paymentConfirmed.'</span> 
                                            <span> Pagamento confirmado </span>
                                        </div>
                                    </div>
                                </div>';
            $produtoEntregue = $info['entregue'] ? 'check_box' : 'check_box_outline_blank'; 
            $enviosteps = '<div class="single-step flex-center direction-row w100">
                            <div class="w100 flex-center-notresize direction-row" style="justify-content: left;">
                                <span class="material-icons">'.$produtoEntregue.'</span>
                                <span class="flex-center-notresize">Envio <i class="material-icons">expand_more</i></span>
                            </div>
                            <div class="escondido flex-center direction-column w100" style="justify-content:left;">
                                <div class="flex-center-notresize w100" style="justify-content:left;">
                                    <span class="material-icons">'.$produtoEntregue.'</span> 
                                    <span>Produto(s) enviado(s)  </span>
                                </div>
                            </div>
                        </div>';
            $garantiaInfo = Site::getInfoDB('pedidos.garantia', '`pedido.id`='.$id);
            $garantiaIniciada = $garantiaInfo ? 'check_box' : 'check_box_outline_blank'; 
            $garantiaFinalizada = isset($garantiaInfo['status']) ? 'check_box' : 'check_box_outline_blank'; 
            $garantiasteps = '<div class="single-step flex-center direction-row w100">
                                <div class="w100 flex-center-notresize direction-row" style="justify-content: left;">
                                    <span class="material-icons">'.$garantiaFinalizada.'</span>
                                    <span class="flex-center-notresize">Garantia <i class="material-icons">expand_more</i></span>
                                </div>
                                <div class="escondido flex-center direction-column w100" style="justify-content:left;">
                                    <div class="flex-center-notresize w100" style="justify-content:left;">
                                        <span class="material-icons">'.$garantiaIniciada.'</span> <span>Garantia iniciada</span>
                                    </div>
                                    <div class="flex-center-notresize w100" style="justify-content:left;">
                                        <span class="material-icons">'.$garantiaFinalizada.'</span> <span>Garantia finalizada</span>
                                    </div>
                                </div>
                            </div>';
            $pedidocriado = $info ? 'active' : '';
            $pagamentocompletado = $payInfo['status'] == 'pago' ? 'active' : '';
            $enviado = $info['entregue'] ? 'active' : '';
            $finalizado = $info['status'] == false ? 'active' : '';
            $data['response'] = '<div idpedido="'.$id.'" class="open-tab flex-center" style=" text-align:center; margin:10px 0; justify-content:flex-start; flex-wrap: unset; flex-direction:column;">
                                    <div class="flex-center w100" style="height:100%; min-height:400px;">
                                        <h4 style="text-align:center; font-size:medium;">STATUS DO PEDIDO</h4>
                                        <div class="flex-center w100 steps direction-column" style="height:100%;">
                                            <div class="w100 flex-center-notresize direction-row areasteps">
                                                <div class="flex-center direction-column " alt="Pedido">
                                                    <span class="material-icons '.$pedidocriado.'">reorder</span>
                                                    <span class="flex-center '.$pedidocriado.'">Pedido criado </span>
                                                </div>
                                                <span class="material-icons '.$pedidocriado.'" style="margin:0 10px;">double_arrow</span>
                                                <div class="flex-center-notresize direction-column " alt="Pagamento">
                                                    <span class="material-icons '.$pagamentocompletado.'">paid</span>
                                                    <span class="flex-center-notresize '.$pagamentocompletado.'">Pagamento confirmado </span>
                                                </div>
                                                <span class="material-icons '.$pagamentocompletado.'" style="margin:0 10px;">double_arrow</span>
                                                <div class="flex-center direction-column  alt="Ticket">
                                                    <span class="material-icons '.$enviado.'"">local_shipping</span>
                                                    <span class="flex-center '.$enviado.'"">Produto entregue </span>
                                                </div>
                                                <span class="material-icons '.$enviado.'"" style="margin:0 10px;">double_arrow</span>
                                                <div class="flex-center direction-column" alt="Pedido">
                                                    <span class="material-icons '.$finalizado.'">check_circle_outline</span>
                                                    <span class="flex-center '.$finalizado.'">Finalizado </span>
                                                </div>
                                            </div>

                                            <div class="flex-center-notresize direction-row w100" style="height:100%; align-items: flex-start;">
                                                '.$pagamentosteps.'
                                                '.$enviosteps.'
                                                '.$garantiasteps.'
                                            </div>
                                            <button class="closetab"><i class="material-icons">close</i></button>
                                            <div style="margin:5px 0;" class="head w100">
                                                <h5 class="flex-center-notresize direction-row">PEDIDO <i class="material-icons">store</i></h5>
                                            </div>
                                                '.$pedidos.'
                                            <div style="margin:10px 0;" class="w100"><h5 style="font-size:medium;">Valor total: <b style="color: #CE0C45">R$ '.number_format($total,2,',','.').'</b></h5></div>
                                        </div>
                                    </div>
                                </div>';

            
        }
    }
    die(json_encode($data));
}else{
    //SAFADINHO TENTANDO ENRTAR PELO LINK DIRETO RS KKKKKK SE FYUDE MLK FDP DO CARALHO
    die(Site::redirecionar(BASE));
}

?>