<?php 
include('../config.php');

error_reporting(E_ALL ^ E_NOTICE); 

$data['sucesso'] = true;
$action = $_POST['action'];
$data['redirect'] = '';
include('../Classes/Site.php');
include('../Classes/MySql.php');
include('../Models/ProductsDefault.php');

$token = @$_POST['token'];
if(!\Site::validar_token($token)){
    $data['msg'] = 'Aconteceu algum erro, contate administração';
    $data['sucesso'] = false;
    die(json_encode($data));
}
if(Site::logado()){
    if(isset($action)){
        $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
        if($action == 'excluirItem'){
            $id = $_POST['id'];
            if(isset($id)){
                $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `cart.users`.`id` = ? AND `cart.users`.`user_id` = ?"); $sql->execute(array($id,$userid)); $cartInfo = $sql->fetch();
                if($cartInfo['cupom.id'] != 0 || $cartInfo['cupom.preco.desconto'] != null){$tinhacupom = true;}
                $sql = MySql::conectar()->prepare("DELETE FROM `cart.users` WHERE `cart.users`.`id` = ? AND `cart.users`.`user_id` = ?");
                if($sql->execute(array($id,$userid))){
                    $data['msg'] = 'O item foi excluído do seu carrinho com sucesso!';
                    $sql = MySql::conectar()->prepare("DELETE FROM `usuarios.cupom.usados` WHERE `cart.id` = ? AND `user.id` = ? AND `stats` = 'pendente'"); $sql->execute(array($id,$userid));
                    $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `user_id` = ? AND `ativo` = 1");
                    $sql->execute(array($userid));
                    if($sql->rowCount() >= 1){
                        $data['haveCart'] = true;
                        $carts = $sql->fetchAll();
                        $valortotal = 0;
                        $data['carts'] = '';
                        foreach($carts as $key => $value){
                            $info = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?");$info->execute(array($value['id.item']));
                            $info = $info->fetch();
                            $cupomInfo = MySql::conectar()->prepare("SELECT * FROM `produtos.default.cupons` WHERE `id` = ?");
                            $cupomInfo->execute(array($value['cupom.id']));
                            $cupomInfo = $cupomInfo->fetch();
                            if($value['cupom.id'] == 0){
                                $valor = number_format($value['valor.un'] * $value['quantidade'],2);
                                $data['carts'] .= '<div class="cartsingle" id="'.$value['id'].'">
                                                    <span class="material-icons" js="removeitemcart">close</span>
                                                    <div class="img" style="width:20%"><img src="'.BASE.'data/images/upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'].'"></div>
                                                    <div style="width:33.3%"><span>'.substr($info['nome'],0,15).'</span></div>
                                                    <div class="body" style="width:33.3%">
                                                        <span>'.$value['quantidade'].'x</span>
                                                        <span>R$'.$valor.'</span>
                                                    </div>
                                                </div>||';
                                $valortotal = $valortotal + $valor;
                            }else{
                                $valor = number_format($value['cupom.preco.desconto'] * $value['quantidade'],2);
                                $data['carts'] .= '<div class="cartsingle" id="'.$value['id'].'">
                                                    <span class="material-icons" js="removeitemcart">close</span>
                                                    <div class="img" style="width:20%"><img src="'.BASE.'data/images/upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'].'"></div>
                                                    <div style="width:33.3%"><span>'.$info['nome'].'</span></div>
                                                    <div class="body" style="width:33.3%">
                                                        <span>'.$value['quantidade'].'x</span>
                                                        <span>R$'.$valor.'</span>
                                                    </div>
                                                </div>||';
                                $valortotal = $valortotal + $value['cupom.preco.desconto'];
                            }
                            $valortotal = number_format(str_replace('.',',',$valortotal), 2);
                        }
                        $total = str_replace('.',',','<span class="total">R$ '.$valortotal.'</span>');
                        $data['carts'] .= '<div class="fot">
                                                <span class="totalCart"><span class="total">'.$total.'</span></span>
                                                <a href="'.BASE.'carrinho"><button>Finalizar compra</button></a>
                                            </div>';
                    }else{
                        $data['carts'] = '';
                        $data['haveCart'] = false;
                    }
                }else{
                    $data['sucesso'] = false;
                    $data['msg'] = 'Aconteceu algum erro ao excluir esse item do seu carrinho';
                }
            }else{
                $data['sucesso'] = false;
                $data['msg'] = 'Aconteceu algum erro ao excluir esse item do seu carrinho';
            }
        }else if($action == 'getCarrinhoTab'){
            if(Site::logado()){
                $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
                $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `user_id` = ? AND `ativo` = 1");
                $sql->execute(array($userid));
                if($sql->rowCount() >= 1){
                    $data['haveCart'] = true;
                    $carts = $sql->fetchAll();
                    $valortotal = 0;
                    $data['carts'] = '';
                    foreach($carts as $key => $value){
                        $info = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?");$info->execute(array($value['id.item']));
                        $info = $info->fetch();
                        $cupomInfo = MySql::conectar()->prepare("SELECT * FROM `produtos.default.cupons` WHERE `id` = ?");
                        $cupomInfo->execute(array($value['cupom.id']));
                        $cupomInfo = $cupomInfo->fetch();
                        if($value['cupom.id'] == 0){
                            $valor = number_format($value['valor.un'] * $value['quantidade'],2);
                            $data['carts'] .= '<div class="cartsingle" id="'.$value['id'].'">
                                                <span class="material-icons" js="removeitemcart">close</span>
                                                <div class="img" ><img src="'.BASE.'data/images/upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'].'"></div>
                                                <div ><span>'.substr($info['nome'],0,15).'</span></div>
                                                <div class="body" >
                                                    <span>'.$value['quantidade'].'x</span>
                                                    <span>R$'.$valor.'</span>
                                                </div>
                                            </div>||';
                            $valortotal = $valortotal + $valor;
                        }else{
                            $valor = number_format($value['cupom.preco.desconto'] * $value['quantidade'],2);
                            $data['carts'] .= '<div class="cartsingle" id="'.$value['id'].'">
                                                <span class="material-icons" js="removeitemcart">close</span>
                                                <div class="img" ><img src="'.BASE.'data/images/upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'].'"></div>
                                                <div ><span>'.substr($info['nome'],0,15).'</span></div>
                                                <div class="body" >
                                                    <span>'.$value['quantidade'].'x</span>
                                                    <span>R$'.$valor.'</span>
                                                </div>
                                            </div>||';
                            $valortotal = $valortotal + $value['cupom.preco.desconto'];
                        }
                        $valortotal = number_format(str_replace('.',',',$valortotal),2);
                    }
                    $total = str_replace('.',',','<span class="total">R$ '.$valortotal.'</span>');
                    $data['carts'] .= '<div class="fot">
                                            <span class="totalCart"><span class="total">'.$total.'</span></span>
                                            <a href="'.BASE.'carrinho"><button>Finalizar compra</button></a>
                                        </div>';
                }else{
                    $data['carts'] = '';
                    $data['haveCart'] = false;
                }
            }
        }else if($action == 'alterarCarrinho'){
            if(Site::logado()){
                $cartId = $_POST['id'];
                $quant = strip_tags($_POST['quant']);
                $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
                if($quant >= 1){
                    $sql = MySql::conectar()->prepare("UPDATE `cart.users` SET `quantidade` = ? WHERE `cart.users`.`id` = ? AND `cart.users`.`user_id` = ? ");
                    $sql->execute(array($quant,$cartId,$userid));
                    $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `id` = ?");
                    $sql->execute(array($cartId));
                    $cartInfo = $sql->fetch();
                    if($cartInfo['cupom.preco.desconto'] != null || $cartInfo['cupom.id'] != 0){
                        if(intval($cartInfo['quantidade']) > 1){
                            $sql = MySql::conectar()->prepare("UPDATE `cart.users` SET `cupom.id` = 0 AND `cupom.preco.desconto` = NULL WHERE `id` = ?"); $sql->execute(array($cartId));
                        }
                        $total = $quant * floatval($cartInfo['valor.un']);
                        $data['inf'] = '<span>R$'.$total.'<br><b>O preço de desconto está disponível apenas para a compra de uma unidade.</b></span>';
                    }else{
                        $data['total'] = 'R$'.$quant * floatval($cartInfo['valor.un']).'';
                    }
                    $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `user_id` = ? AND `ativo` = 1"); $sql->execute(array($userid));
                    $carts = $sql->fetchAll();
                    $valortotal = 0;
                    foreach($carts as $key => $value){
                        if($value['cupom.id'] == 0){
                            $valor = $value['valor.un'] * $value['quantidade'];
                            $valortotal = $valortotal + $valor;
                        }else{
                            $valor = $value['cupom.preco.desconto'] * $value['quantidade'];
                            $valortotal = $valortotal + $value['cupom.preco.desconto'];
                        }
                    }
                    $data['totalCarts'] = 'R$ '.$valortotal;
                }else{
                    $sql = MySql::conectar()->prepare("DELETE FROM `cart.users` WHERE `cart.users`.`id` = ? AND `cart.users`.`user_id` = ? ");
                    $sql->execute(array($cartId,$userid));
                }
            }
        }else if($action == 'fecharPedido'){
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $method = $_POST['method'];
            $numeroPedidos = Site::getRowCountDB('cart.users', '`user_id` = "'.$userid.'" AND `ativo` = 1');
            if(Site::getRowCountDB('cart.users', '`user_id` = "'.$userid.'" AND `ativo` = 1') >= 1){
                $order = Site::getInfoDBAll('cart.users', '`user_id` = "'.$userid.'" AND `ativo` = 1');
                $orders = "";
                $valortotal = 0.00;
                foreach($order as $key => $value){
                    $produtoInfo = Site::getInfoDB('produtos.default','`id`='.$value['id.item']);
                    if($produtoInfo['status'] == 0){
                        $data['msg'] = 'O item '.$produtoInfo['nome'].' está desativado, não é possível fazer a compra.';
                        $data['sucesso'] = false;
                        $sql = MySql::conectar()->prepare("DELETE FROM `cart.users` WHERE `id` = ?"); $sql->execute(array($value['id']));
                        die(json_encode($data));
                    }
                    if(($key + 1 ) == $numeroPedidos){$orders .= $value['id'];
                    }else{$orders .= $value['id'].',';}

                    if($value['cupom.preco.desconto'] == null){
                        $valortotal = $valortotal + ($value['valor.un'] * $value['quantidade']);
                    }else{
                        $valortotal = $valortotal + floatval($value['cupom.preco.desconto']);
                    }
                }
                try{
                    $valortotal = number_format($valortotal,2,'.','.');
                    $date = date('Y-m-d H:i:s');
                    $sql = MySql::conectar()->prepare("INSERT INTO `pedido.cart.users` VALUES (null,?,?,0,?,?,?,?)");
                    if($sql->execute(array($userid,$orders,true,$valortotal,$date,$method))){
                        $pedidoId = MySql::conectar()->lastInsertId();
                        foreach($order as $key => $value){
                            $sql = MySql::conectar()->prepare("UPDATE `cart.users` SET `ativo` = 0 WHERE `id` = ?");
                            $sql->execute(array($value['id']));
                        }
                        
                        $data['msg'] = 'Seu pedido foi criado, aguarde iremos te redirecionar para o pagamento.';
                        $data['redirect'] = BASE.'pagamento?id='.$pedidoId;
                    }else{
                        $data['msg'] = 'Aconteceu algum erro, contate administração no discord.';
                        $data['sucesso'] = false;
                    }
                }catch(Exception $e){
                    return;
                }
                
            }else{
                $data['msg'] = 'Não foi encontrado nenhum item em seu carrinho.';
                $data['sucesso'] = false;
            }
        }else if($action == 'adicionarCarrinho'){
            $userid = Site::getUserInfo($_COOKIE['loginToken'])['id'];
            $date = date('Y-m-d H:i:s');
            $slug = strip_tags($_POST['slug']);
            //get slug
            if(Site::getRowCountDB('produtos.default', '`slug`="'.$slug.'" AND `status` = 1') <= 0){
                $data['sucesso'] = false;
                $data['msg'] = 'Este produto não está mais disponível ou não existe.';
            }else{
                $produtoInfo = Site::getInfoDB('produtos.default', '`slug`="'.$slug.'" AND `status` = 1');
                if($produtoInfo['status'] == 0){
                    $data['msg'] = 'Este produto foi desativado, não é mais possível fazer a compra.';
                    $data['sucesso'] = false;
                    die(json_encode($data));
                }
                $cupom = $_POST['cupom'];
                $valor = $produtoInfo['preco'];
                if($cupom != '' || $cupom){
                    $sql = MySql::conectar()->prepare("SELECT * FROM `produtos.default.cupons` WHERE `code` = ?");
                    $sql->execute(array($cupom));
                    if($sql->rowCount() >= 1){
                        $cupomInfo = $sql->fetch(); 
                        if($cupomInfo['creator.id'] == Site::getUserInfo($_COOKIE['loginToken'])['id']){
                            $data['sucesso'] = false;
                            $data['msg'] = 'Você não pode usar seu próprio cupom.';
                            $data['redirect'] = '';
                        }else{
                            $sql = MySql::conectar()->prepare("SELECT * FROM `usuarios.cupom.usados` WHERE `cupom.id` = ? AND `user.id` = ?");
                            $sql->execute(array($cupomInfo['id'], $userid));
                            $usadoInfo = $sql->fetch();
                            if($usadoInfo['stats'] == 'concluido'){
                                $data['sucesso'] = false;
                                $data['msg'] = 'Esse cupom já foi usado.';
                                $data['redirect'] = '';
                            }else if($usadoInfo['stats'] == 'pendente'){
                                $data['sucesso'] = false;
                                $data['msg'] = 'Você já está usando este cupom em outro produto.';
                                $data['redirect'] = '';
                            }else{
                                $valordesconto = $produtoInfo['preco'] - ($produtoInfo['preco'] * $cupomInfo['porcentdesconto'] / 100);
                                $cupomid = $cupomInfo['id'];
                            }
                        }
                    }else{
                        $data['sucesso'] = false;
                        $data['msg'] = 'Cupom inexistente ou inválido.';
                        $data['redirect'] = '';
                    }
                }else{
                    $valordesconto = null;
                    $cupomid = 0;   
                }
                if($data['sucesso']){
                    $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `id.item` = ? AND `user_id` = ? AND `ativo` = 1"); $sql->execute(array($produtoInfo['id'], $userid));
                    if($sql->rowCount() >= 1){
                        //ja possui no carrinho
                        $data['redirect'] = BASE.'carrinho';
                        
                    }else{
                        $sql = MySql::conectar()->prepare("INSERT INTO `cart.users` VALUES(null,?,1,?,?,?,?,?,1)");
                        if($sql->execute(array($produtoInfo['id'],$cupomid,$valor,$valordesconto,$date,$userid))){
                            $sql = MySql::conectar()->prepare("INSERT INTO `usuarios.cupom.usados` VALUES(null,?,?,?,'pendente')");
                            $sql->execute(array($userid,MySql::conectar()->lastInsertId(),$cupomid));
                        }
                    }
                    $sql = MySql::conectar()->prepare("SELECT * FROM `cart.users` WHERE `user_id` = ? AND `ativo` = 1");
                    $sql->execute(array($userid));
                    if($sql->rowCount() >= 1){
                        $data['haveCart'] = true;
                        $carts = $sql->fetchAll();
                        $valortotal = 0;
                        $data['carts'] = '';
                        foreach($carts as $key => $value){
                            $info = MySql::conectar()->prepare("SELECT * FROM `produtos.default` WHERE `id` = ?");$info->execute(array($value['id.item']));
                            $info = $info->fetch();
                            $cupomInfo = MySql::conectar()->prepare("SELECT * FROM `produtos.default.cupons` WHERE `id` = ?");
                            $cupomInfo->execute(array($value['cupom.id']));
                            $cupomInfo = $cupomInfo->fetch();
                            $imagem = \Models\ProductsDefault::getImageProductFromID($info['id']) != '' ? 'upload/'.\Models\ProductsDefault::getImageProductFromID($info['id'])[0]['name'] : 'nada.webp';
                            if($value['cupom.id'] == 0){
                                $valor = number_format($value['valor.un'] * $value['quantidade'],2);
                                $data['carts'] .= '<div class="cartsingle" id="'.$value['id'].'">
                                                    <span class="material-icons" js="removeitemcart">close</span>
                                                    <div class="img" ><img src="'.BASE.'data/images/'.$imagem.'"></div>
                                                    <div><span>'.substr($info['nome'],0,15).'</span></div>
                                                    <div class="body">
                                                        <span>'.$value['quantidade'].'x</span>
                                                        <span>R$'.$valor.'</span>
                                                    </div>
                                                </div>||';
                                $valortotal = $valortotal + $valor;
                            }else{
                                $valor = number_format($value['cupom.preco.desconto'] * $value['quantidade'],2);
                                $data['carts'] .= '<div class="cartsingle" id="'.$value['id'].'">
                                                    <span class="material-icons" js="removeitemcart">close</span>
                                                    <div class="img" ><img src="'.BASE.'data/images/'.$imagem.'"></div>
                                                    <div ><span>'.substr($info['nome'],0,15).'</span></div>
                                                    <div class="body" >
                                                        <span>'.$value['quantidade'].'x</span>
                                                        <span>R$'.$valor.'</span>
                                                    </div>
                                                </div>||';
                                $valortotal = $valortotal + $value['cupom.preco.desconto'];
                            }
                        }
                        $total = str_replace('.',',','<span class="total">R$ '.$valortotal.'</span>');
                        $data['carts'] .= '<div class="fot">
                                                <span class="totalCart"><span class="total">'.$total.'</span></span>
                                                <a href="'.BASE.'carrinho"><button>Finalizar compra</button></a>
                                            </div>';
                    }
                }
            }
        }
        die(json_encode($data));
    }else{
        Site::redirecionar(BASE.'?error');
    }
}else{
    $data['sucesso'] = false;
    $data['msg'] = 'Percebemos que você ainda não fez login, redirecionando..';
    $data['redirect'] = BASE.'login?ref=item/'.$_POST['slug'];
    die(json_encode($data));
}

?>